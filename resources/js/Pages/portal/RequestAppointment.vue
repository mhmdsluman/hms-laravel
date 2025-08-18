<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// Props: clinicians list + optional existing appointments
const props = defineProps({
  clinicians: { type: Array, default: () => [] },
  // appointments: optional array of existing appointments with shape { id, datetime, patient_name, clinician_id }
  appointments: { type: Array, default: () => [] },
});

// form for requesting an appointment
const form = useForm({
  clinician_id: null,
  appointment_time: '',
  reason_for_visit: '',
});

const submit = () => {
  form.post(route('portal.appointments.request.store'));
};

// --- Date chooser & modal for viewing names on a selected date ---
const today = new Date();
const selectedDate = ref(formatDateInput(today)); // YYYY-MM-DD

const showDateModal = ref(false);
const searchQuery = ref('');

// group appointments by date (YYYY-MM-DD)
const appointmentsByDate = computed(() => {
  const map = {};
  (props.appointments || []).forEach(a => {
    // support either ISO string or raw timestamp
    const dt = new Date(a.datetime || a.appointment_time || a.time);
    if (isNaN(dt)) return;
    const ymd = formatDateInput(dt);
    if (!map[ymd]) map[ymd] = [];
    map[ymd].push({ ...a, datetime: dt.toISOString() });
  });
  // sort each day's appointments by time ascending
  Object.keys(map).forEach(k => {
    map[k].sort((x, y) => new Date(x.datetime) - new Date(y.datetime));
  });
  return map;
});

const appointmentsForSelectedDate = computed(() => {
  return (appointmentsByDate.value[selectedDate.value] || []).filter(a => {
    if (!searchQuery.value) return true;
    return (a.patient_name || '').toLowerCase().includes(searchQuery.value.toLowerCase())
      || (a.clinician_name || '').toLowerCase().includes(searchQuery.value.toLowerCase());
  });
});

// helper to format date for <input type="date"> value
function formatDateInput(d) {
  const date = (d instanceof Date) ? d : new Date(d);
  if (isNaN(date)) return '';
  const yyyy = date.getFullYear();
  const mm = String(date.getMonth() + 1).padStart(2, '0');
  const dd = String(date.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

function formatDateDisplay(iso) {
  const d = new Date(iso);
  return isNaN(d) ? 'Invalid date' : d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTimeOnly(iso) {
  const d = new Date(iso);
  return isNaN(d) ? '' : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// when user selects an existing appointment from modal, pre-fill the form
function chooseExistingAppointment(a) {
  form.clinician_id = a.clinician_id || null;
  // set appointment_time to a datetime-local compatible value
  const dt = new Date(a.datetime);
  form.appointment_time = isNaN(dt) ? '' : dt.toISOString().slice(0,16);
  showDateModal.value = false;
}

// helpers to navigate dates
function prevDay() {
  selectedDate.value = formatDateInput(new Date(new Date(selectedDate.value).getTime() - 24 * 3600 * 1000));
}
function nextDay() {
  selectedDate.value = formatDateInput(new Date(new Date(selectedDate.value).getTime() + 24 * 3600 * 1000));
}

// reactive effect: if clinician list is non-empty and none selected, auto-select first
watch(() => props.clinicians, (val) => {
  if (props.clinicians.length && !form.clinician_id) {
    form.clinician_id = props.clinicians[0].id;
  }
}, { immediate: true });
</script>

<template>
  <Head title="Request an Appointment" />

  <PatientPortalLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request an Appointment</h2>
      </div>
    </template>

    <div class="max-w-5xl mx-auto py-8 px-4">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Calendar / Date chooser -->
        <div class="bg-white shadow-sm rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-md font-semibold">Choose Date</h3>
            <div class="text-sm text-gray-500">{‌{ formatDateDisplay(selectedDate) }}</div>
          </div>

          <div class="flex items-center gap-2 mb-3">
            <button @click="prevDay" type="button" class="px-2 py-1 bg-gray-100 rounded">◀</button>
            <input type="date" v-model="selectedDate" class="border rounded px-2 py-1 flex-1" />
            <button @click="nextDay" type="button" class="px-2 py-1 bg-gray-100 rounded">▶</button>
          </div>

          <div class="text-sm text-gray-700 mb-3">
            <div class="flex items-center justify-between">
              <div>Appointments on this date</div>
              <div class="font-medium">{{ (appointmentsByDate[selectedDate] || []).length }}</div>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              v-if="(appointmentsByDate[selectedDate] || []).length"
              @click="showDateModal = true"
              class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm"
            >
              View Names
            </button>

            <button @click="selectedDate = formatDateInput(new Date())" class="px-3 py-2 bg-gray-100 rounded-md text-sm">Today</button>
          </div>
        </div>

        <!-- Middle: Quick details + availability summary -->
        <div class="bg-white shadow-sm rounded-lg p-4 lg:col-span-2">
          <h3 class="text-lg font-semibold mb-4">Request Details</h3>

          <form @submit.prevent="submit" class="space-y-4 max-w-2xl">
            <div>
              <label for="clinician_id" class="block font-medium text-sm text-gray-700">Clinician</label>
              <select id="clinician_id" v-model="form.clinician_id" class="block mt-1 w-full rounded-md" required>
                <option :value="null" disabled>Select a clinician</option>
                <option v-for="c in clinicians" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label for="appointment_time" class="block font-medium text-sm text-gray-700">Preferred Date & Time</label>
              <input id="appointment_time" type="datetime-local" v-model="form.appointment_time" class="block mt-1 w-full rounded-md" required />
              <div v-if="form.errors.appointment_time" class="text-sm text-red-600 mt-1">{{ form.errors.appointment_time }}</div>
            </div>

            <div>
              <label for="reason_for_visit" class="block font-medium text-sm text-gray-700">Reason for Visit (Optional)</label>
              <textarea id="reason_for_visit" v-model="form.reason_for_visit" rows="4" class="block mt-1 w-full rounded-md"></textarea>
            </div>

            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500">Tip: click "View Names" to pick a time already taken on this date — choosing an existing appointment will prefill clinician & time for easy editing.</div>

              <div class="flex gap-2">
                <button type="button" @click="form.reset()" class="px-4 py-2 bg-gray-100 rounded">Reset</button>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded">Submit Request</button>
              </div>
            </div>
          </form>

          <!-- Small availability preview -->
          <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Availability Preview</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
              <div class="p-2 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Total Appointments (date)</div>
                <div class="font-medium text-gray-800">{{ (appointmentsByDate[selectedDate] || []).length }}</div>
              </div>
              <div class="p-2 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Clinicians</div>
                <div class="font-medium text-gray-800">{{ clinicians.length }}</div>
              </div>
              <div v-for="c in clinicians.slice(0,2)" :key="c.id" class="p-2 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">{{ c.name }}</div>
                <div class="text-sm text-gray-800">{{ (appointmentsByDate[selectedDate] || []).filter(a => a.clinician_id === c.id).length }} appts</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Names on selected date -->
    <div v-if="showDateModal" class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4">
      <div class="absolute inset-0 bg-black opacity-40" @click="showDateModal = false"></div>

      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full z-10 overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
          <div>
            <h3 class="font-semibold">Appointments on {{ formatDateDisplay(selectedDate) }}</h3>
            <div class="text-sm text-gray-500">Select a name to autofill the request form</div>
          </div>
          <div>
            <input v-model="searchQuery" placeholder="Search names or clinician" class="px-3 py-2 border rounded" />
          </div>
        </div>

        <div class="p-4 max-h-80 overflow-auto">
          <div v-if="!appointmentsForSelectedDate.length" class="text-center text-gray-500 py-8">No appointments found on this date.</div>

          <ul v-else class="space-y-2">
            <li v-for="appt in appointmentsForSelectedDate" :key="appt.id" class="p-3 rounded border hover:bg-gray-50 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ appt.patient_name || 'Unknown Patient' }}</div>
                <div class="text-xs text-gray-500">{{ formatTimeOnly(appt.datetime) }} • {{ appt.clinician_name || '' }}</div>
              </div>
              <div class="flex items-center gap-2">
                <button @click.prevent="chooseExistingAppointment(appt)" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Select</button>
                <a :href="route('appointments.show', appt.id)" v-if="appt.id" class="px-3 py-1 bg-gray-100 rounded text-sm">View</a>
              </div>
            </li>
          </ul>
        </div>

        <div class="p-4 border-t flex justify-end">
          <button @click="showDateModal = false" class="px-4 py-2 bg-gray-100 rounded">Close</button>
        </div>
      </div>
    </div>
  </PatientPortalLayout>
</template>

<style scoped>
/* small adjustments */
</style>
