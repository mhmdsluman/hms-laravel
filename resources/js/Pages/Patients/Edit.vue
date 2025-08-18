<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Country, State, City } from 'country-state-city';

const props = defineProps({
  patient: Object,
  providers: {
    type: Array,
    default: () => [],
  },
});

// pick primary insurance policy if available
const primaryPolicy = props.patient?.insurance_policies?.find(p => p.is_primary) || null;

// safe extraction of phone without country prefix
const phoneWithoutCountry = (() => {
  if (!props.patient?.primary_phone) return '';
  const cc = props.patient?.primary_phone_country_code || '';
  try {
    if (cc && props.patient.primary_phone.startsWith(cc)) {
      return props.patient.primary_phone.slice(cc.length);
    }
  } catch (e) { /* fallback below */ }
  return props.patient.primary_phone;
})();

const form = useForm({
  first_name: props.patient?.first_name || '',
  last_name: props.patient?.last_name || '',
  date_of_birth: props.patient?.date_of_birth || '',
  gender: props.patient?.gender || 'Male',
  primary_phone_country_code: props.patient?.primary_phone_country_code || '+249',
  primary_phone: phoneWithoutCountry || '',
  email: props.patient?.email || '',
  addresses: props.patient?.addresses?.length
    ? JSON.parse(JSON.stringify(props.patient.addresses))
    : [{ type: 'Home', street: '', city: '', state: '', postal_code: '', country: 'Somalia', country_iso: 'SO' }],
  // insurance fields prefilled from primary policy if present
  insurance_provider_id: primaryPolicy?.insurance_provider_id || null,
  policy_number: primaryPolicy?.policy_number || '',
  start_date: primaryPolicy?.start_date || '',
  end_date: primaryPolicy?.end_date || '',
});

// Country/State/City dropdown state
const countries = ref([]);
const states = ref([]);
const cities = ref([]);
const selectedCountry = ref(null);
const selectedState = ref(null);

// Initialize country/state/city values on mount
onMounted(() => {
  countries.value = Country.getAllCountries();

  // If the form already has a country value (from patient), try to find it
  const existingCountryName = form.addresses[0]?.country || null;
  if (existingCountryName) {
    const countryData = countries.value.find(c => c.name === existingCountryName || c.isoCode === form.addresses[0].country_iso);
    if (countryData) {
      selectedCountry.value = countryData;
      form.addresses[0].country_iso = countryData.isoCode;
      form.addresses[0].country = countryData.name;

      // populate states
      states.value = State.getStatesOfCountry(countryData.isoCode) || [];

      // try to select a state if present in the address
      const existingStateName = form.addresses[0]?.state || null;
      if (existingStateName && states.value.length) {
        const stateData = states.value.find(s => s.name === existingStateName || s.isoCode === form.addresses[0].state);
        if (stateData) {
          selectedState.value = stateData;
          // populate cities for that state
          cities.value = City.getCitiesOfState(stateData.countryCode, stateData.isoCode) || [];
        }
      }
    }
  } else {
    // prefer Somalia if nothing provided
    const somalia = countries.value.find(c => c.isoCode === 'SO' || c.name?.toLowerCase() === 'somalia');
    if (somalia) {
      selectedCountry.value = somalia;
      form.addresses[0].country_iso = somalia.isoCode;
      form.addresses[0].country = somalia.name;
      states.value = State.getStatesOfCountry(somalia.isoCode) || [];
    }
  }
});

// update states / cities when country selection changes
watch(selectedCountry, (country) => {
  if (country) {
    states.value = State.getStatesOfCountry(country.isoCode) || [];
    cities.value = [];
    form.addresses[0].country = country.name;
    form.addresses[0].country_iso = country.isoCode;
    form.addresses[0].state = '';
    form.addresses[0].city = '';
    selectedState.value = null;
  } else {
    states.value = [];
    cities.value = [];
    form.addresses[0].country = '';
    form.addresses[0].country_iso = '';
    form.addresses[0].state = '';
    form.addresses[0].city = '';
    selectedState.value = null;
  }
});

// update cities when state selection changes
watch(selectedState, (state) => {
  if (state) {
    cities.value = City.getCitiesOfState(state.countryCode, state.isoCode) || [];
    form.addresses[0].state = state.name;
    form.addresses[0].city = '';
  } else {
    cities.value = [];
    form.addresses[0].state = '';
    form.addresses[0].city = '';
  }
});

const submit = () => {
  // ensure postal_code exists (avoid empty)
  if (!form.addresses[0].postal_code) {
    form.addresses[0].postal_code = form.addresses[0].postal_code || 'N/A';
  }
  form.put(route('patients.update', props.patient.id));
};
</script>

<template>
  <Head title="Edit Patient" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Patient: {{ patient.first_name }} {{ patient.last_name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="space-y-6">

                <!-- Personal & Contact Details -->
                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
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
                    <div v-if="form.errors.primary_phone" class="text-sm text-red-600 mt-1">{{ form.errors.primary_phone }}</div>
                  </div>

                  <div class="md:col-span-2">
                    <label for="email" class="block font-medium text-sm text-gray-700">Email (Optional)</label>
                    <input id="email" type="email" v-model="form.email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                  </div>
                </fieldset>

                <!-- Address Details -->
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

                <!-- Primary Insurance -->
                <fieldset class="grid grid-cols-1 gap-6 border-t pt-6">
                  <legend class="text-lg font-medium text-gray-900">Primary Insurance</legend>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                      <label class="block font-medium text-sm">Insurance Provider</label>
                      <select v-model="form.insurance_provider_id" class="mt-1 block w-full">
                        <option :value="null">Self-Pay / No Insurance</option>
                        <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
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

              <div class="flex items-center justify-between mt-6">
                <Link :href="route('patients.show', patient.id)" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
