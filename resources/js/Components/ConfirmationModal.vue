<script setup>
import { defineEmits, defineProps } from 'vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Are you sure?',
    },
    message: {
        type: String,
        default: 'This action cannot be undone.',
    },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="$emit('cancel')">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ message }}</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button @click="$emit('cancel')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button @click="$emit('confirm')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>
</template>
