<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Analytics Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics & Reporting</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Patients Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500 text-sm font-medium">Total Patients</h4>
                        <p class="text-3xl font-bold mt-2">{{ stats.total_patients }}</p>
                    </div>

                    <!-- Bed Occupancy Rate Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500 text-sm font-medium">Bed Occupancy Rate</h4>
                        <p class="text-3xl font-bold mt-2">{{ stats.bed_occupancy_rate }}%</p>
                    </div>

                    <!-- Revenue This Month Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500 text-sm font-medium">Revenue This Month</h4>
                        <p class="text-3xl font-bold mt-2">${{ stats.total_revenue_this_month }}</p>
                    </div>

                    <!-- Average Length of Stay Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500 text-sm font-medium">Avg. Length of Stay</h4>
                        <p class="text-3xl font-bold mt-2">{{ stats.avg_length_of_stay_days }} <span class="text-lg font-medium">days</span></p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
