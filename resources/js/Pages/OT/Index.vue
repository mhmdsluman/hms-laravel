<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

/**
 * Explicitly capture props into `props` and provide safe defaults.
 * (This prevents "scheduledOrders is not defined" when we reference it in computed).
 */
const props = defineProps({
  pendingOrders: { type: Array, default: () => [] },
  scheduledOrders: { type: Array, default: () => [] },
});

// Local copies (safe non-undefined arrays)
const pendingOrders = props.pendingOrders || [];
const scheduledOrders = props.scheduledOrders || [];

/* UI state */
const query = ref('');
const showOnlyToday = ref(false);
const sortBy = ref('time'); // 'time' | 'patient'

/* Safe helper functions */
const patientName = (item) => {
  const p = item?.order?.patient;
  if (!p) return 'Unknown Patient';
  return `${p.first_name || ''} ${p.last_name || ''}`.trim() || 'Unknown Patient';
};

const serviceName = (item) => item?.service?.name || '—';

const formatDateTime = (value) => {
  if (!value) return '';
  try {
    return new Date(value).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
  } catch {
    return value;
  }
};

/* Filtered / sorted lists (use the local arrays defined above) */
const filteredPending = computed(() => {
  const q = (query.value || '').toLowerCase().trim();
  let list = (pendingOrders || []).filter(i => {
    if (!q) return true;
    return (
      patientName(i).toLowerCase().includes(q) ||
      serviceName(i).toLowerCase().includes(q)
    );
  });

  if (sortBy.value === 'patient') {
    list = list.sort((a, b) => patientName(a).localeCompare(patientName(b)));
  }

  return list;
});

const filteredScheduled = computed(() => {
  const q = (query.value || '').toLowerCase().trim();
  let list = (scheduledOrders || []).filter(i => {
    if (!q) return true;
    return (
      patientName(i).toLowerCase().includes(q) ||
      serviceName(i).toLowerCase().includes(q)
    );
  });

  if (showOnlyToday.value) {
    const today = new Date();
    list = list.filter(i => {
      const t = i?.ot_schedule?.scheduled_start_time;
      if (!t) return false;
      const d = new Date(t);
      return d.getFullYear() === today.getFullYear() &&
             d.getMonth() === today.getMonth() &&
             d.getDate() === today.getDate();
    });
  }

  // sort by scheduled time ascending
  list = list.slice().sort((a, b) => {
    const ta = new Date(a?.ot_schedule?.scheduled_start_time || 0).getTime();
    const tb = new Date(b?.ot_schedule?.scheduled_start_time || 0).getTime();
    return ta - tb;
  });

  return list;
});
</script>

<template>
  <Head title="Operating Theater Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h2 class="font-semibold text-2xl text-gray-800">Operating Theater Dashboard</h2>
          <p class="text-sm text-gray-500 mt-1">Manage scheduling, assignments and operative notes</p>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center bg-white border rounded-lg px-3 py-1 shadow-sm">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35" /><circle cx="11" cy="11" r="6" stroke-width="2" /></svg>
            <input v-model="query" placeholder="Search patient or procedure" class="ml-2 w-56 outline-none text-sm" />
          </div>

          <button @click="showOnlyToday = !showOnlyToday" :class="showOnlyToday ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'" class="py-2 px-3 rounded-lg border shadow-sm text-sm">
            <svg v-if="showOnlyToday" class="inline h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            Today
          </button>

          <select v-model="sortBy" class="py-2 px-3 rounded-lg border bg-white text-sm shadow-sm">
            <option value="time">Sort: Time</option>
            <option value="patient">Sort: Patient</option>
          </select>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-white p-4 rounded-2xl shadow-md flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-500">Pending Scheduling</div>
              <div class="text-2xl font-semibold text-gray-800">{{ pendingOrders?.length || 0 }}</div>
            </div>
            <div class="bg-yellow-50 p-3 rounded-full">
              <svg class="h-6 w-6 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl shadow-md flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-500">Scheduled Procedures</div>
              <div class="text-2xl font-semibold text-gray-800">{{ scheduledOrders?.length || 0 }}</div>
            </div>
            <div class="bg-green-50 p-3 rounded-full">
              <svg class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" /></svg>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl shadow-md flex items-center justify-between">
            <div>
              <div class="text-sm text-gray-500">Next Procedure</div>
              <div class="text-2xl font-semibold text-gray-800">
                <span v-if="filteredScheduled.length">{{ formatDateTime(filteredScheduled[0].ot_schedule?.scheduled_start_time) }}</span>
                <span v-else class="text-gray-400">—</span>
              </div>
            </div>
            <div class="bg-blue-50 p-3 rounded-full">
              <svg class="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
            </div>
          </div>
        </div>

        <!-- Main grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Pending list -->
          <section class="lg:col-span-1 bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-800">Pending Scheduling</h3>
              <span class="text-sm text-gray-500">{{ filteredPending.length }} items</span>
            </div>

            <div class="space-y-3">
              <template v-if="filteredPending.length">
                <div v-for="item in filteredPending" :key="item.id" class="flex items-center justify-between p-3 rounded-lg border hover:shadow transition">
                  <div class="flex items-center gap-3">
                    <!-- avatar with initials -->
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold">
                      {{ (item?.order?.patient?.first_name || '?')[0] || '?' }}{{ (item?.order?.patient?.last_name || '?')[0] || '?' }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-800">{{ patientName(item) }}</div>
                      <div class="text-sm text-gray-500">{{ serviceName(item) }}</div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link :href="route('ot.create', { orderItem: item.id })" class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm shadow">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      Schedule
                    </Link>
                  </div>
                </div>
              </template>

              <div v-else class="py-8 text-center text-gray-400">
                No procedures are pending scheduling.
              </div>
            </div>
          </section>

          <!-- Scheduled timeline -->
          <section class="lg:col-span-2 bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-800">Scheduled Procedures</h3>
              <div class="text-sm text-gray-500">{{ filteredScheduled.length }} scheduled</div>
            </div>

            <div class="space-y-4">
              <template v-if="filteredScheduled.length">
                <div v-for="item in filteredScheduled" :key="item.id" class="p-4 rounded-lg border hover:shadow transition flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                      <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-700 font-semibold text-lg">
                        {{ (item?.order?.patient?.first_name || '?')[0] || '?' }}
                      </div>
                    </div>

                    <div>
                      <div class="flex items-center gap-2">
                        <div class="font-semibold text-gray-800">{{ patientName(item) }}</div>
                        <span class="text-sm text-gray-500">• {{ serviceName(item) }}</span>
                        <span class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                          Scheduled
                        </span>
                      </div>

                      <div class="mt-1 text-sm text-gray-500">
                        <svg class="inline h-4 w-4 mr-1 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14" /></svg>
                        {{ formatDateTime(item.ot_schedule?.scheduled_start_time) }}
                        <span v-if="item.ot_schedule?.room" class="mx-2">• Room {{ item.ot_schedule.room }}</span>
                      </div>

                      <div class="mt-2 text-sm text-gray-500">
                        <template v-if="item?.order?.notes">
                          {{ item.order.notes }}
                        </template>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link :href="route('operative-notes.create', { orderItem: item.id })" class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-green-600 text-white text-sm shadow">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /></svg>
                      Add Note
                    </Link>

                    <Link :href="route('ot.edit', { id: item.id })" class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-white border text-sm hover:bg-gray-50">
                      Edit
                    </Link>
                  </div>
                </div>
              </template>

              <div v-else class="py-12 text-center text-gray-400">
                No procedures are currently scheduled.
              </div>
            </div>
          </section>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* small polish for smoother card shadows */
.rounded-2xl { border-radius: 1rem; }
</style>
