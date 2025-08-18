<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    patient: Object,
    providers: Array,
});

const form = useForm({
    patient_id: props.patient.id,
    insurance_provider_id: null,
    policy_number: '',
    group_number: '',
    start_date: '',
    end_date: '',
});

const submit = () => {
    form.post(route('insurance-policies.store'));
};
</script>

<template>
    <Head title="Add Insurance Policy" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Insurance Policy for {{ patient.first_name }} {{ patient.last_name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="insurance_provider_id" class="block font-medium text-sm">Insurance Provider</label>
                                    <select id="insurance_provider_id" v-model="form.insurance_provider_id" class="mt-1 block w-full" required>
                                        <option :value="null" disabled>Select a provider</option>
                                        <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="policy_number" class="block font-medium text-sm">Policy Number</label>
                                    <input id="policy_number" type="text" v-model="form.policy_number" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label for="group_number" class="block font-medium text-sm">Group Number</label>
                                    <input id="group_number" type="text" v-model="form.group_number" class="mt-1 block w-full">
                                </div>
                                <div>
                                    <label for="start_date" class="block font-medium text-sm">Coverage Start Date</label>
                                    <input id="start_date" type="date" v-model="form.start_date" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label for="end_date" class="block font-medium text-sm">Coverage End Date</label>
                                    <input id="end_date" type="date" v-model="form.end_date" class="mt-1 block w-full" required>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('patients.show', patient.id)" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Policy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
