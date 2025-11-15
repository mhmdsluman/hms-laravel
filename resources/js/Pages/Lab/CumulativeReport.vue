<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Chart } from 'chart.js/auto';

const props = defineProps({
    patient: Object,
    test: Object,
    history: Array,
});

const chartCanvas = ref(null);

onMounted(() => {
    const labels = props.history.map(item => new Date(item.created_at).toLocaleDateString());
    const data = props.history.map(item => item.result);

    new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: props.test.name,
                data: data,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
            }],
        },
    });
});
</script>

<template>
    <Head :title="`Cumulative Report for ${test.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cumulative Report for {{ test.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">
                            Patient: {{ patient.first_name }} {{ patient.last_name }}
                        </h3>

                        <div class="mb-6">
                            <canvas ref="chartCanvas"></canvas>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in history" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ new Date(item.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.result }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.comment }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
