<template>
  <div
    class="flex justify-between text-gray-600"
    :class="cn((boop.isSelected || boop.isCommon) && 'dark:bg-gray-800', $attrs.class)"
  >
    <p
      :class="
        cn(
          'text-[0.7rem] text-gray-900 dark:text-gray-400',
          (boop.isSelected || boop.isCommon) && 'text-gray-400 dark:text-gray-500',
        )
      "
    >
      {{ getDisplayName(boop.key_display_name || boop.key) }}
    </p>
    <p
      :class="
        cn(
          'text-balance text-xs font-bold text-black dark:text-gray-200',
          (boop.isSelected || boop.isCommon) && 'font-normal text-gray-500 dark:text-gray-400',
        )
      "
    >
      {{ getDisplayName(boop.value_display_name || boop.value) }}
    </p>
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
