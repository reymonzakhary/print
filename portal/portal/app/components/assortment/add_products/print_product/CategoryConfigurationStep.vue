<template>
  <div class="h-full overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Category Configuration") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Configure category details including name, description, and visibility") }}
      </p>
    </div>

    <main class="category-config-step mx-auto max-w-7xl">
      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Category Details Card -->
        <div
          class="rounded-md border-2 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="border-b border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30"
              >
                <font-awesome-icon
                  :icon="['fal', 'info-circle']"
                  class="text-lg text-blue-600 dark:text-blue-400"
                />
              </div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $t("Category Details") }}
              </h3>
            </div>
          </div>

          <div class="p-6">
            <CategoryEditForm
              :item="selectedCategory"
              :producer="me?.supplier"
              wizard-mode
              type="category"
              ref="editCategoryFormRef"
              @validated="onFormValidated"
              @update:item="onEditCategoryUpdate"
            />
          </div>
        </div>

        <!-- Category Configuration Card -->
        <div
          class="rounded-md border-2 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="border-b border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30"
              >
                <font-awesome-icon
                  :icon="['fal', 'sliders']"
                  class="text-lg text-purple-600 dark:text-purple-400"
                />
              </div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $t("Configuration Settings") }}
              </h3>
            </div>
          </div>

          <div class="p-6">
            <CategoryConfig
              ref="categoryConfigRef"
              wizard-mode
              :item="selectedCategory"
              @validated="onConfigValidated"
            />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";

const emit = defineEmits(["step-completed", "step-validated"]);

const productWizardStore = useProductWizardStore();
import { computed } from "vue";

// Use a computed property for reactivity
const selectedCategory = computed(() => {
  const categoryData = productWizardStore.getStepData("categorySearch");
  return (
    categoryData?.selected_category ||
    categoryData?.selectedCategory ||
    categoryData?.category ||
    productWizardStore.selected_category ||
    {}
  );
});

import { useStore } from "vuex";

const store = useStore();
const me = computed(() => store.state.settings.me);

const editCategoryFormRef = ref(null);
const categoryConfigRef = ref(null);

const editCategoryData = ref({});
const categoryConfigData = ref({});

// Called when either form emits 'validated'
function onFormValidated(valid) {
  emit("step-validated", { isValid: valid });
}
function onConfigValidated(valid) {
  emit("step-validated", { isValid: valid });
}

// Handle updates from CategoryEditForm
function onEditCategoryUpdate(updatedItem) {
  editCategoryData.value = updatedItem;
  emit("step-validated", { isValid: true, data: { editCategory: updatedItem } });
}

// Called by wizard when Next/Save is pressed
async function goNext() {
  // Validate both forms (assume they expose validate method)
  const formValid = editCategoryFormRef.value?.validate?.() ?? true;
  const configValid = categoryConfigRef.value?.validate?.() ?? true;

  if (!formValid || !configValid) {
    emit("step-validated", { isValid: false });
    return;
  }

  // Save changes to the API in both create and edit mode
  try {
    const api = useAPI();
    const { handleSuccess, handleError } = useMessageHandler();

    // Get the current category data with any updates
    const categoryToSave = {
      ...selectedCategory.value,
      ...editCategoryData.value,
    };

    // Save to API
    await api.put(`categories/${selectedCategory.value.slug}`, categoryToSave);
    handleSuccess({ message: "Category configuration saved successfully" });

    // Update wizard store with new data
    productWizardStore.updateStepData("categorySearch", {
      selectedCategory: categoryToSave,
      selected_category: categoryToSave,
    });

    emit("step-completed", {
      stepNumber: 3,
      data: {
        editCategory: categoryToSave,
        categoryConfig: categoryConfigData.value,
      },
    });

    // In edit mode, stay on current step after saving (user navigates manually via sidebar)
    // In create mode, navigate to next step automatically
    if (!productWizardStore.isEditMode) {
      productWizardStore.goToNextStep();
    }
  } catch (error) {
    handleError(error);
    emit("step-validated", { isValid: false });
  }
}

onMounted(() => {
  emit("step-validated", { isValid: true });
})

defineExpose({ goNext });
</script>
