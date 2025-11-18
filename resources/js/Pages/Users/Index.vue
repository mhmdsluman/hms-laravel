<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    users: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({ sort: 'created_at', direction: 'desc', perPage: 10, q: '' }) },
});

// UI state
const query = ref('');
const roleFilter = ref('all');
const perPage = ref(10);

// Defensive access
const usersPage = computed(() => props.users || { data: [], links: [] });
const usersList = computed(() => Array.isArray(usersPage.value.data) ? usersPage.value.data : []);

// Role color mapping (solid colors)
const roleColors = {
  'Admin': 'bg-indigo-600 text-white',
  'User': 'bg-green-600 text-white',
  'Manager': 'bg-yellow-600 text-white',
  'Support': 'bg-blue-600 text-white',
  // Added roles from your system
  'clinician': 'bg-blue-700 text-white',
  'clerk': 'bg-gray-600 text-white',
  'lab': 'bg-purple-600 text-white',
  'pharmacy': 'bg-green-600 text-white',
  'radiology': 'bg-sky-600 text-white',
  'patient': 'bg-slate-500 text-white',
  'nurse': 'bg-cyan-600 text-white',
  'ot_manager': 'bg-orange-600 text-white',
  'Default': 'bg-gray-700 text-white',
};

// derive unique roles present on the current page
const roles = computed(() => {
  const set = new Set();
  usersList.value.forEach(u => { if (u?.role) set.add(u.role); });
  return ['all', ...Array.from(set)];
});

// client-side filtering (filters current page only)
const filtered = computed(() => {
  const q = (query.value || '').toLowerCase().trim();
  return usersList.value.filter(u => {
    if (roleFilter.value !== 'all' && u.role !== roleFilter.value) return false;
    if (!q) return true;
    return (u.name || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q) || (u.speciality || '').toLowerCase().includes(q);
  }).slice(0, perPage.value);
});

// enhanced search suggestions (client-side, current page only)
const suggestions = computed(() => {
  const q = (query.value || '').toLowerCase().trim();
  if (q.length < 2) return [];
  const names = usersList.value.map(u => u.name || '').filter(Boolean);
  const matches = names.filter(n => n.toLowerCase().includes(q));
  // unique and limit
  return Array.from(new Set(matches)).slice(0, 5);
});

// sync incoming filters with local controls
perPage.value = props.filters?.perPage || perPage.value;
query.value = props.filters?.q || query.value;

const currentSort = computed(() => props.filters?.sort || 'created_at');
const currentDirection = computed(() => props.filters?.direction || 'desc');

const toggleDirection = (col) => {
    if (currentSort.value === col) return currentDirection.value === 'asc' ? 'desc' : 'asc';
    return 'asc';
};

const sortHref = (col) => {
    const dir = toggleDirection(col);
    try {
        return typeof route === 'function' ? route('users.index', { sort: col, direction: dir, q: query.value, perPage: perPage.value }) : `?sort=${col}&direction=${dir}&q=${encodeURIComponent(query.value)}&perPage=${perPage.value}`;
    } catch {
        return `?sort=${col}&direction=${dir}&q=${encodeURIComponent(query.value)}&perPage=${perPage.value}`;
    }
};

// map role -> tailwind classes (solid)
const badgeClassFor = (role) => roleColors[role] || roleColors.Default;

// format date
const fmtDate = (d) => {
    try { return new Date(d).toLocaleDateString(); } catch { return d; }
};

// initials helper for avatar fallback
const initials = (name) => {
    if (!name) return 'U';
    return (name.split(' ').map(n => n[0] || '').join('') || 'U').slice(0,2).toUpperCase();
};

// Delete handler (confirm + Inertia delete)
const confirmDelete = (user) => {
  if (!user) return;
  const ok = confirm(`Delete user "${user.name}"? This action cannot be undone.`);
  if (!ok) return;

  let url;
  try {
    url = typeof route === 'function' ? route('users.destroy', user.id) : `/users/${user.id}`;
  } catch {
    url = `/users/${user.id}`;
  }

  router.delete(url, {
    preserveState: true,
    onSuccess: () => {
      // backend flash will handle messaging via layout
    }
  });
};

// click suggestion
const applySuggestion = (s) => { query.value = s; };

// clear helper
const clearSearch = () => { query.value = ''; };

// keep a small debounce to avoid jitter when showing suggestions
let debounceTimer = null;
watch(query, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    // nothing else needed — suggestions/computed will update
  }, 150);
});
</script>

<template>
  <Head title="User Directory" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-lg text-gray-900 truncate">User Directory Management</h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div class="flex items-center gap-3 w-full md:w-auto">

            <!-- Enhanced search box -->
            <div class="relative w-full md:w-96">
              <div class="relative flex items-center bg-white border rounded-lg px-3 py-2">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35" /><circle cx="11" cy="11" r="6" stroke-width="2"/></svg>
                <input v-model="query" placeholder="Search by name, email or speciality" class="ml-2 w-full outline-none text-sm" />
                <button v-if="query" @click="clearSearch" class="ml-2 text-xs px-2 py-1 rounded-md border">Clear</button>
              </div>

              <!-- Suggestions dropdown -->
                            <div v-if="suggestions.length" class="absolute left-0 right-0 mt-1 bg-white border rounded-md shadow z-10">
                                <ul>
                                    <li v-for="s in suggestions" :key="s" class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm" @click="applySuggestion(s)">
                                        {{ s }}
                                    </li>
                                </ul>
                            </div>
            </div>

                        <select v-model="roleFilter" class="rounded-lg border px-3 py-2 text-sm bg-white">
                            <option v-for="r in roles" :key="r" :value="r">{{ r === 'all' ? 'All roles' : r.charAt(0).toUpperCase() + r.slice(1) }}</option>
                        </select>

                        <!-- Per-page selector -->
                        <select v-model="perPage" class="rounded-lg border px-3 py-2 text-sm bg-white">
                            <option :value="10">10 / page</option>
                            <option :value="25">25 / page</option>
                            <option :value="50">50 / page</option>
                        </select>
          </div>

          <div class="flex items-center gap-3">
            <Link :href="typeof route === 'function' ? route('users.create') : '/users/create'" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-indigo-700 hover:bg-indigo-800 text-sm">
              Add New User
            </Link>
            <div class="text-sm text-gray-600">Showing <span class="font-medium text-gray-900">{{ filtered.length }}</span> of <span class="font-medium text-gray-900">{{ usersList.length }}</span></div>
          </div>
        </div>

        <!-- Card / Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-4 py-4 border-b">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800">All Users</h3>
                <span class="text-sm text-gray-500">Manage users, roles and specialties</span>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
                        <table class="min-w-full text-left divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">
                                <Link :href="sortHref('name')" class="flex items-center gap-2">
                                    <span>Name</span>
                                    <span v-if="currentSort === 'name'" class="text-xs">{{ currentDirection === 'asc' ? '▲' : '▼' }}</span>
                                </Link>
                            </th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">
                                <Link :href="sortHref('email')" class="flex items-center gap-2">
                                    <span>Email</span>
                                    <span v-if="currentSort === 'email'" class="text-xs">{{ currentDirection === 'asc' ? '▲' : '▼' }}</span>
                                </Link>
                            </th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">
                                <Link :href="sortHref('role')" class="flex items-center gap-2">
                                    <span>Role</span>
                                    <span v-if="currentSort === 'role'" class="text-xs">{{ currentDirection === 'asc' ? '▲' : '▼' }}</span>
                                </Link>
                            </th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">
                                <Link :href="sortHref('speciality')" class="flex items-center gap-2">
                                    <span>Speciality</span>
                                    <span v-if="currentSort === 'speciality'" class="text-xs">{{ currentDirection === 'asc' ? '▲' : '▼' }}</span>
                                </Link>
                            </th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                        </tr>
                    </thead>

                            <tbody class="bg-white divide-y divide-gray-100">
                                <template v-if="filtered.length">
                                    <tr v-for="user in filtered" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <!-- Avatar: image or initials fallback -->
                                                <div v-if="user.photo_url">
                                                    <img :src="user.photo_url" :alt="user.name" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                                </div>
                                                <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-700">
                                                    {{ initials(user.name) }}
                                                </div>
                                                <div>
                          <!-- START: Clickable Name -->
                          <Link :href="typeof route === 'function' ? route('users.show', user.id) : `/users/${user.id}`" class="font-medium text-gray-800 hover:text-blue-600 hover:underline">
                                    {{ user.name }}
                                </Link>
                          <!-- END: Clickable Name -->
                          <div class="text-xs text-gray-500 mt-0.5">Joined: {{ fmtDate(user.created_at) }}</div>
                        </div>
                      </div>
                    </td>

                        <td class="px-6 py-4 align-middle">
                            <div class="text-sm text-gray-700">{{ user.email }}</div>
                        </td>

                    <td class="px-6 py-4 align-middle">
                      <span :class="['inline-flex items-center px-2 py-1 rounded text-xs font-semibold capitalize', badgeClassFor(user.role)]">{{ (user.role || 'Unassigned').replace('_', ' ') }}</span>
                    </td>

                    <td class="px-6 py-4 align-middle">
                      <div class="text-sm text-gray-700">{{ user.speciality || '—' }}</div>
                    </td>

                    <td class="px-6 py-4 align-middle text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <Link :href="typeof route === 'function' ? route('users.show', user.id) : `/users/${user.id}`" class="px-3 py-1 rounded-md text-white bg-green-600 hover:bg-green-700 text-sm">View</Link>

                                    <Link :href="typeof route === 'function' ? route('users.edit', user.id) : `/users/${user.id}/edit`" class="px-3 py-1 rounded-md text-white bg-blue-600 hover:bg-blue-700 text-sm">Edit</Link>

                                    <button @click="confirmDelete(user)" class="px-3 py-1 rounded-md text-white bg-red-600 hover:bg-red-700 text-sm">Delete</button>
                                </div>
                    </td>
                  </tr>
                </template>

                <tr v-else>
                                <td class="px-6 py-12 text-center text-gray-500" colspan="5">
                                    No users found on this page.
                                    <div class="mt-3">
                                        <Link :href="typeof route === 'function' ? route('users.create') : '/users/create'" class="inline-block px-4 py-2 bg-indigo-700 text-white rounded-md text-sm">Create a user</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
            </table>
          </div>

          <div class="px-4 py-4 border-t bg-white">
            <Pagination :links="usersPage.links" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* keep badges solid (no shades) — any additional CSS tweaks can go here */
</style>
