export const useTenantRepository = () => {
  const api = useAPI();

  async function fetchTenantSettings() {
    let response = await api.get(`/tenant/settings`);

    return response;
  }
  async function fetchExternalProducerConfig() {
    let response = await api.get(`/tenant/plugin`);

    return response;
  }
  async function fetchExternalProducerCategories() {
    let response = await api.get(`/tenant/plugin/categories`);
    // Save the response to localStorage for caching or offline access
    if (response && response.data) {
      try {
        localStorage.setItem("externalProducerCategories", JSON.stringify(response.data));
      } catch (error) {
        console.warn("Failed to save categories to localStorage:", error);
      }
    }
    return response;
  }
  async function syncExternalProducerCategories(data) {
    let response = await api.get(`/tenant/plugin/sync`, { method: "POST", body: data });

    return response;
  }
  async function saveExternalProducerConfig() {
    let response = await api.put(`/tenant/plugin`);
    return response;
  }

  async function updateTenantSettings(updatedTenant) {
    if (!updatedTenant) {
      throw new Error("Tenant settings are required");
    }


    try {
      let response = await api.put(`/tenant/settings`, updatedTenant);
      return response;
    } catch (error) {
      console.error("Error updating tenant settings:", error);
      throw error; // Re-throw to allow handling by caller
    }
  }

  async function fetchContracts() {
    const response = await api.get(`tenant/contracts/`);
    return response.data;
  }
  async function fetchSingleContract(id) {
    const response = await api.get(`tenant/contracts/${id}`);
    return response.data;
  }
  async function updateContract(contract) {
    const response = await api.put(`tenant/contracts/${contract.id}`, contract);
    return response;
  }

  return {
    fetchTenantSettings,
    fetchExternalProducerConfig,
    saveExternalProducerConfig,
    fetchExternalProducerCategories,
    syncExternalProducerCategories,
    updateTenantSettings,
    fetchContracts,
    fetchSingleContract,
    updateContract,
  };
};
