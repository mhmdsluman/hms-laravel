<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import { usePermissions } from '@/Composables/usePermissions';
import {
    HomeIcon,
    UsersIcon,
    CalendarIcon,
    BuildingOfficeIcon,
    ClipboardDocumentListIcon,
    BeakerIcon,
    FilmIcon,
    DocumentTextIcon,
    CogIcon,
    ChartBarIcon,
    DocumentDuplicateIcon,
    CircleStackIcon,
    ArchiveBoxIcon,
    CreditCardIcon,
    ShieldCheckIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

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
          <HomeIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">{{ $t('Dashboard') }}</span>
        </Link>

        <Link :href="route('patients.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <UsersIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">{{ $t('Patient Registration') }}</span>
        </Link>

        <Link :href="route('appointments.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <CalendarIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Appointments</span>
        </Link>

        <Link v-if="hasRole(['admin', 'clerk', 'nurse'])" :href="route('ipd.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <BuildingOfficeIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Inpatient (IPD)</span>
        </Link>

        <Link v-if="hasRole(['admin', 'nurse'])" :href="route('nursing.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ClipboardDocumentListIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Nursing Station</span>
        </Link>

        <Link v-if="hasRole(['admin', 'clerk', 'nurse', 'emergency'])" :href="route('emergency.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ExclamationTriangleIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Emergency</span>
        </Link>

        <div class="px-6 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Departments</div>

        <Link v-if="hasRole(['admin', 'lab'])" :href="route('lab.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <BeakerIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Laboratory</span>
        </Link>

        <Link v-if="hasRole(['admin', 'pharmacy'])" :href="route('pharmacy.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ArchiveBoxIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Pharmacy</span>
        </Link>

        <Link v-if="hasRole(['admin', 'radiology'])" :href="route('radiology.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <FilmIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Radiology</span>
        </Link>

        <Link v-if="hasRole(['admin', 'ot_manager', 'clinician'])" :href="route('ot.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <DocumentTextIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Operating Theater</span>
        </Link>

        <Link v-if="hasRole(['admin', 'clerk'])" :href="route('billing.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <CreditCardIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Billing</span>
        </Link>

        <div class="px-6 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Admin</div>

        <Link v-if="hasRole('admin')" :href="route('users.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <UsersIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">User Management</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('services.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <CircleStackIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Service Catalogue</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('test-catalogue.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <BeakerIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Test Catalogue</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('templates.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <DocumentDuplicateIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Template Builder</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('audit.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ShieldCheckIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Audit Trail</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('formulary.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ArchiveBoxIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Formulary Mgt.</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('order-sets.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <DocumentTextIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Order Set Builder</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('insurance-providers.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ShieldCheckIcon class="h-6 w-6 flex-shrink-0" />
          <span class="ml-3 whitespace-nowrap transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">Insurance Providers</span>
        </Link>

        <Link v-if="hasRole('admin')" :href="route('analytics.index')" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-200">
          <ChartBarIcon class="h-6 w-6 flex-shrink-0" />
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
