export const useMarketplaceRepository = () => {
  const api = useAPI();
  const { t: $t } = useI18n();

  function mapProducerDTOToProducers(dto) {
    return {
      ...dto,
      id: dto.id,
      uuid: dto.uuid,
      name: dto.supplier_info.company_name,
      location: `${dto.address?.city ?? ""}, ${dto.address?.country?.name ?? ""}`,
      sharedCategories: dto.shared_categories,
      rating: 4.5,
      reviews: 100,
      producerInfo: dto.supplier_info,
      activeSince: dto.created_at,
      handshake:
        dto.has_handshake && dto.contract?.st?.name === "accepted"
          ? "accepted"
          : dto.has_handshake && dto.contract?.st?.name === "pending"
            ? "pending"
            : dto.has_handshake && dto.contract?.st?.name === "suspended"
              ? "suspended"
              : dto.has_handshake && dto.contract?.st?.name === "rejected"
                ? "rejected"
                : "false",
      contract: dto.contract,
      logo: dto.logo,
    };
  }

  function mapProducerPaginationDTOToProducerPagination(dto) {
    return {
      currentPage: dto.current_page,
      from: dto.from,
      lastPage: dto.last_page,
      path: dto.path,
      perPage: dto.per_page,
      to: dto.to,
      total: dto.total,
    };
  }

  function mapProducerDTOToProducerDetails(dto) {
    return {
      ...dto,
      id: dto.id,
      uuid: dto.uuid,
      name: dto.supplier_info.company_name,
      location: dto.address
        ? `${dto.address?.city ?? ""}, ${dto.address?.country?.name ?? ""}`
        : $t("location not provided"),
      sharedCategories: dto.shared_categories,
      rating: 4.5,
      reviews: 100,
      producerInfo: dto.supplier_info,
      activeSince: dto.created_at,
      handshake:
        dto.has_handshake && dto.contract?.st?.name === "accepted"
          ? "accepted"
          : dto.has_handshake && dto.contract?.st?.name === "pending"
            ? "pending"
            : dto.has_handshake && dto.contract?.st?.name === "suspended"
              ? "suspended"
              : dto.has_handshake && dto.contract?.st?.name === "rejected"
                ? "rejected"
                : "false",
      contract: dto.contract,
      logo: dto.logo,
    };
  }

  async function fetchProducers(page) {
    const response = await api.get(`/suppliers?&page=${page ?? 1}`);
    const Data = response.data.map(mapProducerDTOToProducers);
    const Pagination = mapProducerPaginationDTOToProducerPagination(response.meta);
    return { data: Data, meta: Pagination };
  }

  async function fetchProducerDetails(id) {
    const response = await api.get(`/suppliers/${id}`);
    return mapProducerDTOToProducerDetails(response.data);
  }

  async function fetchSharedCategories(producerId) {
    const response = await api.get(`/suppliers/${producerId}/categories`);
    return response.data;
  }

  /**
   * TODO: awaiting backend implementation
   * Send a producer status request
   * @param {string} tenantId - The tenant id
   * @returns {Promise}
   * @throws {Error} If the request fails
   */
  async function sendProducerStatusRequest(tenantId) {}

  return {
    fetchProducers,
    fetchProducerDetails,
    fetchSharedCategories,
    sendProducerStatusRequest,
  };
};
