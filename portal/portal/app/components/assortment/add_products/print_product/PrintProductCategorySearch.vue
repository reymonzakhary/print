<template>
  <div
    class="mx-auto flex h-full flex-col p-4"
    :class="{ 'w-full': helpVisible, 'w-2/3': !helpVisible }"
  >
    <!-- Header -->
    <div class="mb-6 text-center">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Search & Select Category") }}
      </h1>
      <p class="mt-2 text-gray-600 dark:text-gray-300">
        {{ getDescriptionTextForComponent() }}
      </p>
    </div>

    <!-- Search Component -->
    <div class="w-full flex-1">
      <AddProductSearch
        :search-url="url"
        type="category"
        :adding_type="addingType"
        :show-navigation-button="false"
        :sticky-search="false"
        @add-new="addNewCategory"
        @category-selected="onCategorySelectedForComponent"
        @search-updated="onSearchUpdated"
        @update-display-name="onUpdateDisplayName"
        @update-name="onUpdateName"
      />
    </div>
  </div>
</template>

<script setup>
/**
 * PrintProductCategorySearch Component
 *
 * This component handles the category selection/creation step in the product wizard.
 * It has been refactored to use the composition pattern for better maintainability.
 *
 * ARCHITECTURE OVERVIEW:
 * =====================
 * - Main Component: Handles template logic and event coordination
 * - useCategoryCreation: Manages complex category creation API calls and business logic
 * - useCategorySearch: Handles search functionality, URL generation, and category fetching
 * - useWizardStep: Manages wizard step validation, navigation, and state persistence
 * - Utilities: categoryDisplayName.js and categoryPayload.js for pure business logic
 *
 * FLOW EXPLANATION:
 * ================
 * 1. User searches for categories using AddProductSearch component
 * 2. Two scenarios:
 *    a) Results found: User can select existing category and customize it
 *    b) No results: User can create completely new category from search term
 * 3. Component validates step and enables navigation
 * 4. When user clicks "Next", addNewCategory() is called to create/link the category
 * 5. After successful creation, wizard progresses to next step
 */

import { computed, onMounted, watch } from "vue";
import { useProductWizardStore } from "@/stores/productWizard";

// Import specialized composables for different concerns
import { useCategoryCreation } from "@/composables/useCategoryCreation"; // API calls & creation logic
import { useCategorySearch } from "@/composables/useCategorySearch"; // Search & URL management
import { useWizardStep } from "@/composables/useWizardStep"; // Step validation & navigation
import { faStickyNote } from "@fortawesome/pro-thin-svg-icons";

// Standard Nuxt composables
const { t: $t, locale } = useI18n();
const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();
const productWizardStore = useProductWizardStore();

// Define events this component can emit to parent wizard
const emit = defineEmits(["step-completed", "next-step", "step-validated"]);

const props = defineProps({
  helpVisible: {
    type: Boolean,
    default: false,
  },
});

// ============================================================================
// COMPOSABLES - Each handles a specific aspect of functionality
// ============================================================================

/**
 * CATEGORY CREATION COMPOSABLE
 * Handles the complex logic of creating categories via API calls
 * - Manages loading states to prevent duplicate POST requests
 * - Contains display name prioritization logic (search term vs form input vs selected item)
 * - Builds API payloads for different category types (producer/preset/blank)
 * - Handles API responses and error states
 */
const { createCategory, isCreatingCategory, resetCreatingState } = useCategoryCreation();

/**
 * CATEGORY SEARCH COMPOSABLE
 * Manages search functionality and URL generation based on product type
 * - Dynamically builds search URLs based on producer/preset/blank type
 * - Handles category fetching from different endpoints
 * - Manages search state and query updates
 * - Provides utility functions for legacy component compatibility
 */
const {
  categories, // Array of fetched categories
  searchQuery, // Current search term
  url, // Dynamic API endpoint URL
  initializeUrl, // Function to set URL based on type/producer
  getCategories, // Function to fetch categories from API
  updateSearchQuery, // Function to update search state
  getAddingType, // Legacy type mapping function
  getDescriptionText, // Description text based on type
} = useCategorySearch();

/**
 * WIZARD STEP COMPOSABLE
 * Handles step validation, state management, and wizard progression
 * - Manages selected category and form state (selectedCategory, name, etc.)
 * - Validates step completion and enables/disables navigation
 * - Handles step completion events and wizard progression
 * - Loads and persists step data to/from wizard store
 * - Manages error states and retry logic
 */
const {
  selectedCategory, // Currently selected category object
  selectedSearchItem, // Selected item from search results
  name, // Custom display name from form input
  displayName, // Display name from form input
  validateStep, // Function to validate step and emit events
  onCategorySelected, // Handler for when user selects a category
  updateDisplayName, // Handler for display name form changes
  updateName, // Handler for name changes
  emitStepCompletion, // Function to emit step completion events
  handleStepError, // Function to handle step validation errors
  loadExistingData, // Function to load existing data from store
} = useWizardStep();

// ============================================================================
// COMPUTED PROPERTIES - Reactive data derived from wizard store
// ============================================================================

/**
 * Product type from previous wizard step (productOrigin)
 * Determines which category endpoint to use and what payload structure to build
 * - "producer": Categories from specific supplier/producer
 * - "preset": Categories from Prindustry's preset library
 * - "blank": Custom categories created from scratch
 */
const type = computed(() => {
  const originData = productWizardStore.getStepData("productOrigin");
  return originData.type || "preset";
});

/**
 * Selected producer from previous wizard step (if applicable)
 * Used for building producer-specific category URLs and API calls
 * Only relevant when type === "producer"
 */
const selectedProducer = computed(() => {
  return productWizardStore.getStepData("selectedProducer") || {};
});

/**
 * Legacy type mapping for backward compatibility with AddProductSearch component
 * Maps new wizard types to the legacy naming convention expected by the search component
 */
const addingType = computed(() => getAddingType(type.value));

// ============================================================================
// WATCHERS - Reactive behavior when data changes
// ============================================================================

/**
 * Watch for changes to selectedSearchItem (from store data)
 * When an item is restored from store (e.g., user navigates back), automatically select it
 * This ensures the component state stays in sync with wizard store data
 */
watch(selectedSearchItem, (newValue) => {
  if (newValue && Object.keys(newValue).length > 0) {
    selectItem(newValue);
  }
});

/**
 * Debug watcher for URL changes
 * Logs when the search URL changes based on type/producer changes
 * Helpful for debugging which endpoint is being used for category search
 */
watch(
  url,
  (newUrl) => {
    console.log(
      "PrintProductCategorySearch - URL updated:",
      newUrl,
      "Type:",
      type.value,
      "Producer:",
      selectedProducer.value,
    );
  },
  { immediate: true },
);

// ============================================================================
// COMPONENT METHODS - Wrapper functions for composable methods
// ============================================================================

/**
 * Initialize the search URL based on current type and producer
 * Wrapper for the composable method to provide component-specific context
 */
const initializeUrlForComponent = () => {
  initializeUrl(type.value, selectedProducer.value);
};

/**
 * Select an item from search results (legacy method)
 * Updates component state and wizard store when user selects a category
 * Note: This is a legacy method - new selection logic should use onCategorySelected from composable
 */
const selectItem = (item) => {
  selectedCategory.value = item;
  productWizardStore.updateStepData("categorySearch", {
    selected_search_item: item,
    selected_category: item,
  });
  validateStepForComponent();
};

/**
 * Validate current step wrapper
 * Calls the composable's validateStep with component-specific context (emit, store)
 */
const validateStepForComponent = () => {
  return validateStep(emit, productWizardStore);
};

/**
 * Fetch categories wrapper
 * Calls the composable's getCategories with component-specific context (api, error handler)
 */
const getCategoriesForComponent = async () => {
  await getCategories(api, handleError);
};

/**
 * MAIN CATEGORY CREATION METHOD
 * =============================
 * This is the core method that creates/links categories via API calls.
 *
 * FLOW:
 * 1. Prevents duplicate calls by checking isCreatingCategory loading state
 * 2. Calls createCategory composable with all necessary parameters
 * 3. Handles success: Updates component state and triggers wizard progression
 * 4. Handles errors: Shows error messages and manages retry logic
 *
 * CALLED BY:
 * - @add-new event from AddProductSearch (when no results found)
 * - goNext() method (when user clicks "Next" button)
 *
 * PARAMETERS:
 * - searchTerm: Direct search term from AddProductSearch (highest priority for display name)
 */
const addNewCategory = async (searchTerm = null) => {
  // DUPLICATE CALL PROTECTION: Prevent multiple simultaneous POST requests
  // This is critical because the method can be triggered from multiple sources
  if (isCreatingCategory.value) {
    return;
  }

  // CALL COMPOSABLE: Delegate complex creation logic to specialized composable
  // The composable handles: display name prioritization, API payload building, HTTP requests
  const result = await createCategory({
    name:
      name?.value ||
      selectedCategory?.value?.name ||
      selectedSearchItem?.value?.name ||
      searchQuery?.value, // From form input (user editing)
    displayName:
      displayName?.value ||
      selectedCategory?.value?.display_name ||
      selectedSearchItem?.value?.display_name ||
      searchQuery?.value, // Current display name (when no item is selected)
    searchQuery: searchQuery?.value, // Stored search query (fallback)
    selectedCategory: selectedCategory?.value, // Selected existing category
    selectedSearchItem: selectedSearchItem?.value, // Selected search result
    type: type.value, // Product type (producer/preset/blank)
    locale: locale.value, // Current locale for display_name
    selectedProducer: selectedProducer.value, // Producer data (if type=producer)
    api, // API instance for HTTP calls
    handleError, // Error handler function
    handleSuccess, // Success handler function
    $t, // Translation function
  });

  // HANDLE SUCCESS: Update local state and trigger wizard progression
  if (result && !result.error) {
    let newBoops = [];
    // Update component reactive state with API response
    selectedCategory.value = result;
    name.value = result.name;
    if (productWizardStore.stepData?.editBoops?.selectedBoops?.length > 0) {
      newBoops = await storeBoxesAndOptions(productWizardStore.stepData.editBoops.selectedBoops);
      productWizardStore.updateStepData("editBoops", {
        selectedBoops: newBoops,
        divided: result.divided || false,
        initialized: true,
        lastUpdated: Date.now(),
      });
    }

    // Persist data to wizard store for step navigation and future reference
    productWizardStore.updateStepData("categorySearch", {
      selected_category: result, // Full category object
      selected_boops: newBoops, // Box/Option structures
      name: result.name, // name for forms
      display_name: result.display_name, // Display name for forms
    });

    // MARK CATEGORY AS CREATED: Lock steps 1-2 after progression beyond step 2
    productWizardStore.setCategoryCreated();

    // SAVE WIZARD STATE: Enable persistence after category creation
    try {
      const saveResult = await productWizardStore.saveWizardState(result.id || result.slug);
      handleSuccess(saveResult);
    } catch (error) {
      handleError(error);
    }

    // EMIT WIZARD EVENTS: Trigger step completion and progression
    // The callback resets loading state AFTER wizard progression completes
    // This prevents race conditions where goNext() might be called again during progression
    emitStepCompletion(emit, result, () => {
      resetCreatingState();
    });

    // HANDLE ERROR: API call failed or returned error response
  } else if (result && result.error) {
    handleStepError(emit, result); // Emit appropriate validation events
    resetCreatingState(); // Reset loading state so user can retry

    // HANDLE NULL: createCategory returned null (already in progress - should not happen due to our check above)
  } else if (result === null) {
    console.log("PrintProductCategorySearch - createCategory already in progress");
  }
};

const storeBoxesAndOptions = async (boops) => {
  try {
    const newBoops = [];

    boops.forEach((box, boxIndex) => {
      const newbox = {
        name: box.name,
        system_key: box.name,
        display_name: box.display_name.find((d) => d.iso === locale.value) || "",
        linked: box.id || "",
        published: true,
        input_type: "checkbox",
      };
      api.post(`boxes`, newbox).then((response) => {
        newBoops[boxIndex] = {
          ...response.data,
          ops: [],
        };
        box.ops.forEach((option, optionIndex) => {
          const newOption = {
            name: option.name || "",
            system_key: option.name || "",
            slug: option.slug || "",
            display_name: option.display_name.find((d) => d.iso === locale.value) || "",
            published: true,
            linked: option.id || "",
            additional: [],
          };
          api.post(`options`, newOption).then((response) => {
            newBoops[boxIndex].ops[optionIndex] = response.data;
          });
        });
      });
    });

    return newBoops;
  } catch (error) {
    console.error("Error saving boxes and options", error);
    handleError(error);
    throw error; // Re-throw to prevent inconsistent state
  }
};
// ============================================================================
// NAVIGATION METHODS - Wizard step navigation handlers
// ============================================================================

/**
 * Navigate to previous step
 * Simple wrapper for wizard store method
 */
const goBack = () => {
  productWizardStore.previousStep();
};

/**
 * NAVIGATE TO NEXT STEP
 * =====================
 * This method handles the "Next" button click in the wizard.
 *
 * LOGIC:
 * 1. If category creation is in progress, just proceed to next step (don't create again)
 * 2. If user has selected a category, create/link it via addNewCategory()
 * 3. If no category selected, show error message
 *
 * RACE CONDITION PREVENTION:
 * The isCreatingCategory check prevents duplicate API calls when:
 * - User rapidly clicks "Next" button
 * - addNewCategory() triggers wizard progression which calls goNext() again
 */
const goNext = async () => {
  // CHECK LOADING STATE: Prevent duplicate category creation during progression
  if (isCreatingCategory.value) {
    productWizardStore.goToNextStep();
    return;
  }

  // VALIDATE SELECTION: Ensure user has selected a category before proceeding
  if (selectedCategory.value && Object.keys(selectedCategory.value).length > 0) {
    await addNewCategory(); // This will handle progression after successful creation
  } else {
    // ERROR: No category selected - show validation message
    handleError({ message: $t("Please select a category first") });
  }
};

// ============================================================================
// COMPONENT API - Methods exposed to parent wizard
// ============================================================================

/**
 * Expose methods that parent wizard can call directly
 * These are the public API of this step component
 */
defineExpose({
  goNext, // Navigate to next step
  goBack, // Navigate to previous step
  validateStep: validateStepForComponent, // Validate current step state
});

// ============================================================================
// EVENT HANDLERS - Template event handlers with composable integration
// ============================================================================

/**
 * Get description text for template
 * Wrapper that provides type and translation context to composable
 */
const getDescriptionTextForComponent = () => {
  return getDescriptionText(type.value, $t);
};

/**
 * Handle category selection from AddProductSearch component
 * Delegates to composable with component-specific validation function
 * Enhanced to automatically load boops data for the selected category
 */
const onCategorySelectedForComponent = (category) => {
  onCategorySelected(category, validateStepForComponent, api, productWizardStore);
};

/**
 * Handle search term updates from AddProductSearch component
 * Updates composable state and persists to wizard store
 */
const onSearchUpdated = (searchTerm) => {
  updateSearchQuery(searchTerm); // Update composable state
  productWizardStore.updateStepData("categorySearch", {
    // Persist to store
    search_query: searchTerm,
  });
};

/**
 * Handle display name updates from ProductNameForm component
 * Delegates to composable with store context for persistence
 */
const onUpdateDisplayName = (newDisplayName) => {
  updateDisplayName(newDisplayName, productWizardStore);
};

/**
 * Handle name updates from ProductNameForm component
 * Currently just delegates to composable for logging
 * Name is auto-generated from display name in most cases
 */
const onUpdateName = (newName) => {
  updateName(newName);
};

// ============================================================================
// COMPONENT LIFECYCLE - Initialization and cleanup
// ============================================================================

/**
 * Component mounted lifecycle hook
 * Initializes the component state and loads data
 *
 * INITIALIZATION SEQUENCE:
 * 1. Initialize search URL based on type/producer
 * 2. Fetch initial categories from API
 * 3. Load any existing step data from wizard store (for navigation back/forward)
 */
onMounted(() => {
  initializeUrlForComponent(); // Set up search endpoint URL
  getCategoriesForComponent(); // Load initial category data
  loadExistingData(productWizardStore); // Restore previous step data if any
});
</script>

<style lang="scss" scoped>
.nested-item {
  &:before {
    content: "";
    position: absolute;
    top: -0.25rem;
    bottom: -0.25rem;
    left: -1.35rem;
    width: 1.25rem;
    background-image: url("data:image/svg+xml,%3Csvg width='22' height='200' viewBox='0 0 12 200' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M 1 0 V 200 M 1 100 H 22' stroke='%23cbd5e1' stroke-width='2'/%3E%3C/svg%3E%0A");
    background-repeat: no-repeat;
    background-position: 0;
  }

  &:last-child:before {
    top: 0.25rem;
    background-image: url("data:image/svg+xml,%3Csvg width='22' height='200' viewBox='0 0 12 200' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M 1 0 V 85 Q 1 100 22 100' stroke='%23cbd5e1' stroke-width='2'/%3E%3C/svg%3E%0A");
  }
}
</style>
