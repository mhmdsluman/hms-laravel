<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    role: 'clerk',
    speciality: '',
    password: '',
    password_confirmation: '',
});

const roles = ['admin', 'clinician', 'clerk', 'lab', 'pharmacy', 'radiology', 'patient', 'nurse', 'ot_manager'];

const submit = () => {
    form.post(route('users.store'));
};
</script>
<template>
    <Head title="Add User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New User</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                                    <input id="name" type="text" v-model="form.name" class="block mt-1 w-full rounded-md" required>
                                </div>
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                    <input id="email" type="email" v-model="form.email" class="block mt-1 w-full rounded-md" required>
                                </div>
                                <div>
                                    <label for="role" class="block font-medium text-sm text-gray-700">Role</label>
                                    <select id="role" v-model="form.role" class="block mt-1 w-full rounded-md" required>
                                        <option v-for="role in roles" :key="role" :value="role">{{ role.toUpperCase() }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="speciality" class="block font-medium text-sm text-gray-700">Speciality (e.g., Cardiology, Hematology)</label>
                                    <input id="speciality" type="text" v-model="form.speciality" class="block mt-1 w-full rounded-md">
                                </div>
                                <div>
                                    <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                                    <input id="password" type="password" v-model="form.password" class="block mt-1 w-full rounded-md" required>
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm Password</label>
                                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="block mt-1 w-full rounded-md" required>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('users.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Create User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
