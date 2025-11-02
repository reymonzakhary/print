export const useFinanceRepository = () => {
  const api = useAPI();
  const invoiceRepository = useInvoiceRepository();

  async function getAllInvoices({ perPage = 1, page = 1 } = {}, searchQuery = "") {
    const response = await api.get(
      `/transactions?per_page=${perPage}&page=${page}&search=${searchQuery}`,
    );
    const transformedInvoices = response.data.map((dto) =>
      invoiceRepository.mapDtoToInvoice(dto, null),
    );
    return [transformedInvoices, response.meta];
  }

  return {
    getAllInvoices,
  };
};
