<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pendingOrders: Array,
    scheduledOrders: Array,
});

const formatDateTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};
</script>

<template>
    <Head title="Operating Theater Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Operating Theater Dashboard</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Pending Scheduling Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Pending Surgical Scheduling</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Procedure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in pendingOrders" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.order.patient.first_name }} {{ item.order.patient.last_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.service.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('ot.create', { orderItem: item.id })" class="px-3 py-1 bg-blue-500 text-white rounded-md">Schedule</Link>
                                    </td>
                                </tr>
                                <tr v-if="pendingOrders.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No procedures are pending scheduling.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Scheduled Procedures Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Scheduled Procedures</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Procedure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in scheduledOrders" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.order.patient.first_name }} {{ item.order.patient.last_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.service.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDateTime(item.ot_schedule.scheduled_start_time) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('operative-notes.create', { orderItem: item.id })" class="px-3 py-1 bg-green-500 text-white rounded-md">Add Operative Note</Link>
                                    </td>
                                </tr>
                                 <tr v-if="scheduledOrders.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No procedures are currently scheduled.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
