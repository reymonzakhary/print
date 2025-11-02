/**
 * Composable for handling category creation logic
 */
import { ref } from "vue";

export const useCategoryCreation = () => {
  const isCreatingCategory = ref(false);

  /**
   * Creates a new category with the provided parameters
   * @param {Object} params - Creation parameters
   * @returns {Promise<Object>} The created category response
   */
  const createCategory = async (params) => {
    const {
      name,
      displayName,
      type,
      locale,
      selectedProducer,
      selectedCategory,
      selectedSearchItem,
      api,
      handleError,
      handleSuccess,
      $t,
    } = params;

    // Prevent multiple simultaneous API calls
    if (isCreatingCategory.value) {
      return null;
    }

    isCreatingCategory.value = true;

    try {
      // Use selectedCategory if available, otherwise use selectedSearchItem
      let DPName = {};

      if (typeof displayName === "object" && displayName !== null && "iso" in displayName) {
        // Wrap single displayName object in array
        DPName = [displayName];
      } else {
        DPName = [{ iso: locale || "en", display_name: displayName }];
      }

      console.log("Creating category with display names:", DPName);

      // Build payload
      const payload = {
        name: name,
        display_name: DPName,
        system_key: name,
        linked: selectedCategory?.linked || selectedSearchItem?.linked || null,
        published: true,
        price_build: {
          collection: false,
          semi_calculation: false,
          full_calculation: true,
          external_calculation: false,
        },
        start_cost: 0,
      };

      // Build API payload
      let requestUrl = "";

      switch (type) {
        case "producer":
          requestUrl = `suppliers/${selectedProducer.id}/categories/${selectedCategory?.slug || selectedCategory?.name}/link`;
          payload = null;
          break;

        case "preset":
          requestUrl = `/categories`;
          break;

        case "blank":
          requestUrl = `/categories`;
          break;

        default:
          throw new Error(`Unknown category type: ${type}`);
      }

      // Make API call
      const response = await api.post(requestUrl, payload);

      // Handle success
      handleSuccess(response);
      return response.data;
    } catch (error) {
      // Handle error
      handleError(error);

      // Return error info for caller to handle
      return {
        error: true,
        isConflictError: error.response?.status === 422,
        message: error.response?.data?.message || error.message,
      };
    } finally {
      // Don't reset loading state here - let the main component handle it after wizard progression
      // This prevents race conditions where goNext() might be called again before wizard completes
    }
  };

  /**
   * Resets the creating state (to be called after wizard progression)
   */
  const resetCreatingState = () => {
    isCreatingCategory.value = false;
  };

  return {
    isCreatingCategory,
    createCategory,
    resetCreatingState,
  };
};
