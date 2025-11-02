export const useTeamRepository = () => {
  const { $api } = useNuxtApp();
  const api = useAPI();

  const transformTeam = (team) => ({
    ...team,
    users: [
      ...team.users.map((user) => ({
        ...user,
        type: "user",
      })),
      ...team.members.map((member) => ({
        ...member,
        type: "member",
      })),
    ],
  });

  async function index({ per_page = 99999 } = {}) {
    const response = await $api("/teams", {
      query: {
        per_page,
      },
      transform(data) {
        return data.map(transformTeam);
      },
    });
    return response.data;
  }

  async function create() {
    const response = await $api("/teams", {
      method: "POST",
      body: {
        name,
      },
    });
    return response.data;
  }

  // async function update() {
  //   const response = await $api();
  //   return response.data;
  // }

  async function read(id) {
    const response = await $api(`/teams/${id}`, {
      transform(data) {
        return transformTeam(data);
      },
    });
    return response.data;
  }

  async function destroy(id) {
    const response = await $api(`/teams/${id}`, {
      method: "DELETE",
    });
    return response;
  }

  async function getAllTeams({ perPage = 99999 } = {}) {
    const response = await api.get(`/teams?per_page=${perPage}`);
    return response.data.map(transformTeam);
  }

  async function getTeamById(id) {
    const response = await api.get(`teams/${id}`);
    return transformTeam(response.data);
  }

  async function createTeam(name) {
    const response = await api.post("teams", { name });
    return response.data;
  }

  async function deleteTeam(id) {
    const response = await api.delete(`teams/${id}`);
    return response;
  }

  async function removeUserFromTeam(teamId, userId, type) {
    const typeEndpoint = type === "user" ? "users" : "members";
    const response = await api.delete(`teams/${teamId}/${typeEndpoint}/${userId}`);
    return response;
  }

  async function getTeamCategories(id) {
    const response = await api.get(`teams/${id}/accessibility`);
    return response.data;
  }

  async function removeCategoryFromTeam(teamId, selectedCategories) {
    const response = await api.delete(
      `teams/${teamId}/accessibility/categories`,
      {},
      {
        ids: selectedCategories,
      },
    );
    return response;
  }

  async function addCategoryToTeam(teamId, categoryId) {
    const response = await api.post(`teams/${teamId}/accessibility`, {
      model: "category",
      model_id: categoryId,
    });
    return response;
  }

  async function getTeamMediaSources(id) {
    const response = await api.get(`acl/teams/${id}/media-sources`);
    return response.data;
  }

  async function addTeamMediaSource(teamId, mediaSourceId) {
    const response = await api.post(`acl/teams/${teamId}/media-sources`, {
      media_sources: [mediaSourceId],
    });
    return response;
  }

  async function removeTeamMediaSource(teamId, mediaSourceId) {
    const response = await api.delete(`acl/teams/${teamId}/media-sources/${mediaSourceId}`);
    return response;
  }

  return {
    getAllTeams,
    getTeamById,
    createTeam,
    deleteTeam,
    removeUserFromTeam,
    getTeamCategories,
    removeCategoryFromTeam,
    addCategoryToTeam,
    getTeamMediaSources,
    addTeamMediaSource,
    removeTeamMediaSource,
    // CRUD
    index,
    create,
    read,
    destroy,
  };
};
