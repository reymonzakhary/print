<template>
  <section class="mb-4 grid grid-cols-2 gap-2">
    <button
      class="rounded border border-blue-300 bg-white py-2 text-xs font-medium text-blue-600 transition-all duration-150"
      :class="[
        isSelectedQuickest
          ? 'border-blue-500 bg-blue-50 text-blue-700 ring-1 ring-blue-500 dark:border-blue-700 dark:bg-blue-900 dark:text-blue-300'
          : 'bg-white text-blue-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:ring-blue-500 dark:bg-gray-700 dark:text-blue-300 dark:hover:bg-blue-900',
      ]"
      @click="$emit('select-quickest')"
    >
      <div class="mx-2 flex items-center justify-between">
        <h2><font-awesome-icon :icon="['fal', 'shipping-fast']" /> {{ $t("Order quickest") }}</h2>
        <div
          v-if="!quickestProducerLogo"
          class="flex size-7 items-center justify-center rounded-full border border-gray-200 bg-gray-100"
        >
          <font-awesome-icon :icon="['fas', 'building']" class="text-lg text-gray-400" />
        </div>
        <NuxtImg
          v-else
          :src="quickestProducerLogo"
          :alt="quickestProducerName"
          class="size-7 rounded-full border border-gray-200 bg-white object-contain p-0.5"
        />
      </div>
    </button>

    <button
      class="rounded border border-green-300 bg-white py-2 text-xs font-medium text-green-600 transition-all duration-150"
      :class="[
        isSelectedCheapest
          ? 'border-green-500 bg-green-50 text-green-700 ring-1 ring-green-500 dark:border-green-700 dark:bg-green-900 dark:text-green-300'
          : 'border-gray-200 bg-white text-gray-700 hover:border-green-300 hover:bg-green-50 hover:text-green-700 focus:ring-green-500 dark:bg-gray-700 dark:text-green-400 dark:hover:bg-green-900',
      ]"
      @click="$emit('select-cheapest')"
    >
      <div class="mx-2 flex items-center justify-between">
        <h2><font-awesome-icon :icon="['far', 'tag']" /> {{ $t("Order cheapest") }}</h2>
        <div
          v-if="!cheapestProducerLogo"
          class="flex size-7 items-center justify-center rounded-full border border-gray-200 bg-gray-100"
        >
          <font-awesome-icon :icon="['fas', 'building']" class="text-lg text-gray-400" />
        </div>
        <NuxtImg
          v-else
          :src="cheapestProducerLogo"
          :alt="cheapestProducerName"
          class="size-7 rounded-full border border-gray-200 bg-white object-contain p-0.5"
        />
      </div>
    </button>
  </section>
</template>

<script setup>
defineProps({
  isSelectedCheapest: {
    type: Boolean,
    default: false,
  },
  isSelectedQuickest: {
    type: Boolean,
    default: false,
  },
  cheapestProducerLogo: {
    type: String,
    default: "",
  },
  quickestProducerLogo: {
    type: String,
    default: "",
  },
  quickestProducerName: {
    type: String,
    default: "",
  },
  cheapestProducerName: {
    type: String,
    default: "",
  },
});

defineEmits(["select-cheapest", "select-quickest"]);

const { t: $t } = useI18n();
</script>
