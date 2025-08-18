<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    labTest: { type: Object, default: null },
    specimenTypes: Array,
    units: Array,
});

const form = useForm({
    name: props.labTest?.name || '',
    specimen_type: props.labTest?.specimen_type || '',
    units: props.labTest?.units || '',
    price: props.labTest?.price || '',
    reference_ranges: Array.isArray(props.labTest?.reference_ranges) && props.labTest.reference_ranges.length
        ? props.labTest.reference_ranges
        : [{ gender: 'All', age_min: 0, age_max: 120, range_low: '', range_high: '', critical_low: '', critical_high: '' }],
});

// State for custom inputs
const selectedSpecimenType = ref('');
const customSpecimenType = ref('');
const selectedUnit = ref('');
const customUnit = ref('');

// Initialize dropdowns based on existing data
if (props.labTest?.specimen_type && props.specimenTypes.includes(props.labTest.specimen_type)) {
    selectedSpecimenType.value = props.labTest.specimen_type;
} else if (props.labTest?.specimen_type) {
    selectedSpecimenType.value = 'Other';
    customSpecimenType.value = props.labTest.specimen_type;
}

if (props.labTest?.units && props.units.includes(props.labTest.units)) {
    selectedUnit.value = props.labTest.units;
} else if (props.labTest?.units) {
    selectedUnit.value = 'Other';
    customUnit.value = props.labTest.units;
}

// Watchers to update the main form data
watch(selectedSpecimenType, (val) => {
    form.specimen_type = val === 'Other' ? customSpecimenType.value : val;
});
watch(customSpecimenType, (val) => {
    if (selectedSpecimenType.value === 'Other') form.specimen_type = val;
});
watch(selectedUnit, (val) => {
    form.units = val === 'Other' ? customUnit.value : val;
});
watch(customUnit, (val) => {
    if (selectedUnit.value === 'Other') form.units = val;
});

const addRange = () => {
    form.reference_ranges.push({ gender: 'All', age_min: 0, age_max: 120, range_low: '', range_high: '', critical_low: '', critical_high: '' });
};
const removeRange = (index) => {
    form.reference_ranges.splice(index, 1);
};

const submit = () => {
    if (props.labTest) {
        form.put(route('test-catalogue.update', props.labTest.id));
    } else {
        form.post(route('test-catalogue.store'));
    }
};
</script>

<template>
    <Head :title="labTest ? 'Edit Lab Test' : 'Create Lab Test'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labTest ? 'Edit Lab Test' : 'Create New Lab Test' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Test Name</label>
                                <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                            </div>
                            <div>
                                <label for="price" class="block font-medium text-sm text-gray-700">Price</label>
                                <input id="price" type="number" step="0.01" v-model="form.price" class="mt-1 block w-full" required>
                            </div>
                            <div>
                                <label for="specimen_type" class="block font-medium text-sm text-gray-700">Specimen Type</label>
                                <select id="specimen_type" v-model="selectedSpecimenType" class="mt-1 block w-full" required>
                                    <option value="" disabled>Select a type</option>
                                    <option v-for="type in specimenTypes" :key="type" :value="type">{{ type }}</option>
                                    <option value="Other">Other...</option>
                                </select>
                                <input v-if="selectedSpecimenType === 'Other'" type="text" v-model="customSpecimenType" class="mt-2 block w-full" placeholder="Specify specimen type">
                            </div>
                            <div>
                                <label for="units" class="block font-medium text-sm text-gray-700">Units</label>
                                <select id="units" v-model="selectedUnit" class="mt-1 block w-full">
                                    <option value="">None</option>
                                    <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                                    <option value="Other">Other...</option>
                                </select>
                                <input v-if="selectedUnit === 'Other'" type="text" v-model="customUnit" class="mt-2 block w-full" placeholder="Specify units">
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="font-semibold">Reference Ranges</h3>
                            <div v-for="(range, index) in form.reference_ranges" :key="index" class="mt-4 p-4 border rounded-md space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm">Gender</label>
                                        <select v-model="range.gender" class="mt-1 block w-full">
                                            <option>All</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm">Age Min</label>
                                        <input type="number" v-model="range.age_min" class="mt-1 block w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm">Age Max</label>
                                        <input type="number" v-model="range.age_max" class="mt-1 block w-full">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                                    <div>
                                        <label class="block text-sm">Range Low</label>
                                        <input type="number" step="any" v-model="range.range_low" class="mt-1 block w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm">Range High</label>
                                        <input type="number" step="any" v-model="range.range_high" class="mt-1 block w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-red-600">Critical Low</label>
                                        <input type="number" step="any" v-model="range.critical_low" class="mt-1 block w-full border-red-300">
                                    </div>
                                    <div class="flex items-end space-x-2">
                                        <div class="flex-grow">
                                            <label class="block text-sm font-bold text-red-600">Critical High</label>
                                            <input type="number" step="any" v-model="range.critical_high" class="mt-1 block w-full border-red-300">
                                        </div>
                                        <button @click.prevent="removeRange(index)" type="button" class="px-3 py-2 bg-red-500 text-white text-sm rounded-md h-10">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <button @click.prevent="addRange" type="button" class="mt-4 px-4 py-2 bg-gray-200 text-sm rounded-md">Add Range</button>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end items-center">
                        <Link :href="route('test-catalogue.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">
                            {{ labTest ? 'Update Test' : 'Save Test' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
