export const usePrivateRepository = () => {
  const api = useAPI();
  const addressRepo = useGenericAddressRepository("members");

  const transformPrivateListData = (data, isUser = false) => ({
    id: data.id,
    name: data.profile.first_name + " " + data.profile.last_name,
    ctx: data.ctx,
    email: data.email,
    orders: data.orders ?? 0,
    verified: !!data.email_verified_at,
    teams: data.teams,
    addresses: data.addresses ? data.addresses.map(addressRepo.transformAddressData) : [],
    isUser: isUser,
  });

  const get = async (withUsers = false) => {
    const response = await api.get(`members?include_profile=true&per_page=10000`);
    if (!withUsers) return response.data.map(transformPrivateListData);
    // Fetch users to get the teams
    let responseUsers = await api.get(`users?include_profile=true&per_page=10000`);
    responseUsers = responseUsers.data.filter((item) => item.id != 1);

    const transformedPrivates = response.data.map((item) => transformPrivateListData(item));
    return [
      ...transformedPrivates,
      ...responseUsers.map((item) => transformPrivateListData(item, true)),
    ];
  };

  const getTrashedMembers = async () => {
    const response = await api.get(`members?include_profile=true&per_page=10000&trashed=true`);
    // Fetch Trashed users
    const transformedPrivates = response.data.map((item) => transformPrivateListData(item));
    return [...transformedPrivates];
  };

  const getById = async (id) => {
    const thePrivate = await api.get(`members/${id}?include_profile=true`);
    return thePrivate.data;
  };

  const create = async (privateData) => {
    const sanitized = {
      ...privateData,
      type: "individual",
    };
    const response = await api.post("members", sanitized);
    return response.data;
  };

  const remove = async (id) => {
    return await api.delete(`members/${id}`);
  };

  const update = async (id, privateData) => {
    const sanitized = {
      ...privateData,
      type: "individual",
    };
    const response = await api.put(`members/${id}`, sanitized);
    return response.data;
  };

  const updateProfile = async (id, profileData) => {
    const response = await api.put(`users/${id}/profile`, profileData);
    return response.data;
  };

  const resendVerification = async (id) => {
    return await api.put(`members/${id}/verification`);
  };

  const sendPassword = async (id) => {
    return await api.put(`members/${id}`, {
      type: "individual",
      send_password: true,
    });
  };

  return {
    transformPrivateListData,
    get,
    getById,
    create,
    remove,
    update,
    updateProfile,
    getTrashedMembers,
    resendVerification,
    sendPassword,
    ...addressRepo,
  };
};
