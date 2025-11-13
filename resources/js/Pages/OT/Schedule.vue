<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  orderItem: { type: Object, required: true },
  surgeons: { type: Array, default: () => [] },
  nurses: { type: Array, default: () => [] },
});

const page = usePage();

const form = useForm({
  scheduled_start_time: '',
  scheduled_end_time: '',
  theater_room: props.orderItem?.ot_schedule?.room || 'OT-1',
  surgeon_id: props.orderItem?.ot_schedule?.surgeon_id || null,
  anesthetist_id: props.orderItem?.ot_schedule?.anesthetist_id || null,
  scrub_nurse_id: props.orderItem?.ot_schedule?.scrub_nurse_id || null,
  notes: props.orderItem?.ot_schedule?.notes || '',
});

const submitting = ref(false);

const patientName = computed(() => {
  const p = props.orderItem?.order?.patient || {};
  return `${p.first_name || ''} ${p.last_name || ''}`.trim() || 'Unknown Patient';
});

const serviceName = computed(() => props.orderItem?.service?.name || 'Procedure');

const submit = () => {
  submitting.value = true;
  form.post(route('ot.store', { orderItem: props.orderItem.id }), {
    onFinish: () => (submitting.value = false),
  });
};
</script>

<template>
  <Head title="Schedule Procedure" />

  <AuthenticatedLayout>
    <template #header>
      <!-- Keep header compact to avoid overflow in fixed header -->
      <h2 class="font-semibold text-lg text-gray-800 truncate">
        Schedule — {{ serviceName }}
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <!-- Flash success -->
        <div v-if="page.props.flash?.success" class="mb-4">
          <div class="rounded-lg bg-green-50 border border-green-100 p-4 text-green-800">
            {{ page.props.flash.success }}
          </div>
        </div>

        <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
          <div class="p-6 border-b">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm text-gray-500">Patient</div>
                <div class="text-lg font-semibold text-gray-800">{{ patientName }}</div>
                <div class="text-sm text-gray-500 mt-1">Order #: <span class="font-medium text-gray-700">{{ props.orderItem?.order?.id || '—' }}</span></div>
                <div class="mt-2 text-sm text-gray-500">Procedure: <span class="font-medium text-gray-700">{{ serviceName }}</span></div>
              </div>

              <div class="flex items-center gap-4">
                <div class="text-right">
                  <div class="text-xs text-gray-400">Estimated duration</div>
                  <div class="text-sm font-semibold text-gray-700">{{ props.orderItem?.service?.estimated_duration || '—' }}</div>
                </div>
                <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-700 font-semibold text-lg">
                  {{ (props.orderItem?.order?.patient?.first_name || '?')[0] || '?' }}
                </div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Start -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Start</label>
                <input
                  v-model="form.scheduled_start_time"
                  type="datetime-local"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  aria-label="Scheduled start time"
                  required
                />
                <p v-if="form.errors.scheduled_start_time" class="mt-1 text-xs text-red-600">{{ form.errors.scheduled_start_time }}</p>
                <p class="mt-1 text-xs text-gray-400">Choose the date and time the patient should be taken to theatre.</p>
              </div>

              <!-- End -->
              <div>
                <label class="block text-sm font-medium text-gray-700">End</label>
                <input
                  v-model="form.scheduled_end_time"
                  type="datetime-local"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  aria-label="Scheduled end time"
                  required
                />
                <p v-if="form.errors.scheduled_end_time" class="mt-1 text-xs text-red-600">{{ form.errors.scheduled_end_time }}</p>
                <p class="mt-1 text-xs text-gray-400">Estimated end time — used for room planning.</p>
              </div>

              <!-- Theater / Room -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Theater Room</label>
                <input
                  v-model="form.theater_room"
                  type="text"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  placeholder="OT-1"
                  required
                />
                <p v-if="form.errors.theater_room" class="mt-1 text-xs text-red-600">{{ form.errors.theater_room }}</p>
              </div>

              <!-- Surgeon -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Surgeon</label>
                <select
                  v-model="form.surgeon_id"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                  required
                >
                  <option :value="null" disabled>Select surgeon</option>
                  <option v-for="s in surgeons" :key="s.id" :value="s.id">
                    {{ s.name }} <span v-if="s.title">— {{ s.title }}</span>
                  </option>
                </select>
                <p v-if="form.errors.surgeon_id" class="mt-1 text-xs text-red-600">{{ form.errors.surgeon_id }}</p>
              </div>

              <!-- Anesthetist -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Anesthetist</label>
                <select
                  v-model="form.anesthetist_id"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                >
                  <option :value="null">Select anesthetist (optional)</option>
                  <option v-for="a in surgeons" :key="`a-${a.id}`" :value="a.id">{{ a.name }}</option>
                </select>
                <p v-if="form.errors.anesthetist_id" class="mt-1 text-xs text-red-600">{{ form.errors.anesthetist_id }}</p>
              </div>

              <!-- Scrub Nurse -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Scrub Nurse</label>
                <select
                  v-model="form.scrub_nurse_id"
                  class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                >
                  <option :value="null">Select scrub nurse (optional)</option>
                  <option v-for="n in nurses" :key="n.id" :value="n.id">{{ n.name }}</option>
                </select>
                <p v-if="form.errors.scrub_nurse_id" class="mt-1 text-xs text-red-600">{{ form.errors.scrub_nurse_id }}</p>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Notes & special instructions</label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="mt-2 block w-full rounded-lg border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-200"
                placeholder="e.g. fasting status, implants, equipment requests..."
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3">
              <Link :href="route('ot.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>

              <button
                type="submit"
                :disabled="form.processing || submitting"
                class="inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-white text-sm shadow hover:brightness-105 disabled:opacity-60"
              >
                <svg v-if="!form.processing && !submitting" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" /></svg>

                <svg v-else class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0" />
                </svg>

                <span>{{ form.processing || submitting ? 'Saving...' : 'Save Schedule' }}</span>
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
</style>
