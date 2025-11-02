import { useStore } from "vuex";

export default defineNuxtPlugin({
  name: "init",
  parallel: true,
  async setup(nuxtApp) {
    const store = useStore();
    const api = useAPI();
    const authStore = useAuthStore();
    const { $i18n: i18n } = nuxtApp;
    const token = useRoute().params.token;
    const { impersonate } = useAuthStore();

    nuxtApp.hook("app:mounted", async () => {
      if (useRoute().fullPath.startsWith("/impersonate/")) {
        const path = useRoute().query.path;
        const disk = useRoute().query.disk;
        await impersonate(token);
        if (path && disk) {
          store.commit("fm/content/setDisk", disk, {
            root: true,
          });
          useRouter().push(`/filemanager?path=${path}&disk=${disk}`);
        } else {
          useRouter().push(`/`);
        }
      }
      const { data, error } = await useLazyAsyncData("initData", async () => {
        if (authStore.check()) {
          const [userData, coreSettingsData, settingsData, tenantInfo, userSettingsData] =
            await Promise.all([
              api.get("/account/me"),
              api.get("/settings"),
              store.dispatch("settings/get_settings", { namespace: "core" }),
              store.dispatch("settings/get_info"),
              store.dispatch("usersettings/get_settings", {
                namespace: "core",
              }),
            ]);
          return { userData, coreSettingsData, settingsData, tenantInfo, userSettingsData };
        }
        throw new Error("User is not authenticated");
      });

      if (error.value) {
        console.error("Failed to fetch initialization data:", error.value);
        authStore.setInitialized(true);
      }

      if (data.value) {
        const { userData, coreSettingsData, settingsData, tenantInfo, userSettingsData } =
          data.value;

        authStore.setUser(userData.data);
        authStore.setCurrencySettings(userData.meta);
        authStore.setPermissions();

        // Legacy Authentication
        store.commit("authentication/set_logged_in", true);

        // Legacy Settings
        store.commit("settings/set_settings", coreSettingsData);
        store.commit("settings/set_info", tenantInfo);
        authStore.setSettings(coreSettingsData);

        // Legacy User Settings
        store.commit("usersettings/set_settings", userSettingsData.data);

        // Legacy Settings
        store.commit("settings/set_me", userData.data);
        store.commit("settings/add_tenant_id", userData.tenant_id);
        store.commit("settings/set_meta", userData.meta);

        setLocale(settingsData);

        authStore.setInitialized(true);
      }
    });

    function setLocale(settings) {
      const languageSetting = settings.find((setting) => setting.key === "manager_language");
      const language = languageSetting.value ? languageSetting.value : "nl";
      i18n.setLocale(language.toLowerCase());
    }
  },
});
