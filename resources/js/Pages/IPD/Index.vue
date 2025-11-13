<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { defineProps, computed } from 'vue';

const props = defineProps({
  // defensive default so template won't break when controller does not pass data
  bedsByWard: { type: Object, default: () => ({}) },
});

/**
 * Convert bedsByWard (object like { WardA: [...], WardB: [...] })
 * into an array of [wardName, bedsArray] so v-for is safe and predictable.
 */
const wards = computed(() => {
  try {
    const obj = props.bedsByWard || {};
    return Object.entries(obj); // [[wardName, bedsArray], ...]
  } catch {
    return [];
  }
});

/**
 * Safely build a route (Ziggy may not be present in some contexts).
 * Fallback is a simple URL string.
 */
const safeRoute = (name, params = {}) => {
  try {
    return typeof route === 'function' ? route(name, params) : null;
  } catch {
    return null;
  }
};

const dischargePatient = async (admissionId) => {
  if (!admissionId) return;
  if (!confirm('Are you sure you want to discharge this patient?')) return;

  // Prefer named route if available, otherwise fallback to RESTful url
  let url;
  try {
    url = typeof route === 'function' ? route('admissions.discharge', admissionId) : `/admissions/${admissionId}/discharge`;
  } catch {
    url = `/admissions/${admissionId}/discharge`;
  }

  // call patch and optionally handle errors
  router.patch(url, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Inertia flash from backend should show success message; nothing more needed here.
    },
    onError: (errors) => {
      // optionally show alert for debugging (keep minimal)
      alert('Failed to discharge patient.');
      console.error(errors);
    }
  });
};
</script>

<template>
  <Head title="Inpatient Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inpatient Bed Management</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- flash messages (defensive access) -->
        <div v-if="$page && $page.props && $page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page && $page.props && $page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
          {{ $page.props.flash.error }}
        </div>

        <!-- Empty state when there are no wards -->
        <div v-if="wards.length === 0" class="bg-white shadow-sm rounded-lg p-6 text-center">
          <p class="text-gray-600">No ward/bed data available.</p>
          <p class="text-sm text-gray-400 mt-2">Make sure the controller passes <code>bedsByWard</code> or load some data.</p>
        </div>

        <div v-else class="space-y-6">
          <!-- Each ward -->
          <div v-for="[ward, beds] in wards" :key="ward" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <h3 class="text-lg font-semibold mb-4">{{ ward }}</h3>

              <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div v-for="bed in (beds || [])" :key="bed?.id"
                     class="p-4 rounded-lg text-center border flex flex-col justify-between"
                     :class="{
                       'bg-green-100 border-green-300': bed?.status === 'Available',
                       'bg-red-100 border-red-300': bed?.status === 'Occupied',
                       'bg-yellow-100 border-yellow-300': bed?.status === 'Cleaning',
                     }"
                >
                  <div>
                    <p class="font-bold">{{ bed?.bed_number ?? '—' }}</p>
                    <p class="text-sm text-gray-600">{{ bed?.status ?? 'Unknown' }}</p>

                    <div v-if="bed?.status === 'Occupied' && bed?.current_admission" class="mt-2 text-xs">
                      <p class="font-semibold">
                        <!-- safe patient access -->
                        {{ (bed.current_admission.patient?.first_name || '') + ' ' + (bed.current_admission.patient?.last_name || '') }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-2 space-y-1">
                    <Link v-if="bed?.status === 'Available'"
                          :href="safeRoute('admissions.create', { bed_id: bed?.id }) || `/admissions/create?bed_id=${bed?.id}`"
                          class="inline-block px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 w-full"
                    >
                      Admit
                    </Link>

                    <div v-if="bed?.status === 'Occupied' && bed?.current_admission">
                      <Link :href="safeRoute('mar.show', bed.current_admission.id) || `/mar/${bed.current_admission.id}`"
                            class="inline-block px-3 py-1 bg-indigo-500 text-white text-xs rounded hover:bg-indigo-600 w-full mb-1"
                      >
                        View MAR
                      </Link>

                      <button @click="dischargePatient(bed.current_admission.id)"
                              class="inline-block px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 w-full"
                      >
                        Discharge
                      </button>
                    </div>
                  </div>
                </div> <!-- end bed -->
              </div> <!-- end grid -->
            </div>
          </div> <!-- end ward -->
        </div> <!-- end wards list -->

      </div>
    </div>
  </AuthenticatedLayout>
</template>
