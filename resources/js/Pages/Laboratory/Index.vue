<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  BeakerIcon,
  ClipboardDocumentCheckIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  CubeIcon,
  DocumentTextIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/solid';
import { ref, computed, watch } from 'vue';

/* --- Props (capture into a local props object) --- */
const props = defineProps({
  pendingLabOrders: {
    type: Array,
    default: () => []
  },
  collectedLabOrders: {
    type: Array,
    default: () => []
  },
  resultsReadyOrders: {
    type: Array,
    default: () => []
  },
  completedOrders: {
    type: Array,
    default: () => []
  },
});

/* --- Safe computed wrappers around props (avoid ReferenceError) --- */
const pendingLabOrders = computed(() => props.pendingLabOrders ?? []);
const collectedLabOrders = computed(() => props.collectedLabOrders ?? []);
const resultsReadyOrders = computed(() => props.resultsReadyOrders ?? []);
const completedOrders = computed(() => props.completedOrders ?? []);

/* --- Helpers & actions --- */
const formatDateTime = (value) => {
  if (!value) return '';
  try {
    return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
  } catch (e) {
    return value;
  }
};

const updateStatus = (orderItem, newStatus) => {
  if (!orderItem?.id) return;
  if (typeof route !== 'function') return;
  router.patch(route('lab.updateStatus', orderItem.id), { status: newStatus }, { preserveScroll: true });
};

const verifyResult = (labResultId) => {
  if (!labResultId) return;
  if (typeof route !== 'function') return;
  router.patch(route('lab-results.verify', labResultId), {}, { preserveScroll: true });
};

/* --- UI state --- */
const searchTerm = ref('');
const filterTab = ref('all'); // all | pending | collected | ready | completed
const showOnlyCritical = ref(false);
const tableLimit = ref(10);
const refreshing = ref(false);

/* --- Totals (use .value since these are computed refs) --- */
const totals = computed(() => ({
  pending: pendingLabOrders.value.length,
  collected: collectedLabOrders.value.length,
  ready: resultsReadyOrders.value.length,
  completed: completedOrders.value.length,
  total: pendingLabOrders.value.length + collectedLabOrders.value.length + resultsReadyOrders.value.length + completedOrders.value.length
}));

/* --- Filtering helpers --- */
const normalize = (s) => (s || '').toString().toLowerCase();
const matchesSearch = (item) => {
  if (!searchTerm.value) return true;
  const q = normalize(searchTerm.value);
  const patient = `${normalize(item.order?.patient?.first_name)} ${normalize(item.order?.patient?.last_name)}`;
  const service = normalize(item.service?.name);
  const resultVal = normalize(item.lab_result?.result_value);
  return patient.includes(q) || service.includes(q) || resultVal.includes(q);
};

const filteredPending = computed(() => pendingLabOrders.value.filter(item => matchesSearch(item)));
const filteredCollected = computed(() => collectedLabOrders.value.filter(item => matchesSearch(item)));
const filteredReady = computed(() => resultsReadyOrders.value.filter(item => {
  if (!matchesSearch(item)) return false;
  if (showOnlyCritical.value) {
    return (item.lab_result?.flag || '').toString().toLowerCase().includes('critical');
  }
  return true;
}));
const filteredCompleted = computed(() => completedOrders.value.filter(item => matchesSearch(item)));

const refresh = async () => {
  refreshing.value = true;
  if (typeof route === 'function') {
    await router.get(route('lab.index'), {}, { preserveState: true, preserveScroll: true });
  } else {
    // fallback: reload page
    window.location.reload();
  }
  refreshing.value = false;
};

const currentList = computed(() => {
  switch (filterTab.value) {
    case 'pending': return filteredPending.value;
    case 'collected': return filteredCollected.value;
    case 'ready': return filteredReady.value;
    case 'completed': return filteredCompleted.value;
    default: return [
      ...filteredPending.value,
      ...filteredCollected.value,
      ...filteredReady.value,
      ...filteredCompleted.value
    ];
  }
});

/* small watch (optional) */
watch(searchTerm, () => {
  // could add debounce here
});
</script>

<template>
  <Head title="Laboratory Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
          <BeakerIcon class="h-7 w-7 text-indigo-500" />
          <div>
            <h2 class="font-semibold text-xl text-gray-900">Laboratory Dashboard</h2>
            <p class="text-sm text-gray-500">Overview of sample collection, results entry and verification.</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Link :href="route('lab-inventory.index')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 shadow-sm rounded-md text-sm font-medium hover:shadow-lg transition">
            <CubeIcon class="h-4 w-4 text-gray-600" /> Manage Inventory
          </Link>

          <button @click="refresh"
                  :disabled="refreshing"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-md text-sm font-medium hover:opacity-95 transition">
            <ArrowPathIcon class="h-4 w-4" /> <span v-if="!refreshing">Refresh</span>
            <span v-else>Refreshing...</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Top summary cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-gradient-to-r from-white to-indigo-50 p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-white rounded-xl shadow flex items-center">
              <ClockIcon class="h-6 w-6 text-yellow-500" />
            </div>
            <div class="flex-1">
              <div class="text-xs font-medium text-gray-500">Pending Collection</div>
              <div class="mt-1 text-2xl font-semibold text-gray-900">{{ totals.pending }}</div>
              <div class="text-xs text-gray-400 mt-1">Awaiting sample pickup</div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-white to-green-50 p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-white rounded-xl shadow flex items-center">
              <ClipboardDocumentCheckIcon class="h-6 w-6 text-green-500" />
            </div>
            <div class="flex-1">
              <div class="text-xs font-medium text-gray-500">Collected - Awaiting Entry</div>
              <div class="mt-1 text-2xl font-semibold text-gray-900">{{ totals.collected }}</div>
              <div class="text-xs text-gray-400 mt-1">Samples in lab</div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-white to-pink-50 p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-white rounded-xl shadow flex items-center">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-500" />
            </div>
            <div class="flex-1">
              <div class="text-xs font-medium text-gray-500">Results Needing Verification</div>
              <div class="mt-1 text-2xl font-semibold text-gray-900">{{ totals.ready }}</div>
              <div class="text-xs text-gray-400 mt-1">Prioritize critical flags</div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-white to-emerald-50 p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-white rounded-xl shadow flex items-center">
              <CheckCircleIcon class="h-6 w-6 text-green-600" />
            </div>
            <div class="flex-1">
              <div class="text-xs font-medium text-gray-500">Recently Completed</div>
              <div class="mt-1 text-2xl font-semibold text-gray-900">{{ totals.completed }}</div>
              <div class="text-xs text-gray-400 mt-1">Results released</div>
            </div>
          </div>
        </div>

        <!-- Controls -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3 w-full sm:w-auto">
              <div class="relative w-full sm:w-80">
                <input v-model="searchTerm" type="text" placeholder="Search patient, test or result..."
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-gray-400" />
              </div>

              <div class="flex items-center gap-2">
                <button @click="filterTab = 'all'"
                        :class="filterTab === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-1 text-sm rounded-md transition">All</button>
                <button @click="filterTab = 'pending'"
                        :class="filterTab === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-1 text-sm rounded-md transition">Pending</button>
                <button @click="filterTab = 'collected'"
                        :class="filterTab === 'collected' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-1 text-sm rounded-md transition">Collected</button>
                <button @click="filterTab = 'ready'"
                        :class="filterTab === 'ready' ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-1 text-sm rounded-md transition">Ready</button>
                <button @click="filterTab = 'completed'"
                        :class="filterTab === 'completed' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-1 text-sm rounded-md transition">Completed</button>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" v-model="showOnlyCritical" class="rounded border-gray-300" />
                Show only critical flags
              </label>

              <select v-model="tableLimit" class="px-3 py-1 border rounded-md text-sm">
                <option value="5">5 / page</option>
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4">
          <div class="p-3 bg-green-50 border border-green-100 rounded-lg text-green-700 flex items-center gap-2">
            <CheckCircleIcon class="h-5 w-5" /> {{ $page.props.flash.success }}
          </div>
        </div>

        <!-- Main content: organized sections with modern tables -->
        <div class="space-y-6">
          <!-- Pending Sample Collection -->
          <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                  <ClockIcon class="h-5 w-5 text-yellow-500" /> Pending Sample Collection
                </h3>
                <div class="text-sm text-gray-500">Showing {{ filteredPending.length }} results</div>
              </div>
            </div>

            <div class="p-4 overflow-x-auto">
              <table class="min-w-full divide-y">
                <thead class="bg-gray-50/60 sticky top-0">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y">
                  <tr v-for="item in filteredPending.slice(0, tableLimit)" :key="`pending-${item.id}`" class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDateTime(item.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                      {{ item.order.patient.first_name }} {{ item.order.patient.last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.service.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button @click="updateStatus(item, 'Sample Collected')"
                              class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:opacity-95 transition">
                        <BeakerIcon class="h-4 w-4" /> Collect
                      </button>
                    </td>
                  </tr>

                  <tr v-if="filteredPending.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">No pending orders.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Pending Result Entry -->
          <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                  <ClipboardDocumentCheckIcon class="h-5 w-5 text-green-500" /> Pending Result Entry
                </h3>
                <div class="text-sm text-gray-500">Showing {{ filteredCollected.length }} results</div>
              </div>
            </div>

            <div class="p-4 overflow-x-auto">
              <table class="min-w-full divide-y">
                <thead class="bg-gray-50/60 sticky top-0">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y">
                  <tr v-for="item in filteredCollected.slice(0, tableLimit)" :key="`collected-${item.id}`" class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDateTime(item.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                      {{ item.order.patient.first_name }} {{ item.order.patient.last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.service.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Link :href="route('lab-results.create', item.id)"
                            class="inline-flex items-center gap-2 px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:opacity-95 transition">
                        <DocumentTextIcon class="h-4 w-4" /> Enter Result
                      </Link>
                    </td>
                  </tr>

                  <tr v-if="filteredCollected.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">No orders awaiting results.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Awaiting Verification -->
          <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                  <ExclamationTriangleIcon class="h-5 w-5 text-red-500" /> Awaiting Verification
                </h3>
                <div class="text-sm text-gray-500">Showing {{ filteredReady.length }} results</div>
              </div>
            </div>

            <div class="p-4 overflow-x-auto">
              <table class="min-w-full divide-y">
                <thead class="bg-gray-50/60 sticky top-0">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flag</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y">
                  <tr v-for="item in filteredReady.slice(0, tableLimit)" :key="`ready-${item.id}`"
                      :class="[ item.lab_result?.flag?.toLowerCase()?.includes('critical') ? 'bg-red-50' : '' , 'hover:bg-gray-50 transition']">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                      {{ item.order.patient.first_name }} {{ item.order.patient.last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.service.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                      {{ item.lab_result?.result_value ?? '-' }} {{ item.service.units ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="item.lab_result?.flag"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{
                              'bg-red-200 text-red-800': (item.lab_result.flag || '').toLowerCase().includes('critical'),
                              'bg-yellow-200 text-yellow-800': ['high','low'].some(k => (item.lab_result.flag || '').toLowerCase() === k),
                              'bg-green-200 text-green-800': (item.lab_result.flag || '').toLowerCase() === 'normal'
                            }">
                        {{ item.lab_result.flag }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button v-if="item.lab_result?.id"
                              @click="verifyResult(item.lab_result.id)"
                              class="px-3 py-1 bg-purple-600 text-white rounded-md text-sm hover:opacity-95 transition">
                        Verify
                      </button>
                    </td>
                  </tr>

                  <tr v-if="filteredReady.length === 0">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No results are awaiting verification.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Recently Completed -->
          <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                  <CheckCircleIcon class="h-5 w-5 text-green-600" /> Recently Completed
                </h3>
                <div class="text-sm text-gray-500">Showing {{ filteredCompleted.length }} results</div>
              </div>
            </div>

            <div class="p-4 overflow-x-auto">
              <table class="min-w-full divide-y">
                <thead class="bg-gray-50/60 sticky top-0">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flag</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y">
                  <tr v-for="item in filteredCompleted.slice(0, tableLimit)" :key="`completed-${item.id}`" class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                      {{ item.order.patient.first_name }} {{ item.order.patient.last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.service.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                      {{ item.lab_result?.result_value ?? '-' }} {{ item.service.units ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="item.lab_result?.flag" class="px-2 inline-flex text-xs font-semibold rounded-full"
                            :class="{
                              'bg-red-200 text-red-800': (item.lab_result.flag || '').toLowerCase().includes('critical'),
                              'bg-yellow-200 text-yellow-800': ['high','low'].some(k => (item.lab_result.flag || '').toLowerCase() === k),
                              'bg-green-200 text-green-800': (item.lab_result.flag || '').toLowerCase() === 'normal'
                            }">
                        {{ item.lab_result.flag }}
                      </span>
                    </td>
                  </tr>

                  <tr v-if="filteredCompleted.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">No recently completed orders.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
input::placeholder {
  color: rgba(107,114,128,0.9);
}
thead.sticky th {
  backdrop-filter: blur(4px);
}
</style>
