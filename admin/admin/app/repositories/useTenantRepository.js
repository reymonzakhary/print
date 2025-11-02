export const useTenantRepository = () => {
  const { $api } = useNuxtApp();
  const appRepository = useAppRepository();
  const { formatDate } = useHelpers();

  function mapDtoToClient(client, apps) {
    return {
      id: client.id,
      name: client.name ?? "",
      companyName: client.company_name ?? "",
      logo: client.logo ?? "",
      domain: client.fqdn ?? "",
      tenantId: client.tenant_id ?? "",
      supplierId: client.host_id ?? "",
      supplier: { supplier: client.supplier ?? false, external: client.external ?? false },
      modules: apps.map((app) => ({
        name: app.name,
        enabled:
          client.configure?.namespaces?.some((namespace) => namespace.namespace === app.name) ??
          false,
        areas: app.areas.map((area) => area.name),
      })),
      createdAt: formatDate(client.created_at, "yyyy-MM-dd HH:mm"),
      updatedAt: formatDate(client.updated_at, "yyyy-MM-dd HH:mm"),
      owner: {
        name: client.name ?? "",
        email: client.email ?? "",
        gender: client.gender ?? "",
      },
    };
  }

  async function getAllTenants(page = 1, search = "") {
    const response = await $api(`/tenants?page=${page}&search=${search}`, { method: "GET" });
    const { data: apps } = await appRepository.index();

    const transformedClients = response.data.map((client) => mapDtoToClient(client, apps));
    const pagination = response.meta;
    return { data: transformedClients, meta: pagination };
  }

  async function deleteTenant(tenantId) {
    return await $api(`/tenants/${tenantId}`, { method: "DELETE" });
  }

  async function createTenant(tenant) {
    return await $api("/tenants", { method: "POST", body: tenant });
  }

  async function updateTenant(tenantId, data) {
    return await $api(`/tenants/${tenantId}`, { method: "POST", body: data });
  }

  return {
    getAllTenants,
    deleteTenant,
    createTenant,
    updateTenant,
  };
};
