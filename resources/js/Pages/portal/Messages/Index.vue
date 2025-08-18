<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    conversations: Array,
});
</script>

<template>
    <Head title="My Messages" />
    <PatientPortalLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Messages</h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="divide-y divide-gray-200">
                    <Link v-for="convo in conversations" :key="convo.id" :href="route('portal.messages.show', convo.id)" class="block p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <p class="font-semibold">Conversation with {{ convo.clinician.name }}</p>
                            <p class="text-sm text-gray-500">{{ new Date(convo.updated_at).toLocaleDateString() }}</p>
                        </div>
                    </Link>
                    <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500">
                        You have no messages.
                    </div>
                </div>
            </div>
        </div>
    </PatientPortalLayout>
</template>
