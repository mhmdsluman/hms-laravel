<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orderItem: Object,
    technologists: Array,
});

const form = useForm({
    scheduled_time: '',
    room: '',
    machine: '',
    technologist_id: null,
    preparation_instructions: '',
});

const submit = () => {
    form.post(route('radiology-schedule.store', props.orderItem.id));
};
</script>

<template>
    <Head title="Schedule Imaging Study" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Schedule: {{ orderItem.service.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4 border-b pb-4">
                            <h3 class="font-semibold">Patient: {{ orderItem.order.patient.first_name }} {{ orderItem.order.patient.last_name }}</h3>
                            <p class="text-sm text-gray-600">UHID: {{ orderItem.order.patient.uhid }}</p>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="scheduled_time" class="block font-medium text-sm text-gray-700">Scheduled Time</label>
                                    <input id="scheduled_time" type="datetime-local" v-model="form.scheduled_time" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label for="technologist_id" class="block font-medium text-sm text-gray-700">Technologist</label>
                                    <select id="technologist_id" v-model="form.technologist_id" class="mt-1 block w-full">
                                        <option :value="null">Select a technologist</option>
                                        <option v-for="tech in technologists" :key="tech.id" :value="tech.id">{{ tech.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="room" class="block font-medium text-sm text-gray-700">Room</label>
                                    <input id="room" type="text" v-model="form.room" class="mt-1 block w-full">
                                </div>
                                <div>
                                    <label for="machine" class="block font-medium text-sm text-gray-700">Machine / Modality</label>
                                    <input id="machine" type="text" v-model="form.machine" class="mt-1 block w-full">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="preparation_instructions" class="block font-medium text-sm text-gray-700">Preparation Instructions</label>
                                    <textarea id="preparation_instructions" v-model="form.preparation_instructions" rows="3" class="mt-1 block w-full"></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('radiology.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
