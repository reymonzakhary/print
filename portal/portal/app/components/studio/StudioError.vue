<template>
  <div class="flex h-full w-full flex-col items-center justify-center gap-4 p-8">
    <div class="text-6xl text-gray-300">
      <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />
    </div>
    <h1 class="text-2xl font-bold text-gray-400">{{ error.statusCode }}</h1>
    <p class="text-center text-gray-400">{{ error.message }}</p>
    <button
      class="mt-4 rounded-lg bg-theme-600 px-4 py-2 text-white hover:bg-theme-700"
      @click="emit('try-again')"
    >
      {{ $t("Try again") }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  error: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["try-again"]);
</script>
