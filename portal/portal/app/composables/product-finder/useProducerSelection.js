export const useProducerSelection = () => {
  const expandedProducerIds = ref(new Set());
  const selectedProducerOption = ref(null);

  /**
   * Process producers data from a variant to create a flat list of price options
   * @param {Object} variant - The product variant with producers
   * @returns {Array} Flat array of price options with producer data
   */
  const createPriceOptionsList = (variant) => {
    if (!variant || !variant.producers) return [];

    return variant.producers
      .flatMap(
        (producer) =>
          producer.prices?.map((priceOpt) => ({
            id:
              priceOpt.id ||
              `${producer.external_id}-${priceOpt.dlv?.actual_days}-${priceOpt.selling_price_ex}`,
            producerId: producer.external_id,
            producerName: producer.name,
            producerLogo: producer.logo,
            price: priceOpt.display_selling_price_ex,
            priceNumeric: parseFloat(
              String(priceOpt.selling_price_ex)
                .replace(/[^0-9.,]/g, "")
                .replace(",", "."),
            ),
            pricePerUnit: priceOpt.display_ppp || null,
            deliveryTime: parseInt(priceOpt.dlv?.actual_days, 10),
            deliveryDay: priceOpt.dlv?.day,
            deliveryDayName: priceOpt.dlv?.day_name,
            deliveryMonth: priceOpt.dlv?.month,
            originalPriceData: priceOpt,
            originalProducerData: { ...(({ prices, ...rest }) => rest)(producer) },
          })) || [],
      )
      .filter((opt) => opt);
  };

  /**
   * Find the cheapest price option from the list
   */
  const findCheapestOption = (priceOptions) => {
    const validOptions = priceOptions.filter((opt) => opt && !isNaN(opt.priceNumeric));
    if (!validOptions.length) return null;
    return [...validOptions].sort((a, b) => a.priceNumeric - b.priceNumeric)[0];
  };

  /**
   * Find the quickest delivery option from the list
   */
  const findQuickestOption = (priceOptions) => {
    const validOptions = priceOptions.filter((opt) => opt && !isNaN(opt.deliveryTime));
    if (!validOptions.length) return null;
    return [...validOptions].sort((a, b) => {
      const deliveryDiff = a.deliveryTime - b.deliveryTime;
      if (deliveryDiff !== 0) return deliveryDiff;
      // If delivery times are equal, sort by price
      const priceA = !isNaN(a.priceNumeric) ? a.priceNumeric : Infinity;
      const priceB = !isNaN(b.priceNumeric) ? b.priceNumeric : Infinity;
      return priceA - priceB;
    })[0];
  };

  /**
   * Toggle the expansion state of a producer accordion
   */
  const toggleProducerAccordion = (producerId) => {
    const newSet = new Set(expandedProducerIds.value);
    if (newSet.has(producerId)) {
      newSet.delete(producerId);
    } else {
      newSet.add(producerId);
    }
    expandedProducerIds.value = newSet;
  };

  /**
   * Filter price options for a specific producer
   */
  const getPriceOptionsForProducer = (allOptions, producerId) => {
    return allOptions.filter((opt) => opt.producerId === producerId);
  };

  /**
   * Check if a producer offers the cheapest option
   */
  const isCheapestProducer = (allOptions, producerId) => {
    const cheapestOption = findCheapestOption(allOptions);
    return cheapestOption && cheapestOption.producerId === producerId;
  };

  /**
   * Check if a producer offers the quickest option
   */
  const isQuickestProducer = (allOptions, producerId) => {
    const quickestOption = findQuickestOption(allOptions);
    return quickestOption && quickestOption.producerId === producerId;
  };

  return {
    expandedProducerIds,
    selectedProducerOption,
    createPriceOptionsList,
    findCheapestOption,
    findQuickestOption,
    toggleProducerAccordion,
    getPriceOptionsForProducer,
    isCheapestProducer,
    isQuickestProducer,
  };
};
