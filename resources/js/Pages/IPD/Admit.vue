<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    bed: Object,
    patients: Array,
    clinicians: Array,
});

const form = useForm({
    bed_id: props.bed.id,
    patient_id: null,
    admitting_doctor_id: null,
    reason_for_admission: '',
});

const submit = () => {
    form.post(route('admissions.store'));
};
</script>
<template>
    <Head title="Admit Patient" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admit Patient to Bed {{ bed.bed_number }} ({{ bed.ward }})
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="patient_id" class="block font-medium text-sm text-gray-700">Patient</label>
                                    <select id="patient_id" v-model="form.patient_id" class="block mt-1 w-full rounded-md" required>
                                        <option :value="null" disabled>Select a patient to admit</option>
                                        <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="admitting_doctor_id" class="block font-medium text-sm text-gray-700">Admitting Doctor</label>
                                    <select id="admitting_doctor_id" v-model="form.admitting_doctor_id" class="block mt-1 w-full rounded-md" required>
                                        <option :value="null" disabled>Select a clinician</option>
                                        <option v-for="c in clinicians" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="reason_for_admission" class="block font-medium text-sm text-gray-700">Reason for Admission</label>
                                    <textarea id="reason_for_admission" v-model="form.reason_for_admission" rows="4" class="block mt-1 w-full rounded-md" required></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('ipd.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Confirm Admission</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
