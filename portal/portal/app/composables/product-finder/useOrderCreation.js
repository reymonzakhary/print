export const useOrderCreation = (
  orderRepository,
  quotationRepository,
  messageHandler,
  toastStore,
) => {
  const { t: $t } = useI18n();
  const isSubmitting = ref(false);
  const orderTypeInProgress = ref(null);

  /**
   * Create order or quotation from basket items
   * @param {String} type - 'orders' or 'quotations'
   * @param {Array} basketItems - Items in the basket
   * @param {String|null} activeOrder - Active order ID if any
   * @param {String|null} activeQuotation - Active quotation ID if any
   */
  const createOrderWithType = async (type, basketItems, activeOrder, activeQuotation) => {
    if (!basketItems.length) return;

    isSubmitting.value = true;
    orderTypeInProgress.value = type;

    try {
      const repository = type === "orders" ? orderRepository : quotationRepository;
      const activeExistingId = type === "orders" ? activeOrder : activeQuotation;

      let targetId;
      if (activeExistingId) {
        targetId = activeExistingId;
      } else {
        const newData = await repository.create();
        targetId = newData.id;
      }

      for (const item of basketItems) {
        if (
          !item.completeData?.option?.originalPriceData ||
          !item.completeData?.option?.originalProducerData ||
          !item.completeData?.option?.price ||
          !item.producerId
        ) {
          console.error(`Item "${item.productName}" missing critical data`);
          messageHandler.handleError(
            new Error(`Item "${item.productName}" is missing data for ${type}.`),
          );
          continue;
        }

        const calculationWithPrice = {
          ...item.completeData.option.originalProducerData,
          tenant_id: item.producerId,
          price: item.completeData.option.originalPriceData || item.completeData.option.price,
        };

        // Handle quantity
        if (item.completeData.variant?.quantity) {
          calculationWithPrice.quantity = item.completeData.variant.quantity;
        } else if (item.quantity) {
          const qtyMatch = String(item.quantity).match(/(\d+)/);
          if (qtyMatch) calculationWithPrice.quantity = parseInt(qtyMatch[1], 10);
        }

        if (type === "orders") {
          await orderRepository.addItemToOrder(targetId, calculationWithPrice);
        } else {
          await quotationRepository.addItemToQuotation(targetId, calculationWithPrice);
        }
      }

      toastStore.addToast({
        type: "success",
        message:
          type === "quotations"
            ? $t("All items added to quotation")
            : $t("All items added to order"),
      });

      return targetId;
    } catch (error) {
      console.error(`Error creating ${type}:`, error);
      messageHandler.handleError(error);
      return null;
    } finally {
      isSubmitting.value = false;
      orderTypeInProgress.value = null;
    }
  };

  return {
    isSubmitting,
    orderTypeInProgress,
    createOrderWithType,
  };
};
