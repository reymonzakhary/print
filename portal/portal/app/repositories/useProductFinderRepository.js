/**
 * @returns {Object} The repository object.
 */
export const useProductFinderRepository = () => {
  const { $api } = useNuxtApp();

  const getCategoryIdFromSku = (sku) => {
    // Split the SKU string by ':' and return the last segment
    if (!sku) return null;
    const parts = sku.split(":");
    return parts.length > 0 ? parts[parts.length - 1] : null;
  };

  /**
   * Transforms a category search result object from the API format to the target format
   * @param {Object} sourceObject - The original object to transform
   * @returns {Object} - The transformed object in the target format
   */
  function transformCategorySearchResult(sourceObject) {
    /**
     * The search result gets a 'nl' key instead of the usual displayName,
     * therefor we add this here to emulate the usual format
     * until the backend is fixed.
     */
    const displayName = sourceObject[locale.value] || sourceObject.displayName || sourceObject.name;

    /**
     * Get the category ID from the SKU
     */
    const id = getCategoryIdFromSku(sourceObject.sku);
    const transformed = { ...sourceObject, id, displayName };
    return transformed;
  }

  /**
   * Gets all categories
   * @returns {Promise<Array>}
   */
  async function getAllCategories() {
    return $api("/marketplace/categories", { query: { search: "", per_page: 99999 } });
  }

  /**
   * Gets all options
   * @param {{ per_page: number }} options
   * @returns {Promise<Array>}
   */
  async function getAllOptions() {
    return $api("/marketplace/options", { query: { search: "", per_page: 99999 } });
  }

  /**
   * Searches for categories
   * @param {string} query
   * @param {string} iso
   * @returns {Promise<Array>}
   */
  async function searchCategories(query) {
    return $api("/finder/categories/search", { query: { search: query, iso: locale.value } });
  }

  /**
   * Gets a single category
   * @param {string} slug
   * @returns
   */
  async function getSingleCategory(id) {
    return $api(`/finder/categories/${id}`);
  }

  /**
   * Sends a batch request to the Nuxt API which in turn does the required
   * requests to the backend API. This way the user won't have to do dozens
   * of API-calls.
   * @param {string} slug
   * @param {Object} payload
   * @returns
   */
  async function getProducts(slug, payload) {
    return $fetch(`/api/product-finder/${slug}`, {
      method: "POST",
      body: payload,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: "Bearer " + useAuthStore().token,
      },
      credentials: "include",
    });
  }

  return {
    getAllCategories,
    getAllOptions,
    getSingleCategory,
    searchCategories,
    getProducts,
    transformCategorySearchResult,
  };
};
