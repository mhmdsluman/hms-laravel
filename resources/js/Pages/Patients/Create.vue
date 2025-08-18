<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Country, State, City } from 'country-state-city';
import axios from 'axios';

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'Male',
    primary_phone_country_code: '+249',
    primary_phone: '',
    email: '',
    addresses: [{
        type: 'Home',
        street: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'Somalia',
        country_iso: 'SO',
    }],
    // Insurance fields (optional)
    insurance_provider_id: null,
    policy_number: '',
    start_date: '',
    end_date: '',
});

// Duplicate check state
const emailStatus = ref({ checking: false, isDuplicate: false, message: '' });
const phoneStatus = ref({ checking: false, isDuplicate: false, message: '' });
let debounceTimeout = null;

// Reusable duplicate check function
const checkDuplicate = (field, value, statusRef) => {
    clearTimeout(debounceTimeout);
    if (!value) {
        statusRef.value = { checking: false, isDuplicate: false, message: '' };
        return;
    }
    statusRef.value = { checking: true, isDuplicate: false, message: 'Checking...' };
    debounceTimeout = setTimeout(() => {
        axios.post(route('patients.checkDuplicate'), { field, value })
            .then(response => {
                statusRef.value.isDuplicate = response.data.is_duplicate;
                statusRef.value.message = response.data.is_duplicate
                    ? `${field.charAt(0).toUpperCase() + field.slice(1)} is already in use.`
                    : 'Available';
            })
            .catch(() => {
                statusRef.value.message = 'Error checking duplicate.';
            })
            .finally(() => {
                statusRef.value.checking = false;
            });
    }, 500);
};

// Watchers for duplicate checks
watch(() => form.email, (newValue) => checkDuplicate('email', newValue, emailStatus));
watch(() => form.primary_phone, (newValue) => {
    const fullPhone = form.primary_phone_country_code + ltrim(newValue, '0');
    checkDuplicate('primary_phone', fullPhone, phoneStatus);
});

// Phone helper
function ltrim(str, chars) {
    return str.replace(new RegExp(`^[${chars}]+`), '');
}

// Country/State/City dropdown state
const countries = ref([]);
const states = ref([]);
const cities = ref([]);
const selectedCountry = ref(null);
const selectedState = ref(null);

onMounted(() => {
    countries.value = Country.getAllCountries();

    // Try to restore last selected country from localStorage
    const lastCountry = localStorage.getItem('lastSelectedCountry');

    if (lastCountry) {
        selectedCountry.value = countries.value.find(c => c.isoCode === lastCountry) || null;
    }

    // If no last country, prefer Somalia (SO) if present, otherwise keep null
    if (!selectedCountry.value) {
        const somalia = countries.value.find(c => c.isoCode === 'SO' || c.name?.toLowerCase() === 'somalia');
        if (somalia) {
            selectedCountry.value = somalia;
            // ensure form.addresses is in sync
            form.addresses[0].country_iso = somalia.isoCode;
            form.addresses[0].country = somalia.name;
        }
    } else {
        // ensure form.addresses is in sync when restored
        form.addresses[0].country_iso = selectedCountry.value.isoCode;
        form.addresses[0].country = selectedCountry.value.name;
    }
});

watch(selectedCountry, (country) => {
    if (country) {
        states.value = State.getStatesOfCountry(country.isoCode);
        cities.value = [];
        form.addresses[0].country_iso = country.isoCode;
        form.addresses[0].country = country.name;
        form.addresses[0].state = '';
        form.addresses[0].city = '';
        selectedState.value = null;
        localStorage.setItem('lastSelectedCountry', country.isoCode);
    } else {
        states.value = [];
        cities.value = [];
        form.addresses[0].country_iso = '';
        form.addresses[0].country = '';
    }
});

watch(selectedState, (state) => {
    if (state) {
        cities.value = City.getCitiesOfState(state.countryCode, state.isoCode);
        form.addresses[0].state = state.name;
        form.addresses[0].city = '';
    } else {
        cities.value = [];
        form.addresses[0].state = '';
    }
});

// Submit handler
const submit = () => {
    form.post(route('patients.store'), {
        onSuccess: () => {
            // Reset to initial defaults
            form.reset();
            // If we had a preferred country, re-apply it to the form after reset
            if (selectedCountry.value) {
                form.addresses[0].country_iso = selectedCountry.value.isoCode;
                form.addresses[0].country = selectedCountry.value.name;
            } else {
                // default to Somalia if available
                const somalia = countries.value.find(c => c.isoCode === 'SO');
                if (somalia) {
                    selectedCountry.value = somalia;
                    form.addresses[0].country_iso = somalia.isoCode;
                    form.addresses[0].country = somalia.name;
                }
            }
        },
    });
};
</script>

<template>
    <Head :title="$t('Patient Registration')" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Patient Registration') }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">

                                <!-- Personal Info -->
                                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="first_name" class="block font-medium text-sm text-gray-700">First Name</label>
                                        <input id="first_name" type="text" v-model="form.first_name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <div v-if="form.errors.first_name" class="text-sm text-red-600 mt-1">{{ form.errors.first_name }}</div>
                                    </div>
                                    <div>
                                        <label for="last_name" class="block font-medium text-sm text-gray-700">Last Name</label>
                                        <input id="last_name" type="text" v-model="form.last_name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <div v-if="form.errors.last_name" class="text-sm text-red-600 mt-1">{{ form.errors.last_name }}</div>
                                    </div>
                                    <div>
                                        <label for="date_of_birth" class="block font-medium text-sm text-gray-700">Date of Birth</label>
                                        <input id="date_of_birth" type="date" v-model="form.date_of_birth" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <div v-if="form.errors.date_of_birth" class="text-sm text-red-600 mt-1">{{ form.errors.date_of_birth }}</div>
                                    </div>
                                    <div>
                                        <label for="gender" class="block font-medium text-sm text-gray-700">Gender</label>
                                        <select id="gender" v-model="form.gender" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                        <div v-if="form.errors.gender" class="text-sm text-red-600 mt-1">{{ form.errors.gender }}</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="primary_phone" class="block font-medium text-sm text-gray-700">Primary Phone Number</label>
                                        <div class="flex mt-1">
                                            <input type="text" v-model="form.primary_phone_country_code" class="w-1/4 rounded-l-md shadow-sm border-gray-300">
                                            <input id="primary_phone" type="tel" v-model="form.primary_phone" class="w-3/4 rounded-r-md shadow-sm border-gray-300" required>
                                        </div>
                                        <div v-if="phoneStatus.message" :class="{'text-red-600': phoneStatus.isDuplicate, 'text-green-600': !phoneStatus.isDuplicate && form.primary_phone}" class="text-xs mt-1">{{ phoneStatus.message }}</div>
                                        <div v-if="form.errors.primary_phone" class="text-sm text-red-600 mt-1">{{ form.errors.primary_phone }}</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="email" class="block font-medium text-sm text-gray-700">Email (Optional)</label>
                                        <input id="email" type="email" v-model="form.email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <div v-if="emailStatus.message" :class="{'text-red-600': emailStatus.isDuplicate, 'text-green-600': !emailStatus.isDuplicate && form.email}" class="text-xs mt-1">{{ emailStatus.message }}</div>
                                        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                                    </div>
                                </fieldset>

                                <!-- Address -->
                                <fieldset class="grid grid-cols-1 gap-6 border-t pt-6">
                                    <legend class="text-lg font-medium text-gray-900">Address</legend>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block font-medium text-sm">Country</label>
                                            <select v-model="selectedCountry" class="mt-1 block w-full" required>
                                                <option :value="null" disabled>Select a country</option>
                                                <option v-for="c in countries" :key="c.isoCode" :value="c">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">State / Province</label>
                                            <select v-model="selectedState" class="mt-1 block w-full" :disabled="!states.length">
                                                <option :value="null">Select a state</option>
                                                <option v-for="s in states" :key="s.isoCode" :value="s">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">City</label>
                                            <select v-model="form.addresses[0].city" class="mt-1 block w-full" :disabled="!cities.length" required>
                                                <option value="" disabled>Select a city</option>
                                                <option v-for="city in cities" :key="city.name" :value="city.name">{{ city.name }}</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block font-medium text-sm">Street Address</label>
                                            <input type="text" v-model="form.addresses[0].street" class="mt-1 block w-full" required>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">Postal Code (Optional)</label>
                                            <input type="text" v-model="form.addresses[0].postal_code" class="mt-1 block w-full">
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Insurance Details -->
                                <fieldset class="grid grid-cols-1 gap-6 border-t pt-6">
                                     <legend class="text-lg font-medium text-gray-900">Insurance Details (Optional)</legend>
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                         <div class="md:col-span-2">
                                            <label class="block font-medium text-sm">Insurance Provider</label>
                                            <select v-model="form.insurance_provider_id" class="mt-1 block w-full">
                                                <option :value="null">Self-Pay / No Insurance</option>
                                                <option v-for="provider in props.providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                                            </select>
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Policy Number</label>
                                            <input type="text" v-model="form.policy_number" class="mt-1 block w-full">
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Coverage Start Date</label>
                                            <input type="date" v-model="form.start_date" class="mt-1 block w-full">
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Coverage End Date</label>
                                            <input type="date" v-model="form.end_date" class="mt-1 block w-full">
                                        </div>
                                     </div>
                                </fieldset>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" :disabled="form.processing || emailStatus.isDuplicate || phoneStatus.isDuplicate" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                                    Register Patient
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
