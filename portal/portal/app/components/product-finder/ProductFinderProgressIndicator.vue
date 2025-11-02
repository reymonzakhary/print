<template>
  <div>
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
      <span>{{ $t("Configuration completeness") }}</span>
      <span class="font-medium" :class="{ 'text-prindustry-600': progressPercentage > 0 }">
        {{ progressPercentage }}%
      </span>
    </div>
    <div class="relative mt-2 h-2 overflow-hidden rounded-full">
      <div class="absolute z-10 h-full w-1 bg-theme-500" :style="`left: ${requiredPercentage}%`" />
      <div
        class="relative h-full bg-prindustry-500"
        style="background: linear-gradient(to right, #ec4899, #3095b4, #a855f7, #3095b4)"
      >
        <div
          class="absolute right-0 h-full bg-gray-100 transition-all duration-500 ease-out dark:bg-gray-800"
          :style="`width: ${100 - progressPercentage}%; clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%)`"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  progressPercentage: {
    type: Number,
    required: true,
  },
  requiredPercentage: {
    type: Number,
    required: true,
  },
});
</script>
