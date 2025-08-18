<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    admission: Object,
});

const formatTime = (value) => {
    if (!value) return '';
    return new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const administerMedication = (marId) => {
    if (confirm('Are you sure you want to mark this medication as administered?')) {
        router.patch(route('mar.update', marId), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Medication Administration Record" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    MAR: {{ admission.patient.first_name }} {{ admission.patient.last_name }} (Bed {{ admission.bed.bed_number }})
                </h2>
                <Link :href="route('ipd.index')" class="text-sm text-blue-600 hover:underline">
                    &larr; Back to IPD Dashboard
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
                        <h3 class="text-lg font-semibold mb-4">Scheduled Medications</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medication</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Administered At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Administered By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="mar in admission.medication_administrations" :key="mar.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ mar.order_item.service.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatTime(mar.scheduled_time) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                'bg-green-100 text-green-800': mar.status === 'Administered',
                                                'bg-yellow-100 text-yellow-800': mar.status === 'Due',
                                              }">
                                                {{ mar.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ mar.administered_time ? formatTime(mar.administered_time) : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ mar.administrator?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button v-if="mar.status === 'Due'" @click="administerMedication(mar.id)" class="px-3 py-1 bg-blue-500 text-white text-xs rounded">
                                                Administer
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="admission.medication_administrations.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No medication orders for this admission.</td>
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
