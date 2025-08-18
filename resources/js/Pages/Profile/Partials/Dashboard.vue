<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    patient: Object,
});

const formatDateTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' });
};

const recentLabResults = computed(() => {
    const results = [];
    props.patient.orders?.forEach(order => {
        order.items.forEach(item => {
            if (item.lab_result) {
                results.push({
                    testName: item.service.name,
                    resultValue: item.lab_result.result_value,
                    date: item.lab_result.created_at,
                });
            }
        });
    });
    return results.slice(0, 5); // Show latest 5 results
});
</script>

<template>
    <Head title="My Dashboard" />

    <PatientPortalLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome back, {{ patient.first_name }}!
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Upcoming Appointments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Your Upcoming Appointments</h3>
                    <div v-if="patient.appointments.length > 0" class="space-y-4">
                        <div v-for="apt in patient.appointments" :key="apt.id" class="p-4 border rounded-lg">
                            <p class="font-bold">Appointment with {{ apt.clinician.name }}</p>
                            <p class="text-sm text-gray-700">{{ formatDateTime(apt.appointment_time) }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-600">You have no upcoming appointments.</p>
                </div>
            </div>

            <!-- Recent Lab Results -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Your Recent Lab Results</h3>
                    <div v-if="recentLabResults.length > 0" class="space-y-4">
                        <div v-for="(result, index) in recentLabResults" :key="index" class="p-4 border rounded-lg">
                            <div class="flex justify-between items-center">
                                <p class="font-bold">{{ result.testName }}</p>
                                <p class="text-sm text-gray-500">{{ new Date(result.date).toLocaleDateString() }}</p>
                            </div>
                            <p class="text-lg font-mono mt-1">{{ result.resultValue }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-600">You have no recent lab results.</p>
                </div>
            </div>
        </div>
    </PatientPortalLayout>
</template>
