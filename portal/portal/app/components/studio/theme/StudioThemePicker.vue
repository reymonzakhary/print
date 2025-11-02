<template>
  <div class="flex flex-col items-center justify-center gap-4">
    <svg class="flex size-32" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 726.74 747.7">
      <path
        :fill="hex"
        stroke="currentColor"
        stroke-width="25"
        d="M15.04,175.74c-1.09-35.9,19.82-62.15,50.24-75.92,20.58-9.32,42.62-14.2,64.13-20.67,43.04-12.96,86.22-25.47,129.35-38.16,24.64-7.26,49.28-14.51,73.93-21.75,23.29-6.84,46.15-4.83,69.21,2.04,80.49,23.97,161.14,47.4,241.67,71.28,21.35,6.33,40.24,16.98,53.7,35.45,8.44,11.58,12.83,24.68,14.08,38.95,1.59,18.11-2,35.87-3.43,53.77-1.76,21.94-4.19,43.82-6.28,65.73-2.19,22.87-4.38,45.75-6.51,68.62-2.07,22.24-3.85,44.5-6.17,66.71-2.38,22.68-3.38,45.51-7.58,68.01-3.61,19.27-13.39,34.8-28.39,46.77-27,21.55-55.56,41.01-83.36,61.5-48.49,35.73-96.88,71.62-144.97,107.91-12.33,9.3-25.48,17.29-40.07,22.81-17.37,6.57-33.95,4.49-50.35-3.22-19.21-9.04-35.65-22.24-52.44-34.89-58.86-44.37-118.23-88.05-177.92-131.3-11.95-8.66-24.42-16.69-35.1-27-14.2-13.7-22.06-30.58-24.57-50.07-3.82-29.48-6.03-59.13-8.82-88.72-2.37-25.11-4.98-50.2-7.38-75.31-2.72-28.47-5.38-56.95-7.97-85.42-1.74-19.02-4.43-37.97-4.99-57.1Z"
      />
    </svg>
    <label
      for="colorPicker"
      class="group flex cursor-pointer items-center gap-2 rounded-lg bg-white px-4 py-2 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600"
    >
      <font-awesome-icon :icon="['fal', 'eye-dropper']" />
      {{ $t("Select Color") }}
      <input id="colorPicker" v-model="hex" type="color" class="sr-only" />
    </label>
    <code class="rounded bg-gray-100 px-2 py-1 text-center text-sm dark:bg-gray-900">
      {{ hex.toUpperCase() }}
    </code>
  </div>
</template>

<script setup>
const hex = defineModel({ type: String, required: true });
</script>

<style scoped>
svg {
  filter: drop-shadow(3px 5px 2px rgb(0 0 0 / 0.4));
}
</style>
