<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    template: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    name: props.template?.name || '',
    type: props.template?.type || 'Clinical Note',
    fields: props.template?.fields.map(f => ({
        label: f.label,
        type: f.type,
        options: Array.isArray(f.options) ? f.options.join(',') : '',
    })) || [{ label: '', type: 'text', options: '' }],
});

const addField = () => {
    form.fields.push({ label: '', type: 'text', options: '' });
};

const removeField = (index) => {
    form.fields.splice(index, 1);
};

const submit = () => {
    if (props.template) {
        form.put(route('templates.update', props.template.id));
    } else {
        form.post(route('templates.store'));
    }
};
</script>

<template>
    <Head :title="template ? 'Edit Template' : 'Create Template'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ template ? 'Edit Template' : 'Create New Template' }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Template Name</label>
                                <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label for="type" class="block font-medium text-sm text-gray-700">Template Type</label>
                                <input id="type" type="text" v-model="form.type" class="mt-1 block w-full" required>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="font-semibold">Template Fields</h3>
                            <div v-for="(field, index) in form.fields" :key="index" class="mt-4 p-4 border rounded-md grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label :for="'label-' + index" class="block font-medium text-sm text-gray-700">Field Label</label>
                                        <input :id="'label-' + index" type="text" v-model="field.label" class="mt-1 block w-full" required>
                                    </div>
                                    <div>
                                        <label :for="'type-' + index" class="block font-medium text-sm text-gray-700">Field Type</label>
                                        <select :id="'type-' + index" v-model="field.type" class="mt-1 block w-full">
                                            <option value="text">Text Input</option>
                                            <option value="textarea">Text Area</option>
                                            <option value="dropdown">Dropdown</option>
                                            <option value="checkbox">Checkbox</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click.prevent="removeField(index)" type="button" class="px-3 py-2 bg-red-500 text-white text-sm rounded-md">&times;</button>
                                </div>
                                <div v-if="field.type === 'dropdown'" class="md:col-span-3">
                                    <label :for="'options-' + index" class="block font-medium text-sm text-gray-700">Dropdown Options (comma-separated)</label>
                                    <input :id="'options-' + index" type="text" v-model="field.options" class="mt-1 block w-full">
                                </div>
                            </div>
                             <button @click.prevent="addField" type="button" class="mt-4 px-4 py-2 bg-gray-200 text-sm rounded-md">Add Field</button>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end items-center">
                        <Link :href="route('templates.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">
                            {{ template ? 'Update Template' : 'Save Template' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
