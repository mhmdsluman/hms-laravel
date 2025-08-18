<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pendingOrders: Array,
    scheduledOrders: Array,
    completedOrders: Array,
});

const formatDateTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};
</script>

<template>
    <Head title="Radiology Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Radiology Dashboard</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                 <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Pending Scheduling Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Pending Scheduling</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in pendingOrders" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDateTime(item.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.order.patient.first_name }} {{ item.order.patient.last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.service.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('radiology-schedule.create', item.id)" class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                                Schedule
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pendingOrders.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No orders are pending scheduling.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Scheduled / Awaiting Report Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Scheduled & Awaiting Report</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in scheduledOrders" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.order.patient.first_name }} {{ item.order.patient.last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ item.service.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('radiology-reports.create', item.id)" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600">
                                                Add Report
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="scheduledOrders.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No studies are awaiting a report.</td>
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
