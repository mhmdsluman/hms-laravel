<template>
    <div>
        <form @submit.prevent="submit">
            <div v-for="(field, idx) in fields" :key="field.key" class="form-row">
                <label :for="field.key">{{ field.label }} <small>{{ field.unit }}</small></label>

                <input
                    :id="field.key"
                    :ref="el => setRef(el, idx)"
                    v-model.number="form.values[field.key]"
                    @keydown.enter.prevent="focusNext(idx)"
                    @keydown.arrow-down.prevent="focusNext(idx)"
                    @keydown.arrow-up.prevent="focusPrev(idx)"
                    @input="onInputChange"
                    type="number"
                    step="any"
                    autocomplete="off"
                />

                <div class="small">Range: {{ ranges[field.key]?.min ?? '-' }} - {{ ranges[field.key]?.max ?? '-' }}</div>
                <div v-if="calculated[field.key.replace('_pct', '_abs')]" class="muted">
                    Calculated Absolute: {{ calculated[field.key.replace('_pct', '_abs')] }} {{ field.unit.replace('%', '10^9/L') }}
                </div>
            </div>

            <div v-if="Object.keys(calculatedOnlyFields).length" class="calculated-section">
                <h3>Calculated Indices</h3>
                <div v-for="(value, key) in calculatedOnlyFields" :key="key" class="calculated-row">
                    <span>{{ getFieldLabel(key) }}:</span>
                    <span>{{ value }} {{ getFieldUnit(key) }}</span>
                    <div class="small">Range: {{ ranges[key]?.min ?? '-' }} - {{ ranges[key]?.max ?? '-' }}</div>
                </div>
            </div>

            <button type="submit" :disabled="isSubmitting">Save CBC</button>
            <div v-if="statusMessage" :class="{ 'success-message': isSuccess, 'error-message': !isSuccess }">
                {{ statusMessage }}
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
});

const fields = [
  { key: 'wbc', label: 'WBC', unit: '10^9/L' },
  { key: 'neutrophils_pct', label: 'Neutrophils (%)', unit: '%' },
  { key: 'lymphocytes_pct', label: 'Lymphocytes (%)', unit: '%' },
  { key: 'monocytes_pct', label: 'Monocytes (%)', unit: '%' },
  { key: 'eosinophils_pct', label: 'Eosinophils (%)', unit: '%' },
  { key: 'basophils_pct', label: 'Basophils (%)', unit: '%' },
  { key: 'rbc', label: 'RBC', unit: '10^12/L' },
  { key: 'hb', label: 'Hb', unit: 'g/dL' },
  { key: 'hct', label: 'Hct', unit: '%' },
  { key: 'rdw_cv', label: 'RDW-CV', unit: '%' },
  { key: 'plt', label: 'Platelets', unit: '10^9/L' },
  { key: 'mpv', label: 'MPV', unit: 'fL' },
];

const calculatedFieldsMeta = {
    mcv: { label: 'MCV', unit: 'fL' },
    mch: { label: 'MCH', unit: 'pg' },
    mchc: { label: 'MCHC', unit: 'g/dL' },
    pct: { label: 'PCT', unit: '%' },
};

const form = useForm({
    patient_id: props.patient.id,
    values: {},
});

const calculated = reactive({});
const ranges = reactive({});
const isSubmitting = ref(false);
const statusMessage = ref('');
const isSuccess = ref(false);

const inputRefs = ref([]);
const setRef = (el, idx) => { inputRefs.value[idx] = el; };

const focusNext = (idx) => {
    const next = idx + 1;
    if (inputRefs.value[next]) inputRefs.value[next].focus();
};
const focusPrev = (idx) => {
    const prev = idx - 1;
    if (inputRefs.value[prev]) inputRefs.value[prev].focus();
};

const onInputChange = () => {
    runClientCalc();
};

function runClientCalc() {
    const values = form.values;
    // WBC absolute
    if (values.wbc) {
        ['neutrophils', 'lymphocytes', 'monocytes', 'eosinophils', 'basophils'].forEach(name => {
            const pctKey = name + '_pct';
            const absKey = name + '_abs';
            if (values[pctKey] != null) {
                calculated[absKey] = +((values[pctKey] / 100.0) * values.wbc).toFixed(3);
            }
        });
    }
    // RBC indices
    if (values.rbc && values.hct) calculated.mcv = +((values.hct * 10.0) / values.rbc).toFixed(2);
    if (values.rbc && values.hb) calculated.mch = +((values.hb * 10.0) / values.rbc).toFixed(2);
    if (values.hct && values.hb) calculated.mchc = +((values.hb * 100.0) / values.hct).toFixed(2);
    // Platelet indices
    if (values.plt && values.mpv) calculated.pct = +(((values.plt * values.mpv) / 10000.0)).toFixed(4);
}

const calculatedOnlyFields = computed(() => {
    const display = {};
    for (const key in calculated) {
        if (!fields.some(f => f.key === key) && calculatedFieldsMeta[key]) {
            display[key] = calculated[key];
        }
    }
    return display;
});

const getFieldLabel = (key) => calculatedFieldsMeta[key]?.label || key;
const getFieldUnit = (key) => calculatedFieldsMeta[key]?.unit || '';

const fetchRanges = async () => {
    try {
        const ageInDays = Math.floor((new Date() - new Date(props.patient.date_of_birth)) / (1000 * 60 * 60 * 24));
        const response = await axios.get(`/api/v1/cbc-ranges?age_days=${ageInDays}&gender=${props.patient.gender}`);
        Object.assign(ranges, response.data);
    } catch (error) {
        console.error("Failed to fetch CBC ranges:", error);
    }
};

onMounted(() => {
    fetchRanges();
});

const submit = async () => {
    isSubmitting.value = true;
    statusMessage.value = '';
    try {
        await axios.post('/cbc-tests', form.data());
        isSuccess.value = true;
        statusMessage.value = 'CBC Test saved successfully.';
        form.reset('values');
        Object.keys(calculated).forEach(key => delete calculated[key]);
    } catch (error) {
        isSuccess.value = false;
        if (error.response && error.response.data.errors) {
            statusMessage.value = Object.values(error.response.data.errors).flat().join(' ');
        } else {
            statusMessage.value = 'An unexpected error occurred.';
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.form-row, .calculated-row {
    margin-bottom: 1rem;
}
.small {
    font-size: 0.8rem;
    color: #666;
}
.muted {
    color: #888;
}
.calculated-section {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}
.success-message {
    color: green;
}
.error-message {
    color: red;
}
</style>
