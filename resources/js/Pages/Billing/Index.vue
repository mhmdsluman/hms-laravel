<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

// Prefer explicit prop but allow fallback from page props (prevents ReferenceError)
const props = defineProps({
  bills: {
    type: Object,
    required: false,
  },
});

const page = usePage();

// Safe bills object (from props if provided, otherwise from inertia page props)
const bills = computed(() => {
  return props.bills ?? page.props.bills ?? { data: [], links: [], current_page: 1, from: 0, to: 0, total: 0 };
});

// UI state
const q = ref('');
const statusFilter = ref('all'); // all, Paid, Unpaid, Pending...
const selected = ref(new Set());
const selectAllOnPage = ref(false);
const showConfirmBulk = ref(false);
const performingBulk = ref(false);
const exportLoading = ref(false);

// ----- Safe Ziggy helpers to avoid Ziggy runtime throws -----
function hasRoute(name) {
  try {
    route(name);
    return true;
  } catch (e) {
    return false;
  }
}
function rRoute(name, params = {}) {
  try {
    return route(name, params);
  } catch (e) {
    // Route missing — warn and return null so callers handle gracefully
    console.warn(`Missing Ziggy route: ${name}`);
    return null;
  }
}
// -----------------------------------------------------------

// Derived helpers
const billsArray = computed(() => (bills.value && bills.value.data) ? bills.value.data : []);

// Client-side quick filtering (works on current page)
const filteredBills = computed(() => {
  const term = q.value.trim().toLowerCase();
  return billsArray.value.filter(b => {
    if (statusFilter.value !== 'all' && b.status !== statusFilter.value) return false;
    if (!term) return true;
    const patient = `${b.patient?.first_name ?? ''} ${b.patient?.last_name ?? ''}`.toLowerCase();
    return patient.includes(term) || String(b.id).includes(term) || String(b.total_amount).includes(term);
  });
});

function humanDate(val) {
  if (!val) return '';
  try {
    return new Date(val).toLocaleString(page.props.locale || 'en-US', { dateStyle: 'medium', timeStyle: 'short' });
  } catch (e) {
    return val;
  }
}

function currency(amount) {
  if (amount == null) return '-';
  try {
    return new Intl.NumberFormat(page.props.locale || 'en-US', { style: 'currency', currency: page.props.currency || 'USD', maximumFractionDigits: 2 }).format(amount);
  } catch (e) {
    return `${amount}`;
  }
}

// row selection
function toggleRow(id) {
  if (selected.value.has(id)) selected.value.delete(id);
  else selected.value.add(id);
  selectAllOnPage.value = billsArray.value.length > 0 && billsArray.value.every(b => selected.value.has(b.id));
}

function toggleSelectAllOnPage() {
  if (!selectAllOnPage.value) {
    billsArray.value.forEach(b => selected.value.add(b.id));
    selectAllOnPage.value = true;
  } else {
    billsArray.value.forEach(b => selected.value.delete(b.id));
    selectAllOnPage.value = false;
  }
}
const selectedCount = computed(() => selected.value.size);

// Server search (press Enter or status change)
function doServerSearch() {
  const url = rRoute('billing.index');
  const params = { q: q.value || undefined, status: statusFilter.value !== 'all' ? statusFilter.value : undefined };
  if (url) router.get(url, params, { preserveState: true, replace: true });
  else router.get(window.location.pathname, params, { preserveState: true, replace: true });
}

// bulk actions
function confirmBulkMarkPaid() {
  if (!selectedCount.value) return;
  showConfirmBulk.value = true;
}

function performBulkMarkPaid() {
  if (!selectedCount.value) return;
  const url = rRoute('billing.bulkPay');
  if (!url) {
    console.warn('Route billing.bulkPay missing. Bulk action aborted.');
    showConfirmBulk.value = false;
    return;
  }
  performingBulk.value = true;
  const ids = Array.from(selected.value);
  router.post(url, { ids }, {
    onSuccess: () => {
      performingBulk.value = false;
      showConfirmBulk.value = false;
      // clear selected for ids returned
      ids.forEach(id => selected.value.delete(id));
    },
    onError: () => {
      performingBulk.value = false;
    },
  });
}

// Export CSV (client-side from current page / filtered rows)
function exportCSV() {
  try {
    exportLoading.value = true;
    const rows = filteredBills.value;
    if (!rows.length) {
      const blob = new Blob(["No data\n"], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'bills.csv';
      a.click();
      URL.revokeObjectURL(url);
      exportLoading.value = false;
      return;
    }

    const header = ['Bill ID', 'Patient', 'Date', 'Amount', 'Status'];
    const csv = [
      header.join(','),
      ...rows.map(b => {
        const id = `#${b.id}`;
        const patient = `"${(b.patient?.first_name ?? '')} ${(b.patient?.last_name ?? '')}"`;
        const date = `"${humanDate(b.created_at)}"`;
        const amount = `${b.total_amount}`;
        const status = `${b.status}`;
        return [id, patient, date, amount, status].join(',');
      })
    ].join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bills_page_${bills.value.current_page ?? 1}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  } finally {
    exportLoading.value = false;
  }
}

function rowMarkPaid(bill) {
  if (!bill || bill.status === 'Paid') return;
  const url = rRoute('billing.markPaid', bill.id);
  if (!url) {
    console.warn('Route billing.markPaid missing. Action aborted.');
    return;
  }
  router.post(url, {}, {
    onSuccess: () => {
      // server refresh expected
    }
  });
}

function printBill(bill) {
  const url = rRoute('billing.show', bill.id);
  if (!url) {
    console.warn('Route billing.show missing. Cannot open print view.');
    return;
  }
  window.open(url, '_blank');
}
</script>

<template>
  <Head title="Billing Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Billing Dashboard</h2>
          <p class="text-sm text-gray-500 mt-1">Manage generated bills, mark payments, export and bulk actions.</p>
        </div>

        <div class="flex items-center gap-3">
          <Link v-if="hasRoute('billing.create')" :href="rRoute('billing.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm text-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Bill
          </Link>

          <button v-else disabled class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400 rounded text-sm" title="Route billing.create is not available">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Bill
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 rounded bg-green-50 text-green-800">
          {{ $page.props.flash.success }}
        </div>

        <!-- Controls -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-80">
              <input
                v-model="q"
                @keyup.enter.prevent="doServerSearch"
                placeholder="Search by patient, bill ID or amount..."
                class="w-full pl-10 pr-4 py-2 rounded-md border border-gray-200 focus:ring-1 focus:ring-indigo-400 focus:outline-none"
                aria-label="Search bills"
              />
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
              </div>
            </div>

            <select v-model="statusFilter" class="rounded-md border border-gray-200 px-3 py-2 text-sm" @change="doServerSearch">
              <option value="all">All statuses</option>
              <option value="Paid">Paid</option>
              <option value="Unpaid">Unpaid</option>
              <option value="Pending">Pending</option>
            </select>

            <button @click="exportCSV" :disabled="exportLoading" class="ml-auto md:ml-0 inline-flex items-center gap-2 px-3 py-2 bg-white border rounded text-sm hover:bg-gray-50">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>
              <span v-if="!exportLoading">Export CSV</span>
              <span v-else class="text-xs text-gray-400">Preparing...</span>
            </button>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-sm text-gray-600">Page: <strong>{{ bills.current_page ?? 1 }}</strong></div>

            <div class="flex items-center gap-2">
              <button
                class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white border text-sm hover:bg-gray-50"
                :disabled="!selectedCount"
                @click="confirmBulkMarkPaid"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Mark Paid ({{ selectedCount }})
              </button>
            </div>
          </div>
        </div>

        <!-- Table card -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-4 border-b flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" class="form-checkbox h-4 w-4" :checked="selectAllOnPage" @change="toggleSelectAllOnPage" />
                <span>Select page</span>
              </label>
              <div v-if="selectedCount" class="text-sm text-gray-700">Selected: <strong>{{ selectedCount }}</strong></div>
            </div>

            <div class="text-sm text-gray-500">Showing <strong>{{ bills.from ?? '-' }}</strong> — <strong>{{ bills.to ?? '-' }}</strong> of <strong>{{ bills.total ?? '-' }}</strong></div>
          </div>

          <!-- desktop table -->
          <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm text-gray-500">#</th>
                  <th class="px-4 py-3 text-left text-sm text-gray-500">Patient</th>
                  <th class="px-4 py-3 text-left text-sm text-gray-500">Date</th>
                  <th class="px-4 py-3 text-right text-sm text-gray-500">Amount</th>
                  <th class="px-4 py-3 text-left text-sm text-gray-500">Status</th>
                  <th class="px-4 py-3 text-center text-sm text-gray-500">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="bill in filteredBills" :key="bill.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-3">
                      <input type="checkbox" class="h-4 w-4" :checked="selected.has(bill.id)" @change="() => toggleRow(bill.id)" />
                      <div class="font-mono text-xs text-gray-700">
                        <Link :href="rRoute('billing.show', bill.id) ?? '#'" class="text-indigo-600 hover:underline">#{{ bill.id }}</Link>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="font-medium text-gray-900">{{ bill.patient?.first_name }} {{ bill.patient?.last_name }}</div>
                    <div class="text-xs text-gray-500">{{ bill.patient?.medical_record_number ? `MRN: ${bill.patient.medical_record_number}` : '' }}</div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ humanDate(bill.created_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold">{{ currency(bill.total_amount) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                      :class="{
                        'bg-green-100 text-green-800': bill.status === 'Paid',
                        'bg-red-100 text-red-800': bill.status === 'Unpaid',
                        'bg-yellow-100 text-yellow-800': bill.status === 'Pending'
                      }"
                    >{{ bill.status }}</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    <div class="inline-flex items-center gap-2">
                      <Link :href="rRoute('billing.show', bill.id) ?? '#'" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-white border text-xs hover:bg-gray-50">View</Link>
                      <button @click="printBill(bill)" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-white border text-xs hover:bg-gray-50">Print</button>
                      <button v-if="bill.status !== 'Paid' && hasRoute('billing.markPaid')" @click="rowMarkPaid(bill)" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">Mark Paid</button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!filteredBills.length">
                  <td colspan="6" class="px-4 py-6 text-center text-gray-500">No bills found on this page.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- mobile: card view -->
          <div class="md:hidden space-y-3 p-4">
            <div v-for="bill in filteredBills" :key="bill.id" class="p-3 border rounded-lg bg-white">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">#{{ bill.id }}</div>
                  <div class="text-xs text-gray-500">{{ bill.patient?.first_name }} {{ bill.patient?.last_name }}</div>
                </div>
                <div class="text-sm font-semibold">{{ currency(bill.total_amount) }}</div>
              </div>

              <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                <div>{{ humanDate(bill.created_at) }}</div>
                <div>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-green-100 text-green-800': bill.status === 'Paid',
                          'bg-red-100 text-red-800': bill.status === 'Unpaid',
                          'bg-yellow-100 text-yellow-800': bill.status === 'Pending'
                        }">{{ bill.status }}</span>
                </div>
              </div>

              <div class="mt-3 flex items-center gap-2">
                <input type="checkbox" class="h-4 w-4" :checked="selected.has(bill.id)" @change="() => toggleRow(bill.id)" />
                <Link :href="rRoute('billing.show', bill.id) ?? '#'" class="text-indigo-600 text-sm">View</Link>
                <button @click="printBill(bill)" class="text-sm text-gray-600">Print</button>
                <button v-if="bill.status !== 'Paid' && hasRoute('billing.markPaid')" @click="rowMarkPaid(bill)" class="ml-auto inline-flex items-center gap-2 px-3 py-1 rounded bg-indigo-600 text-white text-sm">Mark Paid</button>
              </div>
            </div>

            <div v-if="!filteredBills.length" class="p-4 text-center text-gray-500">No bills found.</div>
          </div>

          <!-- pagination + actions footer -->
          <div class="p-4 border-t flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <Pagination :links="bills.links" />
            </div>

            <div class="flex items-center gap-3">
              <button v-if="selectedCount" @click="confirmBulkMarkPaid" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded text-sm">
                Confirm Mark Paid ({{ selectedCount }})
              </button>
              <div v-else class="text-sm text-gray-500">Select bills to take bulk actions</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- confirmation modal (simple) -->
    <div v-if="showConfirmBulk" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/40" @click="showConfirmBulk = false"></div>
      <div class="bg-white rounded-lg shadow-lg p-6 z-10 w-full max-w-md">
        <h3 class="text-lg font-semibold">Confirm Bulk Mark Paid</h3>
        <p class="mt-2 text-sm text-gray-600">You're about to mark <strong>{{ selectedCount }}</strong> bill(s) as <strong>Paid</strong>. This action will be recorded in the audit trail.</p>
        <div class="mt-4 flex items-center justify-end gap-3">
          <button @click="showConfirmBulk = false" class="px-3 py-2 rounded bg-white border text-sm">Cancel</button>
          <button @click="performBulkMarkPaid" :disabled="performingBulk" class="px-4 py-2 rounded bg-indigo-600 text-white text-sm">
            <span v-if="!performingBulk">Confirm</span>
            <span v-else>Working...</span>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* small style helpers */
.form-checkbox { appearance: none; -webkit-appearance: none; border: 1px solid #d1d5db; background: white; border-radius: 4px; display: inline-block; width: 16px; height: 16px; vertical-align: middle; }
.form-checkbox:checked { background-color: #4F46E5; border-color: #4F46E5; background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4.5 10.5l3 3 8-8' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: center; }
</style>
