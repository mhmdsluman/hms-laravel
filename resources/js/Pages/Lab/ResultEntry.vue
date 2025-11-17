<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enter Lab Results for Order #{{ orderItem.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">Patient Information</h3>
                                <p><strong>Name:</strong> {{ orderItem.order.patient.name }}</p>
                                <p><strong>Age:</strong> {{ calculateAge(orderItem.order.patient.dob) }} years</p>
                                <p><strong>Gender:</strong> {{ orderItem.order.patient.gender }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Order Details</h3>
                                <p><strong>Test Name:</strong> {{ orderItem.service.name }}</p>
                                <p><strong>Order Date:</strong> {{ new Date(orderItem.created_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Test Results</h3>
                        <form @submit.prevent="submit">
                            <div v-for="test in testsInOrder" :key="test.id" class="mb-4">
                                <InputLabel :for="`test-${test.id}`" :value="test.name" />

                                <!-- Urinalysis -->
                                <div v-if="test.name === 'Urinalysis (Dipstick)'">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel value="Nitrite" />
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].nitrite">
                                                <option>+ve</option>
                                                <option>-ve</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel value="Pus Cells" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].pus_cells" />
                                        </div>
                                        <div>
                                            <InputLabel value="RBCs" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].rbcs" />
                                        </div>
                                        <div>
                                            <InputLabel value="Epithelial Cells" />
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].epithelial_cells">
                                                <option>Few</option>
                                                <option>+</option>
                                                <option>++</option>
                                                <option>+++</option>
                                                <option>++++</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel value="Crystals" />
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].crystals_type">
                                                <option>Calcium Oxalate</option>
                                                <option>Uric Acid</option>
                                                <option>Triple Phosphate</option>
                                            </select>
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].crystals_density">
                                                <option>Nill</option>
                                                <option>Few</option>
                                                <option>+</option>
                                                <option>++</option>
                                                <option>+++</option>
                                                <option>++++</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel value="Casts" />
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].casts_type">
                                                <option>Hyaline</option>
                                                <option>Granular</option>
                                                <option>RBC</option>
                                            </select>
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].casts_density">
                                                <option>Nill</option>
                                                <option>Few</option>
                                                <option>+</option>
                                                <option>++</option>
                                                <option>+++</option>
                                                <option>++++</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel value="Bacteria" />
                                            <select class="mt-1 block w-full" v-model="form.results[test.id].bacteria">
                                                <option>Absent</option>
                                                <option>Present</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- CBC with calculated fields -->
                                <div v-else-if="test.name === 'Complete Blood Count'">
                                    <!-- This would be a more complex component for CBC -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel value="Hemoglobin" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].hgb" @input="calculateCbc" />
                                        </div>
                                        <div>
                                            <InputLabel value="Hematocrit" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].hct" @input="calculateCbc" />
                                        </div>
                                        <div>
                                            <InputLabel value="RBC" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].rbc" @input="calculateCbc" />
                                        </div>
                                        <div>
                                            <InputLabel value="MCV" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].mcv" disabled />
                                        </div>
                                        <div>
                                            <InputLabel value="MCH" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].mch" disabled />
                                        </div>
                                        <div>
                                            <InputLabel value="MCHC" />
                                            <TextInput type="text" class="mt-1 block w-full" v-model="form.results[test.id].mchc" disabled />
                                        </div>
                                    </div>
                                </div>

                                <!-- Standard test -->
                                <div v-else class="flex items-center">
                                    <TextInput :id="`test-${test.id}`" type="text" class="mt-1 block w-full" v-model="form.results[test.id]" />
                                    <span class="ml-4 text-sm text-gray-500">
                                        {{ getNormalRange(test)[0] }} - {{ getNormalRange(test)[1] }}
                                    </span>
                                </div>

                                <!-- Historical Data -->
                                <div v-if="historicalData[test.id]" class="mt-2">
                                    <h4 class="text-sm font-semibold">Historical Results</h4>
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="py-3 px-6">Date</th>
                                                <th scope="col" class="py-3 px-6">Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="result in historicalData[test.id]" :key="result.id" class="bg-white border-b">
                                                <td class="py-4 px-6">{{ new Date(result.created_at).toLocaleDateString() }}</td>
                                                <td class="py-4 px-6">{{ result.result }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputLabel class="mt-2" value="Comment" />
                                <TextInput type="text" class="mt-1 block w-full" v-model="form.comments[test.id]" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :disabled="form.processing">
                                    Save Results
                                </PrimaryButton>
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
import { computed, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    orderItem: Object,
    labTests: Array,
});

const form = useForm({
    results: {},
    comments: {},
});

const historicalData = ref({});

const testsInOrder = computed(() => {
    // This logic will be improved to handle panels correctly
    if (props.orderItem.service.name === 'Complete Blood Count') {
        return props.labTests.filter(test => test.name === 'Complete Blood Count');
    }
    return props.labTests.filter(test => test.name === props.orderItem.service.name);
});

const calculateAge = (dob) => {
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birth-Date.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};

const getNormalRange = (test) => {
    const age = calculateAge(props.orderItem.order.patient.dob);
    const gender = props.orderItem.order.patient.gender;

    if (test.reference_ranges) {
        const ranges = JSON.parse(test.reference_ranges);
        if (ranges[age]) {
            if (ranges[age][gender]) {
                return ranges[age][gender];
            } else if (ranges[age]['any']) {
                return ranges[age]['any'];
            }
        }
    }
    return [null, null];
};

const calculateCbc = () => {
    const testId = testsInOrder.value[0].id;
    const hgb = form.results[testId]?.hgb;
    const hct = form.results[testId]?.hct;
    const rbc = form.results[testId]?.rbc;

    if (hct && rbc) {
        form.results[testId].mcv = (hct / rbc) * 10;
    }
    if (hgb && rbc) {
        form.results[testId].mch = (hgb / rbc) * 10;
    }
    if (hgb && hct) {
        form.results[testId].mchc = (hgb / hct) * 100;
    }
};

const fetchHistoricalData = async () => {
    if (testsInOrder.value.length > 0) {
        const test = testsInOrder.value[0];
        const response = await fetch(route('lab.test.history', { patient: props.orderItem.order.patient.id, test: test.id }));
        historicalData.value[test.id] = await response.json();
    }
};

onMounted(() => {
    fetchHistoricalData();
    // Initialize the form results object for CBC
    if (props.orderItem.service.name === 'Complete Blood Count') {
        const testId = testsInOrder.value[0].id;
        form.results[testId] = { hgb: '', hct: '', rbc: '', mcv: '', mch: '', mchc: '' };
    }
    // Initialize the form results object for Urinalysis
    if (props.orderItem.service.name === 'Urinalysis (Dipstick)') {
        const testId = testsInOrder.value[0].id;
        form.results[testId] = {
            nitrite: '-ve',
            pus_cells: '0-1',
            rbcs: '0-1',
            epithelial_cells: 'Few',
            crystals_type: 'Calcium Oxalate',
            crystals_density: 'Nill',
            casts_type: 'Hyaline',
            casts_density: 'Nill',
            bacteria: 'Absent',
        };
    }
});

const submit = () => {
    form.post(route('lab.results.store', props.orderItem.id));
};
</script>
