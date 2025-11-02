export const useMoney = () => {
  const { handleError } = useMessageHandler();
  const authStore = useAuthStore();
  const i18n = useI18n();

  function parseCurrencyString(value) {
    return parseFloat(value.replace(/[^0-9.,]/g, "").replace(",", ".")) * 100;
  }

  function removeCurrencySymbol(value) {
    if (typeof value !== "string") return value;
    return value.split(" ")[1];
  }

  function formatCurrency(value, decimals = 100) {
    const currency = authStore.currencySettings;
    const rands = value / decimals;
    try {
      const num = new Intl.NumberFormat(i18n.locale.value, {
        style: "currency",
        currency: currency,
        currencyDisplay: "symbol",
      })
        .format(rands)
        .replace(/^(\D+)/, "$1");
      return num;
    } catch (error) {
      handleError(error);
      return "0,00";
    }
  }

  function getCurrencySymbol() {
    // const currency = authStore.settings.data.find((setting) => setting.key === "currency").value;
    const currency = authStore.currencySettings;
    /**
     * currency returns the iso 4217 currency code
     * https://en.wikipedia.org/wiki/ISO_4217
     */
    try {
      const currencySymbol = new Intl.NumberFormat(i18n.locale, {
        style: "currency",
        currency: currency,
        currencyDisplay: "symbol",
      }).format(0); // 1 is a dummy value to get the symbol
      // only return the symbol
      const formattedSymbol = currencySymbol.replace(/[0-9.,]/g, "").trim();
      return formattedSymbol;
    } catch (error) {
      handleError(error);
      return "";
    }
  }

  function _parsePrice(price) {
    // Extract the numeric part and convert to a float
    const numericPart = parseFloat(price.replace(/[^0-9,.-]+/g, "").replace(",", "."));
    // Extract the currency part
    const currencyPart = price.replace(/[0-9,.-]+/g, "").trim();
    return { numericPart, currencyPart };
  }

  function _formatPrice(numericPart, currencyPart) {
    const formattedNumber = numericPart.toFixed(2).replace(".", ",");
    return `${currencyPart} ${formattedNumber}`;
  }

  function calculateAndFormatPrice(price, multiplier) {
    if (!price) return "";
    const { numericPart, currencyPart } = _parsePrice(price);
    const newNumericPart = numericPart * multiplier;
    return _formatPrice(newNumericPart, currencyPart);
  }

  return {
    parseCurrencyString,
    formatCurrency,
    getCurrencySymbol,
    calculateAndFormatPrice,
    removeCurrencySymbol,
  };
};
