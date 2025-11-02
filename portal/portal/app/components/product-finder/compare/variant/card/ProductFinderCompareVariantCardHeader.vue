<template>
  <div class="flex flex-wrap items-center justify-end">
    <div class="flex w-full flex-col">
      <!-- Quantity -->
      <div class="w-full px-4 pb-4 pt-3 text-right transition-colors ease-in-out">
        <p class="text-xs text-gray-600 dark:text-gray-400">
          {{ $t("amount") }}
        </p>
        <div class="flex items-center justify-end">
          <font-awesome-icon
            :icon="['far', 'boxes']"
            class="mr-1 text-sm text-gray-800 dark:text-gray-200"
          />
          <span :class="cn('text-base font-bold text-gray-800 dark:text-gray-200')">
            {{ quantity ?? "N/A" }}
          </span>
        </div>
      </div>

      <!-- Delivery time -->
      <div class="w-full px-4 pb-4 pt-3 text-right transition-colors ease-in-out">
        <p class="whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
          {{ $t("Delivery from") }}
        </p>
        <div class="flex items-center justify-end">
          <font-awesome-icon
            :icon="['fal', 'truck']"
            class="mr-1 text-sm text-gray-800 dark:text-gray-200"
          />
          <span class="text-base font-bold text-gray-800 dark:text-gray-200">
            {{ deliveryTime ?? "N/A" }}
            {{ $t("day", deliveryTime) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Price -->
    <div class="w-full rounded-tr px-4 pb-4 pt-3 text-right transition-colors ease-in-out">
      <p class="text-xs text-gray-600 dark:text-gray-400">
        {{ $t("Price from") }}
      </p>
      <span
        :class="
          cn(
            'text-nowrap text-base font-bold text-gray-800 dark:text-gray-200',
            !price && 'blur-sm',
          )
        "
      >
        {{ price ?? "€ NV,T0" }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  deliveryTime: {
    type: [String, undefined],
    default: undefined,
  },
  price: {
    type: [String, undefined],
    default: undefined,
  },
  quantity: {
    type: [String, undefined],
    default: undefined,
  },
  // metric: {
  //   type: String,
  //   default: "deliveryTime",
  // },
});

const { cn } = useUtilities();
</script>
