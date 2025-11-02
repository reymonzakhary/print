<template>
  <div class="flex items-center pr-4 text-xs">
    <div class="mr-1 flex flex-1 items-center 2xl:mr-4">
      <!-- <button
        :disabled="producers.length === 0"
        :class="[
          'flex flex-1 items-center justify-center py-4 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-300',
          producers.length === 0 ? 'cursor-not-allowed' : '',
        ]"
        @click.stop="showDetails = !showDetails"
      >
        <font-awesome-icon
          :icon="['fas', 'chevron-down']"
          size="xs"
          :class="[
            'mr-1.5 mt-0.5 transition-transform duration-300',
            { 'rotate-180': showDetails },
          ]"
        />
        <span>{{ showDetails ? $t("Close details") : $t("View details") }}</span>
      </button> -->
      <button
        :disabled="producers.length === 0"
        :class="[
          'm-2 w-full items-center justify-center rounded bg-theme-100 py-2 text-center text-theme-600 hover:bg-theme-200 hover:text-theme-700 dark:bg-theme-700 dark:text-themecontrast-700 dark:hover:bg-theme-800',
          producers.length === 0 ? 'cursor-not-allowed' : '',
          showDetails &&
            'bg-theme-500 text-themecontrast-500 hover:bg-theme-600 hover:text-themecontrast-600 dark:!bg-theme-200 dark:!text-themecontrast-200 dark:hover:bg-theme-800 dark:hover:text-themecontrast-800',
        ]"
        @click.stop="emit('order-variant')"
      >
        <font-awesome-icon
          :icon="['fas', 'shopping-cart']"
          size="xs"
          class="mr-1.5 mt-0.5 transition-transform duration-300"
        />
        <span>{{ $t(".Order") }}</span>
      </button>
    </div>
    <div class="flex items-center gap-4">
      <ProductFinderCompareProducersAvatars
        v-tooltip="$t('{count} producers', { count: producers.length })"
        :producers="producers"
      />

      <!-- Increases the clickable area for easier toggling -->
      <button class="group -m-4 p-4" @click.stop="emit('toggle-selection')">
        <div
          v-if="producers.length > 0"
          :class="
            cn(
              'group flex size-6 items-center justify-center rounded-sm bg-gray-100 text-gray-300 outline-1 transition-colors',
              isSelected && 'bg-theme-600 text-white group-hover:bg-theme-500',
              !isSelected && 'outline outline-gray-300 group-hover:text-gray-400',
            )
          "
          :aria-label="isSelected ? $t('Deselect') : $t('Compare')"
        >
          <font-awesome-icon :icon="['fas', 'check']" class="text-md" />
          <span class="sr-only">{{ isSelected ? $t("Deselect") : $t("Compare") }}</span>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  producers: {
    type: Array,
    default: () => [],
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
  isComparing: {
    type: Boolean,
    default: false,
  },
  showDetails: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-selection", "order-variant"]);

const showDetails = defineModel("showDetails", { type: Boolean, default: false });

const { cn } = useUtilities();
</script>
