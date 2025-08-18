<script setup>
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    conversation: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const form = useForm({
    body: '',
});

const sendMessage = () => {
    form.post(route('portal.messages.store', props.conversation.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="'Conversation with ' + conversation.clinician.name" />
    <PatientPortalLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Conversation with {{ conversation.clinician.name }}
            </h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Message Thread -->
                <div class="space-y-4 mb-6 h-96 overflow-y-auto p-4 border rounded-md">
                    <div v-for="message in conversation.messages.slice().reverse()" :key="message.id" class="flex"
                         :class="message.sender_id === currentUser.id ? 'justify-end' : 'justify-start'">
                        <div class="max-w-lg px-4 py-2 rounded-lg"
                             :class="message.sender_id === currentUser.id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'">
                            <p class="text-sm">{{ message.body }}</p>
                            <p class="text-xs mt-1 text-right" :class="message.sender_id === currentUser.id ? 'text-blue-200' : 'text-gray-500'">
                                {{ new Date(message.created_at).toLocaleTimeString() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                <form @submit.prevent="sendMessage">
                    <div>
                        <label for="body" class="sr-only">Reply</label>
                        <textarea id="body" v-model="form.body" rows="3" class="w-full rounded-md" placeholder="Type your message..."></textarea>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </PatientPortalLayout>
</template>
