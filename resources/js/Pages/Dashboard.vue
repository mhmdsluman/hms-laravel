<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    UsersIcon,
    CalendarIcon,
    BuildingOfficeIcon,
    ClipboardDocumentListIcon,
    BeakerIcon,
    ArchiveBoxIcon,
    FilmIcon,
    CreditCardIcon,
    CogIcon,
    CircleStackIcon,
} from '@heroicons/vue/24/outline';


const props = defineProps({
    stats: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user?.role ?? '');

const allDashboardItems = [
    { name: 'Patient Registration', route: 'patients.index', icon: UsersIcon, roles: ['admin', 'clerk', 'clinician'], color: 'text-blue-500' },
    { name: 'Appointments', route: 'appointments.index', icon: CalendarIcon, roles: ['admin', 'clerk', 'clinician'], color: 'text-teal-500' },
    { name: 'Inpatient (IPD)', route: 'ipd.index', icon: BuildingOfficeIcon, roles: ['admin', 'clerk', 'nurse'], color: 'text-indigo-500' },
    { name: 'Nursing Station', route: 'nursing.index', icon: ClipboardDocumentListIcon, roles: ['admin', 'nurse'], color: 'text-pink-500' },
    { name: 'Laboratory', route: 'lab.index', icon: BeakerIcon, roles: ['admin', 'lab'], color: 'text-purple-500' },
    { name: 'Pharmacy', route: 'pharmacy.index', icon: ArchiveBoxIcon, roles: ['admin', 'pharmacy'], color: 'text-green-500' },
    { name: 'Radiology', route: 'radiology.index', icon: FilmIcon, roles: ['admin', 'radiology'], color: 'text-gray-600' },
    { name: 'Billing', route: 'billing.index', icon: CreditCardIcon, roles: ['admin', 'clerk'], color: 'text-orange-500' },
    { name: 'User Management', route: 'users.index', icon: UsersIcon, roles: ['admin'], color: 'text-red-500' },
    { name: 'Service Catalogue', route: 'services.index', icon: CircleStackIcon, roles: ['admin'], color: 'text-cyan-500' },
    { name: 'Settings', route: 'admin.settings.index', icon: CogIcon, roles: ['admin'], color: 'text-gray-500' },
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
                                        <component :is="item.icon" class="h-10 w-10 mb-2" :class="item.color" />
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
