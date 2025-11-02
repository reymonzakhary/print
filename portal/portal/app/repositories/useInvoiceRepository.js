export const useInvoiceRepository = () => {
  const money = useMoney();
  const api = useAPI();
  const { t: $t } = useI18n();

  function mapDtoToInvoice(dto, orderNumber) {
    try {
      return {
        ...dto,
        order_number: orderNumber || dto.order_number,
        custom_field: {
          ...dto.custom_field,
          products: dto.custom_field.products.filter((p) => p.type === "item"),
          services: dto.custom_field.products.filter((p) => p.type === "service"),
          totalShipping: money.formatCurrency(
            dto.custom_field.products.reduce((acc, item) => acc + item.shipping_cost, 0),
          ),
        },
      };
    } catch (error) {
      console.error("Error mapping invoice DTO:", error);
      return dto; // or handle the error as needed
    }
  }

  async function createInvoiceFromOrder(orderId) {
    if (!orderId) throw new Error("Order ID is required");

    const response = await api.post(`orders/${orderId}/transactions`);
    return response.data;
  }

  async function getAllInvoices(orderId, orderNumber) {
    if (!orderId) throw new Error("Order ID is required");

    const response = await api.get(`orders/${orderId}/transactions`);
    return response.data;
  }

  async function getInvoiceById(orderNumber, invoiceNumber) {
    if (!invoiceNumber) throw new Error("Invoice number is required");
    return new Promise((resolve) => {
      setTimeout(() => resolve(), 1000);
    });
  }

  async function saveInvoice(invoice) {
    if (!invoice) throw new Error("Invoice data is required");
    return new Promise((resolve) => {
      setTimeout(() => resolve(invoice), 1000);
    });
  }

  async function getEmailTemplate(invoiceNumber, language = null) {
    const response = await api.get(
      `quotations/${invoiceNumber}/notifications/template${language ? `?language=${language}` : ""}`,
    );
    response.meta.tags = response.meta.tags.map((tag) =>
      tag.includes("quotation") ? tag.replace("quotation", "invoice") : tag,
    );
    return {
      subject: response.data[0]?.value ?? "",
      body: response.data[1]?.value ?? "",
      tags: response.meta.tags,
    };
  }

  async function downloadInvoicePDF(orderNumber, invoiceNumber) {
    if (!invoiceNumber) throw new Error("Invoice number is required");

    const response = await api.get(
        `/orders/${orderNumber}/transactions/${invoiceNumber}/render/pdf`,
        {
          responseType: "arrayBuffer",
        },
    );
    const url = window.URL.createObjectURL(new Blob([response], { type: "application/pdf" }));
    return url;
  }


  async function downloadTransactionInvoicePDF(orderNumber, invoice) {
    if (!invoice) throw new Error("Invoice number is required");

    try {
      const response = await api.get(
          `/orders/${orderNumber}/transactions/${invoice.id}/render/pdf`,
          {
            responseType: "arrayBuffer",
          },
      );

      const blob = new Blob([response], { type: "application/pdf" });
      const url = window.URL.createObjectURL(blob);

      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `invoice-${invoice.invoice_nr}.pdf`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url); // cleanup
    } catch (error) {
      console.error("Failed to download invoice PDF:", error);
    }
  }

  return {
    mapDtoToInvoice,
    createInvoiceFromOrder,
    getAllInvoices,
    getInvoiceById,
    saveInvoice,
    downloadTransactionInvoicePDF,
    getEmailTemplate,
    downloadInvoicePDF,
  };
};
