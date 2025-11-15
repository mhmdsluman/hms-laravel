<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const hospitalName = computed(() => usePage().props.hospital_name || 'My Hospital');
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
        <div class="flex flex-col items-center">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
            <h1 class="mt-4 text-2xl font-semibold text-gray-700">{{ hospitalName }}</h1>
        </div>

        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
            <Transition name="page" mode="out-in">
                <div :key="$page.url">
                    <slot />
                </div>
            </Transition>
        </div>
    </div>
</template>
