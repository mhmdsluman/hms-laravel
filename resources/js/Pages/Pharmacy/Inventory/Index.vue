<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    inventoryItems: Object,
});

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this inventory item?')) {
        router.delete(route('inventory.destroy', id));
    }
};
</script>

<template>
    <Head title="Inventory Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory Management</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">All Pharmacy Stock</h3>
                            <Link :href="route('inventory.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add New Stock</Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medication</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in inventoryItems.data" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.service.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.batch_number || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.expiry_date || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold">{{ item.quantity_in_stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <Link :href="route('inventory.edit', item.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                        <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="inventoryItems.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No inventory items found.</td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination class="mt-6" :links="inventoryItems.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
