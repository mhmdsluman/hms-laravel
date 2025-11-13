<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppointmentDetailModal from '@/Components/AppointmentDetailModal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  appointments: { type: Array, default: () => [] },
  clinicians: { type: Array, default: () => [] },
  currentDate: {
    type: Object,
    default: () => {
      const now = new Date();
      return { year: now.getFullYear(), month: now.getMonth() + 1, monthName: now.toLocaleString(undefined, { month: 'long' }) };
    },
  },
});

const form = useForm({
  patient_id: null,
  clinician_id: null,
  appointment_time: '',
  reason_for_visit: '',
});

// Modal state
const showDetailModal = ref(false);
const selectedAppointment = ref(null);

const openAppointmentDetails = (appointment) => {
  selectedAppointment.value = appointment;
  showDetailModal.value = true;
};

// Patient Search
const patientSearchQuery = ref('');
const patientSearchResults = ref([]);
const selectedPatient = ref(null);
const isLoadingSearch = ref(false);
let searchDebounce = null;

watch(
  patientSearchQuery,
  (newValue) => {
    clearTimeout(searchDebounce);
    const q = (newValue || '').trim();
    if (!q) {
      patientSearchResults.value = [];
      return;
    }

    searchDebounce = setTimeout(() => {
      isLoadingSearch.value = true;
      axios
        .get(route('patients.search'), { params: { query: q } })
        .then((response) => {
          patientSearchResults.value = response.data || [];
        })
        .catch((error) => {
          console.error('Error searching patients:', error);
          if (error.response) {
            console.error('Response status:', error.response.status);
            console.error('Response data:', error.response.data);
          } else {
            console.error('No response (network/CORS):', error.message);
          }
          patientSearchResults.value = [];
        })
        .finally(() => {
          isLoadingSearch.value = false;
        });
    }, 300);
  },
  { immediate: false }
);

const selectPatient = (patient) => {
  selectedPatient.value = patient;
  form.patient_id = patient?.id ?? null;
  patientSearchQuery.value = '';
  patientSearchResults.value = [];
};

const clearPatientSelection = () => {
  selectedPatient.value = null;
  form.patient_id = null;
};

// Group appointments by day
const appointmentsByDay = computed(() => {
  const acc = {};
  (props.appointments || []).forEach((apt) => {
    if (!apt || !apt.appointment_time) return;
    const d = new Date(apt.appointment_time);
    if (Number.isNaN(d.getTime())) return;
    const day = d.getDate();
    if (!acc[day]) acc[day] = [];
    acc[day].push(apt);
  });
  return acc;
});

// Calendar
const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const today = new Date();
const initialYear = props.currentDate?.year ?? today.getFullYear();
const initialMonthZeroBased = (props.currentDate?.month ? props.currentDate.month - 1 : today.getMonth());
const date = ref(new Date(initialYear, initialMonthZeroBased, 1));

const firstDayOfMonth = computed(() => date.value.getDay());
const daysInMonth = computed(() => new Date(date.value.getFullYear(), date.value.getMonth() + 1, 0).getDate());

const changeMonth = (offset) => {
  date.value = new Date(date.value.getFullYear(), date.value.getMonth() + offset, 1);
  router.get(
    route('appointments.index'),
    { month: date.value.getMonth() + 1, year: date.value.getFullYear() },
    { preserveState: true, preserveScroll: true }
  );
};

const submit = () => {
  form.post(route('appointments.store'), {
    onSuccess: () => {
      form.reset();
      clearPatientSelection();
    },
  });
};
</script>

<template>
  <Head title="Appointment Scheduling" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Appointment Scheduling</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Booking form -->
        <div class="md:col-span-1">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <h3 class="text-lg font-semibold mb-4">Book New Appointment</h3>

              <form @submit.prevent="submit">
                <div class="mb-4">
                  <label for="patient_search" class="block font-medium text-sm text-gray-700">Patient</label>

                  <div v-if="!selectedPatient" class="relative">
                    <input id="patient_search" type="text" v-model="patientSearchQuery" placeholder="Search by name, UHID, or phone..." class="block mt-1 w-full rounded-md shadow-sm border-gray-300" autocomplete="off" />
                    <div v-if="form.errors.patient_id" class="text-sm text-red-600 mt-1">Please select a patient.</div>

                    <div v-if="patientSearchQuery.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                      <div v-if="isLoadingSearch" class="px-4 py-2 text-gray-500">Searching...</div>
                      <div v-else-if="patientSearchResults.length === 0" class="px-4 py-2 text-gray-500">No patients found.</div>
                      <ul v-else>
                        <li v-for="patient in patientSearchResults" :key="patient.id" @click="selectPatient(patient)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                          <div class="font-semibold">{{ patient.first_name }} {{ patient.last_name }}</div>
                          <div class="text-sm text-gray-600">{{ patient.uhid }} • {{ patient.primary_phone }}</div>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <div v-else class="mt-1 p-3 bg-gray-100 border border-gray-300 rounded-md">
                    <div class="flex justify-between items-center">
                      <div>
                        <div class="font-semibold text-gray-900">{{ selectedPatient.first_name }} {{ selectedPatient.last_name }}</div>
                        <div class="text-sm text-gray-600">{{ selectedPatient.uhid }}</div>
                      </div>
                      <button @click="clearPatientSelection" type="button" class="text-sm text-red-600 hover:underline">Change</button>
                    </div>
                  </div>
                </div>

                <div class="mb-4">
                  <label for="clinician_id" class="block font-medium text-sm text-gray-700">Clinician</label>
                  <select id="clinician_id" v-model="form.clinician_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                    <option :value="null" disabled>Select a clinician</option>
                    <option v-for="clinician in props.clinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}</option>
                  </select>
                  <div v-if="form.errors.clinician_id" class="text-sm text-red-600 mt-1">Please select a clinician.</div>
                </div>

                <div class="mb-4">
                  <label for="appointment_time" class="block font-medium text-sm text-gray-700">Date & Time</label>
                  <input id="appointment_time" type="datetime-local" v-model="form.appointment_time" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required />
                  <div v-if="form.errors.appointment_time" class="text-sm text-red-600 mt-1">Please select a valid date and time.</div>
                </div>

                <div class="mb-4">
                  <label for="reason_for_visit" class="block font-medium text-sm text-gray-700">Reason for Visit (Optional)</label>
                  <textarea id="reason_for_visit" v-model="form.reason_for_visit" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300"></textarea>
                </div>

                <div class="flex items-center justify-end">
                  <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">Book Appointment</button>
                </div>
              </form>

            </div>
          </div>
        </div>

        <!-- Calendar -->
        <div class="md:col-span-2">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="flex justify-between items-center mb-4">
                <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded">&lt; Prev</button>
                <h3 class="text-lg font-semibold">{{ props.currentDate.monthName }} {{ props.currentDate.year }}</h3>
                <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded">Next &gt;</button>
              </div>

              <div class="grid grid-cols-7 gap-1 text-center">
                <div v-for="day in daysOfWeek" :key="day" class="font-bold text-sm text-gray-600">{{ day }}</div>

                <div v-for="i in firstDayOfMonth" :key="'blank-' + i" class="border rounded-lg p-2 h-24"></div>

                <div v-for="day in daysInMonth" :key="day" class="border rounded-lg p-2 h-24 overflow-y-auto"
                     :class="{ 'bg-blue-100': day === today.getDate() && props.currentDate.month === today.getMonth() + 1 && props.currentDate.year === today.getFullYear() }">
                  <div class="font-bold">{{ day }}</div>

                  <div v-if="appointmentsByDay[day]" class="text-xs text-left mt-1 space-y-1">
                    <div v-for="apt in appointmentsByDay[day]" :key="apt.id" @click="openAppointmentDetails(apt)" class="bg-blue-500 text-white p-1 rounded cursor-pointer hover:bg-blue-600">
                      <template v-if="apt.patient">
                        {{ apt.patient.first_name.charAt(0) }}. {{ apt.patient.last_name }}
                      </template>
                      <template v-else>
                        <span class="italic">Unknown Patient</span>
                      </template>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <AppointmentDetailModal :show="showDetailModal" :appointment="selectedAppointment" @close="showDetailModal = false" />
  </AuthenticatedLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
