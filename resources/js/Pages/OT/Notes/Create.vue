<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orderItem: Object,
});

const form = useForm({
    preoperative_diagnosis: '',
    postoperative_diagnosis: '',
    procedure_description: props.orderItem.service.name,
    findings: '',
    procedure_start_time: props.orderItem.ot_schedule.scheduled_start_time,
    procedure_end_time: props.orderItem.ot_schedule.scheduled_end_time,
});

const submit = () => {
    form.post(route('operative-notes.store', { orderItem: props.orderItem.id }));
};
</script>

<template>
    <Head title="Operative Note" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Operative Note: {{ orderItem.service.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b pb-4">
                            <div>
                                <h3 class="font-semibold">Patient: {{ orderItem.order.patient.first_name }} {{ orderItem.order.patient.last_name }}</h3>
                            </div>
                            <div v-if="orderItem.operative_note">
                                <Link :href="route('anesthesia-records.create', { operativeNote: orderItem.operative_note.id })" class="px-3 py-1 bg-indigo-500 text-white text-sm rounded-md">
                                    Anesthesia Record
                                </Link>
                            </div>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium text-sm">Pre-operative Diagnosis</label>
                                    <textarea v-model="form.preoperative_diagnosis" rows="2" class="mt-1 block w-full" required></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Post-operative Diagnosis</label>
                                    <textarea v-model="form.postoperative_diagnosis" rows="2" class="mt-1 block w-full" required></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Procedure Performed</label>
                                    <textarea v-model="form.procedure_description" rows="3" class="mt-1 block w-full" required></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Findings</label>
                                    <textarea v-model="form.findings" rows="5" class="mt-1 block w-full" required></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block font-medium text-sm">Procedure Start Time</label>
                                        <input type="datetime-local" v-model="form.procedure_start_time" class="mt-1 block w-full" required>
                                    </div>
                                     <div>
                                        <label class="block font-medium text-sm">Procedure End Time</label>
                                        <input type="datetime-local" v-model="form.procedure_end_time" class="mt-1 block w-full" required>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('ot.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Note & Complete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
