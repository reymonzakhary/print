export const useSettingsRepository = () => {
  const api = useAPI();

  async function getSettings({
    namespace = "",
    area = "",
    sortBy = "name",
    sortDir = "desc",
    page = 1,
    perPage = 25,
    search = "",
  }) {
    if (page < 1 || perPage < 1) {
      throw new Error("Invalid pagination parameters");
    }

    const params = new URLSearchParams({
      namespace,
      area,
      sort_by: sortBy,
      sort_dir: sortDir,
      page: page.toString(),
      per_page: perPage.toString(),
      ...(search && { search }),
    });
    const { data } = await api.get(`/settings?${params}`);
    const settingsMap = new Map(data.map((setting) => [setting.key, setting]));
    return settingsMap;
  }

  async function updateSetting({ namespace, area, key, value }) {
    if (!namespace || !area || !key) {
      throw new Error("Missing required parameters");
    }
    const { data } = await api.put(`/settings/${key}`, { namespace, area, value });
    return data;
  }

  return {
    getSettings,
    updateSetting,
  };
};
