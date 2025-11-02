<template>
  <aside
    :class="
      cn(
        'relative max-w-[clamp(175px,23%,350px)] pb-4 transition-all',
        closeSidebar && 'max-w-[4.5rem]',
      )
    "
  >
    <button
      :class="
        cn(
          'absolute -right-2 top-4 z-50 flex size-6 items-center justify-center rounded-full bg-white text-gray-500 shadow-md transition-colors hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300',
          closeSidebar && '-right-3',
        )
      "
      :aria-label="closeSidebar ? $t('Open sidebar') : $t('Close sidebar')"
      @click="closeSidebar = !closeSidebar"
    >
      <font-awesome-icon
        :icon="['fas', closeSidebar ? 'chevron-right' : 'chevron-left']"
        size="xs"
      />
    </button>
    <div
      v-if="closeSidebar"
      class="h-full rounded-md border border-gray-200 p-2 dark:border-gray-700"
    >
      <slot name="closed" />
    </div>
    <div class="" :class="closeSidebar && 'max-w-0'">
      <div class="space-y-4 p-[1px]">
        <slot />
      </div>
    </div>
  </aside>
</template>

<script setup>
const closeSidebar = defineModel("closeSidebar", {
  type: Boolean,
  default: false,
});

const { cn } = useUtilities();
</script>
