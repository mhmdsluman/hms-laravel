<script setup>
import { ref } from 'vue';

defineProps({
    title: String,
});

const isOpen = ref(false);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 cursor-pointer" @click="isOpen = !isOpen">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">{{ title }}</h3>
                <svg class="h-6 w-6 transform transition-transform duration-200" :class="{'rotate-180': isOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
        <div v-show="isOpen" class="p-6 bg-gray-50 border-t">
            <slot />
        </div>
    </div>
</template>
