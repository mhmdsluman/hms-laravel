<template>
  <Head title="User details" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-lg text-gray-900 truncate">User details</h2>
    </template>

    <div class="py-8">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ user.name }}</h3>
                <p class="text-sm text-gray-500">Profile & account details</p>
              </div>

              <div class="inline-flex items-center gap-2">
                <Link :href="typeof route === 'function' ? route('users.edit', user.id) : `/users/${user.id}/edit`" class="px-3 py-1 rounded-md text-white bg-blue-600 hover:bg-blue-700 text-sm">Edit</Link>
                <Link :href="typeof route === 'function' ? route('users.index') : '/users'" class="px-3 py-1 rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 text-sm">Back</Link>
              </div>
            </div>
          </div>

          <div class="px-6 py-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Name</h4>
                <div class="mt-1 text-gray-800 font-medium">{{ user.name }}</div>
              </div>

              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Email</h4>
                <div class="mt-1 text-gray-800">{{ user.email }}</div>
              </div>

              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Role</h4>
                <div class="mt-1">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                    {{ user.role || '—' }}
                  </span>
                </div>
              </div>

              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Speciality</h4>
                <div class="mt-1 text-gray-800">{{ user.speciality || '—' }}</div>
              </div>

              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Joined</h4>
                <div class="mt-1 text-gray-800">{{ fmtDate(user.created_at) }}</div>
              </div>

              <div>
                <h4 class="text-xs text-gray-500 uppercase tracking-wide">Last updated</h4>
                <div class="mt-1 text-gray-800">{{ fmtDate(user.updated_at) }}</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  user: { type: Object, required: true },
});

const fmtDate = (d) => {
  try {
    return d ? new Date(d).toLocaleString() : '—';
  } catch {
    return d;
  }
};
</script>

<style scoped>
/* small custom tweaks if needed */
</style>
