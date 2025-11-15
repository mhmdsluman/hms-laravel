<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
  CubeIcon,
  ArrowLeftOnRectangleIcon,
  PlusIcon,
  CheckCircleIcon,
  ArrowPathIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/solid';
import { ref, computed } from 'vue';

/* Form with sensible defaults and extra fields */
const form = useForm({
  name: '',
  sku: '',
  item_type: 'Reagent',
  unit: '',
  unit_cost: '',
  quantity_in_stock: 0,
  reorder_level: 10,
  supplier: '',
  description: ''
});

const submitting = computed(() => form.processing);

/* Helpers */
const isLowStock = computed(() => {
  const q = Number(form.quantity_in_stock ?? 0);
  const r = Number(form.reorder_level ?? 0);
  return q <= r;
});

const submit = async () => {
  if (typeof route !== 'function') {
    // fallback: log and don't crash
    console.warn('route() helper not available; cannot submit form via Inertia.');
    return;
  }

  form.post(route('lab-inventory.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // you may want to redirect or reset form; leaving server flash to handle success
    },
    onError: () => {
      // errors will be available in form.errors
    }
  });
};
</script>

<template>
  <Head title="Add Lab Stock" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <CubeIcon class="h-6 w-6 text-indigo-500" />
          <div>
            <h2 class="font-semibold text-lg text-gray-900">Add New Lab Inventory Item</h2>
            <p class="text-sm text-gray-500">Create a new inventory item and track stock levels easily.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route('lab-inventory.index')" class="inline-flex items-center gap-2 px-3 py-2 bg-white border rounded-md text-sm hover:shadow transition">
            <ArrowLeftOnRectangleIcon class="h-4 w-4" /> Back to Inventory
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-3 bg-green-50 border border-green-100 rounded-md text-green-700 flex items-center gap-2">
          <CheckCircleIcon class="h-5 w-5" /> {{ $page.props.flash.success }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Form -->
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Item Name</label>
                <input type="text" v-model="form.name" class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-200" required />
                <div v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">SKU</label>
                  <input type="text" v-model="form.sku" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                  <div v-if="form.errors.sku" class="text-xs text-red-600 mt-1">{{ form.errors.sku }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Item Type</label>
                  <select v-model="form.item_type" class="mt-1 block w-full px-3 py-2 border rounded-md">
                    <option>Reagent</option>
                    <option>Consumable</option>
                    <option>Equipment</option>
                    <option>Other</option>
                  </select>
                  <div v-if="form.errors.item_type" class="text-xs text-red-600 mt-1">{{ form.errors.item_type }}</div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Quantity in Stock</label>
                  <input type="number" v-model.number="form.quantity_in_stock" min="0" class="mt-1 block w-full px-3 py-2 border rounded-md" required />
                  <div v-if="form.errors.quantity_in_stock" class="text-xs text-red-600 mt-1">{{ form.errors.quantity_in_stock }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Re-order Level</label>
                  <input type="number" v-model.number="form.reorder_level" min="0" class="mt-1 block w-full px-3 py-2 border rounded-md" required />
                  <div v-if="form.errors.reorder_level" class="text-xs text-red-600 mt-1">{{ form.errors.reorder_level }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit</label>
                  <input type="text" v-model="form.unit" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                  <div v-if="form.errors.unit" class="text-xs text-red-600 mt-1">{{ form.errors.unit }}</div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit Cost</label>
                  <input type="text" v-model="form.unit_cost" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Supplier</label>
                  <input type="text" v-model="form.supplier" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="4" class="mt-1 block w-full px-3 py-2 border rounded-md"></textarea>
              </div>

              <div class="flex items-center justify-end gap-3">
                <Link :href="route('lab-inventory.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>

                <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-md">
                  <ArrowPathIcon class="h-4 w-4" />
                  <span v-if="!submitting">Save Item</span>
                  <span v-else>Saving...</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Preview Card -->
          <aside class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div class="flex items-start gap-3">
              <div class="rounded-md bg-indigo-50 p-3">
                <PlusIcon class="h-6 w-6 text-indigo-600" />
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-gray-400">Item</div>
                    <div class="font-semibold text-gray-900">{{ form.name || 'New item' }}</div>
                  </div>

                  <div class="text-right">
                    <div class="text-xs text-gray-400">SKU</div>
                    <div class="text-sm text-gray-800">{{ form.sku || '—' }}</div>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <div class="text-xs text-gray-400">In Stock</div>
                    <div class="font-medium" :class="isLowStock ? 'text-red-600' : 'text-gray-800'">{{ form.quantity_in_stock }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400">Re-order Level</div>
                    <div class="font-medium text-gray-800">{{ form.reorder_level }}</div>
                  </div>

                  <div>
                    <div class="text-xs text-gray-400">Unit</div>
                    <div class="font-medium text-gray-800">{{ form.unit || '—' }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400">Unit Cost</div>
                    <div class="font-medium text-gray-800">{{ form.unit_cost || '—' }}</div>
                  </div>
                </div>

                <div class="mt-4">
                  <div v-if="isLowStock" class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                    <ExclamationCircleIcon class="h-4 w-4" /> Low Stock
                  </div>

                  <div v-else class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                    <CheckCircleIcon class="h-4 w-4" /> Ready
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="text-xs text-gray-400">Supplier</div>
              <div class="text-sm text-gray-800">{{ form.supplier || '—' }}</div>
            </div>

            <div v-if="form.description" class="text-sm text-gray-600">
              {{ form.description }}
            </div>
          </aside>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* small niceties */
input::placeholder { color: rgba(107,114,128,0.9); }
</style>
