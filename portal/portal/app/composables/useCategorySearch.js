/**
 * Composable for handling category search logic
 */
import { ref, computed } from "vue";

export const useCategorySearch = () => {
  const categories = ref([]);
  const searchQuery = ref("");
  const url = ref("");

  /**
   * Initializes the search URL based on type and producer
   * @param {string} type - The category type (producer, preset, blank)
   * @param {Object} selectedProducer - The selected producer object
   */
  const initializeUrl = (type, selectedProducer) => {
    switch (type) {
      case "producer":
        if (selectedProducer && selectedProducer.id) {
          url.value = `/suppliers/${selectedProducer.id}/categories?filter=`;
        } else {
          url.value = "/finder/categories/search?search=";
        }
        break;
      case "preset":
        url.value = "/finder/categories/search?search=";
        break;
      case "blank":
        url.value = "/finder/categories/search?search=";
        break;
      default:
        url.value = "/finder/categories/search?search=";
        break;
    }

    console.log(
      "useCategorySearch - URL initialized:",
      url.value,
      "Type:",
      type,
      "Producer:",
      selectedProducer,
    );
  };

  /**
   * Fetches categories from the API
   * @param {Object} api - The API instance
   * @param {Function} handleError - Error handler function
   */
  const getCategories = async (api, handleError) => {
    if (!url.value) {
      return;
    }

    try {
      const response = await api.get(url.value);
      categories.value = response.data || [];
    } catch (error) {
      handleError(error);
      categories.value = []; // Fallback to empty array
    }
  };

  /**
   * Updates the search query
   * @param {string} searchTerm - The new search term
   */
  const updateSearchQuery = (searchTerm) => {
    searchQuery.value = searchTerm;
  };

  /**
   * Maps new types to legacy types for AddProductSearch component compatibility
   * @param {string} type - The category type
   * @returns {string} The mapped legacy type
   */
  const getAddingType = (type) => {
    switch (type) {
      case "producer":
        return "from_producer";
      case "preset":
        return "from_preset";
      case "blank":
        return "blank";
      default:
        return "from_preset";
    }
  };

  /**
   * Gets description text based on type
   * @param {string} type - The category type
   * @param {Function} $t - Translation function
   * @returns {string} The description text
   */
  const getDescriptionText = (type, $t) => {
    switch (type) {
      case "producer":
        return $t("Select a category from the producer's catalog");
      case "preset":
        return $t("Search for a category from our preset library");
      case "blank":
        return $t("Create a custom category or search existing ones");
      default:
        return $t("Select a category to continue");
    }
  };

  return {
    categories,
    searchQuery,
    url,
    initializeUrl,
    getCategories,
    updateSearchQuery,
    getAddingType,
    getDescriptionText,
  };
};
