<template>
  <button :class="tileClass">
    <font-awesome-icon class="fa-5x text-theme-500" :icon="['fad', icon]" />
    <h2 class="mt-4 font-semibold">
      {{ title }}
    </h2>
    <p class="mx-auto mt-4 w-full px-4 text-sm tracking-tight text-gray-400">
      {{ description }}
    </p>
  </button>
</template>

<script>
export default {
  props: {
    tileClass: {
      required: true,
      type: String,
    },
    icon: {
      required: true,
      type: String,
    },
    title: {
      required: true,
      type: String,
    },
    description: {
      required: true,
      type: String,
    },
  },
};
</script>
