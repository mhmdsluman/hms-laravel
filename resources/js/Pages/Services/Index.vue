<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  services: { type: Object, default: () => ({ data: [], links: [] }) },
});

// UI state
const query = ref('');
const departmentFilter = ref('all');
const perPage = ref(10);

// Defensive access
const servicesPage = computed(() => props.services || { data: [], links: [] });
const servicesList = computed(() => Array.isArray(servicesPage.value.data) ? servicesPage.value.data : []);

// Department color mapping (solid colors)
const deptColors = {
  'Laboratory': 'bg-indigo-600 text-white',
  'Radiology': 'bg-green-600 text-white',
  'Pharmacy': 'bg-yellow-600 text-white',
  'Surgery': 'bg-red-600 text-white',
  'Outpatient': 'bg-blue-600 text-white',
  'Default': 'bg-gray-700 text-white',
};

// derive unique departments present on the current page
const departments = computed(() => {
  const set = new Set();
  servicesList.value.forEach(s => {
    if (s?.department) set.add(s.department);
  });
  return ['all', ...Array.from(set)];
});

// client-side filtering (filters current page only)
const filtered = computed(() => {
  const q = (query.value || '').toLowerCase().trim();
  return servicesList.value.filter(s => {
    if (departmentFilter.value !== 'all' && s.department !== departmentFilter.value) return false;
    if (!q) return true;
    return (s.name || '').toLowerCase().includes(q) || (s.department || '').toLowerCase().includes(q);
  }).slice(0, perPage.value);
});

// format price
const fmtPrice = (v) => {
  try {
    if (v === null || v === undefined || v === '') return '—';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(Number(v));
  } catch {
    return v;
  }
};

// map department -> tailwind classes (solid)
const badgeClassFor = (dept) => {
  return deptColors[dept] || deptColors.Default;
};

// Delete handler (confirm + Inertia delete)
const confirmDelete = (service) => {
  if (!service) return;
  const ok = confirm(`Delete service "${service.name}"? This action cannot be undone.`);
  if (!ok) return;
  // Use Ziggy route if available, otherwise attempt typical named route
  let url;
  try {
    url = typeof route === 'function' ? route('services.destroy', service.id) : `/services/${service.id}`;
  } catch {
    url = `/services/${service.id}`;
  }
  router.delete(url, {
    preserveState: true,
    onSuccess: () => {
      // optionally show toast; Inertia flash will handle it from backend
    }
  });
};
</script>

<template>
  <Head title="Service Catalogue" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-lg text-gray-900 truncate">Service Catalogue Management</h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Controls -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex items-center bg-white border rounded-lg px-3 py-2">
              <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35" /><circle cx="11" cy="11" r="6" stroke-width="2"/></svg>
              <input v-model="query" placeholder="Search services or departments" class="ml-2 w-64 md:w-96 outline-none text-sm" />
            </div>

            <select v-model="departmentFilter" class="rounded-lg border px-3 py-2 text-sm bg-white">
              <option v-for="d in departments" :key="d" :value="d">{{ d === 'all' ? 'All departments' : d }}</option>
            </select>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="typeof route === 'function' ? route('services.create') : '/services/create'" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-indigo-700 hover:bg-indigo-800 text-sm">
              Add New Service
            </Link>
            <div class="text-sm text-gray-600">Showing <span class="font-medium text-gray-900">{{ filtered.length }}</span> of <span class="font-medium text-gray-900">{{ servicesList.length }}</span></div>
          </div>
        </div>

        <!-- Card / Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-4 py-4 border-b">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800">All Services</h3>
                <span class="text-sm text-gray-500">Manage catalogue items and pricing</span>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-left">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Name</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Department</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Price</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Code</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y">
                <template v-if="filtered.length">
                  <tr v-for="service in filtered" :key="service.id" class="hover:bg-white">
                    <td class="px-6 py-4 align-middle">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 flex items-center justify-center rounded-md bg-gray-100 text-gray-800 font-semibold text-sm">
                          {{ (service.name || '?').slice(0,2).toUpperCase() }}
                        </div>
                        <div>
                          <div class="font-medium text-gray-800">{{ service.name }}</div>
                          <div class="text-xs text-gray-500 mt-0.5">{{ service.description || '' }}</div>
                        </div>
                      </div>
                    </td>

                    <td class="px-6 py-4 align-middle">
                      <span :class="['inline-flex items-center px-2 py-1 rounded text-xs font-semibold', badgeClassFor(service.department)]">
                        {{ service.department || 'Unassigned' }}
                      </span>
                    </td>

                    <td class="px-6 py-4 align-middle">
                      <div class="font-medium text-gray-800">{{ fmtPrice(service.price) }}</div>
                      <div class="text-xs text-gray-500 mt-0.5">{{ service.billing_type || '' }}</div>
                    </td>

                    <td class="px-6 py-4 align-middle text-sm text-gray-600">{{ service.code || '—' }}</td>

                    <td class="px-6 py-4 align-middle text-right whitespace-nowrap">
                      <div class="inline-flex items-center gap-2">
                        <Link :href="typeof route === 'function' ? route('services.show', service.id) : `/services/${service.id}`" class="px-3 py-1 rounded-md text-white bg-green-600 hover:bg-green-700 text-sm">
                          View
                        </Link>

                        <Link :href="typeof route === 'function' ? route('services.edit', service.id) : `/services/${service.id}/edit`" class="px-3 py-1 rounded-md text-white bg-blue-600 hover:bg-blue-700 text-sm">
                          Edit
                        </Link>

                        <button @click="confirmDelete(service)" class="px-3 py-1 rounded-md text-white bg-red-600 hover:bg-red-700 text-sm">
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <tr v-else>
                  <td class="px-6 py-12 text-center text-gray-500" colspan="5">
                    No services found on this page.
                    <div class="mt-3">
                      <Link :href="typeof route === 'function' ? route('services.create') : '/services/create'" class="inline-block px-4 py-2 bg-indigo-700 text-white rounded-md text-sm">Create a service</Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="px-4 py-4 border-t bg-white">
            <Pagination :links="servicesPage.links" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* keep badges solid (no shades) — any additional CSS tweaks can go here */
</style>
