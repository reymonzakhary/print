<template>
  <div
    class="relative grid h-full flex-1 rounded-b-md shadow-md"
    :class="{
      'grid-cols-[minmax(250px,calc(20%+56px)),auto] 2xl:grid-cols-[minmax(350px,calc(20%+56px)),auto]':
        !panelClosed && $slots.sidebar,
      'grid-cols-[56px,auto]': panelClosed || $slots.sidebar,
      'grid-cols-1': !$slots.sidebar && !$slots.content,
    }"
  >
    <StudioLoader v-if="loading" />
    <template v-else>
      <div v-if="$slots.sidebar" class="h-full overflow-y-auto rounded-bl-md dark:border-gray-900">
        <slot v-if="$slots.sidebar" name="sidebar" />
      </div>
      <div
        v-if="$slots.content"
        class="h-full overflow-y-auto rounded-md border-t border-gray-100 bg-white shadow-md dark:border-gray-800 dark:bg-gray-700"
      >
        <slot v-if="$slots.content" name="content" />
      </div>
      <div v-if="!$slots.sidebar && !$slots.content">
        <slot />
      </div>
    </template>
  </div>
</template>

<script setup>
defineProps({
  loading: {
    type: Boolean,
    required: false,
    default: false,
  },
  panelClosed: {
    type: Boolean,
    required: false,
    default: false,
  },
});
</script>
