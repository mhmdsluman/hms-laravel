<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    orderSet: {
        type: Object,
        default: null,
    },
    services: Array,
});

const form = useForm({
    name: props.orderSet?.name || '',
    description: props.orderSet?.description || '',
    service_ids: props.orderSet?.items.map(item => item.service_id) || [],
});

const servicesByDept = computed(() => {
    return props.services.reduce((acc, service) => {
        if (!acc[service.department]) {
            acc[service.department] = [];
        }
        acc[service.department].push(service);
        return acc;
    }, {});
});

const submit = () => {
    if (props.orderSet) {
        form.put(route('order-sets.update', props.orderSet.id));
    } else {
        form.post(route('order-sets.store'));
    }
};
</script>

<template>
    <Head :title="orderSet ? 'Edit Order Set' : 'Create Order Set'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ orderSet ? 'Edit Order Set' : 'Create New Order Set' }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Order Set Name</label>
                            <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                        </div>
                        <div>
                            <label for="description" class="block font-medium text-sm text-gray-700">Description</label>
                            <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full"></textarea>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="font-semibold">Included Services</h3>
                            <div class="mt-4 space-y-4">
                                <div v-for="(deptServices, department) in servicesByDept" :key="department">
                                    <h4 class="font-medium text-gray-600">{{ department }}</h4>
                                    <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                        <label v-for="service in deptServices" :key="service.id" class="flex items-center space-x-2">
                                            <input type="checkbox" :value="service.id" v-model="form.service_ids" class="rounded">
                                            <span>{{ service.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end items-center">
                        <Link :href="route('order-sets.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">
                            {{ orderSet ? 'Update Order Set' : 'Save Order Set' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
