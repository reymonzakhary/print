<template>
  <div
    :class="
      cn(
        'inline-flex items-center rounded-full border border-gray-200 bg-white py-1 pl-2 pr-3 text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400',
        $attrs.class,
      )
    "
  >
    <font-awesome-icon
      v-if="!boop.isManual"
      :icon="['fas', 'check-circle']"
      class="mb-[1px] mr-1.5 size-2.5 text-gray-500"
    />
    <div
      v-else
      class="mr-1 mt-[0.5px] h-3 w-3 rounded-full bg-gradient-to-r from-theme-500 to-purple-500 opacity-75"
    />
    <span class="truncate text-xs">
      {{ getDisplayName(boop.value_display_name ?? boop.value) }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  boop: {
    type: Object,
    required: true,
  },
});

const { cn } = useUtilities();
const { getDisplayName } = useDisplayName();
</script>
