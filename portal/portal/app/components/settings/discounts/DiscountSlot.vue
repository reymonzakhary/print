<template>
  <article
    class="flex aspect-square h-56 cursor-pointer flex-col items-center justify-center rounded bg-gray-200 dark:bg-gray-900"
  >
    <h1 class="text-xm font-bold text-gray-400">{{ $t("add another discount") }}</h1>
    <font-awesome-icon :icon="['fad', 'badge-percent']" class="fa-4x my-6 text-gray-400" />
    <SalesActionButton :icon="['fal', 'plus']" no-full-width @click="emit('add-discount')">
      {{ $t("add discount") }}
    </SalesActionButton>
  </article>
</template>

<script setup>
const emit = defineEmits(["add-discount"]);
</script>
