<template>
  <header
    :class="
      cn(
        'relative overflow-hidden rounded-lg border-b border-r bg-white px-10 pb-14 pt-12 shadow shadow-gray-300/50 dark:border-gray-900/50 dark:bg-gray-700 dark:shadow-gray-900',
        props.class,
      )
    "
  >
    <PrindustryLogo
      :class="
        cn(
          'absolute -right-12 top-1/2 h-[225%] max-w-[525px] -translate-y-1/2 text-gray-200/55 !opacity-15 dark:text-gray-800',
          props.logoClass,
        )
      "
    />
    <div class="relative z-[1]">
      <slot />
    </div>
  </header>
</template>

<script setup>
const props = defineProps({
  class: {
    type: String,
    default: "",
  },
  logoClass: {
    type: String,
    default: "",
  },
});
const { cn } = useUtilities();
</script>
