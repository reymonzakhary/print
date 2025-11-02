import { ref, computed, onMounted } from "vue";

export const useProductFinderStore = defineStore("productFinder", () => {
  const { formatCurrency } = useMoney();
  const { addToast } = useToastStore();
  const { t: $t } = useI18n();

  const categories = ref([]);
  const options = ref([]);

  const activeQuotation = ref(null);
  const activeOrder = ref(null);

  // Basket Management
  const basketItems = ref([]);
  const STORAGE_KEY = "product-finder-basket";
  // Basket Expiration Management
  const MAX_BASKET_AGE = 1000 * 60 * 60 * 24; // 24 hours
  const DATE_KEY = "product-finder-basket-date";

  // Dialog management
  const isBasketOpen = ref(false);
  const isOrderMode = ref(false);
  const selectedVariant = ref(null);
  const selectedCategory = ref(null);

  // Load basket from localStorage on initialization
  onMounted(() => {
    checkBasketDate();
    loadBasketFromStorage();
  });

  /**
   * In order to prevent mistakes with the production days of basket items,
   * we have to remove the basket items after 24 hours. Otherwise the user
   * will order products with a wrong production date.
   */
  const checkBasketDate = () => {
    const date = localStorage.getItem(DATE_KEY);
    if (date) {
      const diff = new Date() - new Date(date);
      if (diff > MAX_BASKET_AGE) {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(DATE_KEY);
        addToast({
          message: $t("We have removed the items from your basket as they were expired."),
          type: "info",
        });
      }
    }
  };

  // Clear selected variant
  const clearSelectedVariant = () => {
    selectedVariant.value = null;
  };

  // Save basket to localStorage
  const saveBasketToStorage = () => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(basketItems.value));
      localStorage.setItem(DATE_KEY, new Date().toISOString());
    } catch (error) {
      console.error("Error saving basket to localStorage:", error);
    }
  };

  // Load basket from localStorage
  const loadBasketFromStorage = () => {
    try {
      const storedItems = localStorage.getItem(STORAGE_KEY);
      if (storedItems) basketItems.value = JSON.parse(storedItems);
    } catch (error) {
      console.error("Error loading basket from localStorage:", error);
    }
  };

  // Check if item is in basket
  const isItemInBasket = (productId) => basketItems.value.some((i) => i.productId === productId);

  // Add item to basket
  const addItemToBasket = (item) => {
    basketItems.value.push(item);
    saveBasketToStorage();
  };

  // Remove item from basket
  const removeItemFromBasket = (index) => {
    basketItems.value.splice(index, 1);
    saveBasketToStorage();
  };

  // Clear basket
  const clearBasket = () => {
    basketItems.value = [];
    saveBasketToStorage();
  };

  // Open basket dialog
  const openBasketDialog = (orderMode = false, variant = null, category = null) => {
    if (variant) {
      selectedVariant.value = variant;
    }

    if (category) {
      selectedCategory.value = category;
    }

    isOrderMode.value = orderMode;
    isBasketOpen.value = true;
  };

  // Close basket dialog
  const closeBasketDialog = () => {
    isBasketOpen.value = false;
  };

  // Get basket item count (computed)
  const basketItemCount = computed(() => basketItems.value.length);

  // Calculate basket total (computed)
  const basketTotal = computed(() => {
    if (basketItems.value.length === 0) return "€ 0.00";
    const total = basketItems.value.reduce((sum, item) => sum + (item.priceNumeric || 0), 0);
    return formatCurrency(total, 100);
  });

  return {
    categories,
    options,
    activeQuotation,
    activeOrder,

    basketItems,
    basketItemCount,
    basketTotal,

    isBasketOpen,
    isOrderMode,
    selectedVariant,
    selectedCategory,

    isItemInBasket,
    addItemToBasket,
    removeItemFromBasket,
    clearBasket,
    openBasketDialog,
    closeBasketDialog,
    clearSelectedVariant,
  };
});
