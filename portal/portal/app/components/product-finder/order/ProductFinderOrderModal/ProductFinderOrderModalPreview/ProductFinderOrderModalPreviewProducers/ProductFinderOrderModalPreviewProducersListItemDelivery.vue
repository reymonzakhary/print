<template>
  <div
    class="flex items-center justify-between border-b bg-gray-50 p-3 transition-colors hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
    :class="{
      'border-theme-300 bg-theme-50 hover:bg-theme-100 dark:border-theme-700 dark:bg-theme-900':
        isSelected,
    }"
  >
    <div class="flex-grow">
      <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
        {{ $t("Delivery") }}: {{ option.deliveryTime }} {{ $t("days") }}
      </p>
      <p v-if="option.deliveryDayName" class="text-xs text-gray-500 dark:text-gray-400">
        {{ option.deliveryDayName }}, {{ option.deliveryDay }}
        {{ option.deliveryMonth }}
      </p>
    </div>
    <div class="flex-grow text-right">
      <p class="text-sm font-bold text-theme-700 dark:text-theme-300">
        {{ option.price }}
      </p>
      <p v-if="option.pricePerUnit" class="text-xs text-gray-500 dark:text-gray-400">
        {{ option.pricePerUnit }}
      </p>
    </div>
    <button
      class="ml-3 aspect-square flex-shrink rounded-md bg-theme-500 px-2 py-1 text-sm font-medium text-white transition-colors hover:bg-theme-600 focus:outline-none focus:ring-2 focus:ring-theme-500 focus:ring-offset-2 dark:bg-theme-600 dark:hover:bg-theme-700 dark:focus:ring-theme-400"
      @click.stop="$emit('select', option)"
    >
      <font-awesome-icon :icon="['fas', 'cart-plus']" />
    </button>
  </div>
</template>

<script setup>
defineProps({
  option: {
    type: Object,
    required: true,
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["select"]);

const { t: $t } = useI18n();
</script>
