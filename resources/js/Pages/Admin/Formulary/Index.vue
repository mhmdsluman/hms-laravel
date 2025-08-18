<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    medications: Object,
});
</script>

<template>
    <Head title="Formulary Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Formulary Management</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">All Pharmacy Medications</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medication Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formulary Status</th>
                                    <th class="relative px-6 py-3"><span class="sr-only">Edit</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="med in medications.data" :key="med.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ med.name }}
                                        <span v-if="med.is_controlled_substance" class="ml-2 text-xs font-bold text-red-600">(Controlled)</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                'bg-green-100 text-green-800': med.formulary_status === 'Formulary',
                                                'bg-yellow-100 text-yellow-800': med.formulary_status === 'Non-Formulary',
                                                'bg-red-100 text-red-800': med.formulary_status === 'Restricted',
                                                'bg-gray-100 text-gray-800': !med.formulary_status,
                                              }">
                                            {{ med.formulary_status || 'Not Set' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('formulary.edit', med.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination class="mt-6" :links="medications.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
