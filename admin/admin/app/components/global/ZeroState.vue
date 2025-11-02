<template>
  <article>
    <div class="text-center">
      <h1 class="text-xl font-bold text-gray-400">
        {{ props.message }}
      </h1>
      <div class="flex justify-center my-4 -ml-10">
        <font-awesome-icon :icon="['fas', 'inbox']" class="m-4 text-gray-300 fa-3x" />
        <font-awesome-icon :icon="['fas', 'search']" class="my-4 text-gray-400 fa-5x" />
        <font-awesome-icon :icon="['fas', 'info-circle']" class="my-4 text-gray-300 fa-2x" />
      </div>
    </div>
  </article>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    required: true,
  },
});
</script>
