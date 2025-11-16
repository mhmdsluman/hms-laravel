<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    orderItem: Object,
});

const form = useForm({
    result_value: '',
    result_numeric: null,
    notes: '',
});

// Find the correct reference range based on patient demographics
const applicableRange = computed(() => {
    // Safely access nested patient data
    const patient = props.orderItem?.order?.patient;
    if (!patient || !props.orderItem.service?.referenceRanges) {
        return null;
    }

    return props.orderItem.service.referenceRanges.find(range =>
        patient.age >= range.age_min &&
        patient.age <= range.age_max &&
        (range.gender === 'All' || range.gender === patient.gender)
    );
});

// Automatically determine the flag as the user types
const resultFlag = computed(() => {
    if (form.result_numeric === null || !applicableRange.value) {
        return null;
    }
    const value = parseFloat(form.result_numeric);
    if (applicableRange.value.range_low !== null && value < applicableRange.value.range_low) return 'Low';
    if (applicableRange.value.range_high !== null && value > applicableRange.value.range_high) return 'High';
    return 'Normal';
});

const submit = () => {
    // Ensure the main result_value field is populated before submitting
    form.result_value = form.result_numeric !== null ? form.result_numeric.toString() : 'N/A';
    form.post(route('lab-results.store', props.orderItem.id));
};
</script>

<template>
    <Head title="Enter Lab Result" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Enter Result for: {{ orderItem.service.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="orderItem.order?.patient" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold">Patient: {{ orderItem.order.patient.first_name }} {{ orderItem.order.patient.last_name }}</h3>
                            <p class="text-sm text-gray-600">UHID: {{ orderItem.order.patient.uhid }} | Age: {{ orderItem.order.patient.age_display ?? (orderItem.order.patient.age ? orderItem.order.patient.age + ' yrs' : 'N/A') }} | Gender: {{ orderItem.order.patient.gender }}</p>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="result_numeric" class="block font-medium text-sm text-gray-700">Result</label>
                                    <div class="flex items-center mt-1">
                                        <input id="result_numeric" type="number" step="any" v-model="form.result_numeric" class="block w-full rounded-md shadow-sm border-gray-300">
                                        <span v-if="orderItem.service.units" class="ml-4 text-gray-600">{{ orderItem.service.units }}</span>
                                    </div>
                                    <div v-if="applicableRange" class="text-xs text-gray-500 mt-1">
                                        Reference Range: {{ applicableRange.range_low }} - {{ applicableRange.range_high }}
                                    </div>
                                     <div v-else class="text-xs text-gray-500 mt-1">
                                        No applicable reference range found for this patient.
                                    </div>
                                </div>
                                <div v-if="resultFlag">
                                    <label class="block font-medium text-sm text-gray-700">Flag</label>
                                    <span class="mt-1 px-2 inline-flex text-sm leading-5 font-semibold rounded-full"
                                          :class="{
                                            'bg-red-100 text-red-800': resultFlag === 'High' || resultFlag === 'Low',
                                            'bg-green-100 text-green-800': resultFlag === 'Normal',
                                          }">
                                        {{ resultFlag }}
                                    </span>
                                </div>
                                <div>
                                    <label for="notes" class="block font-medium text-sm text-gray-700">Notes (Optional)</label>
                                    <textarea id="notes" v-model="form.notes" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300"></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('lab.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                                    Save Result
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
