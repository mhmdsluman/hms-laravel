<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean, default: null }, // if null, component will try to auto-detect
  exact: { type: Boolean, default: true },  // exact pathname match when auto-detecting
  rounded: { type: Boolean, default: true }, // optional: pill vs square
});

const isActiveAuto = () => {
  try {
    // only run client-side; support absolute or relative hrefs
    if (typeof window === 'undefined') return false;
    const linkUrl = new URL(props.href, window.location.origin);
    const current = new URL(window.location.href);
    return props.exact
      ? linkUrl.pathname === current.pathname
      : current.pathname.startsWith(linkUrl.pathname);
  } catch {
    return false;
  }
};

const isActive = computed(() => (props.active === null ? isActiveAuto() : !!props.active));

// visual classes
const base = 'inline-flex items-center gap-3 px-3 py-2 text-sm font-medium transition-all duration-150';
const surface = computed(() => props.rounded ? 'rounded-lg' : 'rounded');
const neutral = 'text-gray-600 hover:text-gray-900 hover:bg-gray-50';
const activeClasses = 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 shadow-sm';
const inactiveFocus = 'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-100';

const classes = computed(() => [
  base,
  surface.value,
  isActive.value ? activeClasses : neutral,
  inactiveFocus
].join(' '));
</script>

<template>
  <Link
    :href="href"
    :class="classes"
    :aria-current="isActive ? 'page' : undefined"
    role="link"
  >
    <!-- optional icon slot (place an <svg> or icon component here) -->
    <span v-if="$slots.icon" class="flex-none w-5 h-5">
      <slot name="icon" />
    </span>

    <!-- label -->
    <span class="truncate">
      <slot />
    </span>

    <!-- optional badge/pill slot (e.g., unread count) -->
    <span v-if="$slots.badge" class="ml-2 flex-none">
      <slot name="badge" />
    </span>
  </Link>
</template>
