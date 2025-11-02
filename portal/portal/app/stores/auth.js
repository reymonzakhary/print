export const useAuthStore = defineStore("auth", () => {
  const API = useAPI();
  const token = useCookie("prindustry:auth");
  const theUser = ref({});
  const initialized = ref(false);
  const settings = ref({});
  const currencySettings = ref("");

  const checkedPermissions = ref(new Set());
  const permissions = ref(new Set());
  permissions.value.includes = (permission) => {
    checkedPermissions.value.add(permission);
    return permissions.value.has(permission);
  };
  const clearCheckedPermissions = () => (checkedPermissions.value = new Set());

  async function fetchUser() {
    try {
      const response = await API.get("/account/me");
      setUser(response.data);
      setPermissions();
      // Optionally update settings if they come from user data
      // setSettings(response.data.settings);
      // setCurrencySettings(response.data.meta);
      return response.data;
    } catch (error) {
      console.error("Failed to fetch user data:", error);
      // Handle error appropriately, maybe sign out if unauthorized
      token.value = null;
      if (error.response?.status === 401) {
        signOutInvalidToken();
      }
      throw error; // Re-throw error for the caller to handle if needed
    }
  }

  async function signIn({ email, password }) {
    const response = await API.post("/login", { email, password });
    const { access_token } = response.data;
    token.value = access_token;
    return response;
  }

  async function impersonate(impersonateToken) {
    const response = await API.post("/impersonate", {
      token: impersonateToken,
    });
    const { access_token } = response;
    token.value = access_token;
  }

  async function signOut() {
    await API.post("/account/logout");
    token.value = null;
  }

  async function signOutInvalidToken() {
    token.value = null;
  }

  function setUser(user) {
    theUser.value = user;
  }

  function setInitialized(value) {
    initialized.value = value;
  }

  function setPermissions() {
    if (!theUser) throw new Error("Please first run setUser before running setPermissions");
    permissions.value = new Set(theUser.value.permission);
    permissions.value.includes = (permission) => {
      checkedPermissions.value.add(permission);
      return permissions.value.has(permission);
    };
    return theUser.value.permission;
  }

  function setCurrencySettings(meta) {
    currencySettings.value = meta.settings.currency_key;
    return currencySettings;
  }

  function setSettings(coreSettings) {
    settings.value = coreSettings;
    return coreSettings;
  }

  async function sendForgotPassword(email) {
    return await API.post("/password/forget", { email });
  }

  function check() {
    return !!token.value;
  }

  return {
    token,
    signIn,
    signOut,
    signOutInvalidToken,
    setUser,
    theUser,
    sendForgotPassword,
    check,
    initialized,
    setInitialized,
    settings,
    setSettings,
    currencySettings,
    setCurrencySettings,
    // Permissions
    permissions,
    setPermissions,
    checkedPermissions,
    clearCheckedPermissions,
    fetchUser,
    impersonate,
  };
});
