    <script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';

    const props = defineProps({
        appointment: Object,
    });

    const form = useForm({
        bp_systolic: '',
        bp_diastolic: '',
        heart_rate: '',
        respiratory_rate: '',
        temperature_celsius: '',
        oxygen_saturation: '',
        weight_kg: '',
        height_cm: '',
        pain_score: '',
    });

    const submit = () => {
        // Use direct URL to avoid Ziggy route-name mismatches in nested resource configurations
        form.post(`/appointments/${props.appointment.id}/vitals`);
    };
    </script>

    <template>
        <Head title="Record Vitals" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Record Vitals for {{ appointment.patient.first_name }} {{ appointment.patient.last_name }}
                </h2>
            </template>

            <div class="py-12">
                <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <form @submit.prevent="submit">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Blood Pressure -->
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700">Blood Pressure (Systolic / Diastolic)</label>
                                        <div class="flex items-center mt-1">
                                            <input type="number" v-model="form.bp_systolic" class="block w-full rounded-md shadow-sm border-gray-300">
                                            <span class="mx-2">/</span>
                                            <input type="number" v-model="form.bp_diastolic" class="block w-full rounded-md shadow-sm border-gray-300">
                                        </div>
                                    </div>
                                    <!-- Heart Rate -->
                                    <div>
                                        <label for="heart_rate" class="block font-medium text-sm text-gray-700">Heart Rate (bpm)</label>
                                        <input id="heart_rate" type="number" v-model="form.heart_rate" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Respiratory Rate -->
                                    <div>
                                        <label for="respiratory_rate" class="block font-medium text-sm text-gray-700">Respiratory Rate (breaths/min)</label>
                                        <input id="respiratory_rate" type="number" v-model="form.respiratory_rate" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Temperature -->
                                    <div>
                                        <label for="temperature_celsius" class="block font-medium text-sm text-gray-700">Temperature (°C)</label>
                                        <input id="temperature_celsius" type="number" step="0.1" v-model="form.temperature_celsius" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Oxygen Saturation -->
                                    <div>
                                        <label for="oxygen_saturation" class="block font-medium text-sm text-gray-700">Oxygen Saturation (SpO2 %)</label>
                                        <input id="oxygen_saturation" type="number" v-model="form.oxygen_saturation" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Pain Score -->
                                    <div>
                                        <label for="pain_score" class="block font-medium text-sm text-gray-700">Pain Score (0-10)</label>
                                        <input id="pain_score" type="number" v-model="form.pain_score" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Weight -->
                                    <div>
                                        <label for="weight_kg" class="block font-medium text-sm text-gray-700">Weight (kg)</label>
                                        <input id="weight_kg" type="number" step="0.01" v-model="form.weight_kg" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <!-- Height -->
                                    <div>
                                        <label for="height_cm" class="block font-medium text-sm text-gray-700">Height (cm)</label>
                                        <input id="height_cm" type="number" v-model="form.height_cm" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-6">
                                    <Link :href="route('appointments.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                    <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                                        Save Vitals
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </template>
