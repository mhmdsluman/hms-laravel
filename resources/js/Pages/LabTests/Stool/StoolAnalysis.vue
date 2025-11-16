<template>
    <div class="stool-analysis-form">
        <div class="tabs">
            <button @click="activeTab = 'macroscopic'" :class="{ active: activeTab === 'macroscopic' }">Macroscopic</button>
            <button @click="activeTab = 'microscopic'" :class="{ active: activeTab === 'microscopic' }">Microscopic</button>
            <button @click="activeTab = 'chemical'" :class="{ active: activeTab === 'chemical' }">Chemical</button>
        </div>

        <form @submit.prevent="submit">
            <div v-show="activeTab === 'macroscopic'">
                <div v-for="(param, idx) in macroscopicParams" :key="param.id" class="form-row">
                    <label :for="`param-${param.id}`">{{ param.name }}</label>
                    <component :is="getComponentType(param)"
                               :id="`param-${param.id}`"
                               v-model="form.results[param.id]"
                               :type="param.input_type === 'number' ? 'number' : 'text'"
                               :ref="el => fieldRefs[param.id] = el"
                               @keydown.enter.prevent="focusNext(param.id)"
                               @keydown.up.prevent="focusPrev(param.id)"
                               @keydown.down.prevent="focusNext(param.id)"
                               :class="{ 'is-abnormal': flags[param.id] === 'Abnormal' }">
                        <template v-if="param.input_type === 'select'">
                            <option v-for="option in param.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <small v-if="flags[param.id] === 'Abnormal'">Out of normal range</small>
                </div>
            </div>
            <div v-show="activeTab === 'microscopic'">
                <div v-for="(param, idx) in microscopicParams" :key="param.id" class="form-row">
                    <label :for="`param-${param.id}`">{{ param.name }}</label>
                    <component :is="getComponentType(param)"
                               :id="`param-${param.id}`"
                               v-model="form.results[param.id]"
                               :type="param.input_type === 'number' ? 'number' : 'text'"
                               :ref="el => fieldRefs[param.id] = el"
                               @keydown.enter.prevent="focusNext(param.id)"
                               @keydown.up.prevent="focusPrev(param.id)"
                               @keydown.down.prevent="focusNext(param.id)"
                               :class="{ 'is-abnormal': flags[param.id] === 'Abnormal' }">
                        <template v-if="param.input_type === 'select'">
                            <option v-for="option in param.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <small v-if="flags[param.id] === 'Abnormal'">Out of normal range</small>
                </div>
            </div>
            <div v-show="activeTab === 'chemical'">
                <div v-for="(param, idx) in chemicalParams" :key="param.id" class="form-row">
                    <label :for="`param-${param.id}`">{{ param.name }}</label>
                    <component :is="getComponentType(param)"
                               :id="`param-${param.id}`"
                               v-model="form.results[param.id]"
                               :type="param.input_type === 'number' ? 'number' : 'text'"
                               :ref="el => fieldRefs[param.id] = el"
                               @keydown.enter.prevent="focusNext(param.id)"
                               @keydown.up.prevent="focusPrev(param.id)"
                               @keydown.down.prevent="focusNext(param.id)"
                               :class="{ 'is-abnormal': flags[param.id] === 'Abnormal' }">
                        <template v-if="param.input_type === 'select'">
                            <option v-for="option in param.options" :key="option" :value="option">{{ option }}</option>
                        </template>
                    </component>
                    <small v-if="flags[param.id] === 'Abnormal'">Out of normal range</small>
                </div>
            </div>
            <button type="submit" :disabled="form.processing">Save</button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    parameters: Array,
});

const form = useForm({
    order_id: props.order.id,
    results: {},
});

const activeTab = ref('macroscopic');
const flags = reactive({});
const fieldRefs = reactive({});

const macroscopicParams = computed(() => props.parameters.filter(p => p.category === 'macroscopic'));
const microscopicParams = computed(() => props.parameters.filter(p => p.category === 'microscopic'));
const chemicalParams = computed(() => props.parameters.filter(p => p.category === 'chemical'));

const allFields = computed(() => [...macroscopicParams.value, ...microscopicParams.value, ...chemicalParams.value]);
const orderedFieldIds = computed(() => allFields.value.map(p => p.id));

function getFieldIndex(paramId) {
    return orderedFieldIds.value.indexOf(paramId);
}

function focusNext(currentId) {
    const currentIndex = getFieldIndex(currentId);
    const nextId = orderedFieldIds.value[currentIndex + 1];
    if (nextId && fieldRefs[nextId]) {
        fieldRefs[nextId].focus();
    }
}

function focusPrev(currentId) {
    const currentIndex = getFieldIndex(currentId);
    const prevId = orderedFieldIds.value[currentIndex - 1];
    if (prevId && fieldRefs[prevId]) {
        fieldRefs[prevId].focus();
    }
}

function getComponentType(param) {
    return param.input_type === 'select' ? 'select' : 'input';
}

watch(() => form.results, (newResults) => {
    for (const paramId in newResults) {
        const param = props.parameters.find(p => p.id == paramId);
        if (param && param.reference_range) {
            const value = newResults[paramId];
            const range = param.reference_range;
            let isAbnormal = false;
            if (range.normal_text) {
                isAbnormal = value.toLowerCase() !== range.normal_text.toLowerCase();
            } else if (isFinite(value)) {
                if (range.range_low !== null && value < range.range_low) isAbnormal = true;
                if (range.range_high !== null && value > range.range_high) isAbnormal = true;
            }
            flags[paramId] = isAbnormal ? 'Abnormal' : 'Normal';
        }
    }
}, { deep: true });

const submit = () => {
    form.post(route('stool-tests.store'));
};
</script>

<style scoped>
.is-abnormal {
    border-color: red;
}
</style>
