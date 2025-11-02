import Fuse from "fuse.js";

/**
 * Split the provided string by spaces (ignoring spaces within "quoted text") into an array of tokens.
 *
 * @param string The string to tokenize
 * @returns Array of tokens
 */
export const tokeniseStringWithQuotesBySpaces = (string) =>
  string.match(/("[^"]*?"|[^"\s]+)+(?=\s*|\s*$)/g) ?? [];

/**
 * The composable for fuzzy searching
 * @param {Array} items - The array of items to search - could either be a ref or a plain array
 * @param {Object} options - The options for the fuzzy search
 */
export function useFuzzySearch(items, options) {
  const searchQuery = ref("");
  const fuseInstance = ref(null);

  // Create a computed property to handle both ref and non-ref inputs
  const itemsValue = computed(() => {
    return isRef(items) ? items.value : items;
  });

  // Create the fuse instance
  watch(
    () => itemsValue.value,
    (newValue) => {
      if (newValue) {
        fuseInstance.value = new Fuse(newValue, {
          keys: options.keys,
          threshold: options.threshold || 0.3,
          ignoreLocation: options.ignoreLocation ?? true,
          findAllMatches: options.findAllMatches ?? true,
        });
      }
    },
    { immediate: true, deep: true },
  );

  // Computed property for filtered results
  const filteredResults = computed(() => {
    if (!searchQuery.value || !fuseInstance.value) return itemsValue.value;

    // Tokenize the search query
    const tokenizedSearchQuery = tokeniseStringWithQuotesBySpaces(searchQuery.value);

    if (tokenizedSearchQuery.length === 0) return itemsValue.value;

    // Create a complex search query with $and and $or operators
    const complexQuery = {
      $and: tokenizedSearchQuery.map((searchToken) => {
        // Create an $or expression for each search token
        const orFields = options.keys.map((key) => ({ [key]: searchToken }));
        return { $or: orFields };
      }),
    };

    // Perform the search with the complex query
    return fuseInstance.value.search(complexQuery).map((result) => result.item);
  });

  // Method to clear search
  const clearSearch = () => (searchQuery.value = "");

  return {
    searchQuery,
    filteredResults,
    clearSearch,
  };
}
