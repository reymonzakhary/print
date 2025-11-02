<template>
  <div
    v-tooltip="
      createdFrom === 'webshop'
        ? $t('created from webshop')
        : createdFrom === 'mgr'
          ? $t('created from manager')
          : $t('created from API')
    "
    class="flex aspect-square h-8 items-center justify-center rounded-full text-theme-500"
    :class="{ 'has-text-grey': !['webshop', 'mgr'].includes(props.createdFrom) }"
  >
    <font-awesome-icon :icon="icon" />
  </div>
</template>

<script setup>
const props = defineProps({
  createdFrom: {
    type: String,
    required: true,
  },
});

const icon = computed(() => {
  if (props.createdFrom === "webshop") {
    return ["fal", "user"];
  } else if (props.createdFrom === "mgr") {
    return ["fal", "hard-drive"];
  } else {
    return ["fal", "plug"];
  }
});
</script>
