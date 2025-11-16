<template>
    <div class="urine-form-container">
        <div class="tabs">
            <button @click="activeTab = 'physical'" :class="{ active: activeTab === 'physical' }">Physical Exam</button>
            <button @click="activeTab = 'chemical'" :class="{ active: activeTab === 'chemical' }">Chemical Exam</button>
            <button @click="activeTab = 'microscopic'" :class="{ active: activeTab === 'microscopic' }">Microscopic Exam</button>
        </div>

        <form @submit.prevent="submit" class="urine-form">
            <div v-show="activeTab === 'physical'">
                <!-- Physical Exam Fields -->
                <div v-for="(field, idx) in physicalFields" :key="field.key" class="form-row">
                    <label :for="field.key">{{ field.label }}</label>
                    <component :is="field.type === 'select' ? 'select' : 'input'"
                               :id="field.key"
                               v-model="form.values[field.key]"
                               :type="field.type"
                               :ref="el => fieldRefs[field.key] = el"
                               @keydown.enter.prevent="focusNext(field.key)"
                               @keydown.up.prevent="focusPrev(field.key)"
                               @keydown.down.prevent="focusNext(field.key)">
                        <template v-if="field.type === 'select'">
                            <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <div class="field-info">
                        <small>Normal: {{ displayRange(field.key) }}</small>
                        <div v-if="flags[field.key]" class="flag">{{ flags[field.key] }}</div>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'chemical'">
                <!-- Chemical Exam Fields -->
                <div v-for="(field, idx) in chemicalFields" :key="field.key" class="form-row">
                    <label :for="field.key">{{ field.label }}</label>
                    <component :is="field.type === 'select' ? 'select' : 'input'"
                               :id="field.key"
                               v-model="form.values[field.key]"
                               :type="field.type"
                               :ref="el => fieldRefs[field.key] = el"
                               @keydown.enter.prevent="focusNext(field.key)"
                               @keydown.up.prevent="focusPrev(field.key)"
                               @keydown.down.prevent="focusNext(field.key)">
                        <template v-if="field.type === 'select'">
                            <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <div class="field-info">
                        <small>Normal: {{ displayRange(field.key) }}</small>
                        <div v-if="flags[field.key]" class="flag">{{ flags[field.key] }}</div>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'microscopic'">
                <!-- Microscopic Exam Fields -->
                 <div v-for="(field, idx) in microscopicFields" :key="field.key" class="form-row">
                    <label :for="field.key">{{ field.label }}</label>
                    <component :is="field.type === 'select' ? 'select' : 'input'"
                               :id="field.key"
                               v-model="form.values[field.key]"
                               :type="field.type"
                               :ref="el => fieldRefs[field.key] = el"
                               @keydown.enter.prevent="focusNext(field.key)"
                               @keydown.up.prevent="focusPrev(field.key)"
                               @keydown.down.prevent="focusNext(field.key)">
                        <template v-if="field.type === 'select'">
                            <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <div class="field-info">
                        <small>Normal: {{ displayRange(field.key) }}</small>
                        <div v-if="flags[field.key]" class="flag">{{ flags[field.key] }}</div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="isSubmitting">Save Urinalysis</button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    patient: Object,
});

const form = useForm({
    patient_id: props.patient.id,
    values: {},
});

const activeTab = ref('physical');
const ranges = reactive({});
const flags = reactive({});
const fieldRefs = reactive({});
const isSubmitting = ref(false);

const physicalFields = [
    { key: 'color', label: 'Color', type: 'select', options: ['Yellow', 'Amber', 'Red', 'Orange', 'Brown', 'Green'] },
    { key: 'appearance', label: 'Appearance', type: 'select', options: ['Clear', 'Hazy', 'Cloudy', 'Turbid'] },
    { key: 'specific_gravity', label: 'Specific Gravity', type: 'number' },
    { key: 'ph', label: 'pH', type: 'number' },
];

const chemicalFields = [
    { key: 'protein', label: 'Protein', type: 'select', options: ['Negative', 'Trace', '1+', '2+', '3+', '4+'] },
    { key: 'glucose', label: 'Glucose', type: 'select', options: ['Negative', 'Trace', '1+', '2+', '3+', '4+'] },
    { key: 'ketones', label: 'Ketones', type: 'select', options: ['Negative', 'Trace', 'Small', 'Moderate', 'Large'] },
    { key: 'blood', label: 'Blood', type: 'select', options: ['Negative', 'Trace', 'Small', 'Moderate', 'Large'] },
    { key: 'bilirubin', label: 'Bilirubin', type: 'select', options: ['Negative', 'Small', 'Moderate', 'Large'] },
    { key: 'urobilinogen', label: 'Urobilinogen', type: 'select', options: ['Normal', 'Abnormal'] },
    { key: 'nitrite', label: 'Nitrite', type: 'select', options: ['Negative', 'Positive'] },
    { key: 'leukocyte_esterase', label: 'Leukocyte Esterase', type: 'select', options: ['Negative', 'Trace', 'Small', 'Moderate', 'Large'] },
];

const microscopicFields = [
    { key: 'rbcs', label: 'RBCs (/HPF)', type: 'number' },
    { key: 'wbcs', label: 'WBCs (/HPF)', type: 'number' },
    { key: 'epithelial_cells', label: 'Epithelial Cells (/HPF)', type: 'number' },
    { key: 'casts', label: 'Casts (/LPF)', type: 'text' },
    { key: 'crystals', label: 'Crystals (/HPF)', type: 'text' },
    { key: 'bacteria', label: 'Bacteria', type: 'select', options: ['None', 'Few', 'Moderate', 'Many'] },
    { key: 'yeast', label: 'Yeast', type: 'select', options: ['None', 'Few', 'Moderate', 'Many'] },
    { key: 'mucus', label: 'Mucus', type: 'select', options: ['None', 'Few', 'Moderate', 'Many'] },
];

const allFields = computed(() => [...physicalFields, ...chemicalFields, ...microscopicFields]);

const orderedFieldKeys = computed(() => allFields.value.map(f => f.key));

function getFieldIndex(key) {
    return orderedFieldKeys.value.indexOf(key);
}

function focusNext(currentKey) {
    const currentIndex = getFieldIndex(currentKey);
    const nextKey = orderedFieldKeys.value[currentIndex + 1];
    if (nextKey && fieldRefs[nextKey]) {
        fieldRefs[nextKey].focus();
    }
}

function focusPrev(currentKey) {
    const currentIndex = getFieldIndex(currentKey);
    const prevKey = orderedFieldKeys.value[currentIndex - 1];
    if (prevKey && fieldRefs[prevKey]) {
        fieldRefs[prevKey].focus();
    }
}

async function fetchRanges() {
    const ageInDays = (new Date() - new Date(props.patient.date_of_birth)) / (1000 * 60 * 60 * 24);
    const response = await axios.get(`/api/v1/urine-ranges?age_days=${ageInDays}&gender=${props.patient.gender}`);
    Object.assign(ranges, response.data);
}

function displayRange(key) {
    const range = ranges[key];
    if (!range) return 'N/A';
    if (range.normal_text) return range.normal_text;
    if (range.min !== null && range.max !== null) return `${range.min} - ${range.max}`;
    return 'N/A';
}

onMounted(fetchRanges);

function runClientInterpretation() {
    const values = form.values;
    const newFlags = {};

    // Specific Gravity
    if (values.specific_gravity) {
        const sg = parseFloat(values.specific_gravity);
        if (sg < 1.005) newFlags.specific_gravity = 'low';
        else if (sg > 1.030) newFlags.specific_gravity = 'high';
        else newFlags.specific_gravity = 'normal';
    }

    // pH
    if (values.ph) {
        const ph = parseFloat(values.ph);
        if (ph < 4.5 || ph > 8) newFlags.ph = 'abnormal';
        else newFlags.ph = 'normal';
    }

    // Dipstick
    const dipstick = ['protein', 'glucose', 'ketones', 'blood', 'bilirubin', 'urobilinogen', 'nitrite', 'leukocyte_esterase'];
    dipstick.forEach(p => {
        if (values[p] && values[p].toLowerCase() !== 'negative' && ranges[p]?.normal_text?.toLowerCase() === 'negative') {
            newFlags[p] = 'positive';
        }
    });

    // Microscopy
    if (values.rbcs > 2) newFlags.rbcs = 'high';
    if (values.wbcs > 2) newFlags.wbcs = 'high';

    Object.assign(flags, newFlags);
}

watch(() => form.values, runClientInterpretation, { deep: true });

const submit = () => {
    isSubmitting.value = true;
    form.post('/urine-tests', {
        onSuccess: () => {
            form.reset('values');
            Object.keys(flags).forEach(key => delete flags[key]);
        },
        onFinish: () => isSubmitting.value = false,
    });
};
</script>

<style scoped>
.tabs {
    display: flex;
    border-bottom: 1px solid #ccc;
    margin-bottom: 1rem;
}
.tabs button {
    padding: 0.5rem 1rem;
    border: none;
    background: #f0f0f0;
    cursor: pointer;
}
.tabs button.active {
    background: #fff;
    border-bottom: 2px solid blue;
}
.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}
.form-row label {
    width: 200px;
}
.form-row input, .form-row select {
    flex: 1;
}
.field-info {
    margin-left: 1rem;
    width: 200px;
}
.flag {
    color: red;
    font-weight: bold;
}
</style>
