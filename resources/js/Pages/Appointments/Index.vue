<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppointmentDetailModal from '@/Components/AppointmentDetailModal.vue'; // <-- Import the new modal
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    appointments: Array,
    patients: Array,
    clinicians: Array,
    currentDate: Object,
});

const form = useForm({
    patient_id: null,
    clinician_id: null,
    appointment_time: '',
    reason_for_visit: '',
});

// State for the modal
const showDetailModal = ref(false);
const selectedAppointment = ref(null);

const openAppointmentDetails = (appointment) => {
    selectedAppointment.value = appointment;
    showDetailModal.value = true;
};

// Group appointments by day for easy display in the calendar
const appointmentsByDay = computed(() => {
    return props.appointments.reduce((acc, apt) => {
        const day = new Date(apt.appointment_time).getDate();
        if (!acc[day]) {
            acc[day] = [];
        }
        acc[day].push(apt);
        return acc;
    }, {});
});

// Calendar logic
const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const today = new Date();
const date = ref(new Date(props.currentDate.year, props.currentDate.month - 1, 1));

const firstDayOfMonth = computed(() => date.value.getDay());
const daysInMonth = computed(() => new Date(date.value.getFullYear(), date.value.getMonth() + 1, 0).getDate());

const changeMonth = (offset) => {
    date.value.setMonth(date.value.getMonth() + offset);
    router.get(route('appointments.index'), {
        month: date.value.getMonth() + 1,
        year: date.value.getFullYear(),
    }, { preserveState: true, preserveScroll: true });
};

const submit = () => {
    form.post(route('appointments.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Appointment Scheduling" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Appointment Scheduling
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Column 1: Booking Form (no changes) -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Book New Appointment</h3>
                            <form @submit.prevent="submit">
                                <div class="mb-4">
                                    <label for="patient_id" class="block font-medium text-sm text-gray-700">Patient</label>
                                    <select id="patient_id" v-model="form.patient_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <option :value="null" disabled>Select a patient</option>
                                        <option v-for="patient in patients" :key="patient.id" :value="patient.id">{{ patient.first_name }} {{ patient.last_name }}</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="clinician_id" class="block font-medium text-sm text-gray-700">Clinician</label>
                                    <select id="clinician_id" v-model="form.clinician_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <option :value="null" disabled>Select a clinician</option>
                                        <option v-for="clinician in clinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="appointment_time" class="block font-medium text-sm text-gray-700">Date & Time</label>
                                    <input id="appointment_time" type="datetime-local" v-model="form.appointment_time" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
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

                <!-- Column 2: Calendar View -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded">&lt; Prev</button>
                                <h3 class="text-lg font-semibold">{{ currentDate.monthName }} {{ currentDate.year }}</h3>
                                <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded">Next &gt;</button>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center">
                                <div v-for="day in daysOfWeek" :key="day" class="font-bold text-sm text-gray-600">{{ day }}</div>
                                <div v-for="blank in firstDayOfMonth" :key="'blank-' + blank" class="border rounded-lg p-2 h-24"></div>
                                <div v-for="day in daysInMonth" :key="day" class="border rounded-lg p-2 h-24 overflow-y-auto" :class="{'bg-blue-100': day === today.getDate() && currentDate.month === today.getMonth() + 1 && currentDate.year === today.getFullYear()}">
                                    <div class="font-bold">{{ day }}</div>
                                    <div v-if="appointmentsByDay[day]" class="text-xs text-left mt-1 space-y-1">
                                        <div v-for="apt in appointmentsByDay[day]" :key="apt.id"
                                             @click="openAppointmentDetails(apt)"
                                             class="bg-blue-500 text-white p-1 rounded cursor-pointer hover:bg-blue-600">
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

        <!-- Add the modal component to the template -->
        <AppointmentDetailModal
            :show="showDetailModal"
            :appointment="selectedAppointment"
            @close="showDetailModal = false"
        />
    </AuthenticatedLayout>
</template>
