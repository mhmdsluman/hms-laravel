<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orderItem: Object,
    surgeons: Array,
    nurses: Array,
});

const form = useForm({
    scheduled_start_time: '',
    scheduled_end_time: '',
    theater_room: 'OT-1',
    surgeon_id: null,
    anesthetist_id: null,
    scrub_nurse_id: null,
    notes: '',
});

const submit = () => {
    form.post(route('ot.store', { orderItem: props.orderItem.id }));
};
</script>

<template>
    <Head title="Schedule Procedure" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Schedule Procedure: {{ orderItem.service.name }}
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Start Time</label>
                                    <input type="datetime-local" v-model="form.scheduled_start_time" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">End Time</label>
                                    <input type="datetime-local" v-model="form.scheduled_end_time" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Theater Room</label>
                                    <input type="text" v-model="form.theater_room" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Surgeon</label>
                                    <select v-model="form.surgeon_id" class="mt-1 block w-full" required>
                                        <option :value="null" disabled>Select Surgeon</option>
                                        <option v-for="s in surgeons" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Anesthetist</label>
                                    <select v-model="form.anesthetist_id" class="mt-1 block w-full">
                                        <option :value="null">Select Anesthetist</option>
                                        <option v-for="a in surgeons" :key="a.id" :value="a.id">{{ a.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Scrub Nurse</label>
                                    <select v-model="form.scrub_nurse_id" class="mt-1 block w-full">
                                        <option :value="null">Select Scrub Nurse</option>
                                        <option v-for="n in nurses" :key="n.id" :value="n.id">{{ n.name }}</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm text-gray-700">Notes</label>
                                    <textarea v-model="form.notes" rows="3" class="mt-1 block w-full"></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('ot.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
