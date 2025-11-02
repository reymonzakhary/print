<template>
  <div
    class="relative grid grid-cols-2 gap-2 rounded-b-md bg-gray-50 p-4 shadow-md dark:bg-gray-750 dark:shadow-black/30"
  >
    <!-- Best Delivery Option -->
    <div
      class="pointer-events-auto cursor-pointer rounded-md border border-theme-100 bg-white p-2 text-left text-xs hover:bg-theme-50 dark:border-theme-600 dark:bg-gray-700 dark:hover:bg-gray-600"
      @click.stop="$emit('select-delivery', bestDeliveryTime)"
    >
      <div class="flex items-center justify-between text-theme-600 dark:text-theme-400">
        <h2><font-awesome-icon :icon="['fal', 'truck-fast']" /> {{ $t("Best delivery") }}</h2>
        <NuxtImg
          v-if="bestDeliveryTime.tenant.logo"
          :src="bestDeliveryTime.tenant.logo"
          :alt="bestDeliveryTime.tenant.name"
          class="size-6 rounded-full border border-gray-200 object-contain p-0.5"
        />
        <div
          v-else
          class="grid size-5 place-items-center rounded-full border border-gray-200 bg-gray-50 dark:bg-gray-800"
        >
          <font-awesome-icon
            :icon="['fas', 'building']"
            class="ml-0.5 text-gray-400 dark:text-gray-800"
          />
        </div>
      </div>
      <p class="truncate text-xs/3 text-gray-500 dark:text-gray-400">
        {{ bestDeliveryTime.tenant.name }}
      </p>
      <div class="mt-2">
        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
          <font-awesome-icon :icon="['fal', 'truck']" size="sm" />
          {{ bestDeliveryTime.price.dlv.actual_days }}
          {{ $t("day", bestDeliveryTime.price.dlv.actual_days) }}
        </p>
        <p class="text-gray-500 dark:text-gray-400">
          <font-awesome-icon :icon="['fal', 'tag']" />
          {{ bestDeliveryTime.price.display_selling_price_ex }}
        </p>
      </div>
    </div>

    <!-- Best Price Option -->
    <div
      class="pointer-events-auto cursor-pointer rounded-md border border-green-300 bg-white p-2 text-left text-xs hover:!bg-green-50 dark:border-green-800 dark:bg-gray-700 dark:hover:bg-gray-600"
      @click.stop="$emit('select-price', bestPrice)"
    >
      <div class="flex items-center justify-between text-green-600">
        <h2><font-awesome-icon :icon="['far', 'tag']" /> {{ $t("Best price") }}</h2>
        <NuxtImg
          v-if="bestPrice.tenant.logo"
          :src="bestPrice.tenant.logo"
          :alt="bestPrice.tenant.name"
          class="size-6 rounded-full border border-gray-200 object-contain p-0.5"
        />
        <div
          v-else
          class="grid size-5 place-items-center rounded-full border border-gray-200 bg-gray-50 dark:bg-gray-800"
        >
          <font-awesome-icon
            :icon="['fas', 'building']"
            class="ml-0.5 text-gray-400 dark:text-gray-800"
          />
        </div>
      </div>
      <p class="truncate text-xs/3 text-gray-500 dark:text-gray-400">
        {{ bestPrice.tenant.name }}
      </p>
      <div class="mt-2">
        <p class="text-gray-500 dark:text-gray-400">
          <font-awesome-icon :icon="['fal', 'truck']" />
          {{ bestPrice.price.dlv.actual_days }}
          {{ $t("day", bestDeliveryTime.price.dlv.actual_days) }}
        </p>
        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
          <font-awesome-icon :icon="['fal', 'tag']" />
          {{ bestPrice.price.display_selling_price_ex }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  bestDeliveryTime: {
    type: Object,
    required: true,
  },
  bestPrice: {
    type: Object,
    required: true,
  },
});

// watch(
//   () => props.variant,
//   (newVariant) => {
//     console.log("Variant changed:", newVariant);
//   },
//   { immediate: true },
// );

const emit = defineEmits(["select-delivery", "select-price", "close-details"]);
</script>
