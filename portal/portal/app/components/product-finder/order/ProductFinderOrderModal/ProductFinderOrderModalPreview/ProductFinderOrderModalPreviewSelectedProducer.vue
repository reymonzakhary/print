<template>
  <div
    v-if="producer"
    class="mb-4 rounded-lg border border-theme-200 bg-theme-50 p-3 shadow-sm dark:border-theme-700 dark:bg-theme-900"
  >
    <div class="flex items-start gap-3">
      <div
        v-if="!producer.producerLogo"
        class="flex size-8 items-center justify-center rounded-full border border-gray-200 bg-gray-100"
      >
        <font-awesome-icon :icon="['fas', 'building']" class="text-lg text-gray-400" />
      </div>
      <NuxtImg
        v-else
        :src="producer.producerLogo"
        :alt="producer.producerName"
        class="size-8 rounded-full border border-gray-200 bg-white object-contain p-0.5"
      />
      <div class="flex-grow">
        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
          {{ producer.producerName }}
        </p>
        <div class="mt-1 flex items-center justify-between">
          <div class="text-xs text-gray-600 dark:text-gray-400">
            <p>
              {{ $t("Delivery") }}: {{ producer.deliveryTime }}
              {{ $t("days") }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-sm font-bold text-theme-700 dark:text-theme-300">
              {{ producer.price }}
            </p>
            <p v-if="producer.pricePerUnit" class="text-xs text-gray-500 dark:text-gray-400">
              {{ producer.pricePerUnit }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  producer: {
    type: Object,
    default: null,
  },
});

const { t: $t } = useI18n();
</script>
