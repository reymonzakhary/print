<template>
  <button
    :class="
      cn(
        'flex items-center rounded-full bg-gray-150 text-gray-700 transition-all dark:bg-gray-800 dark:text-gray-300',
        readOnly && 'cursor-inherit pr-3',
        !readOnly && 'hover:bg-gray-200 hover:shadow-sm',
        size === 'sm' && 'px-2 py-1 text-sm font-medium',
        size === 'xs' && 'py-1 pl-2 pr-2.5 text-xs font-medium',
      )
    "
  >
    <div class="mr-1 mt-[0.5px] h-3 w-3 rounded-full bg-gradient-to-r" :class="chipGradient" />
    <span v-if="prefix" class="opacity-75">{{ prefix }}</span>
    &nbsp;
    <span>{{ label }}</span>
    <font-awesome-icon
      v-if="!readOnly"
      :icon="['fad', 'xmark']"
      class="ml-1 mt-0.5 h-4 w-4 text-gray-500"
    />
  </button>
</template>

<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  prefix: {
    type: String,
    default: "",
  },
  variant: {
    type: String,
    default: "category",
    validator(value) {
      return ["category", "option"].includes(value);
    },
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: "sm",
    validator(value) {
      return ["xs", "sm"].includes(value);
    },
  },
});

const { cn } = useUtilities();

const chipGradient = computed(() => {
  if (props.variant === "category") {
    return "from-theme-500 to-purple-500";
  }
  return "from-pink-500 to-theme-500";
});
</script>
