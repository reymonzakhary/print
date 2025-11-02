<template>
  <span
    :class="
      cn(
        '-ml-[1.35px] inline-block bg-gradient-to-r bg-clip-text font-medium text-transparent',
        isOption && 'from-pink-500 to-theme-500 dark:from-pink-400 dark:to-theme-400',
        isCategory && 'from-theme-500 to-purple-500 dark:from-theme-400 dark:to-purple-400',
        animationClass,
      )
    "
  >
    {{ label }}
  </span>
</template>

<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  animate: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: "",
    validator: (value) => {
      return ["option", "category"].includes(value);
    },
  },
});

const { cn } = useUtilities();

const isOption = computed(() => props.type === "option");
const isCategory = computed(() => props.type === "category");

const animationClass = computed(() => (props.animate ? "animate-highlight" : ""));
</script>
