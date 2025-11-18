<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import throttle from 'lodash/throttle';

const props = defineProps({
    patients: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const page = usePage();
const showSuccessModal = ref(false);
const successMessage = computed(() => page.props.flash.success);

watch(successMessage, (newValue) => {
    if (newValue) {
        showSuccessModal.value = true;
    }
});

watch(search, throttle(function (value) {
    router.get(route('patients.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Patient List" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Patient Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div class="w-1/2">
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search by name or UHID..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <Link :href="route('patients.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Register New Patient
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UHID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="patient in patients.data" :key="patient.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ patient.uhid }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.first_name }} {{ patient.last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.age_display || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.gender }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.primary_phone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('patients.show', patient.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="patients.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No patients found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination class="mt-6" :links="patients.links" />

                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal :show="showSuccessModal" @confirm="showSuccessModal = false" @cancel="showSuccessModal = false" :message="successMessage" title="Success" />
    </AuthenticatedLayout>
</template>
