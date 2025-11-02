<template>
  <div class="h-full overflow-y-auto w-fit">
    <section class="h-full mr-1 bg-white rounded shadow-md dark:bg-gray-700">
      <div
        class="flex items-center justify-between px-2 py-1 text-sm font-bold tracking-wide uppercase border-b dark:border-gray-900"
      >
        {{ $t("categories") }}
      </div>

      <div class="relative flex w-full px-2 my-3"></div>

      <div v-if="categories.length > 10" class="relative flex w-full my-3">
        <input
          v-model="filter"
          class="w-full px-2 py-1 mx-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          type="text"
          placeholder="Filter..."
        />
        <font-awesome-icon
          class="absolute right-0 mt-2 mr-4 text-gray-600"
          :icon="['fal', 'filter']"
        />
      </div>

      <nav class="flex">
        <ul v-if="type === 'print' && categories && categories.length > 0">
          <li v-for="(category, i) in categories" :key="i" class="relative">
            <button
              class="flex items-center justify-between w-full px-2 py-1 text-left transition-colors duration-75 group hover:bg-gray-200 dark:hover:bg-gray-900"
              :class="{
                'bg-theme-100 text-theme-500 dark:bg-theme-900':
                  active_category[1] === category.slug || active_custom_category.id === category.id,
              }"
              @click="$emit('selected', category.id)"
            >
              <img
                v-if="suppliers?.includes(category.tenant_id)"
                :src="`/img/suppliers/images/logos/${getSupplier(category.tenant_id)
                  .replace(/\s+/g, '-')
                  .toLowerCase()}.jpg`"
              />

              <div
                v-tooltip="
                  category.display_name && $display_name(category.display_name).length > 30
                    ? $display_name(category.display_name)
                    : ''
                "
                class="flex items-center truncate"
              >
                <Thumbnail
                  v-if="category.media && category.media.length > 0 && category.media[0]"
                  disk="tenancy"
                  :file="{ path: category.media[0] }"
                  class="flex px-1"
                />
                {{ category.display_name ? $display_name(category.display_name) : category.name }}
              </div>

              <div class="flex items-center">
                <font-awesome-icon
                  v-show="
                    (active_category[2] === $display_name(category.display_name) ||
                      active_category[2] === category.name) &&
                    $store.state.product.loading_boops &&
                    !boops
                  "
                  :class="{
                    'fa-spin': $store.state.product.loading_boops,
                  }"
                  class="mr-2 text-theme-500"
                  :icon="['fad', 'spinner-third']"
                />
                <font-awesome-icon
                  v-tooltip="$t('this category is usable in webshop')"
                  class="mr-2 text-theme-500"
                  :class="
                    category.published ? 'text-theme-500' : 'text-gray-300 dark:text-gray-600'
                  "
                  :icon="['fal', 'heart-rate']"
                />
              </div>
            </button>
          </li>
        </ul>
        <ul v-if="type === 'custom' && custom_categories && custom_categories.length > 0">
          <li v-for="(category, i) in custom_categories" :key="i" class="relative">
            <button
              class="flex items-center justify-between w-full px-2 py-1 text-left transition-colors duration-75 group hover:bg-gray-200 dark:hover:bg-gray-900"
              :class="{
                'bg-theme-100 text-theme-500 dark:bg-theme-900':
                  active_category[1] === category.slug || active_custom_category.id === category.id,
              }"
              @click="$emit('selected', category.id)"
            >
              <img
                v-if="suppliers?.includes(category.tenant_id)"
                :src="`/img/suppliers/images/logos/${getSupplier(category.tenant_id)
                  .replace(/\s+/g, '-')
                  .toLowerCase()}.jpg`"
              />

              <div
                v-tooltip="
                  category.display_name && $display_name(category.display_name).length > 30
                    ? $display_name(category.display_name)
                    : ''
                "
                class="flex items-center truncate"
              >
                <Thumbnail
                  v-if="category.media && category.media.length > 0 && category.media[0]"
                  disk="tenancy"
                  :file="{ path: category.media[0] }"
                  class="flex px-1"
                />
                {{ category.display_name ? $display_name(category.display_name) : category.name }}
              </div>

              <div class="flex items-center">
                <font-awesome-icon
                  v-show="
                    (active_category[2] === $display_name(category.display_name) ||
                      active_category[2] === category.name) &&
                    $store.state.product.loading_boops &&
                    !boops
                  "
                  :class="{
                    'fa-spin': $store.state.product.loading_boops,
                  }"
                  class="mr-2 text-theme-500"
                  :icon="['fad', 'spinner-third']"
                />
                <font-awesome-icon
                  v-tooltip="$t('this category is usable in webshop')"
                  class="mr-2 text-theme-500"
                  :class="
                    category.published ? 'text-theme-500' : 'text-gray-300 dark:text-gray-600'
                  "
                  :icon="['fal', 'heart-rate']"
                />
              </div>
            </button>
          </li>
        </ul>

        <div v-else class="w-full p-2 text-center">
          <p class="my-2 italic text-gray-400">{{ $t("shop empty") }}</p>
          <nuxt-link
            :to="'/assortment/add-products'"
            class="px-2 py-1 text-sm font-normal transition-colors duration-75 border rounded-full text-theme-500 border-theme-500 hover:text-theme-700"
          >
            <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
            {{ $t("add your first category") }}
          </nuxt-link>
        </div>
      </nav>
    </section>

    <transition name="fade">
      <component
        :is="component"
        v-if="component !== 'CustomProductCategoryForm'"
        :my-cats="categories"
        :cat="active_category"
      ></component>

      <SidePanel v-else-if="component === 'CustomProductCategoryForm'">
        <template #side-panel-header>
          <h2 class="p-4 font-bold tracking-wide uppercase text-theme-900">
            <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" />
            <span class="text-gray-500">Edit</span>
          </h2>
        </template>

        <template #side-panel-content>
          <component :is="component" :category="active_custom_category" :edit="true"></component>
        </template>
      </SidePanel>
    </transition>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";

export default {
  name: "PrintProductCategories",
  props: {
    type: {
      type: String,
      required: true,
    },
  },
  emits: ["selected"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, permissions };
  },
  data() {
    return {
      catType: "normal",
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit"),
              classes: "",
              show: false,
            },
            {
              action: "view_all",
              icon: "pallet",
              title: this.$t("view products"),
              classes: "",
              show: false,
            },
            {
              action: "export",
              icon: "file-export",
              title: this.$t("export"),
              classes: "",
              show: false,
            },
            {
              action: "import",
              icon: "file-import",
              title: this.$t("import"),
              classes: "",
              show: false,
            },
            {
              action: "delete",
              icon: "trash",
              title: this.$t("delete"),
              classes: "text-red-500",
              show: false,
            },
          ],
        },
      ],
      filter: "",
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.categories,
      custom_categories: (state) => state.product.custom_categories,
      active_category: (state) => state.product.active_category,
      active_custom_category: (state) => state.product.active_custom_category,
      boops: (state) => state.product.boops,
      suppliers: (state) => state.suppliers.suppliers,
      assortment_flag: (state) => state.product.assortment_flag,
      component: (state) => state.product.component,
      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  watch: {
    categories(newVal) {
      return newVal;
    },
    custom_categories(newVal) {
      return newVal;
    },
    assortment_flag(newVal) {
      return newVal;
    },
    component(v) {
      return v;
    },
    filter: _.debounce(function (v) {}, 300),
    active_custom_category(v) {
      return v;
    },
  },
  mounted() {
    // TODO: until we implement print_categories as well
    //  this.set_assortment_flag("custom_product");
    this.get_categories();
    this.get_custom_categories();
    // end

    this.get_suppliers();
  },
  methods: {
    ...mapMutations({
      set_active_category: "product/set_active_category",
      set_active_custom_category: "product/set_active_custom_category",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_assortment_flag: "product/set_assortment_flag",
      set_component: "product/set_component",

      set_selected_category: "product_wizard/set_selected_category",
      set_wizard_type: "product_wizard/set_wizard_type",
      set_wizard_component: "product_wizard/set_wizard_component",

      set_cart_flag: "cart/set_cart_flag",

      set_boops: "product/set_boops",
    }),
    ...mapActions({
      get_suppliers: "suppliers/get_suppliers",

      get_categories: "product/get_categories",
      get_custom_categories: "product/get_custom_categories",
      // get_acl_categories: "authentication/get_acl_categories",
      get_custom_products: "product/get_custom_products",
    }),
    selectCategory(category) {
      if (this.permissions.includes("print-assortments-products-read")) {
        this.set_active_category([category.id, category.slug, category.name]);
        this.set_selected_category(category);
        this.set_loading_boops(true);
      }
    },
    selectCustomCategory(category) {
      if (this.permissions.includes("custom-assortments-products-read")) {
        this.get_custom_products({ cat_id: category.id });
        this.set_active_custom_category(category);
      }
    },
    close() {
      this.set_component("");
    },
  },
};
</script>
