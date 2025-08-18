<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    admissions: Array,
});

const formatDateTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};
</script>

<template>
    <Head title="Nursing Station" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nursing Station - Admitted Patients</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bed</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="admission in admissions" :key="admission.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ admission.patient.first_name }} {{ admission.patient.last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ admission.bed.ward }} - {{ admission.bed.bed_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDateTime(admission.admission_time) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <Link :href="route('mar.show', admission.id)" class="px-3 py-1 bg-indigo-500 text-white text-xs rounded hover:bg-indigo-600">MAR</Link>
                                            <Link :href="route('nursing-notes.create', admission.id)" class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Add Note</Link>
                                            <Link :href="route('care-plans.show', admission.id)" class="px-3 py-1 bg-purple-500 text-white text-xs rounded hover:bg-purple-600">Care Plan</Link>
                                            <Link :href="route('shift-handovers.create', admission.id)" class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600">Handover</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="admissions.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No patients are currently admitted.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
