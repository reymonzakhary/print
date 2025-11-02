<template>
  <header class="flex flex-wrap items-center justify-between col-span-full">
    <button class="text-theme-500" @click="emit('onBack')">
      <font-awesome-icon :icon="['fal', 'chevron-left']" />
      <span class="capitalize has-text-weight-normal">
        {{ $t("back") }}
      </span>
    </button>

    <p class="text-lg">
      <slot />
    </p>

    <button class="hidden ml-auto sm:ml-0 card-header-icon md:block" @click="emit('onClose')">
      <font-awesome-icon :icon="['fad', 'circle-xmark']" />
    </button>
  </header>
</template>

<script setup>
const props = defineProps({
  icon: {
    type: Array,
    required: false,
    default: () => [],
  },
  title: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["onBack", "onClose"]);
</script>
