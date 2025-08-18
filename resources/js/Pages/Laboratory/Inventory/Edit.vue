<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
  ArrowPathIcon,
  TrashIcon,
  PencilSquareIcon,
  ArrowLeftOnRectangleIcon,
  ExclamationCircleIcon,
  CubeIcon
} from '@heroicons/vue/24/solid';
import { ref, computed } from 'vue';

/* --- Safe props & computed item --- */
const props = defineProps({
  item: {
    type: Object,
    default: () => ({})
  }
});
const item = computed(() => props.item ?? {});

/* --- Form (use initial values but fall back to sensible defaults) --- */
const form = useForm({
  name: item.value.name ?? '',
  sku: item.value.sku ?? '',
  item_type: item.value.item_type ?? 'Consumable',
  unit: item.value.unit ?? '',
  unit_cost: item.value.unit_cost ?? '',
  quantity_in_stock: item.value.quantity_in_stock ?? 0,
  reorder_level: item.value.reorder_level ?? 0,
  supplier: item.value.supplier ?? '',
  description: item.value.description ?? ''
});

/* --- UI state --- */
const deleting = ref(false);
const showDeleteModal = ref(false);
const submitting = computed(() => form.processing);

/* --- Helpers --- */
const isLowStock = computed(() => {
  if (item.value == null) return false;
  const q = Number(form.quantity_in_stock ?? 0);
  const r = Number(form.reorder_level ?? 0);
  return q <= r;
});

const submit = async () => {
  if (!item.value?.id) return;
  if (typeof route !== 'function') return;
  form.put(route('lab-inventory.update', item.value.id), {
    preserveScroll: true,
    onFinish: () => { /* can handle post-submit UI here */ }
  });
};

const confirmDelete = () => {
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
};

const destroy = async () => {
  if (!item.value?.id) return;
  if (typeof route !== 'function') {
    console.warn('route() helper not available; cannot delete.');
    return;
  }
  deleting.value = true;
  try {
    await router.delete(route('lab-inventory.destroy', item.value.id), { preserveScroll: true });
  } catch (e) {
    console.error(e);
  } finally {
    deleting.value = false;
    showDeleteModal.value = false;
  }
};
</script>

<template>
  <Head title="Edit Lab Stock" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <CubeIcon class="h-6 w-6 text-indigo-500" />
          <div>
            <h2 class="font-semibold text-lg text-gray-900">Edit Lab Inventory Item</h2>
            <p class="text-sm text-gray-500">Update item details, stock and supplier information.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route('lab-inventory.index')" class="inline-flex items-center gap-2 px-3 py-2 bg-white border rounded-md text-sm hover:shadow transition">
            <ArrowLeftOnRectangleIcon class="h-4 w-4" /> Back to Inventory
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-3 bg-green-50 border border-green-100 rounded-md text-green-700">
          {{ $page.props.flash.success }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Form Card -->
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Item Name</label>
                <input type="text" v-model="form.name" class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-200" required />
                <div v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">SKU</label>
                  <input type="text" v-model="form.sku" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                  <div v-if="form.errors.sku" class="text-xs text-red-600 mt-1">{{ form.errors.sku }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Item Type</label>
                  <select v-model="form.item_type" class="mt-1 block w-full px-3 py-2 border rounded-md">
                    <option>Reagent</option>
                    <option>Consumable</option>
                    <option>Equipment</option>
                    <option>Other</option>
                  </select>
                  <div v-if="form.errors.item_type" class="text-xs text-red-600 mt-1">{{ form.errors.item_type }}</div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Quantity in Stock</label>
                  <input type="number" v-model.number="form.quantity_in_stock" min="0" class="mt-1 block w-full px-3 py-2 border rounded-md" required />
                  <div v-if="form.errors.quantity_in_stock" class="text-xs text-red-600 mt-1">{{ form.errors.quantity_in_stock }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Re-order Level</label>
                  <input type="number" v-model.number="form.reorder_level" min="0" class="mt-1 block w-full px-3 py-2 border rounded-md" required />
                  <div v-if="form.errors.reorder_level" class="text-xs text-red-600 mt-1">{{ form.errors.reorder_level }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit</label>
                  <input type="text" v-model="form.unit" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit Cost</label>
                  <input type="text" v-model="form.unit_cost" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Supplier</label>
                  <input type="text" v-model="form.supplier" class="mt-1 block w-full px-3 py-2 border rounded-md" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="4" class="mt-1 block w-full px-3 py-2 border rounded-md"></textarea>
              </div>

              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <Link :href="route('lab-inventory.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="confirmDelete" class="inline-flex items-center gap-2 px-4 py-2 bg-white border text-red-600 rounded-md hover:bg-red-50">
                    <TrashIcon class="h-4 w-4" /> Delete
                  </button>

                  <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-md">
                    <PencilSquareIcon class="h-4 w-4" /> <span v-if="!submitting">Update Item</span><span v-else>Updating...</span>
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Preview Card -->
          <aside class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div class="flex items-start gap-3">
              <div class="rounded-md bg-indigo-50 p-3">
                <CubeIcon class="h-6 w-6 text-indigo-600" />
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-gray-500">Item</div>
                    <div class="font-semibold text-gray-900">{{ form.name || '—' }}</div>
                  </div>

                  <div class="text-right">
                    <div class="text-xs text-gray-400">SKU</div>
                    <div class="text-sm text-gray-800">{{ form.sku || '—' }}</div>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <div class="text-xs text-gray-400">In Stock</div>
                    <div class="font-medium" :class="isLowStock ? 'text-red-600' : 'text-gray-800'">{{ form.quantity_in_stock }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400">Re-order Level</div>
                    <div class="font-medium text-gray-800">{{ form.reorder_level }}</div>
                  </div>

                  <div>
                    <div class="text-xs text-gray-400">Unit</div>
                    <div class="font-medium text-gray-800">{{ form.unit || '—' }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400">Unit Cost</div>
                    <div class="font-medium text-gray-800">{{ form.unit_cost || '—' }}</div>
                  </div>
                </div>

                <div class="mt-4">
                  <div v-if="isLowStock" class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                    <ExclamationCircleIcon class="h-4 w-4" /> Low Stock
                  </div>

                  <div v-else class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                    <ArrowPathIcon class="h-4 w-4" /> Sufficient
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="text-xs text-gray-400">Supplier</div>
              <div class="text-sm text-gray-800">{{ form.supplier || '—' }}</div>
            </div>

            <div v-if="form.description" class="text-sm text-gray-600">
              {{ form.description }}
            </div>
          </aside>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-40 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="cancelDelete"></div>
        <div class="relative bg-white rounded-2xl shadow-lg w-full max-w-md p-6 z-50">
          <h3 class="text-lg font-semibold mb-2">Delete Item</h3>
          <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete <strong>{{ item.value?.name || 'this item' }}</strong>? This action cannot be undone.</p>

          <div class="flex justify-end gap-3">
            <button @click="cancelDelete" class="px-3 py-2 rounded-md bg-gray-100">Cancel</button>
            <button @click="destroy" :disabled="deleting" class="px-4 py-2 rounded-md bg-red-600 text-white">
              <span v-if="!deleting">Yes, delete</span>
              <span v-else>Deleting...</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </AuthenticatedLayout>
</template>

<style scoped>
input::placeholder { color: rgba(107,114,128,0.9); }
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
