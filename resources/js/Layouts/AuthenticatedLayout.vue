<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { hasRole } = usePermissions();
const isSidebarCollapsed = ref(true);

onMounted(() => {
  // Set document direction if the html lang attribute is set to 'ar'
  document.documentElement.dir = document.documentElement.lang === 'ar' ? 'rtl' : 'ltr';
});

const switchLanguage = (lang) => {
  router.get(`/language/${lang}`, {}, {
    onSuccess: () => {
      // reload so backend-provided locale and translations are applied
      window.location.reload();
    },
  });
};

// --- START: Global Flash Message Logic ---
const page = usePage(); // still used by the watcher
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref('success');

watch(
  () => [page.props.value?.flash?.success, page.props.value?.flash?.error],
  ([success, error]) => {
    if (success) {
      flashMessage.value = success;
      flashType.value = 'success';
      showFlash.value = true;
    } else if (error) {
      flashMessage.value = error;
      flashType.value = 'error';
      showFlash.value = true;
    } else {
      showFlash.value = false;
    }

    if (showFlash.value) {
      // auto-hide after 5s
      setTimeout(() => {
        showFlash.value = false;
      }, 5000);
    }
  },
  { deep: true }
);
// --- END: Global Flash Message Logic ---
</script>

<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside
      class="flex-shrink-0 bg-white border-r rtl:border-r-0 rtl:border-l transition-all duration-300 ease-in-out flex flex-col"
      :class="isSidebarCollapsed ? 'w-20' : 'w-64'"
      @mouseenter="isSidebarCollapsed = false"
      @mouseleave="isSidebarCollapsed = true"
    >
      <div class="h-16 flex items-center justify-center border-b flex-shrink-0 sticky top-0 bg-white z-10">
        <h1 class="text-2xl font-bold text-blue-600 transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">HMS</h1>
        <h1 class="text-2xl font-bold text-blue-600 transition-opacity duration-200 absolute" :class="isSidebarCollapsed ? 'opacity-100' : 'opacity-0'">H</h1>
      </div>

      <nav class="mt-4 flex-1 overflow-y-auto">
        <div class="px-6 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Main Menu</div>

        <Link :href="route('dashboard')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">{{ $t('Dashboard') }}</span>
        </Link>

        <Link :href="route('patients.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-3-5.197m0 0A7.962 7.962 0 0112 4.354a7.962 7.962 0 013 3.197m-3-3.197a4 4 0 110 5.292"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">{{ $t('Patient Registration') }}</span>
        </Link>

        <Link :href="route('appointments.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Appointments</span>
        </Link>

        <!-- Use $page.props with optional chaining in the template -->
        <Link v-if="hasRole(['admin', 'clerk', 'nurse'])" :href="route('ipd.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Inpatient (IPD)</span>
        </Link>

        <Link v-if="hasRole(['admin', 'nurse'])" :href="route('nursing.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h.5A2.5 2.5 0 0021.5 5.5V3.935m-18 0A2.965 2.965 0 016 3.055 2.965 2.965 0 018 3.935m13 0a2.965 2.965 0 00-2-2.965 2.965 2.965 0 00-2 2.965m0 0V5.5A2.5 2.5 0 0018.5 8h-.5a2 2 0 00-2 2 2 2 0 11-4 0 2 2 0 00-2-2h-.5A2.5 2.5 0 006.5 5.5V3.935" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Nursing Station</span>
        </Link>

        <Link v-if="hasRole(['admin', 'clerk', 'nurse', 'emergency'])" :href="route('emergency.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Emergency</span>
        </Link>

        <div class="px-6 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Departments</div>

        <Link v-if="hasRole(['admin', 'lab'])" :href="route('lab.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Laboratory</span>
        </Link>

        <Link v-if="hasRole(['admin', 'pharmacy'])" :href="route('pharmacy.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4h.01M18 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Pharmacy</span>
        </Link>

        <Link v-if="hasRole(['admin', 'radiology'])" :href="route('radiology.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Radiology</span>
        </Link>

        <Link v-if="hasRole(['admin', 'ot_manager', 'clinician'])" :href="route('ot.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Operating Theater</span>
        </Link>

        <Link v-if="hasRole(['admin', 'clerk'])" :href="route('billing.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 4h.01M18 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Billing</span>
        </Link>

        <div class="px-6 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Admin</div>

        <Link v-if="hasRole('admin')" :href="route('users.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.084-1.28-.24-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.084-1.28.24-1.857m10 0A5.98 5.98 0 0014 15c-1.657 0-3.123.739-4.144 1.857m4.144-1.857A5.98 5.98 0 0010 15c-1.657 0-3.123.739-4.144 1.857M14 10a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">User Management</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('services.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Service Catalogue</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('test-catalogue.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Test Catalogue</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('templates.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Template Builder</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('audit.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 20.417l5.618-5.618a12.02 12.02 0 008.618-3.04A11.955 11.955 0 0121 12a11.955 11.955 0 01-2.618-6.016z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Audit Trail</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('formulary.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Formulary Mgt.</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('order-sets.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Order Set Builder</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('insurance-providers.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 20.417l5.618-5.618a12.02 12.02 0 008.618-3.04A11.955 11.955 0 0121 12a11.955 11.955 0 01-2.618-6.016z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Insurance Providers</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('analytics.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <svg class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Analytics</span>
        </Link>
      </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="h-16 bg-white border-b rtl:border-r-0 rtl:border-l flex items-center justify-between px-6 flex-shrink-0">
        <div>
          <h2 v-if="$slots.header" class="font-semibold text-xl text-gray-800 leading-tight">
            <slot name="header" />
          </h2>
        </div>

        <div class="flex items-center space-x-4 rtl:space-x-reverse">
          <div class="relative">
            <select @change="switchLanguage($event.target.value)" class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
              <option value="en" :selected="$page.props?.locale === 'en'">English</option>
              <option value="ar" :selected="$page.props?.locale === 'ar'">العربية</option>
            </select>
          </div>

          <!-- Notification Bell -->
          <NotificationBell />

          <div class="relative flex items-center">
            <span class="text-gray-700">{{ $page.props?.auth?.user?.name }}</span>
            <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-600 hover:text-gray-900 ml-2 rtl:mr-2">
              ({{ $t('Log Out') }})
            </Link>
          </div>
        </div>
      </header>

      <!-- START: Main Content Area Wrapper -->
      <div class="relative flex-1 flex flex-col overflow-hidden">
        <!-- START: Global Flash Message -->
        <transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="transform opacity-0 translate-y-2"
          enter-to-class="transform opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="transform opacity-100 translate-y-0"
          leave-to-class="transform opacity-0 translate-y-2"
        >
          <div v-if="showFlash" class="absolute top-4 right-4 z-50 max-w-sm w-full">
            <div
              class="rounded-lg shadow-lg p-4 border-l-4"
              :class="{
                'bg-green-100 border-green-500 text-green-700': flashType === 'success',
                'bg-red-100 border-red-500 text-red-700': flashType === 'error'
              }"
            >
              <div class="flex justify-between items-start">
                <p class="pr-2">{{ flashMessage }}</p>
                <button @click="showFlash = false" class="ml-2 -mt-1 -mr-1 p-1 rounded-md" :class="{ 'hover:bg-green-200': flashType === 'success', 'hover:bg-red-200': flashType === 'error' }">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>
        </transition>
        <!-- END: Global Flash Message -->

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
          <Transition name="page" mode="out-in">
            <div :key="$page.url">
              <slot />
            </div>
          </Transition>
        </main>
      </div>
      <!-- END: Main Content Area Wrapper -->
    </div>
  </div>
</template>
