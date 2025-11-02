<template>
  <li
    class="overflow-hidden rounded border border-gray-200 bg-white transition-all dark:border-gray-900 dark:bg-gray-700"
    :class="{
      'border-theme-300 dark:border-theme-700': isSelected,
    }"
  >
    <!-- Producer Accordion Header -->
    <button
      class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50 focus:outline-none dark:hover:bg-gray-750"
      :class="{
        'hover:bg-theme-50 dark:hover:bg-theme-800': isSelected,
      }"
      @click="$emit('toggle')"
    >
      <div class="flex flex-1 items-center space-x-3 overflow-hidden">
        <div
          v-if="!producer.logo"
          class="flex size-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
        >
          <font-awesome-icon
            :icon="['fas', 'building']"
            class="text-sm text-gray-400 dark:text-gray-600"
          />
        </div>
        <NuxtImg
          v-else
          :src="producer.logo"
          :alt="producer.name"
          class="size-10 flex-shrink-0 rounded-full border border-gray-200 bg-white object-contain p-0.5 dark:border-gray-800"
        />
        <div class="flex-1 overflow-hidden">
          <span class="block truncate text-sm font-medium text-gray-800 dark:text-gray-200">
            {{ producer.name }} {{ mainSlug }} - ( {{ producer.category.slug }} )
          </span>

          <!-- Show selection info when selected and closed -->
          <div
            v-if="isSelected && !isExpanded"
            class="mt-0.5 flex items-center space-x-2 text-xs text-theme-700 dark:text-theme-300"
          >
            <font-awesome-icon :icon="['fas', 'check-circle']" />
            <span> {{ selectedPrice }} / {{ selectedDeliveryTime }} {{ $t("days") }}</span>
          </div>
          <!-- Show default tags when not selected or when open -->
          <div class="mt-0.5 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
            <span
              v-if="isCheapest"
              class="inline-flex items-center text-green-600 dark:text-green-400"
            >
              <font-awesome-icon :icon="['fas', 'tag']" class="mr-1" />
              {{ $t("Cheapest") }}
            </span>
            <span
              v-if="isQuickest"
              class="inline-flex items-center text-blue-600 dark:text-blue-400"
            >
              <font-awesome-icon :icon="['fas', 'truck-fast']" class="mr-1" />
              {{ $t("Quickest") }}
            </span>
          </div>
        </div>
      </div>
      <font-awesome-icon
        :icon="['fas', 'chevron-down']"
        class="size-4 flex-shrink-0 text-gray-500 transition-transform dark:text-gray-400"
        :class="{
          'rotate-180 transform': isExpanded,
        }"
      />
    </button>

    <!-- Accordion Content -->
    <div
      v-show="isExpanded"
      class="border-t border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800"
    >
      <slot />
    </div>
  </li>
</template>

<script setup>
defineProps({
  producer: {
    type: Object,
    required: true,
  },
  isExpanded: {
    type: Boolean,
    default: false,
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
  isCheapest: {
    type: Boolean,
    default: false,
  },
  isQuickest: {
    type: Boolean,
    default: false,
  },
  selectedPrice: {
    type: String,
    default: "",
  },
  selectedDeliveryTime: {
    type: [String, Number],
    default: "",
  },
});

defineEmits(["toggle"]);

const mainSlug = useRoute().params.slug;

const { t: $t } = useI18n();
</script>
