<template>
  <div>
    <section class="flex flex-wrap">
      <div class="w-full p-4 sm:w-2/6 lg:w-1/6">
        <SettingsNav @handle-namespace-filter="handleNamespaceFilter($event)" />
      </div>

      <!-- {{settings}} -->

      <div class="w-full p-4 sm:w-4/6 lg:w-4/6">
        <transition name="slide">
          <SettingsForm @handle-namespace-filter="handleNamespaceFilter($event)" />
        </transition>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  setup() {
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, permissions };
  },
  head() {
    return {
      title: `${this.$t("settings")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      settings: (state) => state.settings.settings,
      namespace: (state) => state.settings.namespace,
      area_active: (state) => state.settings.area_active,
    }),
  },
  // watch: {
  // 	settings: {
  // 		handler(newVal) {
  // 			console.log(newVal);
  // 			return newVal;
  // 		},
  // 		deep: true,
  // 	},
  // },
  created() {
    const route = useRoute();
    if (route.hash.substring(1)) {
      switch (route.hash.substring(1)) {
        case "invoice":
          this.set_namespace("orders");
          this.set_area_active("invoice");
          break;

        case "order":
          this.set_namespace("orders");
          this.set_area_active("order");
          break;

        case "quotation":
          this.set_namespace("orders");
          this.set_area_active("quotation");
          break;

        case "orders":
          this.set_namespace("orders");
          this.set_area_active("order");
          break;

        case "quotations":
          this.set_namespace("orders");
          this.set_area_active("quotation");
          break;

        case "campaigns":
          this.set_namespace("campaigns");
          this.set_area_active("");
          break;
      }

      this.getSettings({
        namespace: this.namespace,
        area: this.area_active,
        sort_by: "name",
        sort_dir: "desc",
        page: 1,
        per_page: 25,
        filter: "",
      });
    } else {
      this.getSettings({
        namespace: "core",
        area: "",
        sort_by: "name",
        sort_dir: "desc",
        page: 1,
        per_page: 25,
        filter: "",
      });
    }
  },
  methods: {
    ...mapMutations({
      set_component: "settings/set_areas",
      set_namespace: "settings/set_namespace",
      set_area_active: "settings/set_area_active",
      set_settings: "settings/set_settings",
    }),
    ...mapActions({
      // get_settings: "settings/get_settings",
    }),
    async getSettings({ namespace, area, sort_by, sort_dir, per_page, page, filter }) {
      this.api
        .get(
          `settings?namespace=${namespace ? namespace : ""}&area=${area ? area : ""}&sort_by=${sort_by ? sort_by : "name"}&sort_dir=${sort_dir ? sort_dir : "desc"}&page=${page ? page : ""}&per_page=${per_page ? per_page : 9999999}&search=${filter ? filter : ""}`,
        )
        .then((response) => {
          this.set_settings(response);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },

    handleNamespaceFilter(e) {
      this.getSettings({
        namespace: e.namespace,
        area: e.area,
        sort_by: "name",
        sort_dir: "desc",
        page: 1,
        per_page: 25,
        filter: e.filter,
      });
    },
  },
};
</script>
