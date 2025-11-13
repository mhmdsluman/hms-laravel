<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
  service: { type: Object, default: () => ({}) },
});

// Inertia form initialized from props (defensive)
const form = useForm({
  name: props.service?.name || '',
  department: props.service?.department || '',
  price: props.service?.price ?? '',
  description: props.service?.description || '',
  code: props.service?.code || '',
});

const submitting = ref(false);
const clientErrors = ref({});

// department suggestions & solid color mapping
const deptOptions = [
  'Surgery',
  'Laboratory',
  'Radiology',
  'Pharmacy',
  'Outpatient',
  'Emergency',
  'Anaesthesia',
];

const deptColors = {
  Surgery: 'bg-red-600 text-white',
  Laboratory: 'bg-indigo-600 text-white',
  Radiology: 'bg-green-600 text-white',
  Pharmacy: 'bg-yellow-600 text-white',
  Outpatient: 'bg-blue-600 text-white',
  Emergency: 'bg-pink-600 text-white',
  Anaesthesia: 'bg-teal-600 text-white',
  Default: 'bg-gray-700 text-white',
};

const badgeClassFor = (dept) => deptColors[dept] || deptColors.Default;

// derived preview values
const pricePreview = computed(() => {
  const v = form.price;
  if (v === '' || v === null || v === undefined) return '—';
  try {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(v));
  } catch {
    return v;
  }
});

const validateClient = () => {
  clientErrors.value = {};
  if (!form.name || !form.name.trim()) clientErrors.value.name = 'Please enter a service name.';
  if (!form.department || !form.department.trim()) clientErrors.value.department = 'Please specify a department.';
  const p = Number(form.price);
  if (isNaN(p) || p <= 0) clientErrors.value.price = 'Please enter a valid price greater than 0.';
  return Object.keys(clientErrors.value).length === 0;
};

const chooseDept = (d) => {
  form.department = d;
};

const submit = () => {
  form.errors = {};
  if (!validateClient()) return;

  submitting.value = true;
  form.put(route('services.update', props.service?.id), {
    onFinish: () => (submitting.value = false),
  });
};
</script>

<template>
  <Head title="Edit Service" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-lg text-gray-900 truncate">Edit Service</h2>
    </template>

    <div class="py-8">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- flash -->
        <div v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-green-600 text-white px-4 py-3">
          {{ page.props.flash.success }}
        </div>

        <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
          <div class="p-6 border-b flex items-start justify-between gap-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Edit Service</h3>
              <p class="text-sm text-gray-500 mt-1">Update name, department, price and metadata for this catalogue item.</p>
            </div>

            <!-- Live preview -->
            <div class="flex-shrink-0">
              <div class="w-44 rounded-lg p-3 border flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <div :class="['inline-flex items-center px-2 py-1 rounded text-sm font-semibold', badgeClassFor(form.department || 'Default')]">
                    {{ (form.department && form.department.length) ? form.department : 'Unassigned' }}
                  </div>
                  <div class="text-xs text-gray-500">Preview</div>
                </div>

                <div class="mt-2">
                  <div class="text-sm text-gray-600">Name</div>
                  <div class="font-medium text-gray-900 truncate">{{ form.name || 'Service name' }}</div>
                </div>

                <div class="mt-1">
                  <div class="text-sm text-gray-600">Price</div>
                  <div class="font-medium text-gray-900">{{ pricePreview }}</div>
                </div>

                <div class="mt-1 text-xs text-gray-400">Code: <span class="font-medium text-gray-700">{{ form.code || '—' }}</span></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Service Name</label>
              <input
                id="name"
                type="text"
                v-model="form.name"
                class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                placeholder="e.g. Appendectomy (Open)"
                aria-describedby="name-error"
              />
              <p v-if="clientErrors.name" id="name-error" class="mt-1 text-xs text-red-600">{{ clientErrors.name }}</p>
              <p v-else-if="form.errors.name" id="name-error" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>

            <!-- Department -->
            <div>
              <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
              <input
                id="department"
                list="dept-list"
                v-model="form.department"
                class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                placeholder="e.g. Surgery"
                aria-describedby="department-error"
              />
              <datalist id="dept-list">
                <option v-for="d in deptOptions" :key="d" :value="d" />
              </datalist>

              <div class="mt-3 flex flex-wrap gap-2">
                <div v-for="d in deptOptions" :key="d" @click="chooseDept(d)"
                     class="cursor-pointer inline-flex items-center gap-2 px-3 py-1 rounded text-sm font-semibold"
                     :class="[deptColors[d] || deptColors.Default]">
                  {{ d }}
                </div>
              </div>

              <p v-if="clientErrors.department" id="department-error" class="mt-1 text-xs text-red-600">{{ clientErrors.department }}</p>
              <p v-else-if="form.errors.department" id="department-error" class="mt-1 text-xs text-red-600">{{ form.errors.department }}</p>
            </div>

            <!-- Price & Code -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price (USD)</label>
                <input
                  id="price"
                  type="number"
                  step="0.01"
                  v-model="form.price"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  aria-describedby="price-error"
                />
                <p v-if="clientErrors.price" id="price-error" class="mt-1 text-xs text-red-600">{{ clientErrors.price }}</p>
                <p v-else-if="form.errors.price" id="price-error" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
              </div>

              <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Code (optional)</label>
                <input
                  id="code"
                  type="text"
                  v-model="form.code"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  placeholder="e.g. SRG-001"
                />
                <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
              <textarea
                id="description"
                rows="3"
                v-model="form.description"
                class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                placeholder="Short description or clinician-facing notes..."
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
              <Link :href="route('services.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>

              <button
                type="submit"
                :disabled="form.processing || submitting"
                class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm text-white"
                :class="form.processing || submitting ? 'bg-indigo-400' : 'bg-indigo-700 hover:bg-indigo-800'"
              >
                <svg v-if="!(form.processing || submitting)" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                <svg v-else class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0" />
                </svg>

                <span>{{ form.processing || submitting ? 'Updating...' : 'Update Service' }}</span>
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.rounded-2xl { border-radius: 1rem; }

/* clickable pills */
.cursor-pointer { cursor: pointer; }

/* hide datalist default rendering (minor cosmetics) */
datalist { display: none; }
</style>
