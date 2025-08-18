<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    appointments: Object,
});

const formatDateTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' });
};
</script>

<template>
    <Head title="My Appointments" />
    <PatientPortalLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Appointments</h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clinician</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="apt in appointments.data" :key="apt.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ formatDateTime(apt.appointment_time) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ apt.clinician.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="{
                                        'bg-green-100 text-green-800': apt.status === 'Completed',
                                        'bg-blue-100 text-blue-800': apt.status === 'Scheduled',
                                        'bg-red-100 text-red-800': apt.status === 'Cancelled',
                                      }">
                                    {{ apt.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination class="mt-6" :links="appointments.links" />
            </div>
        </div>
    </PatientPortalLayout>
</template>
