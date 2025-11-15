<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recent_activity: Array,
});
</script>

<template>
    <Head title="LIMS Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">LIMS Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Samples Collected Today</h4>
                        <p class="text-3xl font-bold">{{ stats.samples_collected_today }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Tests Pending Validation</h4>
                        <p class="text-3xl font-bold">{{ stats.tests_pending_validation }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">QC Failures Today</h4>
                        <p class="text-3xl font-bold">{{ stats.qc_failures }}</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link :href="route('lab.index')" class="text-center p-4 border rounded-lg hover:bg-gray-100">
                                <p class="text-lg font-medium">View All Orders</p>
                            </Link>
                            <Link :href="route('lab.index')" class="text-center p-4 border rounded-lg hover:bg-gray-100">
                                <p class="text-lg font-medium">Enter QC Results</p>
                            </Link>
                            <Link :href="route('lab.index')" class="text-center p-4 border rounded-lg hover:bg-gray-100">
                                <p class="text-lg font-medium">Manage QC Lots</p>
                            </Link>
                            <Link :href="route('lab.index')" class="text-center p-4 border rounded-lg hover:bg-gray-100">
                                <p class="text-lg font-medium">View Reports</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="order in recent_activity" :key="order.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.order_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.patient.first_name }} {{ order.patient.last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</td>
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
