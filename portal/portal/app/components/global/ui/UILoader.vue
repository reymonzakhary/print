<template>
  <div class="text-4xl text-center text-theme-400" :class="color">
    <font-awesome-icon :icon="[iconType, 'spinner-third']" class="fa-spin" />
  </div>
</template>

<script>
export default {
  name: "UILoader",
  props: {
    color: {
      type: String,
      default: "text-theme-400",
    },
    iconType: {
      type: String,
      default: "fal",
    },
  },
};
</script>
