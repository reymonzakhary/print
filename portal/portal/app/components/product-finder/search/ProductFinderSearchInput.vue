<template>
  <div
    :class="
      cn(
        'relative rounded bg-white text-left shadow-sm dark:bg-gray-700',
        props.class,
        disabled && 'opacity-50',
      )
    "
  >
    <input
      id="smart-search-input"
      v-bind="$attrs"
      v-model="model"
      :class="
        cn(
          'relative z-[1] w-full rounded border border-theme-400 bg-white py-3 pe-10 ps-4 text-sm text-gray-800 transition-all focus:border-theme-500 focus:outline-none focus:ring focus:ring-theme-200 dark:bg-gray-700 dark:text-white',
          // 'border border-theme-400 dark:border-theme-600 dark:bg-gray-700 dark:text-white',
          // 'focus:border-theme-500 focus:outline-none focus:ring-1 focus:ring-theme-200 dark:focus:border-theme-700 dark:focus:ring-theme-600',
          inputClass,
          disabled && 'cursor-not-allowed',
        )
      "
      :disabled="disabled"
    />
    <slot />
    <button
      v-if="model"
      class="absolute end-[2px] top-1/2 z-[2] grid aspect-square h-[calc(100%_-_2px)] -translate-y-1/2 place-items-center rounded-md text-gray-400 hover:bg-gray-50"
      @click="model = ''"
    >
      <font-awesome-icon :icon="['fal', 'xmark']" class="text-lg" />
    </button>
    <font-awesome-icon
      v-if="!model"
      :icon="['fal', 'magnifying-glass']"
      class="absolute end-3 top-1/2 z-[2] h-4 w-4 -translate-y-1/2 text-gray-400"
    />
  </div>
</template>

<script setup>
import { onMounted, nextTick } from "vue";

const model = defineModel({ type: String });
defineOptions({
  inheritAttrs: false,
});

onMounted(async () => {
  await nextTick();
  document.getElementById("smart-search-input")?.focus();
});

const props = defineProps({
  inputClass: {
    type: String,
    default: "",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  class: {
    type: String,
    default: "",
  },
});

const { cn } = useUtilities();
</script>
