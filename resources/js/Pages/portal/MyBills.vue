<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    bills: Object,
});
</script>

<template>
    <Head title="My Bills" />
    <PatientPortalLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Bills</h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bill Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="bill in bills.data" :key="bill.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ new Date(bill.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ bill.total_amount }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="{
                                        'bg-red-100 text-red-800': bill.status === 'Unpaid',
                                        'bg-green-100 text-green-800': bill.status === 'Paid',
                                      }">
                                    {{ bill.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination class="mt-6" :links="bills.links" />
            </div>
        </div>
    </PatientPortalLayout>
</template>
