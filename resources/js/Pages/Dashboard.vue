<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user?.role ?? '');

// Define all possible dashboard items with colors
const allDashboardItems = [
    { name: 'Patient Registration', route: 'patients.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-3-5.197m0 0A7.962 7.962 0 0112 4.354a7.962 7.962 0 013 3.197m-3-3.197a4 4 0 110 5.292', roles: ['admin', 'clerk', 'clinician'], color: 'text-blue-500' },
    { name: 'Appointments', route: 'appointments.index', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', roles: ['admin', 'clerk', 'clinician'], color: 'text-teal-500' },
    { name: 'Inpatient (IPD)', route: 'ipd.index', icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z', roles: ['admin', 'clerk', 'nurse'], color: 'text-indigo-500' },
    { name: 'Nursing Station', route: 'nursing.index', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h.5A2.5 2.5 0 0021.5 5.5V3.935m-18 0A2.965 2.965 0 016 3.055 2.965 2.965 0 018 3.935m13 0a2.965 2.965 0 00-2-2.965 2.965 2.965 0 00-2 2.965m0 0V5.5A2.5 2.5 0 0018.5 8h-.5a2 2 0 00-2 2 2 2 0 11-4 0 2 2 0 00-2-2h-.5A2.5 2.5 0 006.5 5.5V3.935', roles: ['admin', 'nurse'], color: 'text-pink-500' },
    { name: 'Laboratory', route: 'lab.index', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', roles: ['admin', 'lab'], color: 'text-purple-500' },
    { name: 'Pharmacy', route: 'pharmacy.index', icon: 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', roles: ['admin', 'pharmacy'], color: 'text-green-500' },
    { name: 'Radiology', route: 'radiology.index', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', roles: ['admin', 'radiology'], color: 'text-gray-600' },
    { name: 'Billing', route: 'billing.index', icon: 'M9 8h6m-5 4h.01M18 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', roles: ['admin', 'clerk'], color: 'text-orange-500' },
    { name: 'User Management', route: 'users.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.084-1.28-.24-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.084-1.28.24-1.857m10 0A5.98 5.98 0 0014 15c-1.657 0-3.123.739-4.144 1.857m4.144-1.857A5.98 5.98 0 0010 15c-1.657 0-3.123.739-4.144 1.857M14 10a4 4 0 11-8 0 4 4 0 018 0z', roles: ['admin'], color: 'text-red-500' },
    { name: 'Service Catalogue', route: 'services.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', roles: ['admin'], color: 'text-cyan-500' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Total Patients</h4>
                        <p class="text-3xl font-bold">{{ stats.total_patients }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Upcoming Appointments</h4>
                        <p class="text-3xl font-bold">{{ stats.upcoming_appointments }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Pending Lab Orders</h4>
                        <p class="text-3xl font-bold">{{ stats.pending_lab_orders }}</p>
                    </div>
                     <div class="bg-white p-6 rounded-lg shadow">
                        <h4 class="text-gray-500">Pending Prescriptions</h4>
                        <p class="text-3xl font-bold">{{ stats.pending_pharmacy_orders }}</p>
                    </div>
                </div>

                <!-- Navigation Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                            <template v-for="item in allDashboardItems" :key="item.route">
                                <div v-if="item.roles.includes(userRole)">
                                    <Link :href="route(item.route)" class="flex flex-col items-center justify-center p-4 border rounded-lg hover:bg-gray-100 hover:shadow-md transition-shadow">
                                        <svg class="h-10 w-10 mb-2" :class="item.color" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                                        </svg>
                                        <span class="text-sm font-medium text-center">{{ item.name }}</span>
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
