<template>
  <div class="h-full overflow-hidden">
    <VTour ref="dashboardTour" name="dashboardTour" :steps="steps" highlight />
    <section class="container relative h-full">
      <div
        class="absolute inset-x-0 -left-[20%] top-[25%] z-0 -translate-y-1/2 rotate-[0.45rad] transform-gpu overflow-hidden px-36 opacity-75 blur-3xl"
        aria-hidden="true"
      >
        <div
          class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-theme-300 to-pink-500 opacity-50"
          style="
            clip-path: polygon(
              74.1% 44.1%,
              100% 61.6%,
              97.5% 26.9%,
              85.5% 0.1%,
              80.7% 2%,
              72.5% 32.5%,
              60.2% 62.4%,
              52.4% 68.1%,
              47.5% 58.3%,
              45.2% 34.5%,
              27.5% 76.7%,
              0.1% 64.9%,
              17.9% 100%,
              27.6% 76.8%,
              76.1% 97.7%,
              74.1% 44.1%
            );
          "
        />
      </div>
      <header class="relative z-10 p-4">
        <h1
          v-if="store.state.settings.me && store.state.settings.me.profile"
          class="flex items-center text-xl font-bold uppercase tracking-wide"
        >
          {{ $t("welcome") }}

          {{ store.state.settings.me.profile.first_name }}
          {{ store.state.settings.me.profile.middle_name }}
          {{ store.state.settings.me.profile.last_name }}

          <button
            id="tour-button"
            class="ml-4 rounded-full border border-theme-500 px-2 text-sm text-theme-500"
            @click="startTour"
          >
            <font-awesome-icon :icon="['fal', 'route']" />
            {{ $t("start tour") }}
          </button>
        </h1>
        <h2 class="text-sm italic text-gray-600">
          {{ $t("This what we have for you today") }}
        </h2>
      </header>

      <div
        v-if="permissions"
        class="relative z-10 mb-12 mt-4 grid grid-cols-12 items-stretch gap-4 p-4"
      >
        <!-- me widget -->
        <section
          v-if="me && me.profile"
          id="dashboard-profile"
          class="col-span-12 rounded-md sm:col-span-6 lg:col-span-3"
        >
          <UserProfile :profile="me.profile" :has-view-permission="true" />
          <div
            class="mt-2 rounded-md bg-white text-center shadow-md shadow-gray-300/50 dark:bg-gray-700 dark:shadow-gray-900/50"
          >
            <nuxt-link
              v-if="permissions.includes('account-settings-access')"
              to="/my-account"
              class="mx-auto my-4 inline-block rounded-full bg-theme-100 px-2 py-1 text-center text-sm text-theme-500"
            >
              <font-awesome-icon :icon="['fal', 'gear']" />
              {{ $t("Account settings") }}
            </nuxt-link>
          </div>
        </section>

        <!-- orders -->
        <section
          v-if="permissions.includes('orders-access') && permissions.includes('orders-list')"
          id="dashboard-orders"
          class="col-span-12 flex flex-col items-center justify-center rounded-md bg-white px-4 py-2 shadow-md shadow-gray-300/50 dark:bg-gray-700 dark:shadow-gray-900 sm:col-span-6 lg:col-span-3"
        >
          <OrderStatistics class="h-full w-full" :type="'orders'" />

          <nuxt-link
            to="/orders"
            class="transition:color mx-auto mt-3 rounded-full bg-theme-400 px-4 py-1 text-sm text-themecontrast-400 duration-150 hover:bg-theme-500 hover:text-themecontrast-600"
          >
            <font-awesome-icon :icon="['fal', 'file-invoice-dollar']" />
            {{ capitalizeFirstLetter($t("to orders")) }}
          </nuxt-link>
        </section>

        <!-- quotations -->
        <!-- v-if="meta.modules.namespaces.find((ns) => ns.area === 'quotation')" -->
        <section
          v-if="hasPermissionGroup(newPermissions.quotations.groups.moduleAccess)"
          id="dashboard-quotations"
          class="col-span-12 flex flex-col items-center justify-between rounded-md bg-white px-4 py-2 shadow-md shadow-gray-300/50 dark:bg-gray-700 dark:shadow-gray-900 sm:col-span-6 lg:col-span-3"
        >
          <!-- <font-awesome-icon :icon="['fad', 'file-signature']" class="mb-12 text-theme-400 fa-5x" /> -->
          <OrderStatistics class="h-full w-full" :type="'quotations'" />

          <nuxt-link
            to="/quotations"
            class="transition:color mx-auto mt-3 rounded-full bg-theme-400 px-4 py-1 text-sm text-themecontrast-400 duration-150 hover:bg-theme-500 hover:text-themecontrast-600"
          >
            <font-awesome-icon :icon="['fal', 'file-signature']" class="mr-1" />
            {{ capitalizeFirstLetter($t("to quotations")) }}
          </nuxt-link>
        </section>

        <!-- <section
          v-if="permissions.includes('campaigns-access')"
          class="flex flex-col col-span-12 justify-between items-center p-4 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 sm:col-span-6 lg:col-span-3"
        >
          <CampaignsStatistics></CampaignsStatistics>
        </section> -->
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations, useStore } from "vuex";

export default {
  name: "Dashboard",
  provide: {
    endpoint: "users",
  },
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const store = useStore();
    const { permissions, theUser: me } = storeToRefs(useAuthStore());
    const { permissions: newPermissions, hasPermissionGroup } = usePermissions();
    return { store, permissions, me, capitalizeFirstLetter, newPermissions, hasPermissionGroup };
  },
  data() {
    return {
      steps: [
        {
          target: "#tour-button",
          title: this.capitalizeFirstLetter(this.$t("welcome to the Prindustry Manager!")),
          body: this.capitalizeFirstLetter(
            this.$t("This tour will give you a quick look around the application"),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#theme-switcher",
          title: this.capitalizeFirstLetter(this.$t("We support darkmode!")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("Find yourself working after hours? Or just a darkmode fan in general? We got you!"),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#notifications",
          title: this.capitalizeFirstLetter(this.$t("System notifications")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is the place where the system notifies you about background processes and action updates. Think things like new orders, product generations, new messages, etc."),
          ),
          popperConfig: {
            placement: "bottom",
          },
        },
        {
          target: "#messages",
          title: this.capitalizeFirstLetter(this.$t("Messages")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("Here you will find all the messages you receive from other users in the system."),
          ),
          popperConfig: {
            placement: "bottom",
          },
        },
        {
          target: "#documentation",
          title: this.capitalizeFirstLetter(this.$t("Documentation")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("Need more information? Visit our helpcenter for in depth documentation."),
          ),
          popperConfig: {
            placement: "bottom",
          },
        },
        {
          target: "#manage-menu",
          title: this.capitalizeFirstLetter(this.$t("Manage menu")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is the place where you can find all the tools to manage your account and the system. Managable parts of all the modules can also be found here"),
          ),
          popperConfig: {
            placement: "bottom",
          },
        },
        {
          target: "#language-switcher",
          title: this.capitalizeFirstLetter(this.$t("Choose your language")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("We support multiple languages and we will add more!"),
          ),
          popperConfig: {
            placement: "left",
          },
        },
        {
          target: "#account-settings",
          title: this.capitalizeFirstLetter(this.$t("Account settings")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("Change your personal app color or logout of the system."),
          ),
          popperConfig: {
            placement: "left",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/']",
          title: this.capitalizeFirstLetter(this.$t("welcome to your dashboard!")),
          body: this.capitalizeFirstLetter(
            this.$t("This is where you will find your acount info and settings"),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/marketplace/product-finder']",
          title: this.capitalizeFirstLetter(this.$t("the Marktetplace")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is where you can find all the products and producers available in the Prindustry ecosystem."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/quotations']",
          title: this.capitalizeFirstLetter(this.$t("your quotations")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("this is where you can find all your quotations, create new ones and manage existing quotations."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/orders']",
          title: this.$t("your orders"),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("this is where you can find all your orders, create new ones and manage existing orders."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/finances']",
          title: this.capitalizeFirstLetter(this.$t("your invoices")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("this is where you can find all your invoices and change the layout or look and feel."),
          ),
          popperConfig: {
            placement: "right",
          },
        },

        {
          target: "#sidebarmenu [href='/manager/customers']",
          title: this.capitalizeFirstLetter(this.$t("customers")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("this is where you can find all your customers, create new ones and manage existing customers."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#sidebarmenu [href='/manager/assortment']",
          title: this.capitalizeFirstLetter(this.$t("assortment")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("this is where you can find and can manage all the products in your assortment"),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        // {
        //   target: "#sidebarmenu [href='/manager/cms']",
        //   title: this.capitalizeFirstLetter(this.$t("cms")),
        //   body: this.capitalizeFirstLetter(
        //     this.$t("Here you can manage your webshop or brandportal"),
        //   ),
        //   popperConfig: {
        //     placement: "right",
        //   },
        // },
        {
          target: "#sidebarmenu [href='/manager/filemanager']",
          title: this.capitalizeFirstLetter(this.$t("Media manager")),
          body: this.capitalizeFirstLetter(
            this.$t("this is where you manage all the files used in the system."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "#dashboard-profile",
          title: this.capitalizeFirstLetter(this.$t("profile")),
          body: this.capitalizeFirstLetter(this.$t("a quick glance at your profile")),
        },
        {
          target: "#dashboard-orders",
          title: this.capitalizeFirstLetter(this.$t("orders")),
          body: this.$t("a quick glance at your orders"),
        },
        {
          target: "#dashboard-quotations",
          title: this.capitalizeFirstLetter(this.$t("quotations")),
          body: this.capitalizeFirstLetter(this.$t("a quick glance at your quotations")),
        },
      ],
    };
  },
  head() {
    return {
      title: `${this.$t("dashboard")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      meta: (state) => state.settings.meta,
    }),
  },
  mounted() {
    this.select_user(this.store.state.settings.me);
  },
  methods: {
    ...mapMutations({
      select_user: "users/select_user",
    }),
    startTour() {
      this.$refs.dashboardTour.resetTour();
      this.$refs.dashboardTour.startTour();
    },
  },
};
</script>
