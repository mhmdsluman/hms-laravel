<template>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <h4 class="text-lg font-semibold text-yellow-800">AI Assistant Summary</h4>
        <div v-if="summary.length > 0">
            <ul>
                <li v-for="item in summary" :key="item.test.id">
                    This patient has a history of {{ item.direction }} {{ item.test.name }} since {{ new Date(item.first_abnormal_date).toLocaleDateString() }}.
                </li>
            </ul>
        </div>
        <div v-else>
            <p>No significant abnormal result history found.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    patientId: Number,
});

const summary = ref([]);

const fetchSummary = async () => {
    const response = await fetch(route('patients.abnormal-summary', props.patientId));
    summary.value = await response.json();
};

onMounted(() => {
    fetchSummary();
});
</script>
