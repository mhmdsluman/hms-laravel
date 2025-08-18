<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    provider: Object,
});

const form = useForm({
    name: props.provider.name,
    contact_person: props.provider.contact_person,
    phone: props.provider.phone,
    email: props.provider.email,
});

const submit = () => {
    form.put(route('insurance-providers.update', props.provider.id));
};
</script>
<template>
    <Head title="Edit Insurance Provider" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Insurance Provider</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700">Provider Name</label>
                                    <input id="name" type="text" v-model="form.name" class="block mt-1 w-full rounded-md" required>
                                </div>
                                <div>
                                    <label for="contact_person" class="block font-medium text-sm text-gray-700">Contact Person</label>
                                    <input id="contact_person" type="text" v-model="form.contact_person" class="block mt-1 w-full rounded-md">
                                </div>
                                <div>
                                    <label for="phone" class="block font-medium text-sm text-gray-700">Phone</label>
                                    <input id="phone" type="text" v-model="form.phone" class="block mt-1 w-full rounded-md">
                                </div>
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                    <input id="email" type="email" v-model="form.email" class="block mt-1 w-full rounded-md">
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('insurance-providers.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md">Update Provider</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
