/**
 * Repository for managing lists in localStorage
 */
export const useListsRepository = () => {
  /**
   * Storage key for lists
   */
  const STORAGE_KEY = "product_finder_lists";

  /**
   * Get all lists from localStorage
   * @returns {Array} Array of list objects
   */
  function getLists() {
    const listsJson = localStorage.getItem(STORAGE_KEY);
    return listsJson ? JSON.parse(listsJson) : [];
  }

  /**
   * Get a single list by ID
   * @param {string} listId - ID of the list to retrieve
   * @returns {Object|null} List object or null if not found
   */
  function getList(listId) {
    const lists = getLists();
    return lists.find((list) => list.id === listId) || null;
  }

  /**
   * Save lists to localStorage
   * @param {Array} lists - Array of list objects to save
   */
  function saveLists(lists) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(lists));
  }

  /**
   * Create a new list
   * @param {Object} list - List object with name, color (optional)
   * @returns {Object} Created list with ID
   */
  function createList(list) {
    const lists = getLists();

    // Create new list with ID and defaults
    const newList = {
      id: `list_${Date.now()}`,
      name: list.name,
      color: list.color || null,
      categories: [],
      amount: 0,
      createdAt: new Date().toISOString(),
      ...list,
    };

    // Add to list collection
    lists.push(newList);
    saveLists(lists);

    return newList;
  }

  /**
   * Update an existing list
   * @param {string} listId - ID of the list to update
   * @param {Object} updates - Object with properties to update
   * @returns {Object|null} Updated list or null if not found
   */
  function updateList(listId, updates) {
    const lists = getLists();
    const index = lists.findIndex((list) => list.id === listId);

    if (index === -1) return null;

    // Update list with new values
    const updatedList = {
      ...lists[index],
      ...updates,
      updatedAt: new Date().toISOString(),
    };

    // If categories were updated, update the amount as well
    if (updates.categories) {
      updatedList.amount = updates.categories.length;
    }

    lists[index] = updatedList;
    saveLists(lists);

    return updatedList;
  }

  /**
   * Delete a list
   * @param {string} listId - ID of the list to delete
   * @returns {boolean} True if deleted, false if not found
   */
  function deleteList(listId) {
    const lists = getLists();
    const filteredLists = lists.filter((list) => list.id !== listId);

    if (filteredLists.length === lists.length) return false;

    saveLists(filteredLists);
    return true;
  }

  /**
   * Reorder lists array
   * @param {Array} reorderedLists - New order of lists
   * @returns {Array} Updated lists array
   */
  function reorderLists(reorderedLists) {
    saveLists(reorderedLists);
    return reorderedLists;
  }

  return {
    getLists,
    getList,
    createList,
    updateList,
    deleteList,
    reorderLists,
  };
};
