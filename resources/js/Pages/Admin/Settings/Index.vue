<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    hospital_name: props.settings.hospital_name,
    hospital_address: props.settings.hospital_address,
    hospital_phone: props.settings.hospital_phone,
    hospital_email: props.settings.hospital_email,
    hospital_logo: null,
});

const logoPreview = ref(null);

function updateLogoPreview(event) {
    const file = event.target.files[0];
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Hospital Details</h3>
                                    <div class="mt-4">
                                        <label for="hospital_name" class="block text-sm font-medium text-gray-700">Hospital Name</label>
                                        <input type="text" id="hospital_name" v-model="form.hospital_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div class="mt-4">
                                        <label for="hospital_address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <input type="text" id="hospital_address" v-model="form.hospital_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div class="mt-4">
                                        <label for="hospital_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" id="hospital_phone" v-model="form.hospital_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div class="mt-4">
                                        <label for="hospital_email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" id="hospital_email" v-model="form.hospital_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div class="mt-4">
                                        <label for="hospital_logo" class="block text-sm font-medium text-gray-700">Logo</label>
                                        <input type="file" id="hospital_logo" @change="updateLogoPreview" @input="form.hospital_logo = $event.target.files[0]" class="mt-1 block w-full">
                                        <img v-if="logoPreview" :src="logoPreview" class="mt-4 max-h-40">
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Print Header Preview</h3>
                                    <div class="mt-4 border p-4 rounded-md">
                                        <div class="print-header">
                                            <div class="header-content">
                                                <div class="hospital-info">
                                                    <img v-if="logoPreview" :src="logoPreview" alt="Hospital Logo" class="logo">
                                                    <h1>{{ form.hospital_name }}</h1>
                                                </div>
                                                <div class="contact-info">
                                                    <p>{{ form.hospital_address }}</p>
                                                    <p>Phone: {{ form.hospital_phone }}</p>
                                                    <p>Email: {{ form.hospital_email }}</p>
                                                </div>
                                            </div>
                                            <h2 class="document-title">Document Title</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
