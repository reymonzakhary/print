<template>
  <ul class="h-full px-2 text-sm text-center text-white bg-theme-500 dark:bg-theme-700">
    <li v-for="index in 5" :key="index" class="w-full">
      <SkeletonLine
        class="h-24 bg-transparent rounded dark:bg-transparent"
        style="
          --background: linear-gradient(
            90deg,
            rgba(204, 215, 255, 0) 0%,
            rgba(204, 215, 255, 1) 51%,
            rgba(204, 215, 255, 0) 100%
          );
        "
      />
    </li>
  </ul>
</template>
