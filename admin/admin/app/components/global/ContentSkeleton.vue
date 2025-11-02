<template>
  <div
    class="grid h-full place-items-center bg-gradient-to-tr from-gray-300 via-white to-gray-200 dark:from-theme-900 dark:via-black dark:to-theme-800"
  >
    <div class="text-center">
      <div class="stack">
        <font-awesome-icon
          class="text-theme-400 dark:text-theme-200"
          size="5x"
          spin
          :icon="['fat', 'spinner-third']"
        />
        <font-awesome-icon
          class="text-theme-400 dark:text-theme-200 fa-lg"
          :icon="['fad', 'key']"
          fade
        />
      </div>
      <p class="w-full mt-4 font-bold text-center">Authenticating...</p>
    </div>
  </div>
</template>
