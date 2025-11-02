<template>
  <div class="flex items-center gap-3 text-sm text-gray-500">
    <template v-for="(item, index) in items" :key="index">
      <!-- Item with link -->
      <template v-if="item.to && !item.active">
        <NuxtLink :to="item.to" class="hover:text-gray-700">
          {{ item.label }}
        </NuxtLink>
      </template>

      <!-- Active/current item (no link) -->
      <template v-else-if="item.active">
        <span class="font-medium text-gray-900 dark:text-gray-200">{{ item.label }}</span>
      </template>

      <!-- Regular text item -->
      <template v-else>
        <span>{{ item.label }}</span>
      </template>

      <!-- Chevron separator (except after last item) -->
      <font-awesome-icon
        v-if="index < items.length - 1"
        :icon="['fas', 'chevron-right']"
        class="size-4.5 -ml-1"
      />
    </template>
  </div>
</template>

<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => [
      { label: "Marketplace" },
      { label: "Product Finder", to: "/marketplace/product-finder" },
      { label: "Category", active: true },
    ],
  },
  category: {
    type: String,
    default: () => null,
  },
});

// If category is provided, update the last item to show category name
const items = computed(() => {
  if (props.category && props.items.length > 0) {
    const result = [...props.items];
    result[result.length - 1].label = props.category;
    return result;
  }
  return props.items;
});
</script>
