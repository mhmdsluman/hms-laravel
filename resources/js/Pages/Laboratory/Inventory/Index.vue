<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  PlusIcon,
  ArrowDownTrayIcon,
  PencilIcon,
  CubeIcon,
  ExclamationCircleIcon,
  TruckIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/solid';
import { ref, computed } from 'vue';

/* Safe props wrapper - default ensures .data and .links exist */
const rawProps = defineProps({
  items: {
    type: Object,
    default: () => ({ data: [], links: [] })
  }
});
const items = computed(() => rawProps.items || { data: [], links: [] });

/* UI state */
const searchTerm = ref('');
const showLowStock = ref(false);
const selectedType = ref('all');
const tableLimit = ref(10);
const refreshing = ref(false);
const selectedIds = ref([]); // selected rows for bulk actions

/* Adjust stock modal state */
const adjustModal = ref({
  show: false,
  item: null,
  operation: 'add', // 'add' or 'remove'
  amount: 0
});

/* Helpers */
const formatNumber = (n) => (n === null || n === undefined ? '-' : n);
const isLowStock = (item) => {
  if (!item) return false;
  return Number(item.quantity_in_stock) <= Number(item.reorder_level);
};

const refresh = async () => {
  refreshing.value = true;
  if (typeof route === 'function') {
    await router.get(route('lab-inventory.index'), {}, { preserveState: true, preserveScroll: true });
  } else {
    window.location.reload();
  }
  refreshing.value = false;
};

/* Filtering & searching */
const normalized = (s) => (s || '').toString().toLowerCase();
const matchesSearch = (item) => {
  if (!searchTerm.value) return true;
  const q = normalized(searchTerm.value);
  return (
    normalized(item.name).includes(q) ||
    normalized(item.item_type).includes(q) ||
    normalized(String(item.sku)).includes(q)
  );
};

const filteredList = computed(() => {
  const list = items.value.data || [];
  return list.filter((it) => {
    if (!matchesSearch(it)) return false;
    if (selectedType.value !== 'all' && it.item_type !== selectedType.value) return false;
    if (showLowStock.value && !isLowStock(it)) return false;
    return true;
  });
});

/* Types for filter dropdown (derived from data) */
const types = computed(() => {
  const set = new Set();
  (items.value.data || []).forEach((i) => i.item_type && set.add(i.item_type));
  return Array.from(set).sort();
});

/* Selection helpers */
const toggleSelect = (id) => {
  const idx = selectedIds.value.indexOf(id);
  if (idx === -1) selectedIds.value.push(id);
  else selectedIds.value.splice(idx, 1);
};
const selectAllVisible = (checked) => {
  if (checked) selectedIds.value = filteredList.value.map(i => i.id);
  else selectedIds.value = [];
};

/* Export CSV (simple client-side) */
const exportCsvFrom = (rows, filename = 'lab-inventory.csv') => {
  if (!rows || !rows.length) return;
  const header = ['ID','Name','SKU','Type','In Stock','Reorder Level','Unit','Unit Cost','Description'];
  const csv = [
    header.join(','),
    ...rows.map(r => [
      r.id,
      `"${(r.name || '').replace(/"/g,'""')}"`,
      `"${(r.sku || '').replace(/"/g,'""')}"`,
      `"${(r.item_type || '').replace(/"/g,'""')}"`,
      r.quantity_in_stock ?? '',
      r.reorder_level ?? '',
      `"${(r.unit || '')}"`,
      r.unit_cost ?? '',
      `"${(r.description || '').replace(/"/g,'""')}"`,
    ].join(','))
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
};

const exportAll = () => exportCsvFrom(items.value.data || [], 'lab-inventory-all.csv');
const exportSelected = () => {
  const rows = (items.value.data || []).filter(i => selectedIds.value.includes(i.id));
  exportCsvFrom(rows, 'lab-inventory-selected.csv');
};

/* Adjust stock modal actions */
const openAdjustModal = (item) => {
  adjustModal.value.show = true;
  adjustModal.value.item = { ...item };
  adjustModal.value.operation = 'add';
  adjustModal.value.amount = 0;
};
const closeAdjustModal = () => {
  adjustModal.value.show = false;
  adjustModal.value.item = null;
  adjustModal.value.amount = 0;
};
const submitAdjust = async () => {
  const item = adjustModal.value.item;
  const amount = Number(adjustModal.value.amount || 0);
  const op = adjustModal.value.operation;
  if (!item?.id || amount <= 0) return;

  if (typeof route === 'function') {
    try {
      await router.patch(route('lab-inventory.adjustStock', item.id), { amount, operation: op }, { preserveScroll: true });
      closeAdjustModal();
    } catch (e) {
      console.error('Adjust request failed', e);
    }
  } else {
    console.warn('No route() helper available; cannot perform adjust via Inertia.');
    closeAdjustModal();
  }
};
</script>

<template>
  <Head title="Lab Inventory" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
          <CubeIcon class="h-7 w-7 text-indigo-500" />
          <div>
            <h2 class="font-semibold text-xl text-gray-900">Laboratory Inventory Management</h2>
            <p class="text-sm text-gray-500">Track stock, reorder levels and quickly adjust inventory.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route('lab-inventory.create')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 shadow-sm rounded-md text-sm font-medium hover:shadow-lg transition">
            <PlusIcon class="h-4 w-4 text-green-600" /> Add New Item
          </Link>

          <button @click="exportAll" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 shadow-sm rounded-md text-sm hover:bg-gray-50">
            <ArrowDownTrayIcon class="h-4 w-4 text-gray-600" /> Export CSV
          </button>

          <button @click="refresh" :disabled="refreshing"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-md text-sm font-medium hover:opacity-95 transition">
            <ArrowPathIcon class="h-4 w-4" /> <span v-if="!refreshing">Refresh</span><span v-else>Refreshing...</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Flash -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="p-3 bg-green-50 border border-green-100 rounded-lg text-green-700 flex items-center gap-2">
          <CheckCircleIcon class="h-5 w-5" /> {{ $page.props.flash.success }}
        </div>

        <!-- Controls -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div class="flex items-center gap-3 w-full md:w-1/2">
            <div class="relative w-full">
              <input v-model="searchTerm" type="text" placeholder="Search by name, SKU or type..."
                     class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200" />
              <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-gray-400" />
            </div>

            <select v-model="selectedType" class="px-3 py-2 border rounded-md text-sm">
              <option value="all">All Types</option>
              <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
            </select>

            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
              <input type="checkbox" v-model="showLowStock" class="rounded border-gray-300" /> Show low stock only
            </label>
          </div>

          <div class="flex items-center gap-3">
            <button @click="exportSelected" :disabled="!selectedIds.length"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border rounded-md text-sm hover:bg-gray-50">
              <DocumentDuplicateIcon class="h-4 w-4" /> Export Selected
            </button>

            <div class="text-sm text-gray-500">Showing <strong>{{ filteredList.length }}</strong> items</div>
          </div>
        </div>

        <!-- Inventory table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4">
            <table class="min-w-full divide-y">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    <input type="checkbox" :checked="filteredList.length && selectedIds.length === filteredList.length"
                           @change="(e) => selectAllVisible(e.target.checked)" />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">In Stock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                  <th class="px-6 py-3 relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y">
                <tr v-for="item in filteredList.slice(0, tableLimit)" :key="item.id" class="hover:bg-gray-50 transition">
                  <td class="px-4 py-4 whitespace-nowrap">
                    <input type="checkbox" :value="item.id" v-model="selectedIds" />
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div class="flex-shrink-0 rounded-md bg-indigo-50 h-10 w-10 flex items-center justify-center text-indigo-600">
                        <CubeIcon class="h-5 w-5" />
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                        <div class="text-xs text-gray-400">{{ item.sku || '—' }}</div>
                      </div>
                    </div>
                    <div v-if="item.description" class="mt-2 text-xs text-gray-500">{{ item.description }}</div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.item_type || '—' }}</td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                      :class="isLowStock(item) ? 'text-red-600' : 'text-gray-800'">
                    {{ formatNumber(item.quantity_in_stock) }}
                    <span v-if="isLowStock(item)" class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">
                      <ExclamationCircleIcon class="h-4 w-4" /> Low
                    </span>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatNumber(item.reorder_level) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.unit || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.unit_cost ?? '—' }}</td>

                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end gap-2">
                    <Link :href="route('lab-inventory.edit', item.id)" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900">
                      <PencilIcon class="h-4 w-4" /> Edit
                    </Link>

                    <button @click="openAdjustModal(item)"
                            class="inline-flex items-center gap-2 px-3 py-1 bg-white border rounded-md text-sm hover:bg-gray-50">
                      <TruckIcon class="h-4 w-4" /> Adjust
                    </button>
                  </td>
                </tr>

                <tr v-if="filteredList.length === 0">
                  <td colspan="8" class="px-6 py-10 text-center text-gray-400">No items matched your filters.</td>
                </tr>
              </tbody>
            </table>

            <!-- pagination + per page -->
            <div class="mt-6 flex items-center justify-between">
              <div>
                <label class="text-sm text-gray-600">Rows per page:</label>
                <select v-model="tableLimit" class="ml-2 px-2 py-1 border rounded-md text-sm">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                </select>
              </div>

              <!-- SAFE: ensure links is always an array -->
              <Pagination :links="items.links || []" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Adjust Stock Modal -->
    <transition name="fade">
      <div v-if="adjustModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeAdjustModal"></div>

        <div class="relative bg-white rounded-2xl shadow-lg w-full max-w-md p-6 z-10">
          <h3 class="text-lg font-semibold mb-2">Adjust Stock — {{ adjustModal.item?.name }}</h3>
          <p class="text-sm text-gray-500 mb-4">Current: <strong>{{ adjustModal.item?.quantity_in_stock ?? '-' }}</strong> — Reorder: <strong>{{ adjustModal.item?.reorder_level ?? '-' }}</strong></p>

          <div class="space-y-3">
            <div>
              <label class="block text-sm text-gray-600">Operation</label>
              <select v-model="adjustModal.operation" class="mt-1 px-3 py-2 border rounded-md w-full">
                <option value="add">Add to stock</option>
                <option value="remove">Remove from stock</option>
              </select>
            </div>

            <div>
              <label class="block text-sm text-gray-600">Amount</label>
              <input type="number" v-model.number="adjustModal.amount" min="0" class="mt-1 px-3 py-2 border rounded-md w-full" />
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button @click="closeAdjustModal" class="px-4 py-2 rounded-md bg-gray-100 text-sm">Cancel</button>
              <button @click="submitAdjust" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm">Submit</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AuthenticatedLayout>
</template>

<style scoped>
/* small niceties */
input::placeholder { color: rgba(107,114,128,0.9); }
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
thead.sticky th { backdrop-filter: blur(4px); }
</style>
