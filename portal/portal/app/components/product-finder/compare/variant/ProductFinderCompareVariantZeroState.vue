<template>
  <div class="col-span-2 row-span-full flex flex-col items-center justify-center py-12 text-center">
    <div class="grid place-items-center rounded-full bg-gray-200 p-3 px-3.5 dark:bg-gray-900/30">
      <font-awesome-icon
        :icon="['far', 'face-monocle']"
        class="h-12 w-12 text-gray-500 dark:text-gray-400"
      />
    </div>
    <h3 class="mt-4 max-w-2xl text-balance text-lg font-medium text-gray-900 dark:text-gray-100">
      {{ $t("Oops, no variants with producers were found") }}
    </h3>
    <p class="mt-2 max-w-lg text-pretty text-center text-gray-500 dark:text-gray-400">
      {{ $t("Try choosing different options.") }}
    </p>
  </div>
</template>
