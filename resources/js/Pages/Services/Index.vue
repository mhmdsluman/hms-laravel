<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    services: Object,
});
</script>

<template>
    <Head title="Service Catalogue" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Service Catalogue Management</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- **MODIFIED**: Added a safer check for the flash message -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">All Services</h3>
                            <Link :href="route('services.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add New Service</Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="relative px-6 py-3"><span class="sr-only">Edit</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="service in services.data" :key="service.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ service.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ service.department }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${{ service.price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('services.edit', service.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination class="mt-6" :links="services.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
