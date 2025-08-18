<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth.user);

const notifications = ref([]);
const showDropdown = ref(false);

const fetchNotifications = () => {
    // In a real app, you'd fetch initial notifications via an API endpoint
    // For now, we'll start with an empty list and rely on real-time events.
};

onMounted(() => {
    fetchNotifications();

    if (user.value) {
        window.Echo.private(`App.Models.User.${user.value.id}`)
            .notification((notification) => {
                notifications.value.unshift(notification);
            });
    }
});
</script>

<template>
    <div class="relative">
        <button @click="showDropdown = !showDropdown" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="notifications.length > 0" class="absolute top-0 right-0 -mt-1 -mr-1 h-2 w-2 rounded-full bg-red-500"></span>
        </button>

        <div v-if="showDropdown" @click.away="showDropdown = false" class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
            <div class="py-1">
                <div class="px-4 py-2 text-sm text-gray-700 font-semibold border-b">Notifications</div>
                <div v-if="notifications.length > 0" class="max-h-64 overflow-y-auto">
                    <a v-for="notification in notifications" :key="notification.id" :href="notification.link" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        {{ notification.message }}
                    </a>
                </div>
                <div v-else class="px-4 py-4 text-sm text-gray-500 text-center">
                    You have no new notifications.
                </div>
            </div>
        </div>
    </div>
</template>
