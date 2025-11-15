<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import PrintModal from '@/Components/PrintModal.vue';
import InvoiceHistoryModal from '@/Components/InvoiceHistoryModal.vue';
import AbnormalResultsSummary from '@/Components/AbnormalResultsSummary.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  patient: Object,
});

// safeRoute helper: returns null if named route is missing (prevents Ziggy runtime errors)
const safeRoute = (name, ...params) => {
  try {
    return route(name, ...params);
  } catch (e) {
    return null;
  }
};

// Modal / toggle state (one toggle per section controls showing history)
const showConfirmModal = ref(false);
const showPreviousOpNotes = ref(false);
const showPreviousRadReports = ref(false);
const showPreviousLabResults = ref(false);
const showAllHandovers = ref(false);
const showPreviousNursingNotes = ref(false);
const showPreviousVitals = ref(false);
const showInvoiceHistoryModal = ref(false);

// Print modal state (from code 2)
const showPrintModal = ref(false);
const pdfToPrint = ref('');
const openPrintModal = (labResultId) => {
  const url = safeRoute('print.lab-result', labResultId);
  if (!url) {
    console.warn("Route 'print.lab-result' not found. Cannot open print modal.");
    return;
  }
  pdfToPrint.value = url;
  showPrintModal.value = true;
};

// Delete action
const deletePatient = () => {
  router.delete(route('patients.destroy', props.patient.id));
};

// Helpers: date/time formatting
const formatDateTime = (value) => {
  if (!value) return '';
  const date = new Date(value);
  const now = new Date();
  const diffSeconds = Math.round((now - date) / 1000);
  const absoluteTime = date.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });

  if (diffSeconds < 60) return `just now - ${absoluteTime}`;
  const diffMinutes = Math.round(diffSeconds / 60);
  if (diffMinutes < 60) return `${diffMinutes} min ago - ${absoluteTime}`;
  const diffHours = Math.round(diffMinutes / 60);
  if (diffHours < 24) {
    const hours = Math.floor(diffMinutes / 60);
    const minutes = diffMinutes % 60;
    return `${hours} hr ${minutes} min ago - ${absoluteTime}`;
  }
  const diffDays = Math.round(diffHours / 24);
  if (diffDays < 30) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago - ${absoluteTime}`;
  const diffMonths = Math.round(diffDays / 30.44);
  if (diffMonths < 12) return `${diffMonths} month${diffMonths > 1 ? 's' : ''} ago - ${absoluteTime}`;
  const diffYears = Math.round(diffDays / 365.25);
  return `${diffYears} year${diffYears > 1 ? 's' : ''} ago - ${absoluteTime}`;
};

const formatPolicyDate = (startDate, endDate) => {
  if (!startDate || !endDate) return '';
  const start = new Date(startDate);
  const end = new Date(endDate);
  const now = new Date();
  const formattedStart = start.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  const formattedEnd = end.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  if (end < now) return `${formattedStart} to ${formattedEnd} (Expired)`;
  const monthsRemaining = (end.getFullYear() - now.getFullYear()) * 12 + (end.getMonth() - now.getMonth());
  if (monthsRemaining <= 0) return `${formattedStart} to ${formattedEnd} (Expires this month)`;
  return `${formattedStart} to ${formattedEnd} (${monthsRemaining} months remaining)`;
};

// utility to check recentness (24 hours)
const MS_24_HOURS = 24 * 60 * 60 * 1000;
const isWithin24Hours = (dateStr) => {
  if (!dateStr) return false;
  const d = new Date(dateStr);
  return (Date.now() - d.getTime()) <= MS_24_HOURS;
};

// helper to pick a good timestamp for an item (ordered, item created, or report created)
const itemTimestamp = (item) => {
  return item?.ordered_at || item?.created_at || item?.lab_result?.created_at || item?.radiology_report?.created_at || item?.operative_note?.created_at || null;
};

// --- Computed collections (robust, with fallback sorting) ---
const allHandovers = computed(() => {
  if (!props.patient?.admissions) return [];
  return props.patient.admissions.flatMap(a => a.shift_handovers || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});
const latestHandover = computed(() => allHandovers.value[0] || null);
const previousHandovers = computed(() => allHandovers.value.slice(1));

const allNursingNotes = computed(() => {
  if (!props.patient?.admissions) return [];
  return props.patient.admissions.flatMap(a => a.nursing_notes || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});
const latestNursingNote = computed(() => allNursingNotes.value[0] || null);
const previousNursingNotes = computed(() => allNursingNotes.value.slice(1));

const allVitals = computed(() => (props.patient?.vitals || []).slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at)));
const latestVital = computed(() => allVitals.value[0] || null);
const previousVitals = computed(() => allVitals.value.slice(1));

const allLabResults = computed(() => {
  if (!props.patient?.orders) return [];
  return props.patient.orders.flatMap(o => o.items || []).filter(i => i.lab_result || i.service?.category === 'lab').sort((a, b) => (new Date(itemTimestamp(b)) - new Date(itemTimestamp(a))));
});
const latestLabResult = computed(() => allLabResults.value[0] || null);
const previousLabResults = computed(() => allLabResults.value.slice(1));

const allRadReports = computed(() => {
  if (!props.patient?.orders) return [];
  return props.patient.orders.flatMap(o => o.items || []).filter(i => i.radiology_report || i.service?.category === 'radiology').sort((a, b) => (new Date(itemTimestamp(b)) - new Date(itemTimestamp(a))));
});
const latestRadReport = computed(() => allRadReports.value[0] || null);
const previousRadReports = computed(() => allRadReports.value.slice(1));

const allOpNotes = computed(() => {
  if (!props.patient?.orders) return [];
  return props.patient.orders.flatMap(o => o.items || []).filter(i => i.operative_note || i.service?.category === 'operative').sort((a, b) => (new Date(itemTimestamp(b)) - new Date(itemTimestamp(a))));
});
const latestOpNote = computed(() => allOpNotes.value[0] || null);
const previousOpNotes = computed(() => allOpNotes.value.slice(1));

// convenience booleans
const hasLabResults = computed(() => allLabResults.value.length > 0);
const hasRadiologyReports = computed(() => allRadReports.value.length > 0);
const hasOperativeNotes = computed(() => allOpNotes.value.length > 0);
const hasNursingNotes = computed(() => allNursingNotes.value.length > 0);
const hasHandovers = computed(() => allHandovers.value.length > 0);
const hasVitals = computed(() => allVitals.value.length > 0);

// --- "new since last 24 hours" flags for collapsed indicator ---
const hasRecentLab = computed(() => allLabResults.value.some(i => isWithin24Hours(itemTimestamp(i))));
const hasRecentRad = computed(() => allRadReports.value.some(i => isWithin24Hours(itemTimestamp(i))));
const hasRecentOp = computed(() => allOpNotes.value.some(i => isWithin24Hours(itemTimestamp(i))));
const hasRecentHandovers = computed(() => allHandovers.value.some(h => isWithin24Hours(h.created_at)));
const hasRecentNursing = computed(() => allNursingNotes.value.some(n => isWithin24Hours(n.created_at)));
const hasRecentVitals = computed(() => allVitals.value.some(v => isWithin24Hours(v.created_at)));

// primary address
const primaryAddress = computed(() => {
  if (props.patient?.addresses && props.patient.addresses.length > 0) {
    return props.patient.addresses[0];
  }
  return null;
});
</script>

<template>
  <Head :title="'Patient Profile - ' + patient.uhid" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Patient Profile</h2>
          <div class="mt-1 text-sm text-gray-500">{{ patient.first_name }} {{ patient.last_name }} — UHID: <span class="font-mono">{{ patient.uhid }}</span></div>
        </div>

        <div class="flex items-center space-x-2">
          <Link :href="route('patients.edit', patient.id)" class="px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600">Edit</Link>

          <a v-if="safeRoute('print.patient', patient.id)" :href="safeRoute('print.patient', patient.id)" target="_blank" class="px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">Print Profile</a>

          <button @click="showInvoiceHistoryModal = true" class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">Invoice History</button>

          <Link :href="route('patients.index')" class="px-3 py-2 text-sm text-blue-600 hover:underline">Back to Patients</Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 bg-green-100 text-green-700 rounded">
          {{ $page.props.flash.success }}
        </div>

        <!-- Profile Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
              <!-- Left: Avatar + quick identifiers -->
              <div class="flex items-center gap-4">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center text-2xl font-semibold text-gray-600">
                  {{ (patient.first_name || '').charAt(0) }}{{ (patient.last_name || '').charAt(0) }}
                </div>
                <div>
                  <div class="text-lg font-semibold">{{ patient.first_name }} {{ patient.last_name }}</div>
                  <div class="text-sm text-gray-500">UHID: <span class="font-mono">{{ patient.uhid }}</span></div>
                  <div class="mt-2 flex items-center gap-2">
                    <span class="text-sm px-2 py-1 bg-gray-100 rounded">Age: {{ patient.age || 'N/A' }}</span>
                    <span class="text-sm px-2 py-1 bg-gray-100 rounded">Gender: {{ patient.gender || 'N/A' }}</span>
                    <span v-if="hasRecentLab || hasRecentRad || hasRecentNursing || hasRecentVitals" class="text-sm px-2 py-1 bg-red-50 text-red-700 rounded">New updates</span>
                  </div>
                </div>
              </div>

              <!-- Middle: Summary details -->
              <div class="lg:col-span-1">
                <h3 class="text-md font-medium text-gray-700 mb-2">Summary</h3>
                <dl class="grid grid-cols-1 gap-2 text-sm text-gray-700">
                  <div class="flex justify-between"><dt class="font-medium">Patient No</dt><dd>{{ patient.uhid || '—' }}</dd></div>
                  <div class="flex justify-between"><dt class="font-medium">Phone</dt><dd>{{ patient.primary_phone || '—' }}</dd></div>
                  <div class="flex justify-between"><dt class="font-medium">Email</dt><dd>{{ patient.email || 'Not provided' }}</dd></div>
                  <div class="flex justify-between"><dt class="font-medium">DOB</dt><dd>{{ patient.date_of_birth || '—' }}</dd></div>
                </dl>
              </div>

              <!-- Right: Actions & insurance -->
              <div class="lg:col-span-1 space-y-3">
                <div class="bg-gray-50 p-3 rounded">
                  <div class="text-sm font-medium text-gray-600">Insurance</div>
                  <div class="text-sm text-gray-800 mt-1">{{ patient.insurance?.provider || 'Not Provided' }}</div>
                  <div v-if="patient.insurance" class="text-xs text-gray-500 mt-1">{{ formatPolicyDate(patient.insurance.start_date, patient.insurance.end_date) }}</div>
                </div>

                <div class="flex gap-2">
                  <button @click="openPrintModal(patient.latest_lab_id)" class="flex-1 px-3 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Print Latest Lab</button>
                  <button @click="showConfirmModal = true" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Radiology -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3 relative">
            <h4 class="text-lg font-semibold">Radiology Reports</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentRad && !showPreviousRadReports" class="h-3 w-3 rounded-full bg-red-600 block" title="New radiology order within 24 hours"></span>
              <button @click="showPreviousRadReports = !showPreviousRadReports" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasRadiologyReports" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50">
              <div class="flex justify-between items-center mb-2">
                <p class="font-semibold">{{ latestRadReport?.service?.name || 'Radiology' }}</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(latestRadReport?.radiology_report?.created_at || itemTimestamp(latestRadReport)) }}</p>
              </div>
              <p class="text-sm whitespace-pre-wrap">{{ latestRadReport?.radiology_report?.report_text || 'Report not ready' }}</p>
            </div>

            <div v-show="showPreviousRadReports" class="space-y-3 pt-3 border-t">
              <div v-for="item in previousRadReports" :key="item.radiology_report?.id || item.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">{{ item.service?.name || 'Radiology' }}</p>
                  <p class="text-xs text-gray-500">{{ formatDateTime(item.radiology_report?.created_at || itemTimestamp(item)) }}</p>
                </div>
                <p class="text-sm whitespace-pre-wrap">{{ item.radiology_report?.report_text || 'Report not ready' }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No radiology reports found.</div>
        </section>

        <!-- Lab (summary + history) -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold">Lab Results</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentLab && !showPreviousLabResults" class="h-3 w-3 rounded-full bg-red-600 block" title="New lab order within 24 hours"></span>
              <button @click="showPreviousLabResults = !showPreviousLabResults" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasLabResults" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50">
              <div class="flex justify-between items-center mb-2">
                <p class="font-semibold">{{ latestLabResult?.service?.name || 'Lab Test' }}</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(latestLabResult?.lab_result?.created_at || itemTimestamp(latestLabResult)) }}</p>
              </div>
              <div>
                <p class="text-lg font-mono bg-gray-100 p-2 rounded">{{ latestLabResult?.lab_result?.result_value || 'Pending' }}</p>
                <p v-if="latestLabResult?.lab_result?.notes" class="text-sm mt-2 whitespace-pre-wrap">{{ latestLabResult.lab_result.notes }}</p>
              </div>
            </div>

            <div v-show="showPreviousLabResults" class="space-y-3 pt-3 border-t">
              <div v-for="item in previousLabResults" :key="item.lab_result?.id || item.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">{{ item.service?.name || 'Lab Test' }}</p>
                  <p class="text-xs text-gray-500">{{ formatDateTime(item.lab_result?.created_at || itemTimestamp(item)) }}</p>
                </div>
                <p class="text-lg font-mono bg-gray-100 p-2 rounded">{{ item.lab_result?.result_value || 'Pending' }}</p>
                <p v-if="item.lab_result?.notes" class="text-sm mt-2 whitespace-pre-wrap">{{ item.lab_result.notes }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No lab results found.</div>
        </section>

        <!-- AI Assistant Summary -->
        <AbnormalResultsSummary :patient-id="patient.id" />

        <!-- Enhanced Lab Results Card: per-order listing with Print button -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-4">Lab Reports</h3>
                <div v-if="patient.labOrders && patient.labOrders.length > 0" class="space-y-4">
                    <div v-for="labOrder in patient.labOrders" :key="labOrder.id" class="p-4 border rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-semibold">Order #{{ labOrder.order_id }}</p>
                                <p class="text-sm text-gray-500">{{ formatDateTime(labOrder.created_at) }}</p>
                            </div>
                            <a :href="route('print.labResult', labOrder.id)" target="_blank" class="px-3 py-1 bg-gray-600 text-white text-xs rounded-md hover:bg-gray-700">
                                Print Report
                            </a>
                        </div>
                        <table class="w-full text-sm text-left text-gray-500 mt-2">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Test</th>
                                    <th scope="col" class="py-3 px-6">Result</th>
                                    <th scope="col" class="py-3 px-6">Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="result in labOrder.results" :key="result.id" class="bg-white border-b">
                                    <td class="py-4 px-6">{{ result.test.name }}</td>
                                    <td class="py-4 px-6">{{ result.result }}</td>
                                    <td class="py-4 px-6">{{ result.comment }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-4">
                    No lab reports found for this patient.
                </div>
            </div>
        </div>

        <!-- Operative Notes -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold">Operative Notes</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentOp && !showPreviousOpNotes" class="h-3 w-3 rounded-full bg-red-600 block" title="New operative note/order within 24 hours"></span>
              <button @click="showPreviousOpNotes = !showPreviousOpNotes" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasOperativeNotes" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50">
              <div class="flex justify-between items-center mb-2">
                <div>
                  <p class="font-semibold">{{ latestOpNote?.service?.name || 'Procedure' }}</p>
                  <p class="text-sm text-gray-500" v-if="latestOpNote?.operative_note?.surgeon">Surgeon: {{ latestOpNote.operative_note.surgeon.name }}</p>
                </div>
                <p class="text-sm text-gray-500">{{ formatDateTime(latestOpNote?.operative_note?.created_at || itemTimestamp(latestOpNote)) }}</p>
              </div>
              <div class="mt-4 space-y-2 text-sm">
                <p v-if="latestOpNote?.operative_note?.postoperative_diagnosis"><strong>Post-operative Diagnosis:</strong> {{ latestOpNote.operative_note.postoperative_diagnosis }}</p>
                <p v-if="latestOpNote?.operative_note?.findings"><strong>Findings:</strong> {{ latestOpNote.operative_note.findings }}</p>
              </div>
            </div>

            <div v-show="showPreviousOpNotes" class="space-y-3 pt-3 border-t">
              <div v-for="item in previousOpNotes" :key="item.operative_note?.id || item.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">{{ item.service?.name || 'Procedure' }}</p>
                  <p class="text-xs text-gray-500">{{ formatDateTime(item.operative_note?.created_at || itemTimestamp(item)) }}</p>
                </div>
                <div class="text-sm">
                  <p v-if="item.operative_note?.postoperative_diagnosis"><strong>Post-op Dx:</strong> {{ item.operative_note.postoperative_diagnosis }}</p>
                  <p v-if="item.operative_note?.findings"><strong>Findings:</strong> {{ item.operative_note.findings }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No operative notes found.</div>
        </section>

        <!-- Shift Handovers -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold">Shift Handovers</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentHandovers && !showAllHandovers" class="h-3 w-3 rounded-full bg-red-600 block" title="New handover within 24 hours"></span>
              <button @click="showAllHandovers = !showAllHandovers" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasHandovers" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50">
              <div class="flex justify-between items-center mb-2">
                <p class="font-semibold">Latest Handover from {{ latestHandover?.outgoing_nurse?.name || 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(latestHandover?.created_at) }}</p>
              </div>
              <p class="text-sm whitespace-pre-wrap">{{ latestHandover?.summary }}</p>
            </div>

            <div v-if="showAllHandovers" class="space-y-3 pt-3 border-t">
              <div v-for="handover in previousHandovers" :key="handover.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">Handover from {{ handover.outgoing_nurse?.name || 'N/A' }}</p>
                  <p class="text-xs text-gray-500">{{ formatDateTime(handover.created_at) }}</p>
                </div>
                <p class="text-sm whitespace-pre-wrap">{{ handover.summary }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No shift handovers found.</div>
        </section>

        <!-- Nursing Notes -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold">Nursing Notes</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentNursing && !showPreviousNursingNotes" class="h-3 w-3 rounded-full bg-red-600 block" title="New nursing note within 24 hours"></span>
              <button @click="showPreviousNursingNotes = !showPreviousNursingNotes" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasNursingNotes" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50" v-if="latestNursingNote">
              <div class="flex justify-between items-center mb-2">
                <p class="font-semibold">Note by {{ latestNursingNote?.nurse?.name || 'Nurse' }}</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(latestNursingNote?.created_at) }}</p>
              </div>
              <p class="text-sm whitespace-pre-wrap">{{ latestNursingNote?.note }}</p>
            </div>

            <div v-show="showPreviousNursingNotes" class="space-y-3 pt-3 border-t">
              <div v-for="note in previousNursingNotes" :key="note.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">Note by {{ note.nurse?.name || 'Nurse' }}</p>
                  <p class="text-xs text-gray-500">{{ formatDateTime(note.created_at) }}</p>
                </div>
                <p class="text-sm whitespace-pre-wrap">{{ note.note }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No nursing notes found.</div>
        </section>

        <!-- Vitals History -->
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
          <header class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold">Vitals History</h4>
            <div class="flex items-center space-x-3">
              <span v-if="hasRecentVitals && !showPreviousVitals" class="h-3 w-3 rounded-full bg-red-600 block" title="New vitals within 24 hours"></span>
              <button @click="showPreviousVitals = !showPreviousVitals" class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">History</button>
            </div>
          </header>

          <div v-if="hasVitals" class="space-y-4">
            <div class="p-4 border rounded-lg bg-blue-50" v-if="latestVital">
              <div class="flex justify-between items-center mb-2">
                <p class="font-semibold">{{ formatDateTime(latestVital?.created_at) }}</p>
                <p class="text-sm text-gray-500">Recorded by: {{ latestVital?.recorder?.name || 'N/A' }}</p>
              </div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="font-medium text-gray-600">BP:</span> {{ latestVital?.bp_systolic }}/{{ latestVital?.bp_diastolic }} mmHg</div>
                <div><span class="font-medium text-gray-600">HR:</span> {{ latestVital?.heart_rate }} bpm</div>
                <div><span class="font-medium text-gray-600">Temp:</span> {{ latestVital?.temperature_celsius }} °C</div>
                <div><span class="font-medium text-gray-600">SpO2:</span> {{ latestVital?.oxygen_saturation }} %</div>
              </div>
            </div>

            <div v-show="showPreviousVitals" class="space-y-3 pt-3 border-t">
              <div v-for="vital in previousVitals" :key="vital.id" class="p-3 border rounded bg-white">
                <div class="flex justify-between items-center mb-1">
                  <p class="font-medium">{{ formatDateTime(vital.created_at) }}</p>
                  <p class="text-xs text-gray-500">Recorded by: {{ vital.recorder?.name || 'N/A' }}</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div><span class="font-medium text-gray-600">BP:</span> {{ vital.bp_systolic }}/{{ vital.bp_diastolic }} mmHg</div>
                  <div><span class="font-medium text-gray-600">HR:</span> {{ vital.heart_rate }} bpm</div>
                  <div><span class="font-medium text-gray-600">Temp:</span> {{ vital.temperature_celsius }} °C</div>
                  <div><span class="font-medium text-gray-600">SpO2:</span> {{ vital.oxygen_saturation }} %</div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4">No vitals have been recorded.</div>
        </section>

        <!-- Danger zone -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 border-t border-red-200 bg-red-50">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold text-red-800">Danger Zone</h3>
                <p class="text-sm text-red-600 mt-1">Deleting a patient record will hide it from view.</p>
              </div>
              <button @click="showConfirmModal = true" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Delete Patient Record
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Print Modal -->
    <PrintModal
      :show="showPrintModal"
      :pdfUrl="pdfToPrint"
      @close="showPrintModal = false"
    />

    <ConfirmationModal
      :show="showConfirmModal"
      title="Delete Patient Record"
      message="Are you sure you want to delete this patient? This action will mark the record as deleted and hide it from view."
      @confirm="deletePatient"
      @cancel="showConfirmModal = false"
    />

    <InvoiceHistoryModal
        :show="showInvoiceHistoryModal"
        :patient="patient"
        @close="showInvoiceHistoryModal = false"
    />
  </AuthenticatedLayout>
</template>

<style scoped>
/* indicator alignment tweaks */
section header { min-height: 36px; }

/* fade transitions for history areas (used where v-show + transitions may be applied) */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
