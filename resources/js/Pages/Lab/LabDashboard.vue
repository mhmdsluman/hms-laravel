<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lab Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Active Lab Orders</h3>
                            <input type="text" v-model="search" placeholder="Search orders..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Order ID</th>
                                    <th scope="col" class="py-3 px-6">Patient</th>
                                    <th scope="col" class="py-3 px-6">Tests</th>
                                    <th scope="col" class="py-3 px-6">Progress</th>
                                    <th scope="col" class="py-3 px-6">Estimated Time</th>
                                    <th scope="col" class="py-3 px-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in activeOrders" :key="order.id" class="bg-white border-b">
                                    <td class="py-4 px-6">{{ order.order_id }}</td>
                                    <td class="py-4 px-6">{{ order.patient.name }}</td>
                                    <td class="py-4 px-6">{{ order.tests.map(t => t.name).join(', ') }}</td>
                                    <td class="py-4 px-6">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: order.progress + '%' }"></div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">{{ order.estimated_time }}</td>
                                    <td class="py-4 px-6">
                                        <Link :href="route('lab.results.create', order.id)" class="font-medium text-blue-600 hover:underline">Enter Results</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    activeOrders: Array,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, (value) => {
    router.get('/lab', { search: value }, {
        preserveState: true,
        replace: true,
    });
});
</script>
