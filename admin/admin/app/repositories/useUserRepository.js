export const useUserRepository = () => {
  const { $api } = useNuxtApp();

  function mapDtoToClient(dto) {
    return {
      id: `${dto.id}`,
      gender: dto.profile?.gender || "",
      firstName: dto.profile?.first_name || "",
      lastName: dto.profile?.last_name || "",
      username: dto.username || "",
      email: dto.email || "",
      password: "",
      company: {
        name: dto.company?.name || "",
        description: dto.company?.description || "",
        taxNumber: dto.company?.tax_nr || "",
        chamberOfCommerce: dto.company?.coc || "",
        url: dto.company?.url || "",
        authorization: dto.company?.authorization || "",
      },
      address: {
        country: `${dto.company?.addresses[0]?.country.id}` || "",
        street: dto.company?.addresses[0]?.address || "",
        number: dto.company?.addresses[0]?.number || "",
        zipcode: dto.company?.addresses[0]?.zip_code || "",
        city: dto.company?.addresses[0]?.city || "",
      },
      verified: dto.email_verified_at !== null,
    };
  }

  async function getAllUsers() {
    const { data } = await $api("/users");

    const transformedUsers = data.map(mapDtoToClient);
    return transformedUsers;
  }

  function deleteUser(tenantId) {
    return $api(`/users/${tenantId}`, { method: "DELETE" });
  }

  async function createUser(user) {
    const payload = {
      gender: user.gender,
      first_name: user.firstName,
      last_name: user.lastName,
      email: user.email,
      password: user.password,
      username: user.username,
      company_name: user.company.name,
      description: user.company.description,
      tax_nr: user.company.taxNumber,
      url: user.company.url,
      coc: user.company.chamberOfCommerce,
      authorization: user.company.authorization,
      authToken: "1234",
      authUsername: "",
      authPassword: "",
      address: user.address.street,
      number: user.address.number,
      city: user.address.city,
      zip_code: user.address.zipcode,
      region: "",
      country_id: parseInt(user.address.country, 10),
      type: "other",
    };
    const { data } = await $api("/users", { method: "POST", body: payload });
    return mapDtoToClient(data);
  }

  async function updateUser(userId, user) {
    const payload = {
      gender: user.gender,
      first_name: user.firstName,
      last_name: user.lastName,
      ...(user.email ? { email: user.email } : {}),
    };

    const { data } = await $api(`/users/${userId}`, { method: "PUT", body: payload });
    return mapDtoToClient(data);
  }

  return {
    getAllUsers,
    createUser,
    updateUser,
    deleteUser,
  };
};
