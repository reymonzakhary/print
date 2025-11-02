<template>
  <div class="">
    <h2 class="sticky top-0 w-full p-2 text-sm font-bold uppercase tracking-wide">
      <font-awesome-icon
        :icon="['fal', namespace_icon ? namespace_icon : 'wrench']"
        class="mr-2 text-theme-500"
        fixed-width
      />
      {{ $t(namespace) }} {{ $t("settings") }}
    </h2>

    <!-- {{ settings }} -->

    <div
      class="sticky top-0 z-10 flex w-full flex-wrap items-center justify-between rounded-t bg-theme-400 p-2 text-xs"
    >
      <div class="order-1 flex w-2/3 items-center sm:order-none sm:w-1/3">
        <div class="relative mr-2 flex">
          <input
            v-model="filter"
            class="input py-1"
            type="text"
            placeholder="Search setting..."
            @focus="((searching = true), (namespace_bckp = namespace), set_namespace('search'))"
            @blur="((searching = false), set_namespace(namespace_bckp))"
          />
          <font-awesome-icon
            class="absolute right-0 mr-2 mt-2 text-gray-600"
            :icon="['fal', 'search']"
          />
        </div>
      </div>

      <div v-if="settings.meta && !searching" class="flex w-2/3 justify-end sm:w-1/3">
        <div class="text-xs text-themecontrast-400">
          {{ $t("page") }}
          <select
            v-model="page"
            class="rounded border border-white bg-theme-400 text-sm text-themecontrast-400"
            @change="
              get_settings({
                namespace: namespace,
                area: active,
                page: page,
                per_page: perPage,
                filter: filter,
              })
            "
          >
            <option v-for="i in settings.meta.last_page" :key="i" :value="i">
              {{ i }}
            </option>
          </select>
          of <span class="text-sm">{{ settings.meta.last_page }}</span>
        </div>

        <span class="mx-1 flex items-center pl-2">
          <select
            v-model="perPage"
            class="rounded border border-white bg-theme-400 text-sm text-white"
            @change="
              get_settings({
                namespace: namespace,
                area: active,
                page: page,
                per_page: perPage,
                filter: filter,
              })
            "
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <p class="ml-2 text-xs text-white">{{ $t("per page") }}</p>
        </span>
      </div>

      <div v-else class="flex w-2/3 justify-end sm:w-1/3">
        <p class="font-semibold text-white">search results</p>
      </div>

      <!-- <div class="flex justify-end w-1/3 sm:w-1/6 md:w-1/3">
				<button
					class="flex items-center px-2 py-1 bg-white rounded-full text-theme-500 hover:bg-theme-100"
					@click="createOrder()"
				>
					<font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
					{{ $t("create") }} {{ $t("new") }}
				</button>
			</div> -->
    </div>

    <section
      class="w-full rounded bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
    >
      <SettingsAreaNav
        v-if="!searching"
        :namespace="namespace"
        :settings="settings"
        :page="page"
        :per-page="perPage"
        :filter="filter"
        class="sticky top-10 z-10 pt-2"
        @on-area-click="$emit('handleNamespaceFilter', $event)"
      />

      <template v-for="(setting, i) in newsettings" :key="i">
        <SettingsFormFields v-if="isAppear(setting.key)" :setting="setting" :i="i" />
      </template>
    </section>
  </div>
</template>

<script>
import _ from "lodash";
import { mapState, mapActions, mapMutations } from "vuex";

export default {
  emits: ["handleNamespaceFilter"],
  data() {
    return {
      validate: "",
      filter: "",
      page: 1,
      perPage: 20,
      searching: "",
      namespace_bckp: "",
      area_bckp: "",
      active: "",
      exFields: [
        "quotation_logo_width",
        "quotation_logo_position",
        "quotation_logo_full_document_width",
        "quotation_logo",
        "quotation_letterhead_size",
        "quotation_font_size",
        "quotation_font",
        "quotation_expires_after",
        "quotation_customer_address_position_direction",
        "quotation_customer_address_position",
        "quotation_background",
        "invoice_logo_width",
        "invoice_logo_position",
        "invoice_logo_full_document_width",
        "invoice_logo",
        "invoice_letterhead_size",
        "invoice_font_size",
        "invoice_font",
        "invoice_customer_address_position_direction",
        "invoice_customer_address_position",
        "invoice_background",
      ],
    };
  },
  computed: {
    ...mapState({
      namespace: (state) => state.settings.namespace,
      namespace_icon: (state) => state.settings.namespace_icon,
      settings: (state) => state.settings.settings,
    }),
    newsettings() {
      return _.cloneDeep(this.settings.data);
    },
  },
  watch: {
    filter: _.debounce(function () {
      this.$emit("handleNamespaceFilter", {
        filter: this.filter,
      });
    }, 300),
    settings: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
      immediate: true,
    },
    newsettings: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
    },
  },
  mounted() {
    if (this.settings.meta) {
      this.page = this.settings.meta.current_page;
      this.perPage = this.settings.meta.per_page;
    }
  },
  methods: {
    ...mapMutations({
      set_namespace: "settings/set_namespace",
    }),
    ...mapActions({
      get_settings: "settings/get_settings",
    }),
    check(v, setting) {
      if (setting.value.includes(v)) {
        const index = setting.value.indexOf(v);
        setting.value.splice(index, 1);
      } else {
        setting.value.push(v);
      }

      setting.value = setting.value.join();
      setting.value = setting.value.replace(/,\s*$/, "");
      setting.value = setting.value.replace(/,\s*^/, "");
    },
    isAppear(name) {
      return !this.exFields.includes(name);
    },
  },
};
</script>
