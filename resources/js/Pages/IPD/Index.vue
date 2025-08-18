<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    bedsByWard: Object,
});

const dischargePatient = (admissionId) => {
    if (confirm('Are you sure you want to discharge this patient?')) {
        router.patch(route('admissions.discharge', admissionId));
    }
};
</script>

<template>
    <Head title="Inpatient Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inpatient Bed Management</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="space-y-6">
                    <div v-for="(beds, ward) in bedsByWard" :key="ward" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">{{ ward }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                <div v-for="bed in beds" :key="bed.id" class="p-4 rounded-lg text-center border flex flex-col justify-between"
                                     :class="{
                                        'bg-green-100 border-green-300': bed.status === 'Available',
                                        'bg-red-100 border-red-300': bed.status === 'Occupied',
                                        'bg-yellow-100 border-yellow-300': bed.status === 'Cleaning',
                                     }">
                                    <div>
                                        <p class="font-bold">{{ bed.bed_number }}</p>
                                        <p class="text-sm text-gray-600">{{ bed.status }}</p>
                                        <div v-if="bed.status === 'Occupied' && bed.current_admission" class="mt-2 text-xs">
                                            <p class="font-semibold">{{ bed.current_admission.patient.first_name }} {{ bed.current_admission.patient.last_name }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        <Link v-if="bed.status === 'Available'" :href="route('admissions.create', { bed_id: bed.id })" class="inline-block px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 w-full">
                                            Admit
                                        </Link>
                                        <div v-if="bed.status === 'Occupied' && bed.current_admission">
                                            <Link :href="route('mar.show', bed.current_admission.id)" class="inline-block px-3 py-1 bg-indigo-500 text-white text-xs rounded hover:bg-indigo-600 w-full mb-1">
                                                View MAR
                                            </Link>
                                            <button @click="dischargePatient(bed.current_admission.id)" class="inline-block px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 w-full">
                                                Discharge
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
