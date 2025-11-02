<template>
  <div class="flex items-end">
    <span role="img" :aria-label="$t('Rating: {rating} out of 5 stars', { rating })">
      <font-awesome-icon
        v-for="i in fullStars"
        :key="'full-star-' + i"
        :icon="['fas', 'star']"
        :class="{ 'text-amber-400': variant === 'default', 'text-black': variant === 'dark' }"
      />

      <font-awesome-icon
        v-if="hasHalfStar"
        :key="'half-star'"
        :icon="['far', 'star-half-stroke']"
        :class="{ 'text-amber-400': variant === 'default', 'text-black': variant === 'dark' }"
      />

      <font-awesome-icon
        v-for="i in emptyStars"
        :key="'empty-star-' + i"
        :icon="['far', 'star']"
        :class="{ 'text-amber-400': variant === 'default', 'text-black': variant === 'dark' }"
      />
    </span>
    <span
      class="ml-2 w-8 text-sm font-bold"
      :class="{ 'text-theme-500': variant === 'default', 'text-black': variant === 'dark' }"
    >
      {{ rating }}
    </span>

    <span v-if="reviews > 0" class="ml-2 text-sm text-gray-600 dark:text-gray-300">
      ({{ reviews }} {{ $t("reviews") }})
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  rating: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 5,
  },
  reviews: {
    type: Number,
    required: true,
  },
  variant: {
    type: String,
    default: "default",
  },
});

const fullStars = computed(() => {
  const stars = Math.floor(Math.max(0, Math.min(5, props.rating)));
  return stars;
});
const hasHalfStar = computed(() => {
  if (props.rating < 0) return false;
  const fraction = props.rating - Math.floor(props.rating);
  return fraction >= 0.5 && fraction < 1;
});
const emptyStars = computed(() => {
  const total = fullStars.value + (hasHalfStar.value ? 1 : 0);
  return Math.max(0, 5 - total);
});
</script>

<style scoped>
/* Your styles here */
</style>
