<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'Male',
    primary_phone: '',
    chief_complaint: '',
});

const submit = () => {
    form.post(route('emergency.store'));
};
</script>

<template>
    <Head title="ER Registration" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Emergency Patient Registration</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm">First Name</label>
                                    <input type="text" v-model="form.first_name" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Last Name</label>
                                    <input type="text" v-model="form.last_name" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Date of Birth</label>
                                    <input type="date" v-model="form.date_of_birth" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Gender</label>
                                    <select v-model="form.gender" class="mt-1 block w-full">
                                        <option>Male</option>
                                        <option>Female</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm">Phone Number</label>
                                    <input type="tel" v-model="form.primary_phone" class="mt-1 block w-full" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm">Chief Complaint</label>
                                    <textarea v-model="form.chief_complaint" rows="3" class="mt-1 block w-full" required></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('emergency.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-red-600 text-white rounded-md">Register Patient</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
