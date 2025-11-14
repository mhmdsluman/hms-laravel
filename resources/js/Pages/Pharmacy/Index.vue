<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  pendingOrders: { type: Array, default: () => [] }, // expected shape: { id, created_at, service: { name }, order: { patient: { first_name, last_name }}, status, clinician? }
  inventory: { type: Object, default: () => ({ data: [] }) },
});

// --- Helpers ---
const safeRoute = (name, ...params) => {
  try { return route(name, ...params); }
  catch (e) { return null; }
};

const formatDateTime = (value) => {
  if (!value) return '';
  const d = new Date(value);
  if (isNaN(d)) return '';
  return d.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};

// --- UI state ---
const q = ref('');
const statusFilter = ref('All'); // All | Pending | Dispensed | Cancelled
const dateFilter = ref(''); // yyyy-mm-dd
const sortBy = ref('created_at'); // created_at | patient | medication
const sortDir = ref('desc'); // asc | desc
const page = ref(1);
const perPage = ref(12);

const showOrderModal = ref(false);
const selectedOrder = ref(null);
const actionLoading = ref(false);
const activeTab = ref('orders'); // orders | inventory

// --- Derived data ---
const statuses = computed(() => {
  const map = { All: 'All' };
  props.pendingOrders.forEach(o => map[o.status] = o.status);
  return Object.keys(map);
});

const filtered = computed(() => {
  const qLower = q.value.trim().toLowerCase();
  return props.pendingOrders
    .filter(o => {
      if (statusFilter.value !== 'All' && o.status !== statusFilter.value) return false;
      if (dateFilter.value) {
        const d = new Date(o.created_at);
        const ymd = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        if (ymd !== dateFilter.value) return false;
      }
      if (!qLower) return true;
      const patient = `${o.order?.patient?.first_name || ''} ${o.order?.patient?.last_name || ''}`.toLowerCase();
      const med = (o.service?.name || '').toLowerCase();
      return patient.includes(qLower) || med.includes(qLower) || (o.status || '').toLowerCase().includes(qLower);
    })
    .sort((a, b) => {
      let aKey = a[sortBy.value];
      let bKey = b[sortBy.value];
      if (sortBy.value === 'patient') {
        aKey = `${a.order?.patient?.last_name || ''}${a.order?.patient?.first_name || ''}`;
        bKey = `${b.order?.patient?.last_name || ''}${b.order?.patient?.first_name || ''}`;
      } else if (sortBy.value === 'medication') {
        aKey = a.service?.name || '';
        bKey = b.service?.name || '';
      } else {
        aKey = new Date(a.created_at).getTime() || 0;
        bKey = new Date(b.created_at).getTime() || 0;
      }
      if (aKey < bKey) return sortDir.value === 'asc' ? -1 : 1;
      if (aKey > bKey) return sortDir.value === 'asc' ? 1 : -1;
      return 0;
    });
});

const total = computed(() => filtered.value.length);
const pages = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)));
const paged = computed(() => {
  const start = (page.value - 1) * perPage.value;
  return filtered.value.slice(start, start + perPage.value);
});

// small stats
const stats = computed(() => {
  const s = { Pending: 0, Dispensed: 0, Cancelled: 0, Total: props.pendingOrders.length };
  props.pendingOrders.forEach(o => {
    if (s[o.status] !== undefined) s[o.status] += 1;
  });
  return s;
});

// --- Actions ---
function openDetails(o) {
  selectedOrder.value = o;
  showOrderModal.value = true;
}

async function confirmAndDispense(itemId) {
  const url = safeRoute('dispensations.create', itemId) || safeRoute('dispensations.store'); // try both shapes
  if (!url) {
    alert('Dispense route not available. Contact admin.');
    return;
  }
  if (!confirm('Mark this order as dispensed?')) return;
  actionLoading.value = true;
  try {
    await router.post(url, { id: itemId });
    // success handled by Inertia page refresh; keep UI snappy by closing modals
    showOrderModal.value = false;
  } finally {
    actionLoading.value = false;
  }
}

function goToInventory() {
  activeTab.value = 'inventory';
}

function changePage(p) {
  page.value = Math.max(1, Math.min(p, pages.value));
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<template>
  <Head title="Pharmacy Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pharmacy Dashboard</h2>
          <p class="text-sm text-gray-500 mt-1">Manage medication orders, dispense quickly and keep inventory in sync.</p>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route('inventory.create')" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-900">Add Inventory Item</Link>

          <Link :href="safeRoute('pharmacy.reports') || '#'" class="px-4 py-2 bg-white border rounded-md text-sm text-gray-700 hover:shadow-sm">Reports</Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- flash -->
        <div v-if="$page.props.flash?.success" class="p-3 bg-green-50 text-green-800 rounded-md border border-green-100">
          {{ $page.props.flash.success }}
        </div>

        <!-- summary cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 bg-white rounded-lg shadow-sm border">
            <div class="text-sm text-gray-500">Total Orders</div>
            <div class="mt-2 text-2xl font-semibold text-gray-800">{{ stats.Total }}</div>
          </div>

          <div class="p-4 bg-white rounded-lg shadow-sm border">
            <div class="text-sm text-gray-500">Pending</div>
            <div class="mt-2 text-2xl font-semibold text-amber-600">{{ stats.Pending }}</div>
          </div>

          <div class="p-4 bg-white rounded-lg shadow-sm border">
            <div class="text-sm text-gray-500">Dispensed</div>
            <div class="mt-2 text-2xl font-semibold text-green-600">{{ stats.Dispensed }}</div>
          </div>
        </div>

        <!-- tabs -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button @click="activeTab = 'orders'"
                    :class="[
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                      activeTab === 'orders'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    ]">
              Medication Orders
            </button>
            <button @click="activeTab = 'inventory'"
                    :class="[
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                      activeTab === 'inventory'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    ]">
              Inventory
            </button>
          </nav>
        </div>

        <div v-if="activeTab === 'orders'">
            <!-- controls -->
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center gap-2 w-full md:w-2/3">
                        <div class="relative w-full">
                            <input v-model="q" type="search" placeholder="Search patient, medication or status..." class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-300" />
                            <button @click="q=''" v-if="q" class="absolute right-2 top-2 text-sm text-gray-500">Clear</button>
                        </div>
                        <select v-model="statusFilter" class="border rounded-md px-3 py-2 text-sm">
                            <option value="All">All statuses</option>
                            <option v-for="s in statuses" :key="s" v-if="s !== 'All'">{{ s }}</option>
                        </select>
                        <input type="date" v-model="dateFilter" class="border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div class="flex items-center gap-2 justify-end w-full md:w-auto">
                        <select v-model="sortBy" class="border rounded-md px-3 py-2 text-sm">
                            <option value="created_at">Newest</option>
                            <option value="patient">Patient</option>
                            <option value="medication">Medication</option>
                        </select>
                        <button @click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'" class="px-3 py-2 bg-gray-100 rounded-md text-sm">
                            {{ sortDir === 'asc' ? 'Asc' : 'Desc' }}
                        </button>
                        <select v-model.number="perPage" class="border rounded-md px-3 py-2 text-sm">
                            <option :value="8">8 / page</option>
                            <option :value="12">12 / page</option>
                            <option :value="24">24 / page</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- orders table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medication</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placed</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in paged" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700">#{{ item.id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ item.order?.patient?.first_name }} {{ item.order?.patient?.last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ item.order?.patient?.patient_no || '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.service?.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ formatDateTime(item.created_at) }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-amber-100 text-amber-700': item.status === 'Pending',
                                                'bg-green-100 text-green-700': item.status === 'Dispensed',
                                                'bg-red-100 text-red-700': item.status === 'Cancelled',
                                                'bg-gray-100 text-gray-700': !item.status
                                            }"
                                        >
                                            {{ item.status || 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium">
                                        <div class="inline-flex items-center gap-2">
                                            <button @click="openDetails(item)" class="px-3 py-1 bg-white border rounded text-sm hover:shadow">Details</button>
                                            <Link
                                                v-if="safeRoute('dispensations.create', item.id)"
                                                :href="safeRoute('dispensations.create', item.id)"
                                                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                                            >
                                                Dispense
                                            </Link>
                                            <button v-else @click="confirmAndDispense(item.id)" :disabled="actionLoading" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                                Dispense
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="paged.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No orders found. Try changing filters or check inventory.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-500">Showing <strong>{{ (page-1)*perPage + (paged.length ? 1 : 0) }}</strong> to <strong>{{ (page-1)*perPage + paged.length }}</strong> of <strong>{{ total }}</strong> orders</div>
                        <div class="flex items-center gap-2">
                            <button @click="changePage(page - 1)" :disabled="page <= 1" class="px-3 py-1 rounded border bg-white">Prev</button>
                            <div class="px-3 py-1 bg-gray-50 rounded text-sm">Page {{ page }} / {{ pages }}</div>
                            <button @click="changePage(page + 1)" :disabled="page >= pages" class="px-3 py-1 rounded border bg-white">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <!-- inventory table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4">
                    <table class="min-w-full divide-y">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">In Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-order</th>
                                <th class="px-6 py-3 relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y">
                            <tr v-for="item in inventory.data" :key="item.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                                    <div class="text-xs text-gray-400">{{ item.description || '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                                    :class="{ 'text-red-600': item.quantity <= item.reorder_level }">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.reorder_level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end gap-2">
                                    <Link :href="route('inventory.edit', item.id)" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="inventory.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">No inventory items found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex items-center justify-between p-4">
                    <Pagination :links="inventory.links || []" />
                </div>
            </div>
        </div>

      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4">
      <div class="absolute inset-0 bg-black opacity-40" @click="showOrderModal = false"></div>

      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full z-10 overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-lg">Order #{{ selectedOrder?.id }}</h3>
            <div class="text-sm text-gray-500">{{ formatDateTime(selectedOrder?.created_at) }}</div>
          </div>
          <div>
            <button @click="showOrderModal = false" class="text-gray-500 hover:text-gray-800 text-lg">&times;</button>
          </div>
        </div>

        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-gray-500">Patient</div>
              <div class="font-medium">{{ selectedOrder?.order?.patient?.first_name }} {{ selectedOrder?.order?.patient?.last_name }}</div>
              <div class="text-sm text-gray-500 mt-1">Patient No: {{ selectedOrder?.order?.patient?.patient_no || '—' }}</div>

              <div class="mt-4 text-xs text-gray-500">Medication</div>
              <div class="font-medium">{{ selectedOrder?.service?.name }}</div>
              <div class="text-sm text-gray-500 mt-1">{{ selectedOrder?.service?.notes || '' }}</div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Status</div>
              <div class="mt-1">
                <span class="px-2 py-1 rounded-full text-sm font-semibold" :class="{
                  'bg-amber-100 text-amber-700': selectedOrder?.status === 'Pending',
                  'bg-green-100 text-green-700': selectedOrder?.status === 'Dispensed',
                  'bg-red-100 text-red-700': selectedOrder?.status === 'Cancelled'
                }">{{ selectedOrder?.status || 'Unknown' }}</span>
              </div>

              <div class="mt-4 text-xs text-gray-500">Clinician</div>
              <div class="font-medium">{{ selectedOrder?.clinician?.name || selectedOrder?.clinician_name || '—' }}</div>

              <div class="mt-4">
                <button v-if="selectedOrder?.status === 'Pending'" @click="confirmAndDispense(selectedOrder.id)" :disabled="actionLoading" class="px-4 py-2 bg-green-600 text-white rounded">Mark Dispensed</button>
                <Link v-if="safeRoute('dispensations.create', selectedOrder?.id)" :href="safeRoute('dispensations.create', selectedOrder?.id)" class="ml-2 px-4 py-2 bg-white border rounded text-sm">Open Dispense</Link>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 border-t flex justify-end">
          <button @click="showOrderModal = false" class="px-4 py-2 bg-gray-100 rounded">Close</button>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<style scoped>
/* subtle polished look */
table th { font-weight: 600; letter-spacing: 0.02em; }
</style>
