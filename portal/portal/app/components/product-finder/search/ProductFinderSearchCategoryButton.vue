<template>
  <button
    v-tooltip="disabled ? 'Binnenkort beschikbaar' : null"
    :class="
      cn(
        'group flex w-full cursor-pointer items-center space-x-3 rounded-lg p-2 text-left',
        disabled && 'cursor-not-allowed bg-gray-50 dark:bg-gray-700',
        !disabled && 'hover:bg-gray-50 dark:hover:bg-gray-600',
        $attrs.class,
      )
    "
    :disabled="disabled"
    @click="navigateTo(`/marketplace/product-finder/${category.slug}`)"
  >
    <div
      class="grid h-10 w-10 place-items-center overflow-hidden rounded-lg bg-gradient-to-br from-theme-50 to-theme-200 p-1 dark:from-theme-300"
    >
      <img
        v-if="variant === 'single' && (category.image || props.image)"
        :src="props.image || category.image"
        :alt="category.displayName"
        class="h-full w-full object-cover"
      />
      <font-awesome-icon v-else-if="disabled" :icon="['fal', 'spinner']" class="h-4 w-4" />
      <font-awesome-icon
        v-else-if="variant !== 'single'"
        :icon="['fal', 'arrow-right']"
        class="h-4 w-4"
      />
      <font-awesome-icon v-else :icon="['fal', 'image']" class="h-4 w-4 text-white" />
    </div>
    <span v-if="variant === 'single'" class="text-sm text-gray-700 dark:text-gray-200">
      {{ category.displayName }}

      <div class="flex w-full cursor-pointer items-center text-xs hover:text-theme-500">
        <UIPrindustryBox line="text-prindustry" class="mr-1 h-3" />
        {{ category.name }}
      </div>
    </span>
    <span v-if="variant === 'all'" class="text-sm text-gray-700 dark:text-gray-200">
      {{ $t("See all { count } categories", { count: count }) }}
    </span>
  </button>
</template>

<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: false,
    default: () => ({
      name: "Category Name",
      slug: "category-slug",
      image: "https://placehold.co/100",
    }),
  },
  variant: {
    type: String,
    default: "single",
    validator: (value) => {
      return ["single", "all"].includes(value);
    },
  },
  count: {
    type: Number,
    default: 0,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  image: {
    type: String,
    default: null,
  },
});

const { cn } = useUtilities();
</script>
