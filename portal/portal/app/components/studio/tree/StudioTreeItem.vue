<template>
  <li class="mb-1">
    <button
      class="flex w-full items-center gap-2 rounded rounded-l-none border-l-2 border-l-transparent px-3 py-2 text-sm transition-colors"
      :class="{
        '!border-l-theme-500 bg-theme-100 hover:bg-theme-100 dark:bg-theme-900 dark:hover:bg-theme-900':
          isActive,
        'hover:border-l-theme-500 hover:bg-gray-200 dark:hover:bg-gray-800': !disabled,
        'cursor-not-allowed opacity-50': disabled,
      }"
      :disabled="disabled"
      @click="emit('click')"
    >
      <font-awesome-icon
        :icon="['fal', icon]"
        class="text-gray-400"
        :class="{ 'text-theme-600 dark:text-theme-400': isActive }"
        fixed-width
      />
      <span>{{ displayName }}</span>
    </button>
  </li>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true,
  },
  displayName: {
    type: String,
    required: true,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["click"]);
</script>
