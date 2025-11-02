<template>
  <div class="flex items-end">
    <UIButton
      v-for="n in 5"
      :key="n"
      variant="link"
      :aria-label="$t('Rate {n} stars', { n })"
      role="radio"
      :aria-checked="n <= hoveredStar"
      @mouseover="hoverStar(n)"
      @mouseleave="resetHover"
      @click="emit('update:rating', n)"
    >
      <font-awesome-icon :icon="[n <= hoveredStar ? 'fas' : 'fal', 'star']" />
    </UIButton>
    <p class="ml-4 font-mono text-sm text-gray-500">
      {{ hoveredStar }} <span v-if="description">{{ $t("stars or more") }}</span>
    </p>
  </div>
</template>

<script setup>
// props
const props = defineProps({
  description: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:rating']);

// data
const hoveredStar = ref(0);

// lifecycle
onMounted(() => {
  // Your onMounted logic here
});

// methods
const hoverStar = (n) => {
  hoveredStar.value = n;
};

const resetHover = () => {
  hoveredStar.value = 0;
};
</script>
