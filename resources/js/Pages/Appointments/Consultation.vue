<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOption,
  ComboboxOptions,
} from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';
import axios from 'axios';

const props = defineProps({
  appointment: Object,
  services: Object,
  template: Object,
  orderSets: {
    type: Array,
    default: () => [],
  },
  orderedServiceIds: {
    type: Array,
    default: () => [],
  },
});

const activeTab = ref('notes');

// --- Diagnosis Search (ICD-10) ---
const diagnosisQuery = ref('');
const diagnosisCodes = ref([]);
const selectedDiagnosis = ref(props.appointment?.clinical_note?.provisional_diagnosis_code ?? null);

watch(diagnosisQuery, (newQuery) => {
  // Small guard: don't request on empty query
  if (!newQuery || newQuery.length < 2) {
    diagnosisCodes.value = [];
    return;
  }

  // Live lookup - replace with debounced request if necessary
  axios.get(route('diagnosis-codes.index', { query: newQuery }))
    .then(response => {
      diagnosisCodes.value = Array.isArray(response.data) ? response.data : [];
    })
    .catch(() => {
      diagnosisCodes.value = [];
    });
});

// Notes form (includes provisional diagnosis id)
const notesForm = useForm({
  template_id: props.template?.id ?? null,
  fields: {},
  provisional_diagnosis_code_id: props.appointment?.clinical_note?.provisional_diagnosis_code_id ?? null,
});

// Sync selectedDiagnosis into notesForm
watch(selectedDiagnosis, (newVal) => {
  notesForm.provisional_diagnosis_code_id = newVal ? newVal.id : null;
});

// Build initial fields from template + existing clinical note data
onMounted(() => {
  const initialFields = {};
  if (props.template && Array.isArray(props.template.fields)) {
    props.template.fields.forEach(field => {
      const existingData = props.appointment?.clinical_note?.data?.find(d => d.template_field_id === field.id);
      initialFields[field.id] = existingData ? existingData.value : '';
    });
  }
  notesForm.fields = initialFields;
});

// Orders form
const ordersForm = useForm({
  service_ids: [],
});

// Apply an order set (adds all service IDs, avoiding duplicates)
const applyOrderSet = (orderSet) => {
  if (!orderSet || !Array.isArray(orderSet.items)) return;
  const serviceIds = orderSet.items.map(i => i.service_id).filter(Boolean);
  ordersForm.service_ids = [...new Set([...(ordersForm.service_ids || []), ...serviceIds])];
};

// Check if service already ordered
const isServiceOrdered = (serviceId) => {
  if (!props.orderedServiceIds) return false;
  return props.orderedServiceIds.includes(serviceId);
};

// Count pending services in an order set
const pendingCountForSet = (orderSet) => {
  if (!orderSet || !Array.isArray(orderSet.items)) return 0;
  return orderSet.items.filter(i => isServiceOrdered(i.service_id)).length;
};

// Submit handlers
const submitNotes = () => {
  notesForm.post(route('clinical-notes.store', props.appointment.id));
};

const submitOrders = () => {
  ordersForm.post(route('orders.store', props.appointment.id), {
    onSuccess: () => ordersForm.reset(),
  });
};

// Utility
const formatDateTime = (value) => {
  if (!value) return '';
  return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};
</script>

<template>
  <Head title="Consultation" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Consultation: {{ appointment.patient.first_name }} {{ appointment.patient.last_name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Left Column: Patient Info & Vitals -->
          <div class="lg:col-span-1 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
              <h3 class="font-semibold text-lg mb-2">Patient Details</h3>
              <p><strong>UHID:</strong> {{ appointment.patient.uhid }}</p>
              <p class="mt-2 text-sm text-gray-600">Age: {{ appointment.patient.age_display ?? 'N/A' }}</p>
              <p class="mt-1 text-sm text-gray-600">Gender: {{ appointment.patient.gender ?? 'N/A' }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
              <h3 class="font-semibold text-lg mb-2">Vitals History</h3>
              <div v-if="appointment.patient.vitals && appointment.patient.vitals.length > 0" class="space-y-2">
                <div v-for="vital in appointment.patient.vitals.slice(0, 3)" :key="vital.id" class="text-sm border-b pb-2">
                  <p class="font-bold">{{ formatDateTime(vital.created_at) }}</p>
                  <p>BP: {{ vital.bp_systolic ?? '—' }}/{{ vital.bp_diastolic ?? '—' }} | HR: {{ vital.heart_rate ?? '—' }}</p>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500">No vitals recorded.</div>
            </div>
          </div>

          <!-- Center Column: Tabs for Notes & Orders -->
          <div class="lg:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-6 px-6">
                <button
                  @click="activeTab = 'notes'"
                  :class="['py-4 px-1 border-b-2 font-medium text-sm', activeTab === 'notes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500']">
                  Clinical Notes
                </button>
                <button
                  @click="activeTab = 'orders'"
                  :class="['py-4 px-1 border-b-2 font-medium text-sm', activeTab === 'orders' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500']">
                  Place Orders
                </button>
              </nav>
            </div>

            <!-- Notes Tab Content -->
            <div v-show="activeTab === 'notes'" class="p-6">
              <form @submit.prevent="submitNotes">
                <div v-if="template" class="space-y-4">
                  <div v-for="field in template.fields" :key="field.id">
                    <label :for="'field-' + field.id" class="block font-medium text-sm text-gray-700">{{ field.label }}</label>

                    <input
                      v-if="field.type === 'text'"
                      :id="'field-' + field.id"
                      type="text"
                      v-model="notesForm.fields[field.id]"
                      class="mt-1 block w-full rounded-md shadow-sm border-gray-300"
                    />

                    <textarea
                      v-if="field.type === 'textarea'"
                      :id="'field-' + field.id"
                      v-model="notesForm.fields[field.id]"
                      rows="5"
                      class="mt-1 block w-full rounded-md shadow-sm border-gray-300"
                    ></textarea>

                    <select
                      v-if="field.type === 'dropdown'"
                      :id="'field-' + field.id"
                      v-model="notesForm.fields[field.id]"
                      class="mt-1 block w-full rounded-md shadow-sm border-gray-300"
                    >
                      <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                    </select>
                  </div>

                  <!-- Diagnosis Search Box (ICD-10 Combobox) -->
                  <div>
                    <label for="diagnosis" class="block font-medium text-sm text-gray-700">Provisional Diagnosis (ICD-10)</label>
                    <Combobox v-model="selectedDiagnosis" as="div" class="relative mt-1">
                      <div class="relative">
                        <ComboboxInput
                          class="w-full rounded-md border-gray-300 px-3 py-2"
                          @input="diagnosisQuery = $event.target.value"
                          :display-value="(code) => code ? `${code.code} - ${code.description}` : ''"
                          placeholder="Search diagnosis code..."
                        />
                        <ComboboxButton class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-none">
                          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" />
                        </ComboboxButton>

                        <ComboboxOptions v-if="diagnosisCodes.length > 0" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg">
                          <ComboboxOption v-for="code in diagnosisCodes" :key="code.id" :value="code" v-slot="{ active, selected }">
                            <li :class="['relative cursor-default select-none py-2 pl-3 pr-9', active ? 'bg-blue-600 text-white' : 'text-gray-900']">
                              <span :class="['block truncate', selected ? 'font-semibold' : '']">{{ code.code }} - {{ code.description }}</span>
                              <span v-if="selected" :class="['absolute inset-y-0 right-0 flex items-center pr-4', active ? 'text-white' : 'text-blue-600']">
                                <CheckIcon class="h-5 w-5" />
                              </span>
                            </li>
                          </ComboboxOption>
                        </ComboboxOptions>
                      </div>
                    </Combobox>
                  </div>
                </div>

                <div v-else class="text-center text-gray-500 py-8">
                  <p>No "Clinical Note" template found.</p>
                  <p class="text-sm">Please create one in the Admin Template Builder.</p>
                </div>

                <div v-if="template" class="flex justify-end mt-6">
                  <button type="submit" :disabled="notesForm.processing" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Notes & Complete
                  </button>
                </div>
              </form>
            </div>

            <!-- Orders Tab Content -->
            <div v-show="activeTab === 'orders'" class="p-6">
              <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ $page.props.flash.success }}
              </div>

              <div v-if="ordersForm.errors.service_ids" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                {{ ordersForm.errors.service_ids }}
              </div>

              <form @submit.prevent="submitOrders">
                <!-- Order Sets Quick Actions (if available) -->
                <div v-if="orderSets && orderSets.length" class="mb-6 border-b pb-6">
                  <h4 class="font-semibold mb-2">Quick Actions: Order Sets</h4>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="set in orderSets"
                      :key="set.id"
                      type="button"
                      @click.prevent="applyOrderSet(set)"
                      class="flex items-center gap-2 px-3 py-1 bg-gray-200 text-sm rounded-md hover:bg-gray-300"
                    >
                      <span>{{ set.name }}</span>

                      <!-- Pending count badge -->
                      <span
                        v-if="set.items && pendingCountForSet(set) > 0"
                        class="bg-blue-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full"
                      >
                        {{ pendingCountForSet(set) }} pending
                      </span>
                    </button>
                  </div>
                </div>

                <div class="space-y-6">
                  <div v-for="(group, department) in services" :key="department">
                    <h4 class="font-semibold mb-2">{{ department }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                      <label
                        v-for="service in group"
                        :key="service.id"
                        class="flex items-center space-x-3 p-2 border rounded-md"
                        :class="{ 'cursor-not-allowed bg-gray-100 text-gray-500': service.formulary_status === 'Restricted' || isServiceOrdered(service.id) }"
                      >
                        <input
                          type="checkbox"
                          :value="service.id"
                          v-model="ordersForm.service_ids"
                          class="rounded border-gray-300 text-blue-600 shadow-sm"
                          :disabled="service.formulary_status === 'Restricted' || isServiceOrdered(service.id)"
                        />
                        <span class="flex-grow">{{ service.name }}</span>
                        <span v-if="isServiceOrdered(service.id)" class="text-xs text-blue-600 font-semibold">(Pending)</span>
                        <span v-else-if="service.formulary_status" class="flex-shrink-0 flex items-center space-x-1">
                          <span v-if="service.formulary_status === 'Restricted'" title="Restricted">🔒</span>
                          <span
                            class="h-2 w-2 rounded-full"
                            :class="{
                              'bg-green-500': service.formulary_status === 'Formulary',
                              'bg-yellow-500': service.formulary_status === 'Non-Formulary',
                              'bg-red-500': service.formulary_status === 'Restricted',
                            }"
                            :title="service.formulary_status"
                          ></span>
                        </span>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end mt-6">
                  <button
                    type="submit"
                    :disabled="ordersForm.processing || (ordersForm.service_ids || []).length === 0"
                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                  >
                    Place Selected Orders
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
