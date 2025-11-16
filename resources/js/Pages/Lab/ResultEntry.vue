<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enter Lab Results for {{ orderItem.service.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- ... patient and order details ... -->
                    </div>

                    <div class="p-6 border-t border-gray-200">
                        <form @submit.prevent="submit">
                            <div v-for="test in testsInPanel" :key="test.id" class="grid grid-cols-4 gap-4 items-center mb-4 pb-4 border-b">
                                <InputLabel :for="`test-${test.id}`" :value="test.name" class="col-span-1 font-bold" />

                                <TextInput :id="`test-${test.id}`" type="text" class="mt-1 block w-full col-span-1"
                                           v-model="form.results[test.id]"
                                           :class="{ 'border-red-500 ring-red-500': isAbnormal(test.id) }"
                                           @input="() => checkFlag(test.id)" />

                                <div class="col-span-1 text-sm text-gray-600">
                                    <span v-if="test.units">{{ test.units }}</span>
                                    <div v-if="getNormalRange(test)">
                                        Range: {{ getNormalRange(test) }}
                                    </div>
                                </div>

                                <div class="col-span-1">
                                    <span v-if="flags[test.id]" class="font-semibold" :class="getFlagClass(flags[test.id])">
                                        {{ flags[test.id] }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :disabled="form.processing">Save Results</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { LabResultFlaggingService } from '@/Services/LabResultFlaggingService';

const props = defineProps({
    orderItem: Object,
});

const form = useForm({
    results: {},
});

const flags = reactive({});

const testsInPanel = computed(() => {
    if (props.orderItem.service.is_panel) {
        return props.orderItem.service.tests;
    }
    return [props.orderItem.service];
});

const getNormalRange = (test) => {
    if (!test.reference_ranges || test.reference_ranges.length === 0) return null;
    const range = test.reference_ranges[0]; // Simplified for now
    if (range.normal_text) return range.normal_text;
    if (range.range_low !== null && range.range_high !== null) return `${range.range_low} - ${range.range_high}`;
    return null;
};

const checkFlag = (testId) => {
    const test = testsInPanel.value.find(t => t.id === testId);
    const result = form.results[testId];
    const patient = props.orderItem.order.patient;
    const ageInDays = Math.floor((new Date() - new Date(patient.date_of_birth)) / (1000 * 60 * 60 * 24));

    flags[testId] = LabResultFlaggingService.getFlag(test, result, ageInDays, patient.gender);
};

const isAbnormal = (testId) => {
    const flag = flags[testId];
    return flag === 'Low' || flag === 'High' || flag === 'Abnormal' || flag === 'Present';
};

const getFlagClass = (flag) => {
    if (isAbnormal(flag)) {
        return 'text-red-600';
    }
    if (flag === 'Normal') {
        return 'text-green-600';
    }
    return 'text-gray-500';
};

const submit = () => {
    const resultsPayload = Object.entries(form.results).map(([testId, result]) => ({
        service_id: testId,
        result: result,
    }));

    useForm({ results: resultsPayload }).post(route('lab.results.store', props.orderItem.id));
};
</script>
