<template>
  <div class="mt-[10%] flex items-center justify-center text-center text-gray-500">
    <div class="flex flex-col items-center space-y-6">
      <!-- Information icon -->
      <div class="relative flex h-20 w-20 items-center justify-center text-theme-600">
        <div class="absolute inset-0 rounded-full bg-gray-100 opacity-50" />
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-12 w-12 fill-current">
          <path
            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-.55 0-1-.45-1-1v-4c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm1-8h-2V7h2v2z"
          />
        </svg>
      </div>

      <!-- Title -->
      <div>
        <p class="text-xl font-semibold text-gray-600">Meer filters nodig</p>
      </div>

      <!-- Description -->
      <p class="max-w-sm text-sm leading-relaxed text-gray-600">
        Er zijn nog te veel mogelijkheden om weer te geven. Kies een filter in het linkermenu om je
        selectie verder te beperken.
      </p>

      <!-- Add wizard button -->
      <button
        class="mt-2 rounded-md bg-theme-500 px-4 py-2 text-white hover:bg-theme-600"
        @click="$emit('open-wizard')"
      >
        Open productkiezer
      </button>

      <!-- Suggestion indicators -->
      <div class="flex items-center gap-2 text-sm text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="h-5 w-5 fill-current">
          <path
            d="M10 1.875c-4.55 0-8.125 3.575-8.125 8.125 0 4.55 3.575 8.125 8.125 8.125 4.55 0 8.125-3.575 8.125-8.125 0-4.55-3.575-8.125-8.125-8.125zm0 14.688a6.52 6.52 0 01-6.562-6.563A6.52 6.52 0 0110 3.438a6.52 6.52 0 016.562 6.562A6.52 6.52 0 0110 16.563z"
          />
          <path
            d="M10 5.938a.78.78 0 00-.781.78v4.688c0 .431.35.781.781.781.431 0 .781-.35.781-.781V6.72A.78.78 0 0010 5.937zm0 7.812a.781.781 0 100-1.562.781.781 0 000 1.562z"
          />
        </svg>
        <span>Tip: Begin met de belangrijkste kenmerken</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(["open-wizard"]);
</script>
