<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    operativeNote: Object,
});

const form = useForm({
    anesthesia_type: 'General',
    notes: '',
});

const submit = () => {
    form.post(route('anesthesia-records.store', { operativeNote: props.operativeNote.id }));
};
</script>

<template>
    <Head title="Anesthesia Record" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Anesthesia Record for {{ operativeNote.orderItem.order.patient.first_name }} {{ operativeNote.orderItem.order.patient.last_name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium text-sm">Anesthesia Type</label>
                                    <select v-model="form.anesthesia_type" class="mt-1 block w-full" required>
                                        <option>General</option>
                                        <option>Spinal</option>
                                        <option>Epidural</option>
                                        <option>Regional</option>
                                        <option>Local</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm">Anesthesia Notes</label>
                                    <textarea v-model="form.notes" rows="10" class="mt-1 block w-full" placeholder="Log vitals, medications, fluids, and other events here..."></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('operative-notes.create', { orderItem: operativeNote.order_item_id })" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Anesthesia Record</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
