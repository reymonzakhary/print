<template>
  <div class="mt-[10%] flex items-center justify-center text-center text-gray-500">
    <div class="flex flex-col items-center space-y-6">
      <!-- Professional spinner animation -->
      <div class="relative h-20 w-20">
        <!-- Simple spinner with brand color -->
        <div class="absolute inset-0 rounded-full bg-gray-100 opacity-50 dark:bg-gray-800" />
        <svg viewBox="0 0 24 24" class="h-full w-full text-theme-600">
          <path
            fill="currentColor"
            d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z"
            opacity=".25"
          />
          <path
            fill="currentColor"
            d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"
          >
            <animateTransform
              attributeName="transform"
              dur="1s"
              repeatCount="indefinite"
              type="rotate"
              values="0 12 12;360 12 12"
            />
          </path>
        </svg>
      </div>

      <!-- Professional title -->
      <div>
        <p class="text-xl font-semibold text-theme-600">Product Finder</p>
      </div>

      <!-- Professional description -->
      <p class="max-w-sm text-sm leading-relaxed text-gray-600">
        We're analyzing all options to find the perfect product match for your specific
        requirements.
      </p>

      <!-- Status indicators -->
      <div class="flex space-x-3">
        <span
          class="h-2 w-2 rounded-full bg-gray-300"
          :class="{ 'bg-theme-500': loadingProgress > 25 }"
        />
        <span
          class="h-2 w-2 rounded-full bg-gray-300"
          :class="{ 'bg-theme-500': loadingProgress > 50 }"
        />
        <span
          class="h-2 w-2 rounded-full bg-gray-300"
          :class="{ 'bg-theme-500': loadingProgress > 75 }"
        />
      </div>

      <!-- Clean progress bar -->
      <div class="mt-2 h-2 w-64 overflow-hidden rounded-full bg-gray-200">
        <div
          class="h-full rounded-full bg-theme-500 transition-all duration-300"
          :style="`width: ${loadingProgress}%`"
        />
      </div>

      <!-- Processing status text -->
      <div class="text-xs font-medium text-gray-500">
        <span class="inline-block min-w-[200px]">
          {{ loadingStatus }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  loadingProgress: {
    type: Number,
    default: 0,
  },
  loadingStatus: {
    type: String,
    default: "Initializing search",
  },
});
</script>
