<template>
  <article>
    <div class="text-center">
      <h1 class="text-xl font-bold text-gray-400">
        {{ props.message }}
      </h1>
      <div class="my-4 -ml-10 flex justify-center">
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
        <font-awesome-icon :icon="props.icon" class="fa-5x my-4 text-gray-400" />
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
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
  icon: {
    type: [String, Array],
    default: () => ["fas", "search"],
  },
});
</script>
