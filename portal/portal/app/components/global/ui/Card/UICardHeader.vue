<template>
  <header
    :class="{
      'bg-theme-400 text-themecontrast-400 dark:bg-theme-400': backgroundColor,
      'text-theme-900': !backgroundColor,
      '!max-h-full': noFixedHeight,
      'rounded-b': roundedFull,
      '!justify-normal': useTabs,
    }"
    class="flex min-h-[42px] flex-wrap items-center justify-between rounded-t p-2 text-xs md:flex-nowrap"
  >
    <div :class="{ 'w-full': !$slots.center && !$slots.right && !useTabs }">
      <slot name="left" />
    </div>
    <div
      v-if="!!$slots.center"
      class="mx-auto my-2 text-center md:my-0"
      :class="{ 'w-full md:w-auto': !!$slots.center, flex: useTabs }"
      role="tablist"
    >
      <slot name="center" />
    </div>
    <div
      v-if="!!$slots.right"
      class="ml-auto text-right md:ml-0"
      :class="{ 'w-full': !$slots.center && !$slots.left && !useTabs }"
    >
      <slot name="right" />
    </div>
  </header>
</template>

<script>
export default {
  props: {
    backgroundColor: {
      type: Boolean,
      default: true,
      required: false,
    },
    noFixedHeight: {
      type: Boolean,
      default: false,
      required: false,
    },
    useTabs: {
      type: Boolean,
      default: false,
      required: false,
    },
    roundedFull: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
};
</script>
