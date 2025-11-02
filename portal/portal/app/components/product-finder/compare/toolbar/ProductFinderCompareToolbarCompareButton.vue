<template>
  <!-- Compare/Exit Compare button -->
  <button
    v-if="!comparisonActive"
    class="relative flex items-center rounded-lg border border-theme-100 bg-theme-500 px-4 py-1 text-sm font-medium text-white shadow-sm transition-all hover:bg-theme-600 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
    :disabled="selectedCount < 2"
    @click="comparisonActive = true"
  >
    <font-awesome-icon :icon="['fas', 'code-compare']" class="mr-1.5" />
    {{ $t("Compare") }}
    <span
      v-if="selectedCount > 0"
      class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-bold text-theme-600"
    >
      {{ selectedCount }}
    </span>

    <!-- Pulse effect when items are selected -->
    <span v-if="selectedCount >= 2" class="absolute -right-1 -top-1 flex h-3 w-3">
      <span
        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-theme-200 opacity-75"
      />
      <span class="relative inline-flex h-3 w-3 rounded-full bg-theme-100" />
    </span>
  </button>

  <button
    v-else
    class="relative flex items-center rounded-lg border border-gray-300 bg-white px-4 py-1 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow-md dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:shadow-md"
    @click="comparisonActive = false"
  >
    <font-awesome-icon :icon="['fas', 'xmark']" class="mr-1.5" />
    {{ $t("Quit") }}
  </button>
</template>

<script setup>
defineProps({
  selectedCount: {
    type: Number,
    default: 0,
  },
});

const comparisonActive = defineModel({ type: Boolean, required: true });
</script>

<style scoped>
/* Animation for the pulse effect */
@keyframes ping {
  75%,
  100% {
    transform: scale(2);
    opacity: 0;
  }
}
.animate-ping {
  animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
