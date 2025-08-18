<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orderItem: Object,
});

const form = useForm({
    report_text: '',
    study_instance_uid: '',
});

const submit = () => {
    form.post(route('radiology-reports.store', props.orderItem.id));
};
</script>

<template>
    <Head title="Add Radiology Report" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Report for: {{ orderItem.service.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4 border-b pb-4">
                            <h3 class="font-semibold">Patient: {{ orderItem.order.patient.first_name }} {{ orderItem.order.patient.last_name }}</h3>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="study_instance_uid" class="block font-medium text-sm text-gray-700">Study Instance UID (for DICOM)</label>
                                    <input id="study_instance_uid" type="text" v-model="form.study_instance_uid" class="block mt-1 w-full rounded-md">
                                </div>
                                <div>
                                    <label for="report_text" class="block font-medium text-sm text-gray-700">Report Findings</label>
                                    <textarea id="report_text" v-model="form.report_text" rows="10" class="block mt-1 w-full rounded-md" required></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('radiology.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Report & Complete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
