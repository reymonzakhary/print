export const useLexiconRepository = () => {
  const { $api } = useNuxtApp();
  const API_PREFIX = "/lexicons"; // Assuming $api is configured for /api/v1/mgr base path

  /**
   * Gets lexicon items, supports query parameters for filtering.
   * @param {Object} params - Query parameters (e.g., { key: 'some.key', language: 'en', namespace: 'mail', area: 'quotation', per_page: 1 })
   * @returns {Promise<Array>}
   */
  async function index(params = {}) {
    const response = await $api(API_PREFIX, { query: params });
    if (response && response.data) return response.data;

    // Fallback for unexpected structure or if area is not in params (though it should be for lexicons)
    console.error(
      "[useLexiconRepository] Lexicons not found in expected location in API response or area missing from params:",
      response,
      params,
    );
    return []; // Return empty array if data is not in the expected format
  }

  /**
   * Creates a new lexicon item
   * @param {Object} payload - The lexicon data to create
   * @returns {Promise<Object>}
   */
  async function create(payload) {
    return $api(API_PREFIX, {
      method: "POST",
      body: payload,
    });
  }

  /**
   * Gets a single lexicon item by ID
   * @param {string|number} id - The ID of the lexicon item
   * @returns {Promise<Object>}
   */
  async function read(id) {
    return $api(`${API_PREFIX}/${id}`);
  }

  /**
   * Updates a lexicon item.
   * The API route is PUT|PATCH api/v1/mgr/lexicons/{lexicon}
   * We'll use PATCH as it's generally preferred for partial updates if supported.
   * The payload should contain the fields to update, e.g., { value: "new value" }.
   * The full lexicon object might be required by the backend, or just the changed fields.
   * For now, we assume the backend can handle a payload with just the 'value'.
   * @param {string|number} id - The ID of the lexicon item to update
   * @param {Object} payload - The data to update (e.g., { value: "new value" })
   * @returns {Promise<Object>}
   */
  async function update(id, payload) {
    return $api(`${API_PREFIX}/${id}`, {
      method: "PUT", // Changed to PUT as per user provided routes, though PATCH is often for partial.
      // The API specifies PUT|PATCH, so PUT should be fine.
      body: payload,
    });
  }

  /**
   * Deletes a lexicon item
   * @param {string|number} id - The ID of the lexicon item to delete
   * @returns {Promise<Object>}
   */
  async function destroy(id) {
    return $api(`${API_PREFIX}/${id}`, {
      method: "DELETE",
    });
  }

  return {
    index,
    create,
    read,
    update,
    destroy,
  };
};
