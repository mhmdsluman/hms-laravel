<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    provider: Object,
    services: Array,
});

const form = useForm({
    contracts: [],
});

// Initialize the form with existing contract data or default values for all services
onMounted(() => {
    form.contracts = props.services.map(service => {
        const existingContract = props.provider.contracts.find(c => c.service_id === service.id);
        return {
            service_id: service.id,
            service_name: service.name,
            service_department: service.department,
            coverage_percentage: existingContract?.coverage_percentage || 100,
            co_pay_amount: existingContract?.co_pay_amount || 0,
            requires_pre_authorization: existingContract?.requires_pre_authorization || false,
        };
    });
});

const submit = () => {
    form.put(route('insurance-contracts.update', props.provider.id));
};
</script>

<template>
    <Head :title="'Manage Contract - ' + provider.name" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manage Insurance Contract: {{ provider.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coverage %</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Co-Pay ($)</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pre-Auth Req.</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(contract, index) in form.contracts" :key="contract.service_id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium">{{ contract.service_name }}</div>
                                            <div class="text-xs text-gray-500">{{ contract.service_department }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" v-model="contract.coverage_percentage" class="w-24 rounded-md">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" step="0.01" v-model="contract.co_pay_amount" class="w-24 rounded-md">
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="checkbox" v-model="contract.requires_pre_authorization" class="rounded">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end items-center">
                        <Link :href="route('insurance-providers.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
