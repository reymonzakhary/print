<template>
  <div class="flex justify-center transition-all duration-300">
    <div class="relative inline-flex rounded-full bg-white p-1 pt-0.5 shadow-sm dark:bg-gray-800">
      <button
        :class="[
          'relative rounded-full px-4 py-2 text-sm font-medium transition-all',
          mode === 'steps'
            ? 'border border-gray-100 bg-gray-50 text-theme-600 shadow-inner dark:bg-gray-700 dark:text-theme-400'
            : 'text-gray-600 hover:text-theme-500 dark:text-gray-300',
        ]"
        @click="$emit('mode-change', 'steps')"
      >
        {{ $t("Step by step") }}
      </button>
      <button
        :class="[
          'relative rounded-full px-4 py-2 text-sm font-medium transition-all',
          mode === 'all'
            ? 'border border-gray-100 bg-gray-50 text-theme-600 shadow-inner dark:bg-gray-700 dark:text-theme-400'
            : 'border border-white text-gray-600 hover:text-theme-500 dark:text-gray-300',
        ]"
        @click="$emit('mode-change', 'all')"
      >
        {{ $t("All at once") }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  mode: {
    type: String,
    required: true,
    validator: (value) => ["steps", "all"].includes(value),
  },
});

defineEmits(["mode-change"]);
</script>
