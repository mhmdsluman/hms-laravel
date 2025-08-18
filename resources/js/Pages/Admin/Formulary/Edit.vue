<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    medication: Object,
});

const form = useForm({
    formulary_status: props.medication.formulary_status || 'Formulary',
    is_controlled_substance: props.medication.is_controlled_substance || false,
});

const statuses = ['Formulary', 'Non-Formulary', 'Restricted'];

const submit = () => {
    form.put(route('formulary.update', props.medication.id));
};
</script>
<template>
    <Head title="Edit Formulary Status" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Formulary Status</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <p class="font-medium">Medication:</p>
                                <p class="text-lg font-semibold">{{ medication.name }}</p>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="formulary_status" class="block font-medium text-sm text-gray-700">Formulary Status</label>
                                    <select id="formulary_status" v-model="form.formulary_status" class="block mt-1 w-full rounded-md" required>
                                        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                                    </select>
                                </div>
                                <div class="border-t pt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.is_controlled_substance" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500" />
                                        <span class="ml-2 text-sm text-gray-600 font-bold">Is Controlled Substance</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('formulary.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
