<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    admission: Object,
});

const form = useForm({
    items: props.admission.care_plan?.items.length ? props.admission.care_plan.items : [{ nursing_diagnosis: '', goals: '', interventions: '' }],
});

const addItem = () => {
    form.items.push({ nursing_diagnosis: '', goals: '', interventions: '' });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.post(route('care-plans.store', props.admission.id));
};
</script>

<template>
    <Head title="Nursing Care Plan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nursing Care Plan: {{ admission.patient.first_name }} {{ admission.patient.last_name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div v-for="(item, index) in form.items" :key="index" class="p-4 border rounded-lg relative">
                            <button @click.prevent="removeItem(index)" type="button" class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full">&times;</button>
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Nursing Diagnosis</label>
                                    <textarea v-model="item.nursing_diagnosis" rows="3" class="mt-1 block w-full rounded-md" required></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Goals / Outcomes</label>
                                    <textarea v-model="item.goals" rows="3" class="mt-1 block w-full rounded-md" required></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Interventions</label>
                                    <textarea v-model="item.interventions" rows="3" class="mt-1 block w-full rounded-md" required></textarea>
                                </div>
                            </div>
                        </div>
                        <button @click.prevent="addItem" type="button" class="mt-4 px-4 py-2 bg-gray-200 text-sm rounded-md">Add Diagnosis</button>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end items-center">
                        <Link :href="route('nursing.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Save Care Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
