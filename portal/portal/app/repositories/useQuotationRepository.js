const editStatus = { name: "editing", code: 324, description: "" };

export const useQuotationRepository = () => {
  const api = useAPI();
  const { t: $t, locale } = useI18n();
  const { isSameStatus } = useOrderStatus();

  const addressRepo = useGenericAddressRepository("contexts");

  const transformQuotationData = (data) => ({
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
      : data.items
          .sort((a, b) => new Date(a.created_at) - new Date(b.created_at)),
  });

  async function getAllQuotations({
    includeItems = true,
    perPage = 1000,
    sortBy = "created_at",
    sortOrder = "desc",
    search = "",
    page = 1,
    status = false,
    trashed = false,
  } = {}) {
    let url;
    if (trashed) {
      url = `quotations/trashed?include_items=${includeItems}&per_page=${perPage}&sort_by=${sortBy}&sort_order=asc&page=${page}`;
    } else {
      url = `quotations?include_items=${includeItems}&per_page=${perPage}&sort_by=${sortBy}&sort_order=${sortOrder}&search=${search}&page=${page}${status ? `&status=${status}` : ""}`;
    }
    try {
      const response = await api.get(url);
      const transformed = response.data.map(transformQuotationData);

      const withTrashedProp = transformed.map((quotation) => {
        return { ...quotation, trashed };
      });
      return [withTrashedProp, response.meta];
    } catch (err) {
      if (err instanceof Error) {
        throw err;
      } else {
        throw new Error($t("An error occurred while fetching quotations. Please try again later."));
      }
    }
  }

  async function getQuotationById(id, trashed = false) {
    const { data, meta } = await api.get(`quotations/${trashed ? "trashed/" : ""}${id}`);
    const sanitized = transformQuotationData(data);
    return [sanitized, meta];
  }

  async function createQuotation() {
    const response = await api.post("quotations", { ctx_id: 1 });
    const id = response.data.id;
    updateQuotation(id, { payment_method: "invoice" });
    return response.data;
  }

  async function updateQuotation(id, data) {
    const response = await api.put(`quotations/${id}`, data);
    return response.data;
  }

  async function deleteQuotation(id) {
    const response = await api.delete(`quotations/${id}`);
    return response.data;
  }

  async function addItemToQuotation(quotationId, data) {
    const response = await api.post(`quotations/${quotationId}/items`, data);
    return response.data;
  }

  async function updateItem({ quotationId, itemId, data }) {
    const response = await api.put(`quotations/${quotationId}/items/${itemId}`, data);
    return response.data;
  }

  async function removeItemFromQuotation(quotationId, itemId) {
    const response = await api.delete(`quotations/${quotationId}/items/${itemId}`);
    return response.data;
  }

  async function addAddressToItem({ quotationId, itemId, data }) {
    const response = await api.put(`quotations/${quotationId}/items/${itemId}/addresses`, data);
    return response.data;
  }

  async function getItemPrice({ type, divided, category, product, quantity, deliveryDays }) {
    const response = await api.post(
      `http://prindustry.test/api/v1/mgr/shops/categories/${category}/products`,
      { type, product, quantity, divided, dlv: deliveryDays },
    );
    return response.data;
  }

  async function getAllServices() {
    const response = await api.get("services?per_page=99999");
    return response.data;
  }

  async function removeServiceFromQuotation(quotationId, serviceId) {
    const response = await api.delete(`quotations/${quotationId}/services/${serviceId}`);
    return response.data;
  }

  async function createService(id, data) {
    if (!data.name || !data.qty) {
      throw new Error("Missing required service properties: name, qty");
    }
    const response = await api.post(`/quotations/${id}/services`, data);
    return response.data;
  }

  async function updateService({ quotationId, serviceId, data }) {
    const response = await api.put(`quotations/${quotationId}/services/${serviceId}`, data);
    return response.data;
  }

  async function getPickupAddresses(context) {
    const response = await addressRepo.getAddresses(context);
    return response.map(addressRepo.transformAddressData);
  }

  async function getHistory({ id, page, perPage }) {
    const response = await api.get(`quotations/${id}/history?page=${page}&per_page=${perPage}`);
    return [response.data, response.meta];
  }

  async function getEmailTemplate(quotationId, language = null) {
    const response = await api.get(
      `quotations/${quotationId}/notifications/template${language ? `?language=${language}` : ""}`,
    );
    return {
      subject: response.data.find((item) => item.template.endsWith("subject"))?.value ?? "",
      body: response.data.find((item) => item.template.endsWith("body"))?.value ?? "",
      tags: response.meta?.tags ?? [],
    };
  }

  async function beautifyTag(tag) {
    tag = tag.replace(/\[\[%*|]]/g, "").replace(/\./g, " ");
    return tag;
  }

  async function emailQuotation(quotationId, data) {
    const response = await api.post(`quotations/${quotationId}/notifications/mails`, data);
    return response;
  }

  async function getQuotationMedia(quotationId) {
    const response = await api.get(`quotations/${quotationId}/media`);
    return response;
  }

  async function uploadQuotationMedia(quotationId, file) {
    const response = await api.post(`quotations/${quotationId}/media`, file, { isFormData: true });
    return response.result.fileManager[0];
  }

  async function deleteQuotationMedia(quotationId, mediaId) {
    const response = await api.delete(`quotations/${quotationId}/media/${mediaId}`);
    return response.data;
  }

  return {
    getAllQuotations,
    getQuotationById,
    createQuotation,
    create: createQuotation,
    updateQuotation,
    deleteQuotation,
    addItemToQuotation,
    addItem: addItemToQuotation,
    removeItemFromQuotation,
    updateItem,
    addAddressToItem,
    getItemPrice,
    getAllServices,
    removeServiceFromQuotation,
    createService,
    updateService,
    getPickupAddresses,
    getHistory,
    getEmailTemplate,
    beautifyTag,
    emailQuotation,
    getQuotationMedia,
    uploadQuotationMedia,
    deleteQuotationMedia,
  };
};
