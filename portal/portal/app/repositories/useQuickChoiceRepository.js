export const useQuickChoiceRepository = () => {
  /**
   * Fetches and processes a list of favorite products from local storage.
   * Filters the list to ensure unique favorites based on the `slug` property
   * and sorts them in descending order based on the `count` property.
   *
   * @return {Promise<Object[]>} A promise that resolves to an array of unique favorite products, sorted by count in descending order.
   */
  async function index() {
    const favorites = JSON.parse(localStorage.getItem("product-finder-favorites") || "[]");
    const uniqueFavorites = favorites.filter(
      (favorite, index, self) => index === self.findIndex((f) => f.slug === favorite.slug),
    );
    return uniqueFavorites.sort((a, b) => (b.count || 0) - (a.count || 0));
  }

  /**
   * Updates the count of a specific category in the list of favorites stored in local storage.
   * If the category is not present, it adds it with an initial count of 1.
   * Ensures the list does not exceed a maximum of 20 items.
   *
   * @param {Object} category - The category object containing a `slug` property to identify it.
   * @return {Promise<void>} A promise that resolves when the count is updated and stored.
   */
  async function updateCount(category) {
    const uniqueFavorites = await index();
    const existingCategory = uniqueFavorites.find((fav) => fav.slug === category.slug);

    if (existingCategory) existingCategory.count = (existingCategory.count || 0) + 1;
    else uniqueFavorites.push({ slug: category.slug, count: 1 });

    if (uniqueFavorites.length > 20) uniqueFavorites.splice(20);
    localStorage.setItem("product-finder-favorites", JSON.stringify(uniqueFavorites));
  }

  async function remove(category) {
    const favorites = await index();
    const filteredFavorites = favorites.filter((fav) => fav.slug !== category.slug);
    localStorage.setItem("product-finder-favorites", JSON.stringify(filteredFavorites));
  }

  /**
   * Clears the Quick Choice Items
   * @returns
   */
  async function clear() {
    localStorage.removeItem("product-finder-favorites");
  }

  return {
    index,
    updateCount,
    clear,
    remove,
  };
};
