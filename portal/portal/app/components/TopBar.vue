<template>
  <header
    id="topbar"
    class="fixed top-0 z-40 w-screen items-center bg-gray-100 py-1 dark:bg-gray-800 md:relative md:flex md:h-full md:bg-transparent md:dark:bg-transparent"
    role="navigation"
    aria-label="main navigation"
  >
    <nav class="flex h-full w-screen items-center justify-between px-2 md:w-auto">
      <div class="flex items-center">
        <nuxt-link class="flex w-auto items-center" :to="'/'">
          <font-awesome-icon
            v-if="tenantLogo && me.supplier"
            :icon="['fas', 'industry-windows']"
            v-tooltip="$t('You are listed as a producer in the prindustry ecosystem!')"
            class="mr-2 mt-1 text-gray-500 dark:text-gray-400"
          />
          <img
            v-if="tenantLogo"
            id="tenant-logo"
            :src="tenantLogo"
            alt="Tenant Logo"
            class="z-0 h-8 max-w-none rounded md:hidden 2xl:flex"
          />

          <font-awesome-icon
            v-if="tenantLogo"
            :icon="['fas', 'wave-pulse']"
            class="mx-2 mt-1 text-gray-500 dark:text-gray-400 md:hidden 2xl:flex"
          />
          <img
            id="prindustry-logo"
            src="~/assets/images/Prindustry_logo.png"
            alt="Prindustry Logo"
            class="h-8 max-w-none"
          />
        </nuxt-link>
        <span class="mt-2 text-sm font-semibold uppercase italic md:hidden lg:flex">
          {{ $t("manager") }}
        </span>
        <small class="ml-2 mt-2 whitespace-nowrap font-mono text-gray-500">
          v{{ $config.public.version }}
        </small>
      </div>

      <button
        role="button"
        class="mr-4 md:hidden"
        aria-label="menu"
        aria-expanded="false"
        data-target="navbarBasicExample"
        :class="{ 'is-active': toggled }"
        @click="toggle"
      >
        <font-awesome-icon :icon="['fal', toggled ? 'close' : 'bars']" class="fa-lg" />
      </button>
    </nav>

    <transition name="slide">
      <nav
        v-show="toggled || large"
        class="absolute block w-screen bg-gray-100 p-2 shadow-lg transition dark:bg-gray-800 md:relative md:flex md:items-center md:justify-between md:bg-transparent md:py-0 md:shadow-none md:dark:bg-transparent"
      >
        <!-- theme switch :class="{ 'hidden': !toggled }" -->
        <p class="mt-2 text-center text-xs font-bold uppercase text-gray-700 md:hidden">
          {{ $t("theme settings") }}
        </p>
        <div id="theme-switcher" class="relative flex items-center justify-center lg:ml-8">
          <font-awesome-icon
            :icon="['fad', 'sun']"
            class="fa-lg"
            :class="{ 'text-yellow-500': !isDarkMode }"
          />

          <div
            class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
            :class="[isDarkMode ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="toggle"
              class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
              :class="[
                isDarkMode ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
              ]"
            />
            <input
              id="toggle"
              v-model="isDarkMode"
              type="checkbox"
              name="toggle"
              class="h-full w-full appearance-none focus:outline-none active:outline-none"
            />
          </div>

          <font-awesome-icon
            :icon="['fad', 'moon-stars']"
            class="fa-lg"
            :class="{ 'text-white': isDarkMode }"
          />

          <div class="ml-4 flex items-center">
            <font-awesome-icon
              v-if="isAutoTheme"
              :icon="['far', 'square-check']"
              class="fa-sm text-theme-500"
              @click="toggleAutoTheme(false)"
            />
            <font-awesome-icon
              v-else
              :icon="['far', 'square']"
              class="fa-sm text-theme-500"
              @click="toggleAutoTheme(true)"
            />

            <p class="ml-1 md:hidden lg:flex">{{ $t("auto") }}</p>

            <!-- tooltip -->
            <font-awesome-icon
              v-tooltip.right="'Changes theme based on OS settings'"
              :icon="['far', 'circle-info']"
              class="fa-sm ml-1"
            />
          </div>
        </div>

        <!-- Action buttons -->
        <p
          class="my-4 border-t pt-4 text-center text-xs font-bold uppercase text-gray-700 md:hidden"
        >
          {{ $t("actions") }}
        </p>

        <div
          v-if="permissions && $store.state.authentication.logged_in"
          class="flex flex-col md:ml-2 md:flex-row md:items-center"
        >
          <div>
            <button
              id="notifications"
              type="button"
              class="relative z-10 flex h-9 w-full items-center rounded-t-md border border-gray-200 bg-white pl-2 pr-4 transition-colors duration-150 ease-linear hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:border-gray-900 dark:bg-gray-700 dark:hover:bg-gray-800 md:rounded-l-full md:border-r-0 md:pr-0 xl:pr-4"
              title="Notifications &amp; Messages"
              :disabled="notifications.length === 0"
              @click.stop="(set_status(false), (showNotifications = !showNotifications))"
            >
              <span
                class="mr-1 flex items-center rounded-full px-2 py-1"
                :class="{
                  'notification-alert': new_notifications,
                  'bg-theme-50 text-theme-500': !new_notifications,
                }"
              >
                <font-awesome-icon
                  :icon="[
                    notifications.length > 0 ? 'fas' : 'fal',
                    new_notifications ? 'bell-on' : 'bell',
                  ]"
                  class="fa-md mr-2"
                />
                <!-- <span v-if="new_notifications" class="absolute flex h-4">
                  <span
                    class="loading absolute h-4 w-11 rounded-full bg-theme-100 dark:bg-theme-200"
                  />
                </span> -->
                <small v-if="notifications.length > 0" class="marker text-xs">
                  {{ notifications.length }}
                </small>
                <small v-else class="marker text-xs text-theme-500"> 0 </small>
              </span>
              <span class="inline-block text-sm sm:hidden xl:inline-block">
                {{ $t("notifications") }}
              </span>
            </button>

            <div
              v-if="showNotifications"
              class="firefox:bg-opacity-100 absolute left-0 top-36 z-50 w-full overflow-y-auto overflow-x-hidden rounded border bg-white/60 p-2 shadow-lg backdrop-blur-md dark:divide-black dark:border-black dark:bg-black/60 md:left-auto md:top-12 md:-ml-32 md:w-96"
              style="max-height: calc(100vh - 4rem)"
            >
              <button
                v-if="notifications.length > 0"
                class="flex h-full w-full items-center justify-center rounded-full bg-white text-theme-400 shadow-md hover:bg-theme-100 dark:bg-gray-700"
                @click.stop="delete_all_notifications()"
              >
                <font-awesome-icon :icon="['fal', 'check']" class="mr-2" />
                {{ $t("mark all as read") }}
              </button>
              <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="`bg-gradient-to-r shadow-sm from-${notification.color}-100 dark:from-${notification.color}-800 border-2 border-white to-white dark:to-gray-700 text-${notification.color}-800 dark:text-${notification.color}-200 dark:from-${notification.color}-900 my-2`"
                class="flex w-full justify-between overflow-x-hidden rounded p-4"
              >
                <section class="flex w-11/12 flex-col">
                  <h2 class="font-bold">
                    {{ notification.title ? $t(notification.title) : $t("general message") }}
                  </h2>
                  <p class="truncate" :title="notification.text">{{ notification.text }}</p>
                  <NuxtLink
                    v-if="notification.link"
                    :to="notification.link"
                    :class="`text-${notification.color}-500 -mr-8 ml-auto mt-4 rounded-full border px-4 py-1 text-sm transition hover:bg-${notification.color}-500 hover:text-white border-${notification.color}-500 dark:border-${notification.color}-800 dark:text-${notification.color}-200`"
                  >
                    {{ $t("open") }}
                    <font-awesome-icon :icon="['fal', 'arrow-right']" class="ml-2" />
                  </NuxtLink>
                </section>
                <section class="flex w-1/12 justify-end">
                  <button
                    :class="`hover:bg-${notification.color}-200 dark:hover:text-${notification.color}-800 -mr-2 -mt-2 h-8 w-8 flex-shrink-0 rounded-full`"
                    @click.stop="delete_notification(notification)"
                  >
                    <font-awesome-icon :icon="['fal', 'xmark']" />
                  </button>
                </section>
              </div>
              <div
                class="border-green-500 from-green-100 text-green-800 hover:bg-green-200 dark:from-green-800 dark:text-green-200 dark:hover:bg-green-500"
              />
              <div
                class="border-amber-500 from-amber-100 text-amber-800 hover:bg-amber-200 dark:from-amber-800 dark:text-amber-200 dark:hover:bg-amber-500"
              />
              <div
                class="border-orange-500 from-orange-100 text-orange-800 hover:bg-orange-200 dark:from-orange-800 dark:text-orange-200 dark:hover:bg-orange-500"
              />
              <div
                class="border-red-500 from-red-100 text-red-800 hover:bg-red-200 dark:from-red-800 dark:text-red-200 dark:hover:bg-red-500"
              />
              <div
                class="border-blue-500 from-blue-100 text-blue-800 hover:bg-blue-200 dark:from-blue-800 dark:text-blue-200 dark:hover:bg-blue-500"
              />
            </div>
          </div>

          <button
            id="messages"
            type="button"
            class="relative z-10 flex h-9 w-full items-center border border-gray-200 bg-white pl-2 pr-4 transition-colors duration-150 ease-linear hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:border-gray-900 dark:bg-gray-700 dark:hover:bg-gray-800 md:rounded-none md:pr-0 xl:pr-2"
            title="Notifications &amp; Messages"
            @click.stop="navigateTo('/messages')"
          >
            <span class="mr-1 flex items-center rounded-full bg-theme-50 px-2 py-1">
              <font-awesome-icon :icon="['fal', 'messages']" class="fa-md mr-2 text-theme-500" />
              <small v-if="messages?.length > 0" class="marker text-xs text-theme-500">
                {{ messages.length }}
              </small>
              <small v-else class="marker text-xs text-theme-500"> 0 </small>
            </span>
            <span class="inline-block text-sm sm:hidden xl:inline-block">
              {{ $t("messages") }}
            </span>
          </button>

          <!-- DOCUMENTATION BUTTON -->
          <button
            id="documentation"
            type="button"
            class="relative z-10 flex h-9 w-full items-center border border-l-0 border-gray-200 bg-white px-3 transition-colors duration-150 ease-linear hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:border-gray-900 dark:bg-gray-700 dark:hover:bg-gray-800 md:rounded-none xl:px-4"
            title="Documentation"
            @click.stop="
              navigateTo('https://support.prindustry.com/hc/en-gb', {
                external: true,
                open: {
                  target: 'newwindow',
                  windowFeatures: {
                    noopener: true,
                    noreferrer: true,
                  },
                },
              })
            "
          >
            <font-awesome-icon
              :icon="['fal', 'book-spells']"
              class="mr-0 text-theme-500 dark:text-theme-300 xl:mr-2"
            />
            <span class="inline-block text-sm sm:hidden xl:inline-block">
              {{ $t("documentation") }}
            </span>
          </button>

          <!-- MANAGE MENU -->
          <ItemMenu
            id="manage-menu"
            :menu-title="$t('manage')"
            :menu-items="managerItems"
            button-icon="wrench"
            menu-icon="caret-down"
            menu-class="relative z-0 flex items-center w-full px-4 text-sm transition-colors duration-150 ease-linear bg-white border md:rounded-r-full h-9 md:border-l-0 border-gray-200 hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:bg-gray-700 dark:hover:bg-gray-800 dark:border-gray-900"
            dropdown-class="w-full md:w-56"
            :menu-title-class="{
              'font-black text-theme-500': isRouteActive('manager'),
            }"
            @item-clicked="menuItemClicked($event)"
          />

          <!-- <button
					type="button"
					class="flex items-center px-4 py-2 transition-colors duration-150 ease-linear bg-white border border-l-0 border-gray-200 hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:bg-gray-700 dark:hover:bg-gray-900 dark:border-gray-900"
					id="show_support_widget"
					title="Support menu"
				>
					<font-awesome-icon
						:icon="['fal', 'book-spells']"
						class="mr-2 text-theme-500"
					/>
					<div class="inline-block text-sm sm:hidden lg:inline-block">
						{{ $t("documentation") }}
					</div>
          </button>-->

          <!-- <a
					href="mailto:support@prindustry.nl?subject="
					target="_BLANK"
					class="flex items-center px-4 py-2 transition-colors duration-150 ease-linear bg-white border border-l-0 md:rounded-r-full border-gray-200 hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:bg-gray-700 dark:hover:bg-gray-900 dark:border-gray-900"
					title="New ticket"
				>
					<font-awesome-icon
						:icon="['fal', 'ticket']"
						class="mr-2 text-theme-500"
					/>
					<div class="inline-block text-sm sm:hidden lg:inline-block">
						{{ $t("Support") }}
					</div>
          </a>-->
        </div>
        <div v-else class="flex flex-col md:flex-row md:items-center">
          <TopBarMenuPlaceholder />
        </div>

        <!-- <div id="timetracker">
        </div>-->
        <!-- Language -->
        <section class="flex flex-col items-center justify-center md:flex-row md:items-center">
          <div class="relative flex w-full flex-col md:w-auto">
            <button
              id="language-switcher"
              class="relative flex h-9 cursor-pointer flex-col rounded-b-md border border-gray-200 bg-white px-4 py-2 transition-colors duration-150 ease-linear hover:bg-gray-100 focus:bg-gray-300 focus:outline-none dark:border-gray-900 dark:bg-gray-700 dark:hover:bg-gray-800 md:mx-1 md:w-auto md:items-center md:rounded-full md:border-l-0 lg:mr-8"
              @click.prevent.stop="showLanguageDropdown = !showLanguageDropdown"
            >
              <div class="flex w-full items-center">
                <font-awesome-icon
                  :icon="['fas', 'language']"
                  class="text-theme-400 md:hidden lg:flex"
                />
                <span class="mx-2 text-sm uppercase">
                  {{ $i18n.locale }}
                </span>
                <font-awesome-icon :icon="['fal', 'caret-down']" class="ml-auto" />
              </div>
            </button>

            <ul
              v-if="showLanguageDropdown"
              class="absolute mt-9 w-full min-w-32 divide-y rounded bg-white/80 text-left shadow-lg backdrop-blur-md group-hover:visible dark:bg-gray-900/80"
            >
              <li v-for="locale in availableLocales" :key="locale.code">
                <a
                  href="#"
                  class="block p-2 hover:text-theme-500"
                  @click.prevent.stop="
                    api
                      .put('/settings/manager_language', {
                        area: 'language',
                        multi_select: false,
                        namespace: 'core',
                        value: `${locale}`.toUpperCase(),
                      })
                      .then(() => $i18n.setLocale(locale), (showLanguageDropdown = false))
                      .catch((error) => handleError(error))
                  "
                >
                  {{ locale }}
                </a>
              </li>
            </ul>
          </div>

          <p
            v-if="me && me.profile"
            class="my-4 border-b border-gray-400 pt-4 text-center text-xs font-bold uppercase text-gray-700 md:m-0 md:pr-4 md:pt-0"
          >
            {{ me.profile.first_name }}
            {{ me.profile.middle_name }}
            {{ me.profile.last_name }}
          </p>

          <figure
            v-if="me && me.profile"
            class="mb-4 flex h-24 w-24 flex-shrink-0 items-center justify-center overflow-hidden rounded-full border bg-gray-300 md:mb-0 md:h-10 md:w-10"
            :class="{
              'bg-theme-400': !me.profile?.avatar,
              'bg-gray-300': me.profile?.avatar,
            }"
          >
            <img v-if="me.profile?.avatar" class="h-full" :src="me.profile?.avatar" />

            <!-- <font-awesome-icon
              v-else
              :icon="['fad', randomUserIcon]"
              class="m-auto flex text-2xl text-gray-500"
            /> -->
            <div v-else class="font-bold text-themecontrast-400">{{ getInitials }}</div>
          </figure>

          <!-- Account menu -->
          <ItemMenu
            id="account-settings"
            :menu-items="accountItems"
            menu-icon="caret-down"
            menu-class="relative items-center justify-center hidden w-8 h-8 md:w-auto lg:w-8 rounded-full md:flex hover:bg-gray-200 dark:hover:bg-gray-800"
            dropdown-class="right-0 w-48"
            @item-clicked="menuItemClicked($event)"
          />
        </section>
      </nav>
    </transition>
  </header>
</template>

<script>
import { mapState, mapMutations, mapGetters, useStore } from "vuex";

export default {
  name: "TopBar",
  setup() {
    // imports
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const showNotifications = ref(false);
    const showManagerSettings = ref(false);
    const store = useStore();
    const themeStore = useThemeStore();
    const { handleError } = useMessageHandler();
    const { fetchMessages } = useMessagesRepository();

    // theme related computed properties
    const isDarkMode = computed({
      get: () => themeStore.activeTheme === "dark",
      set: (val) => themeStore.setActiveTheme(val ? "dark" : "light"),
    });

    const isAutoTheme = computed({
      get: () => themeStore.autoTheme,
      set: (val) => themeStore.setAutoTheme(val),
    });

    // theme related methods
    function toggleAutoTheme(value) {
      isAutoTheme.value = value;
      if (value) {
        themeStore.detectColorScheme();
      }
    }

    // Watch for system color scheme changes
    onMounted(() => {
      if (import.meta.client) {
        // Initialize theme from stored settings
        themeStore.initTheme();

        // Set up event listeners for system theme preference changes
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
          if (themeStore.autoTheme && e.matches) {
            themeStore.setActiveTheme("dark");
          }
        });

        window.matchMedia("(prefers-color-scheme: light)").addEventListener("change", (e) => {
          if (themeStore.autoTheme && e.matches) {
            themeStore.setActiveTheme("light");
          }
        });

        // Initial detection
        if (themeStore.autoTheme) {
          themeStore.detectColorScheme();
        }
      }
    });

    // data
    const randomUserIcon = useState("randomProfileIcon", () => `user-xmark`);
    const tenantLogo = ref("");
    const messages = ref([]);

    // lifecycle
    onMounted(() => {
      fetchTenantLogo();
      fetchMessages().then((response) => {
        const groupedMessages = response.reduce((acc, message) => {
          if (message.from === "sender") {
            const parentId = message.parent_id || "root";
            if (!acc[parentId]) {
              acc[parentId] = [];
            }
            acc[parentId].push(message);
          }
          return acc;
        }, {});
        messages.value = Object.values(groupedMessages).flat();
      });
    });

    onMounted(async () => {
      window.addEventListener("click", handleClickOutside);
      // setLanguage(settings);
    });

    onBeforeUnmount(() => {
      window.removeEventListener("click", handleClickOutside);
    });

    // methods
    const fetchTenantLogo = async () => {
      try {
        const info = await api.get("/info");
        tenantLogo.value = info.logo;
      } catch (error) {
        console.error("Error fetching info:", error);
      }
    };

    function handleClickOutside() {
      if (showNotifications.value) {
        showNotifications.value = false;
      }

      if (showManagerSettings.value) {
        showManagerSettings.value = false;
      }
    }

    // return
    return {
      showNotifications,
      showManagerSettings,
      permissions,
      store,
      themeStore,
      api,
      handleError,
      randomUserIcon,
      tenantLogo,
      messages,
      fetchTenantLogo,
      fetchMessages,
      handleClickOutside,
      isDarkMode,
      isAutoTheme,
      toggleAutoTheme,
    };
  },

  // old code TODO: refactor to setup
  data() {
    return {
      showLanguageDropdown: false,
      permissionsFlag: true,
      toggled: false,
      large: true,
    };
  },
  computed: {
    ...mapState({
      me: (state) => state.settings.me,
      meta: (state) => state.settings.meta,
      info: (state) => state.settings.info,
      notifications: (state) => state.notification.notifications,
      new_notifications: (state) => state.notification.new_notifications,
    }),
    ...mapGetters({
      automation: "settings/automation",
    }),
    // Permissions
    maySeeAppSettings() {
      return (
        this.permissions.includes("settings-access") && this.permissions.includes("settings-list")
      );
    },
    maySeeTenantSettings() {
      return this.permissions.includes("company-config-access");
    },
    // computed
    getInitials() {
      if (!this.me?.profile) return "";
      const { first_name, last_name } = this.me.profile;
      return `${first_name?.[0] || ""}${last_name?.[0] || ""}`.toUpperCase();
    },

    availableLocales() {
      return this.$i18n.locales.value
        .filter((i) => i.code !== this.$i18n.locale)
        .map((i) => i.code);
    },

    accountItems() {
      return [
        {
          heading:
            this.me && this.me.profile
              ? `${this.me.profile.first_name} ${this.me.profile.middle_name ?? ""} ${this.me.profile.last_name}`
              : "user",
          items: [
            {
              action: "theme",
              icon: "palette",
              title: this.$t("Theme Settings"),
              classes: "text-sm",
              show: true,
            },
            {
              action: "logout",
              icon: "power-off",
              title: this.$t("Log-out"),
              classes: "text-red-600 text-sm",
              show: true,
            },
          ],
        },
      ];
    },
    managerItems() {
      return [
        {
          heading: {
            title: this.$t("Manager"),
            show: this.maySeeAppSettings || this.maySeeTenantSettings,
          },
          items: [
            {
              action: "managersettings",
              icon: "screwdriver-wrench",
              title: this.$t("app settings"),
              classes: "text-sm",
              show: this.maySeeAppSettings,
            },
            {
              action: "tenantsettings",
              icon: "crown",
              title: this.$t("Tenant settings"),
              classes: "text-sm",
              show: this.maySeeTenantSettings,
            },
            {
              action: "studio",
              icon: "hammer-brush",
              title: this.$t("Studio"),
              classes: "text-sm",
              show: true,
            },
          ],
        },
        {
          heading: {
            title: this.$t("Users"),
            show:
              (this.permissions.includes("users-access") &&
                this.permissions.includes("users-list")) ||
              this.permissions.includes("teams-access") ||
              this.permissions.includes("acl-access") ||
              this.permissions.includes("media-sources-access"),
          },
          items: [
            {
              action: "users",
              icon: "users",
              title: this.$t("Users"),
              classes: "text-sm",
              show:
                this.permissions.includes("users-access") &&
                this.permissions.includes("users-list"),
            },
            {
              action: "teams",
              icon: "user-group-crown",
              title: this.$t("Teams"),
              classes: "text-sm",
              show: this.permissions.includes("teams-access"),
            },
            {
              action: "acl",
              icon: "key",
              title: this.$t("Roles and permissions"),
              classes: "text-sm",
              show: this.permissions.includes("acl-access"),
            },
            {
              action: "mediasources",
              icon: "photo-film-music",
              title: "Media sources",
              classes: "text-sm",
              show: this.permissions.includes("media-sources-access"),
            },
          ],
        },
        {
          heading: {
            title: this.$t("Assortment"),
            show:
              this.permissions.includes("print-assortments-machines-access") ||
              this.permissions.includes("print-assortments-printing-methods-access") ||
              this.permissions.includes("print-assortments-boxes-access") ||
              this.permissions.includes("print-assortments-options-access"),
          },
          items: [
            {
              action: "assortment",
              icon: "box-full",
              title: this.$t("Assortment settings"),
              classes: "text-sm",
              show:
                this.permissions.includes("print-assortments-machines-access") ||
                this.permissions.includes("print-assortments-printing-methods-access") ||
                this.permissions.includes("print-assortments-boxes-access") ||
                this.permissions.includes("print-assortments-options-access"),
            },
          ],
        },
        {
          heading: {
            title: this.$t("CMS"),
            show: this.permissions.includes("cms-access"),
          },
          items: [
            {
              action: "templates",
              icon: "brush",
              title: this.$t("Page Templates"),
              classes: "text-sm",
              show: this.permissions.includes("cms-access"),
            },
            {
              action: "resourcegroups",
              icon: "window-restore",
              title: this.$t("Page groups"),
              classes: "text-sm",
              show: this.permissions.includes("cms-access"),
            },
          ],
        },
        {
          heading: {
            title: this.$t("Design tools"),
            show:
              this.permissions.includes("design-providers-templates-access") ||
              this.permissions.includes("design-providers-templates-access") ||
              this.permissions.includes("blueprints-automation-access"),
          },
          items: [
            {
              action: "designproviders",
              icon: "pen-paintbrush",
              title: this.$t("Design Tools"),
              classes: "text-sm",
              show: this.permissions.includes("design-providers-templates-access"),
            },
            {
              action: "blueprints",
              icon: "shoe-prints",
              title: this.$t("Blueprints"),
              classes: "text-sm",
              show: this.permissions.includes("blueprints-automation-access"),
            },
          ],
        },
      ];
    },
  },
  watch: {
    notifications: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
    },
    permissions(v) {
      return v;
    },
  },
  mounted() {
    if (import.meta.client) {
      window.addEventListener("resize", this.toggleNavbar);
      this.toggleNavbar();

      if (window.localStorage.getItem("notifications")) {
        this.notifications_from_localstorage(
          JSON.parse(window.localStorage.getItem("notifications")),
        );
      }
    }
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.toggleNavbar);
  },
  methods: {
    toggleNavbar() {
      if (window.innerWidth < 768) {
        this.large = false;
      } else {
        this.large = true;
      }
    },
    hideNotifications() {
      this.showNotifications = false;
    },
    menuItemClicked(event) {
      this.toggled = false;
      switch (event) {
        case "managersettings":
          navigateTo("/manage/settings");
          break;
        case "acl":
          navigateTo("/manage/roles-permissions");
          break;

        case "users":
          navigateTo("/manage/users");
          break;

        case "teams":
          navigateTo("/manage/teams");
          break;

        case "usersettings":
          navigateTo("/usersettings");
          break;

        case "theme":
          navigateTo("/manage/studio?section=theme");
          break;

        case "templates":
          navigateTo("/manage/cms/templates");
          break;

        case "resourcegroups":
          navigateTo("/manage/cms/page-groups");
          break;

        case "designproviders":
          navigateTo("/manage/design");
          break;

        case "assortment":
          navigateTo("/manage/assortment-settings");
          break;

        case "blueprints":
          navigateTo("/manage/blueprints");
          break;

        case "tenantsettings":
          navigateTo("/manage/tenant-settings/tenant-information");
          break;

        case "mediasources":
          navigateTo("/manage/media-sources");
          break;

        case "logout":
          navigateTo("/auth/logout");
          break;

        case "studio":
          navigateTo("/manage/studio");
          break;

        default:
          break;
      }
    },
    ...mapMutations({
      set_notification: "notification/set_notification",
      set_status: "notification/set_status",
      notifications_from_localstorage: "notification/notifications_from_localstorage",
      delete_notification: "notification/delete_notification",
      delete_all_notifications: "notification/delete_all_notifications",
    }),
    isRouteActive(url) {
      const pathSegments = this.$route.path.split("/").filter((segment) => segment);
      const lastSegment = pathSegments[pathSegments.length - 1];
      const firstSegment = pathSegments[0];
      if (lastSegment === url || firstSegment === url) {
        return true;
      } else {
        return false;
      }
    },
    showSettings() {
      if (this.permissions.value) {
        // manager items
        if (this.permissions.value.includes("settings-access")) {
          this.managerItems[0].items[0].show = true;
        }
        if (this.permissions.value.includes("users-access")) {
          this.managerItems[0].items[1].show = true;
        }
        if (this.permissions.value.includes("teams-access")) {
          this.managerItems[0].items[2].show = true;
        }
        if (this.permissions.value.includes("acl-access")) {
          this.managerItems[0].items[3].show = true;
        }
        if (this.permissions.value.includes("cms-access")) {
          this.managerItems[0].items[4].show = true;
          this.managerItems[0].items[5].show = true;
        }
        if (this.permissions.value.includes("design-providers-templates-access")) {
          this.managerItems[0].items[6].show = true;
        }
        if (
          this.permissions.value.includes("print-assortments-machines-access") ||
          this.permissions.value.includes("print-assortments-printing-methods-access") ||
          this.permissions.value.includes("print-assortments-boxes-access") ||
          this.permissions.value.includes("print-assortments-options-access")
        ) {
          this.managerItems[0].items[8].show = true;
        }
        if (this.permissions.value.includes("blueprints-automation-access")) {
          this.managerItems[0].items[9].show = true;
        }
        if (this.permissions.value.includes("company-config-access")) {
          this.managerItems[0].items[10].show = true;
        }
        if (this.permissions.value.includes("media-sources-access")) {
          this.managerItems[0].items[11].show = true;
        }
        if (this.permissions.value.includes("media-sources-access")) {
          this.managerItems[0].items[11].show = true;
        }

        // account items
        if (this.permissions.value.includes("account-settings-access")) {
          this.accountItems[0].items[0].show = true;
          this.accountItems[0].items[1].show = true;
        }
      }
    },
    toggle() {
      this.toggled = !this.toggled;
    },
  },
};
</script>
