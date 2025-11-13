<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    billableEvents: Array,
});

const selectedEvents = ref([]);

const form = useForm({
    event_ids: [],
});

function generateBill() {
    form.event_ids = selectedEvents.value;
    form.post(route('admin.billable-events.generate-bill'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Billable Events" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Billable Events</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-end mb-4">
                            <button @click="generateBill" class="px-4 py-2 bg-indigo-600 text-white rounded-md" :disabled="selectedEvents.length === 0">Generate Bill</button>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <input type="checkbox" @change="selectedEvents = $event.target.checked ? billableEvents.map(e => e.id) : []">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="event in billableEvents" :key="event.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="event.id" v-model="selectedEvents">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ event.patient.first_name }} {{ event.patient.last_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ event.service.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ new Date(event.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
