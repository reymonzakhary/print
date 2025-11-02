<template>
  <li class="relative w-full">
    <NuxtLink
      :to="to"
      :class="
        cn(
          'mx-2 my-0.5 block truncate rounded-md px-1 py-3 text-xs shadow-gray-300 dark:shadow-black xl:text-sm',
          !disabled &&
            'transition-all duration-150 hover:bg-gray-100 hover:shadow-md dark:hover:bg-gray-800 dark:hover:shadow-sm',
          disabled && 'coming-soon py-4 text-gray-400',
          props.active &&
            'bg-white font-bold text-theme-500 shadow-md shadow-gray-300 dark:bg-gray-700 dark:shadow-sm dark:shadow-black',
        )
      "
    >
      <font-awesome-icon
        :class="{ 'text-theme-500': props.active }"
        :icon="props.active ? activeIcon : icon"
        size="2x"
      />
      <slot />
      <p class="truncate capitalize">{{ $t(label) }}</p>
    </NuxtLink>
  </li>
</template>

<script setup>
const props = defineProps({
  active: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: Array,
    default: () => ["fal", "file-signature"],
  },
  activeIcon: {
    type: Array,
    default: () => ["fad", "file-signature"],
  },
  label: {
    type: String,
    default: "Quotations",
  },
  to: {
    type: [String, undefined],
    default: undefined,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const { cn } = useUtilities();
</script>
