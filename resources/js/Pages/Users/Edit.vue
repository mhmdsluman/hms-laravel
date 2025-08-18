<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    user: Object,
});

// A list of common medical specialities
const medicalSpecialities = [
    'Cardiology', 'Dermatology', 'Endocrinology', 'Gastroenterology',
    'Hematology', 'Infectious Disease', 'Nephrology', 'Neurology',
    'Oncology', 'Pediatrics', 'Psychiatry', 'Pulmonology', 'Radiology',
    'Rheumatology', 'General Surgery', 'Orthopedic Surgery',
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    speciality: props.user.speciality,
    password: '',
    password_confirmation: '',
});

// A ref to manage the dropdown selection
const selectedSpeciality = ref('');
// A ref for the custom speciality input
const customSpecialityInput = ref('');

// Check if the user's current speciality is in our pre-defined list
if (props.user.speciality && medicalSpecialities.includes(props.user.speciality)) {
    selectedSpeciality.value = props.user.speciality;
} else if (props.user.speciality) {
    selectedSpeciality.value = 'Other';
    customSpecialityInput.value = props.user.speciality;
}

// Watch for changes in the dropdown and update the main form data
watch(selectedSpeciality, (newValue) => {
    if (newValue !== 'Other') {
        form.speciality = newValue;
        customSpecialityInput.value = '';
    } else {
        form.speciality = customSpecialityInput.value;
    }
});

watch(customSpecialityInput, (newValue) => {
    if (selectedSpeciality.value === 'Other') {
        form.speciality = newValue;
    }
});


const roles = ['admin', 'clinician', 'clerk', 'lab', 'pharmacy', 'radiology', 'patient', 'nurse', 'ot_manager'];

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>
<template>
    <Head title="Edit User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User: {{ user.name }}</h2>
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
                                <div v-if="['clinician', 'nurse', 'lab', 'radiology', 'ot_manager', 'pharmacy'].includes(form.role)">
                                    <label for="speciality" class="block font-medium text-sm text-gray-700">Speciality</label>
                                    <select id="speciality" v-model="selectedSpeciality" class="block mt-1 w-full rounded-md">
                                        <option value="">None</option>
                                        <option v-for="speciality in medicalSpecialities" :key="speciality" :value="speciality">{{ speciality }}</option>
                                        <option value="Other">Other...</option>
                                    </select>
                                    <input v-if="selectedSpeciality === 'Other'" id="custom_speciality" type="text" v-model="customSpecialityInput" class="block mt-2 w-full rounded-md" placeholder="Please specify speciality">
                                </div>
                                <div class="border-t pt-4">
                                    <p class="text-sm text-gray-600">Leave password fields blank to keep the current password.</p>
                                </div>
                                <div>
                                    <label for="password" class="block font-medium text-sm text-gray-700">New Password</label>
                                    <input id="password" type="password" v-model="form.password" class="block mt-1 w-full rounded-md">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm New Password</label>
                                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="block mt-1 w-full rounded-md">
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('users.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md">Update User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
