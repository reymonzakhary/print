<template>
  <article
    class="flex flex-col items-center justify-center h-56 bg-gray-200 rounded cursor-pointer dark:bg-gray-900 aspect-square"
  >
    <h1 class="font-bold text-gray-400 text-xm">{{ $t("add another margin") }}</h1>
    <font-awesome-icon :icon="['fad', 'hand-holding-dollar']" class="my-6 text-gray-400 fa-4x" />
    <SalesActionButton :icon="['fal', 'plus']" no-full-width @click="emit('add-margin')">
      {{ $t("add margin") }}
    </SalesActionButton>
  </article>
</template>

<script setup>
const emit = defineEmits(["add-margin"]);
</script>
