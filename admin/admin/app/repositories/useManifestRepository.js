export const useManifestRepository = () => {
  const { $api } = useNuxtApp();

  // data retrieval methods
  async function getManifest(id) {
    const response = await $api(`/categories/${id}/manifest`, {
      method: "GET",
    });
    if (!("divided" in response.data)) {
      response.data.divided = false;
    }
    response.data.suppliers = await getSuppliersManifests(id);
    return response;
  }

  async function getSuppliersManifests(id) {
    try {
      const response = await $api(`/categories/${id}/suppliers`, {
        method: "GET",
      });
      return response.data.suppliers;
    } catch (error) {
      return [];
    }
  }

  async function getManifestFromProducer(id, producer_id) {
    const response = await $api(`/categories/${id}/suppliers/${producer_id}/manifest/load`, {
      method: "POST",
    });
    if (!("divided" in response.data)) {
      response.data.divided = false;
    }
    return response;
  }

  async function saveManifest(id, category) {
    // remove unused fields from manifest boxes and  options
    category.boops = category.boops.map((item) => {
      const { matched, created_at, suppliers, additional, display_start_cost, ...cleanItem } = item;
      return {
        ...cleanItem,
        ops: item.ops.map((option) => {
          const { matched, created_at, suppliers, additional, display_start_cost, ...rest } =
            option;
          return rest;
        }),
      };
    });

    const response = await $api(`/categories/${id}/manifest`, { method: "POST", body: category });
    return response;
  }

  async function updateManifest(id, category) {
    // remove unused fields from manifest boxes and  options
    category.boops = category.boops.map((item) => {
      const { matched, created_at, suppliers, additional, display_start_cost, ...cleanItem } = item;
      return {
        ...cleanItem,
        ops: item.ops.map((option) => {
          const { matched, created_at, suppliers, additional, display_start_cost, ...rest } =
            option;
          return rest;
        }),
      };
    });

    const response = await $api(`/categories/${id}/manifest`, { method: "PUT", body: category });
    return response;
  }

  return {
    // methods
    getManifest,
    getManifestFromProducer,
    saveManifest,
    updateManifest,
  };
};
