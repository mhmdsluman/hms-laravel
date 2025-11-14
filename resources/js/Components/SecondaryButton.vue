<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="btn-3d btn-outline"
    >
        <slot />
    </button>
</template>
