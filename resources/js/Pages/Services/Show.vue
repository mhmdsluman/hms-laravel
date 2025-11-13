<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    service: Object,
});
</script>

<template>
    <Head title="Service Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Service Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        {{ service.name }}
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <span class="font-medium text-gray-700">Department:</span>
                            <span class="ml-2 text-gray-900">{{ service.department }}</span>
                        </div>

                        <div>
                            <span class="font-medium text-gray-700">Price:</span>
                            <span class="ml-2 text-gray-900">${{ Number(service.price).toFixed(2) }}</span>
                        </div>

                        <div>
                            <span class="font-medium text-gray-700">Created At:</span>
                            <span class="ml-2 text-gray-900">{{ new Date(service.created_at).toLocaleString() }}</span>
                        </div>

                        <div>
                            <span class="font-medium text-gray-700">Last Updated:</span>
                            <span class="ml-2 text-gray-900">{{ new Date(service.updated_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <Link
                            :href="route('services.index')"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600"
                        >
                            Back
                        </Link>

                        <div class="flex space-x-2">
                            <Link
                                :href="route('services.edit', service.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700"
                            >
                                Edit
                            </Link>

                            <Link
                                as="button"
                                method="delete"
                                :href="route('services.destroy', service.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700"
                            >
                                Delete
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
