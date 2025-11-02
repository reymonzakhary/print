/**
 * This is a repository done "the right way" according to official Nuxt documentation.
 *
 * Check out this video for more information on the pattern:
 * https://www.youtube.com/watch?v=jXH8Tr-exhI
 *
 * Or the Nuxt documentation which briefly mentions it:
 * https://nuxt.com/docs/getting-started/data-fetching
 *
 * @param {Function} fetch - The fetch function to use.
 * @returns {Object} The repository object.
 */
export const createRepository = () => {
  // Grabs the $api instance which is injected by the api plugin
  // Works exactly like the $fetch function
  const { $api } = useNuxtApp();

  /**
   * Gets all items
   * @param {{ per_page: number }} options
   * @returns
   */
  async function index({ per_page = 99999 } = {}) {
    return $api("/", {
      query: {
        per_page,
      },
    });
  }

  /**
   * Creates a new item
   * @param {Object} payload
   * @returns
   */
  async function create(payload) {
    return $api("/", {
      method: "POST",
      body: payload,
    });
  }

  /**
   * Gets a single item
   * @param {string} id
   * @returns
   */
  async function read(id) {
    return $api(`/${id}`);
  }

  /**
   * Updates an item
   * @param {Object} payload
   * @returns
   */
  async function update(payload) {
    return $api("/", {
      method: "PUT",
      body: payload,
    });
  }

  /**
   * Deletes an item
   * @param {string} id
   * @returns
   */
  async function destroy(id) {
    return $api(`/${id}`, {
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
