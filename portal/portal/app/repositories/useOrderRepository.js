const editStatus = {
  name: "editing",
  code: 324,
  description: "",
};

export const useOrderRepository = () => {
  const api = useAPI();
  const { t: $t } = useI18n();
  const { isSameStatus } = useOrderStatus();

  const addressRepo = useGenericAddressRepository("contexts");

  const transformOrderData = (data) => ({
    ...data,
    status: data.editing
      ? {
          ...editStatus,
          is(code) {
            return isSameStatus(this.code, code);
          },
        }
      : {
          ...data.status,
          is(code) {
            return isSameStatus(this.code, code);
          },
        },
    delivery_address: data.delivery_address
      ? addressRepo.transformAddressData(data.delivery_address)
      : data.pickup_address
        ? addressRepo.transformAddressData(data.pickup_address)
        : null,
    invoice_address: data.invoice_address
      ? addressRepo.transformAddressData(data.invoice_address)
      : null,
    items: !data.items
      ? []
      : data.items.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)),
  });

  async function getAllOrders({
    includeItems = true,
    perPage = 1000,
    sortBy = "created_at",
    sortOrder = "desc",
    search = "",
    page = 1,
    status = false,
    archived = false,
  } = {}) {
    try {
      const response = await api.get(
        `orders?include_items=${includeItems}&per_page=${perPage}&sort_by=${sortBy}&sort_order=${sortOrder}&search=${search}&page=${page}${status ? `&status=${status}` : ""}${archived ? `&archived=${archived}` : ""}`,
      );
      const transformed = response.data.map(transformOrderData);
      return [transformed, response.meta];
    } catch (err) {
      if (err instanceof Error) {
        throw err;
      } else {
        throw new Error($t("An error occurred while fetching orders. Please try again later."));
      }
    }
  }

  async function getOrderById(id, options = {}) {
    const { signal } = options;
    const { data, meta } = await api.get(`orders/${id}`, { signal });

    const sanitized = transformOrderData(data);
    return [sanitized, meta];
  }

  async function createOrder() {
    const response = await api.post("orders", { ctx_id: 1 });
    return response.data;
  }

  async function updateOrder(id, data) {
    const response = await api.put(`orders/${id}`, data);
    return response.data;
  }

  async function addItemToOrder(orderId, data) {
    const response = await api.post(`orders/${orderId}/items`, data);
    return response.data;
  }

  async function updateItem({ orderId, itemId, data }) {
    const response = await api.put(`orders/${orderId}/items/${itemId}`, data);
    return response.data;
  }

  async function removeItemFromOrder(orderId, itemId) {
    const response = await api.delete(`orders/${orderId}/items/${itemId}`);
    return response.data;
  }

  async function addAddressToItem({ orderId, itemId, data }) {
    const response = await api.put(`orders/${orderId}/items/${itemId}/addresses`, data);
    return response.data;
  }

  async function getItemPrice({ type, divided, category, product, quantity, deliveryDays }) {
    const response = await api.post(
      `http://prindustry.test/api/v1/mgr/shops/categories/${category}/products${deliveryDays ? `?dlv=${deliveryDays}` : ""}`,
      {
        type,
        product,
        quantity,
        divided,
      },
    );
    return response.data;
  }

  async function getAllServices() {
    const response = await api.get("services?per_page=99999");
    return response.data;
  }

  async function removeServiceFromOrder(orderId, serviceId) {
    const response = await api.delete(`orders/${orderId}/services/${serviceId}`);
    return response.data;
  }

  async function createService(id, data) {
    if (!data.name || !data.qty) {
      throw new Error("Missing required service properties: name, qty");
    }
    const response = await api.post(`/orders/${id}/services`, data);
    return response.data;
  }

  async function updateService({ orderId, serviceId, data }) {
    const response = await api.put(`orders/${orderId}/services/${serviceId}`, data);
    return response.data;
  }

  async function getPickupAddresses(ctxId) {
    const response = await addressRepo.getAddresses(ctxId);
    return response.map(addressRepo.transformAddressData);
  }

  async function getHistory({ id, page, perPage }) {
    const response = await api.get(`orders/${id}/history?page=${page}&per_page=${perPage}`);
    return [response.data, response.meta];
  }

  async function emailOrder(orderId) {
    const response = await api.post(`orders/${orderId}/notifications/mails`);
    return response;
  }

  async function setArchived(orderId, archived) {
    const response = await api.put(`orders/${orderId}`, { archived });
    return response.data;
  }

  async function getOrderMedia(orderId) {
    const response = await api.get(`orders/${orderId}/media`);
    return response;
  }

  async function uploadOrderMedia(orderId, file) {
    const response = await api.post(`orders/${orderId}/media`, file, { isFormData: true });
    return response.result.fileManager[0];
  }

  async function deleteOrderMedia(orderId, mediaId) {
    const response = await api.delete(`orders/${orderId}/media/${mediaId}`);
    return response.data;
  }

  return {
    getAllOrders,
    getOrderById,
    createOrder,
    create: createOrder,
    updateOrder,
    addItemToOrder,
    addItem: addItemToOrder,
    removeItemFromOrder,
    updateItem,
    addAddressToItem,
    getItemPrice,
    getAllServices,
    removeServiceFromOrder,
    createService,
    updateService,
    getPickupAddresses,
    getHistory,
    emailOrder,
    setArchived,
    getOrderMedia,
    uploadOrderMedia,
    deleteOrderMedia,
  };
};
