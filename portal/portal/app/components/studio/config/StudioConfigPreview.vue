<template>
  <div
    class="relative flex h-full w-full transform justify-center overflow-y-auto bg-gray-500 p-8 shadow-inner shadow-black/20 dark:bg-gray-700 dark:text-white"
  >
    <!-- Background Grid -->
    <div
      class="absolute inset-0 z-0 bg-[radial-gradient(#4b5563_1px,transparent_1px)] [background-size:16px_16px]"
    />
    <!-- Preview -->
    <div class="relative z-10 h-full w-full">
      <slot />
    </div>
  </div>
</template>
