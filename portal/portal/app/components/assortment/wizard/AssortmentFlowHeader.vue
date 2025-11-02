<template>
  <header class="bg-gray-100 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between">
      <!-- Left: Step info -->
      <div class="flex items-center">
        <!-- Back/Close button -->
        <button
          type="button"
          class="mr-4 rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-200 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
          @click="$emit('close-wizard')"
          :title="$t('Close wizard')"
        >
          <font-awesome-icon :icon="['fal', 'times']" class="text-xl" />
        </button>

        <div class="flex items-center">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            <template v-if="$t(productWizardStore.currentStepTitleWithCategory)">
              {{ $t(productWizardStore.currentStepTitleWithCategory) }}
            </template>
            <template v-else-if="manifestCategoryName">
              Product Manifest: {{ manifestCategoryName }}
            </template>
            <template v-else>
              {{ $t("Product Manifest") }}
            </template>
          </h2>
          <div
            class="ml-3 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400"
          >
            {{ productWizardStore.currentSubStepNumber }}/{{
              productWizardStore.totalSubStepsInCurrentStep
            }}
          </div>
        </div>
        <div class="ml-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
          <div class="h-1 w-32 rounded-full bg-gray-200 dark:bg-gray-700">
            <div
              class="h-1 rounded-full bg-emerald-500 transition-all duration-300"
              :style="{ width: `${currentStepProgress}%` }"
            ></div>
          </div>
          <span class="ml-2">{{ currentStepProgress }}%</span>
        </div>
      </div>

      <!-- Right: Save status -->
      <div class="flex items-center space-x-4">
        <!-- Auto-save indicator -->
        <div class="flex items-center text-sm">
          <div v-if="isDirty" class="flex items-center text-yellow-600 dark:text-yellow-400">
            <div class="mr-2 h-3 w-3 animate-spin rounded-full border-b-2 border-yellow-600"></div>
            {{ $t("Saving...") }}
          </div>
          <div v-else-if="lastSaved" class="flex items-center text-green-600 dark:text-green-400">
            <font-awesome-icon :icon="['fas', 'check']" class="mr-1" />
            {{ $t("Saved") }} {{ formatTimeAgo(lastSaved) }}
          </div>
          <div v-else class="flex items-center text-gray-500 dark:text-gray-400">
            <font-awesome-icon :icon="['fal', 'cloud']" class="mr-1" />
            {{ $t("Not saved") }}
          </div>
        </div>

        <!-- Help toggle -->
        <button
          type="button"
          class="group relative rounded-lg bg-theme-600 px-4 py-2 text-white transition-all duration-200 hover:bg-theme-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-theme-500 focus:ring-offset-2"
          @click="$emit('toggle-help')"
        >
          <div class="flex items-center space-x-2">
            <font-awesome-icon :icon="['fas', 'question-circle']" class="text-sm" />
            <span class="text-sm font-medium">Help</span>
          </div>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from "vue";
import { useProductWizardStore } from "~/stores/productWizard";

const { t: $t } = useI18n();
const productWizardStore = useProductWizardStore();

const props = defineProps({
  lastSaved: {
    type: Date,
    default: null,
  },
  isDirty: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-help", "close-wizard"]);

const currentStepProgress = computed(() => productWizardStore.currentStepProgressPercentage);

const formatTimeAgo = (date) => {
  if (!date) return "";
  const now = new Date();
  const diff = now - date;
  const minutes = Math.floor(diff / 60000);
  if (minutes < 1) return "now";
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  return `${hours}h ago`;
};

// --- Fallback for category name in header ---
const manifestCategoryName = computed(() => {
  // Only for Product Manifest step
  if (productWizardStore.currentStep !== 7) return "";
  let category =
    productWizardStore.stepData?.categorySearch?.selectedCategory ||
    productWizardStore.selected_category;
  if (!category) {
    console.warn(
      "[Product Manifest] Category data missing in wizard store!",
      productWizardStore.stepData,
      productWizardStore.selected_category,
    );
    return "";
  }
  if (Array.isArray(category.display_name) && category.display_name.length > 0) {
    return category.display_name[0].display_name;
  } else if (typeof category.display_name === "string") {
    return category.display_name;
  } else if (category.name) {
    return category.name;
  }
  return "";
});
</script>
