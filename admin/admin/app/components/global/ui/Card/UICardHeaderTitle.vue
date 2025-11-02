<template>
  <h1 class="text-base dark:text-theme-50">
    <font-awesome-icon v-if="icon" :icon="icon" class="mr-1" />
    {{ title }}
  </h1>
</template>

<script>
export default {
  name: "UICardHeaderTitle",
  props: ["icon", "title"],
};
</script>
