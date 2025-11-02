<template>
  <li
    class="group cursor-pointer space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-700"
  >
    <div @click="$emit('toggle')">
      <div class="flex items-start gap-4">
        <ProductFinderUICategoryImage
          :image="item.image || null"
          :title="item.productName || 'Unknown Product'"
          :loading="false"
        />

        <div class="flex-1">
          <div class="flex items-start justify-between">
            <h3 class="font-medium text-gray-900 dark:text-white">
              {{ item.productName || "Unknown Product" }}
            </h3>
            <div class="flex items-center gap-2">
              <button
                class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                :disabled="disabled"
                @click.stop="$emit('remove')"
              >
                <font-awesome-icon :icon="['fas', 'trash']" />
              </button>
              <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ item.price || "N/A" }}
              </div>
            </div>
          </div>

          <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <ProductFinderBasketItemProducer :value="item.producer" />
            <div class="flex items-center gap-2">
              <ProductFinderBasketItemDelivery :value="item.deliveryTime" />
              <div class="h-3 w-px bg-gray-300" />
              <ProductFinderBasketItemQuantity :value="item.quantity" />
            </div>
          </div>

          <!-- Accordion toggle -->
          <div class="mt-2 flex w-full items-center justify-between text-xs text-theme-500">
            <span>{{ isExpanded ? $t("Hide details") : $t("Show details") }}</span>
            <font-awesome-icon
              :icon="['fas', 'chevron-down']"
              :class="{ 'rotate-180': isExpanded }"
              class="transition-transform duration-300"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Details content -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-96 opacity-100"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="max-h-96 opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div v-if="isExpanded" class="space-y-4">
        <Separator />
        <ProductFinderBasketItemBoops :product="item.completeData.variant.items" />
      </div>
    </Transition>
  </li>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true,
  },
  isExpanded: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["toggle", "remove"]);

const { t: $t } = useI18n();
</script>
