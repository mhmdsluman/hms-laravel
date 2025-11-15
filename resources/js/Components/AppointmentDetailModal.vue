<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  show: { type: Boolean, default: false },
  appointment: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['close']);

// safeRoute helper (prevents Ziggy runtime throws if a route isn't registered)
const safeRoute = (name, ...params) => {
  try {
    return route(name, ...params);
  } catch (e) {
    return null;
  }
};

const formattedTime = computed(() => {
  const t = props.appointment?.appointment_time || props.appointment?.datetime || props.appointment?.time;
  if (!t) return '';
  const d = new Date(t);
  if (isNaN(d)) return '';
  return d.toLocaleString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit',
  });
});

const statusClass = (status) => {
  return {
    Completed: 'bg-green-100 text-green-800',
    Scheduled: 'bg-blue-100 text-blue-800',
    Cancelled: 'bg-red-100 text-red-800',
  }[status] || 'bg-gray-100 text-gray-800';
};

const updateStatus = (status) => {
  if (!props.appointment?.id) return;
  const url = safeRoute('appointments.updateStatus', props.appointment.id);
  if (!url) {
    console.warn("Route 'appointments.updateStatus' not found. Status update aborted.");
    return;
  }
  router.patch(url, { status }, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  });
};

const generateBill = () => {
  if (!props.appointment?.id) return;
  // backend route is named 'billing.store' (not 'bills.store')
  const url = safeRoute('billing.store', props.appointment.id);
  if (!url) {
    console.warn("Route 'billing.store' not found. Cannot generate bill.");
    return;
  }
  // optional: confirm before creating a bill
  if (!confirm('Generate bill for this appointment?')) return;
  router.post(url, {}, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  });
};

// Accessibility: close on ESC
const onKeyDown = (e) => {
  if (e.key === 'Escape') emit('close');
};

onMounted(() => document.addEventListener('keydown', onKeyDown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeyDown));
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black opacity-50" @click="emit('close')" aria-hidden="true"></div>

    <div
      role="dialog"
      aria-modal="true"
      class="relative z-10 bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto overflow-hidden"
    >
      <header class="flex items-start justify-between p-5 border-b">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Appointment Details</h3>
          <p class="text-sm text-gray-500 mt-1">Quick actions and appointment info</p>
        </div>

        <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">&times;</button>
      </header>

      <div class="p-6">
        <div v-if="appointment && appointment.id" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Patient</dt>
              <dd class="mt-1 text-lg text-gray-900 font-semibold">{{ appointment.patient?.first_name }} {{ appointment.patient?.last_name }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Clinician</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ appointment.clinician?.name || appointment.clinician_name || '—' }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Date & Time</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formattedTime }}</dd>
            </div>
          </div>

          <div class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(appointment.status)}`">{{ appointment.status }}</span>
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Reason</dt>
              <dd class="mt-1 text-sm text-gray-700">{{ appointment.reason || appointment.notes || '—' }}</dd>
            </div>

            <div v-if="appointment.room || appointment.location" class="text-sm text-gray-600">
              <div v-if="appointment.room">Room: {{ appointment.room }}</div>
              <div v-if="appointment.location">Location: {{ appointment.location }}</div>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-gray-500 py-6">No appointment selected.</div>
      </div>

      <footer class="flex items-center justify-between p-4 border-t bg-gray-50">
        <div class="flex items-center gap-2">
          <button @click="emit('close')" class="px-4 py-2 bg-white border rounded text-sm">Close</button>
          <button v-if="appointment.status === 'Scheduled'" @click="updateStatus('Completed')" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Mark Completed</button>
          <button v-if="appointment.status === 'Scheduled'" @click="updateStatus('Cancelled')" class="px-4 py-2 bg-red-600 text-white rounded text-sm">Cancel</button>
        </div>

        <div class="flex items-center gap-2">
          <Link v-if="appointment.status === 'Scheduled' && safeRoute('consultation.create', appointment.id)" :href="safeRoute('consultation.create', appointment.id)" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Start Consultation</Link>
          <Link v-if="appointment.status === 'Scheduled' && safeRoute('appointments.vitals.create', appointment.id)" :href="safeRoute('appointments.vitals.create', appointment.id)" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Record Vitals</Link>
          <button v-if="appointment.status === 'Completed' && appointment.id" @click="generateBill" class="px-4 py-2 bg-purple-600 text-white rounded text-sm">Generate Bill</button>
        </div>
      </footer>
    </div>
  </div>
</template>

<style scoped>
/* subtle improvements */
</style>
