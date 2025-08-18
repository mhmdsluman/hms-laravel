<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    visits: Array,
});

const formatTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Emergency Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Emergency Department</h2>
                <Link :href="route('emergency.create')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Register New ER Patient
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Current ER Patients</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="visit in visits" :key="visit.id" class="p-4 border rounded-lg"
                                 :class="{ 'border-yellow-400 bg-yellow-50': visit.status === 'Waiting' }">
                                <p class="font-bold">{{ visit.patient.first_name }} {{ visit.patient.last_name }}</p>
                                <p class="text-sm text-gray-600">Arrived: {{ formatTime(visit.arrival_time) }}</p>
                                <p class="mt-2"><strong>Complaint:</strong> {{ visit.chief_complaint }}</p>
                                <p class="mt-2 text-xs font-bold uppercase">{{ visit.status }}</p>
                            </div>
                            <div v-if="visits.length === 0" class="col-span-full text-center text-gray-500 py-8">
                                No patients currently in the Emergency Department.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
