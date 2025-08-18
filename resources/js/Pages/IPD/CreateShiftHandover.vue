<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    admission: Object,
});

const form = useForm({
    summary: '',
});

const submit = () => {
    form.post(route('shift-handovers.store', props.admission.id));
};
</script>

<template>
    <Head title="Shift Handover Note" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shift Handover for {{ admission.patient.first_name }} {{ admission.patient.last_name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <label for="summary" class="block font-medium text-sm text-gray-700">Handover Summary</label>
                                <textarea id="summary" v-model="form.summary" rows="10" class="block mt-1 w-full rounded-md" required placeholder="Enter a summary of the patient's status, any events during the shift, and tasks for the next nurse..."></textarea>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('nursing.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Handover</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
