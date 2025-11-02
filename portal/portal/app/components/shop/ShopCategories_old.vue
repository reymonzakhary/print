<template>
  <div class="container w-full">
    <section class="w-full mb-1">
      <div
        class="flex items-center justify-between px-2 py-1 text-sm font-bold tracking-wide uppercase border-b dark:border-gray-900"
      >
        <nuxt-link
          v-if="permissions.includes('cart-access') && cart_flag === 'add'"
          :to="/assortment/add-categories"
          class="text-sm font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
          @click="set_cart_flag('view'), $router.push('/cart')"
        >
          <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
          {{ $t("back") }}
        </nuxt-link>

        {{ $t("categories") }}
      </div>

      <div
        v-if="
          permissions.includes('print-assortments-products-access') &&
          permissions.includes('custom-assortments-products-access')
        "
        class="relative flex w-full px-2 my-3"
      >
        <button
          class="w-1/2 px-2 py-1 text-xs border border-r-0 rounded-l"
          :class="{
            'bg-theme-400 text-white border-theme-600':
              assortment_flag === 'print_product',
          }"
          @click="
            get_categories().then(() => {
              set_assortment_flag('print_product');
            })
          "
        >
          print product
        </button>
        <button
          class="w-1/2 px-2 py-1 text-xs border border-l-0 rounded-r"
          :class="{
            'bg-theme-400 text-white border-theme-600':
              assortment_flag === 'custom_product',
          }"
          @click="
            get_custom_categories().then(() => {
              set_assortment_flag('custom_product');
            })
          "
        >
          custom product
        </button>
      </div>

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

      <nav>
        <div
          v-if="categories.length > 0"
          class="flex items-center justify-center w-full"
        >
          <template v-for="(category, i) in categories">
            <button
              :key="i"
              class="flex flex-col items-center justify-between w-1/6 p-2 m-2 text-left transition-colors duration-75 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 hover:bg-gray-200 dark:hover:bg-gray-900 dark:bg-gray-700"
              :class="{
                'bg-theme-100 text-theme-500 dark:bg-theme-900':
                  active_category[1] === category.slug,
              }"
              @click="
                assortment_flag === 'print_product'
                  ? selectCategory(category)
                  : selectCustomCategory(
                      category.id,
                      category.slug,
                      category.name,
                    ),
                  $parent.scrollToEnd()
              "
            >
              <div
                v-tooltip="
                  category.display_name &&
                  $display_name(category.display_name).length > 30
                    ? $display_name(category.display_name)
                    : ''
                "
                class="h-24"
              >
                <ShopImage disk="tenancy" :file="{ path: category.media[0] }" />
              </div>

              <h2 class="pt-2 truncate">
                {{
                  category.display_name
                    ? $display_name(category.display_name)
                    : category.name
                }}
              </h2>

              <div class="flex items-center">
                <font-awesome-icon
                  v-show="
                    (active_category[2] ===
                      $display_name(category.display_name) ||
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
              </div>
            </button>
          </template>
        </div>

        <div v-else class="w-full p-2 text-center">
          <p class="my-2 italic text-gray-400">
            {{ $t("shop is empty") }}
          </p>
        </div>
      </nav>
    </section>

    <transition name="fade">
      <component
        :is="component"
        :my-cats="categories"
        :cat="active_category"
      ></component>
    </transition>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  name: "PrintProductCategories",
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
      component: "",
      filter: "",
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.categories,
      active_category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      suppliers: (state) => state.suppliers.suppliers,
      assortment_flag: (state) => state.product.assortment_flag,
      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  watch: {
    categories(newVal) {
      return newVal;
    },
    assortment_flag(newVal) {
      return newVal;
    },
    component(v) {
      return v;
    },
    filter: _.debounce(function (v) {}, 300),
  },
  created() {
    this.get_suppliers();

    if (this.suppliers && this.suppliers.length > 0) {
      this.menuItems[0].items[4].show = false;
    }

    if (this.permissions.includes("print-assortments-categories-update")) {
      this.menuItems[0].items[3].show = true;
    }
    if (
      this.permissions.includes("print-assortments-categories-update") &&
      this.permissions.includes("print-assortments-boxes-list") &&
      this.permissions.includes("print-assortments-options-list") &&
      this.permissions.includes("margins-list")
    ) {
      this.menuItems[0].items[0].show = true;
    }
    if (this.permissions.includes("print-assortments-products-list")) {
      this.menuItems[0].items[1].show = true;
    }
    if (
      this.permissions.includes("print-assortments-products-list") &&
      this.permissions.includes("print-assortments-boxes-list") &&
      this.permissions.includes("print-assortments-options-list")
    ) {
      this.menuItems[0].items[2].show = true;
    }
    if (this.permissions.includes("print-assortments-categories-delete")) {
      this.menuItems[0].items[4].show = true;
    }
  },
  methods: {
    ...mapMutations({
      set_active_category: "product/set_active_category",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_assortment_flag: "product/set_assortment_flag",

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
      get_custom_products: "product/get_custom_products",
    }),
    selectCategory(category) {
      if (this.permissions.includes("print-assortments-products-read")) {
        this.set_active_category([category.id, category.slug, category.name]);
        this.set_selected_category(category);
        this.set_loading_boops(true);
      }
    },
    selectCustomCategory(category, slug, name) {
      if (this.permissions.includes("custom-assortments-products-read")) {
        this.set_active_category([category, slug, name]);
        this.get_custom_products(slug);
      }
    },
    activateModal() {
      this.set_wizard_type("");
      this.set_wizard_component("AddProductOverview");
    },
    activateDetails(category, id) {
      // this.$store.commit('product/set_details_level', 'producer')
      this.set_active_collection("");
      this.set_active_category([category, id]);
      // this.$store.commit('product/activate_details', true)
      this.$router.push("/assortment/details");
    },

    /**
     * This processes the menu clicks
     * @param {String} event menu-item clicked
     * @return {Function}  respective funtion gets executed
     * @todo create functions for the current console.log's
     */
    menuItemClicked(event, category, category_id) {
      switch (event) {
        case "edit":
          this.activateDetails(category, category_id);
          break;

        case "view_all":
          this.$router.push("/assortment/category-products");
          break;

        case "export":
          this.$axios
            .post(`categories/${this.active_category[1]}/assortment/export`, {
              type: "xlsx",
            })
            .then((response) => this.handleSuccess(response));
          break;

        case "import":
          this.component = "PrintProductImport";
          break;

        case "delete":
          this.component = "CategoryRemoveItem";
          break;

        default:
          break;
      }
    },
  },
};
</script>
