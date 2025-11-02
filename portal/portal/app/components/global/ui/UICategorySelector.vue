<template>
  <div class="h-full overflow-y-auto w-full max-h-[14rem]">
    <!-- {{ value }} -->
    <section class="mr-1 bg-white rounded shadow-md dark:bg-gray-700">
      <div
        class="flex items-center justify-between px-2 text-sm font-bold tracking-wide uppercase  dark:border-gray-900"
      >
        <nuxt-link
          v-if="permissions.includes('cart-access') && cart_flag === 'add'"
          to="assortment/add-categories"
          class="text-sm font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
          @click="set_cart_flag('view'), $router.push('/cart')"
        >
          <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
          {{ $t("back") }}
        </nuxt-link>

        <!-- {{ $t("categories") }} -->

        <!-- <div class="flex space-x-4">
          <nuxt-link
            v-if="
              permissions.includes('print-assortments-margins-access') ||
              permissions.includes('print-assortments-boxes-access') ||
              permissions.includes('print-assortments-options-access') ||
              permissions.includes('print-assortments-machines-access') ||
              permissions.includes(
                'print-assortments-printing-methods-access',
              ) ||
              permissions.includes('print-assortments-catalogues-access') ||
              permissions.includes('print-assortments-system-catalogues-access')
            "
            to="/manage/assortment-settings"
            class="text-sm font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
            @click="activateModal()"
          >
            <font-awesome-icon :icon="['fal', 'gear']" class="" />
            {{ $t("settings") }}
          </nuxt-link>
          <nuxt-link
            v-if="
              (permissions.includes('print-assortments-categories-create') &&
                cart_flag === 'view') ||
              permissions.includes('custom-assortments-categories-create')
            "
            :to="'/assortment/add-products'"
            class="text-sm font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
            @click="activateModal()"
          >
            <font-awesome-icon :icon="['fal', 'plus']" class="" />
            {{ $t("new") }}
          </nuxt-link>
        </div> -->
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
            get_categories({
              per_page: 20,
              page: pagination && pagination.page ? pagination.page : 1,
            }).then(() => {
              set_assortment_flag('print_product');
            })
          "
        >
          {{ $t("print product") }}
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
          {{ $t("custom product") }}
        </button>
      </div>

      <!-- <div v-if="categories.length > 3" class="relative flex w-full my-3">
        <input
          v-model="filter"
          class="w-full px-2 py-1 mx-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          type="text"
          placeholder="Filter"
        />
        <font-awesome-icon
          class="absolute right-0 mt-2 mr-4 text-gray-600"
          :icon="['fal', 'filter']"
        />
      </div> -->

      <nav>
        <ul
          v-if="
            (categories && categories.length > 0) ||
            (custom_categories && custom_categories.length > 0)
          "
        >
          <li
            v-for="(category, i) in assortment_flag === 'print_product'
              ? categories
              : custom_categories"
            :key="i"
            class="relative"
          >
            <button
              class="flex items-center justify-between w-full px-2 py-1 text-left transition-colors duration-75 group hover:bg-gray-200 dark:hover:bg-gray-900"
              :class="{
                'bg-theme-100 text-theme-500 dark:bg-theme-900':
                  value === category.slug || value === category.id,
              }"
              @click="
                $emit(
                  'input',
                  assortment_flag === 'print_product'
                    ? category.slug
                    : category.id,
                )
              "
            >
              <img
                v-if="suppliers?.includes(category.tenant_id)"
                :src="`/img/suppliers/images/logos/${getSupplier(
                  category.tenant_id,
                )
                  .replace(/\s+/g, '-')
                  .toLowerCase()}.jpg`"
              />

              <div
                v-tooltip="
                  category.display_name &&
                  $display_name(category.display_name).length > 30
                    ? $display_name(category.display_name)
                    : ''
                "
                class="flex items-center truncate"
              >
                <Thumbnail
                  v-if="
                    category.media &&
                    category.media.length > 0 &&
                    category.media[0]
                  "
                  disk="assets"
                  :file="{ path: category.media[0] }"
                  class="flex px-1"
                />
                {{
                  category.display_name
                    ? $display_name(category.display_name)
                    : category.name
                }}
              </div>

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

                <template v-if="assortment_flag === 'print_product'">
                  <span
                    v-tooltip="$t('this category has no manifest')"
                    class="flex"
                  >
                    <font-awesome-icon
                      v-show="!category.has_manifest"
                      class="text-amber-500"
                      :icon="['fal', 'scroll-old']"
                    />
                    <font-awesome-icon
                      v-show="!category.has_manifest"
                      class="mr-2 text-xs text-amber-500"
                      :icon="['fas', 'exclamation']"
                    />
                  </span>
                  <span
                    v-tooltip="$t('products not generated for this category')"
                    class="flex"
                  >
                    <font-awesome-icon
                      v-show="!category.has_products"
                      class="text-amber-500"
                      :icon="['fal', 'box-full']"
                    />
                    <font-awesome-icon
                      v-show="!category.has_products"
                      class="mr-2 text-xs text-amber-500"
                      :icon="['fas', 'exclamation']"
                    />
                  </span>
                  <font-awesome-icon
                    v-tooltip="$t('this category is shared to the finder')"
                    :class="
                      category.shareable
                        ? 'text-theme-500'
                        : 'text-gray-300 dark:text-gray-600'
                    "
                    class="mr-2 text-theme-500"
                    :icon="['fal', 'radar']"
                  />
                  <font-awesome-icon
                    v-tooltip="$t('supplier category')"
                    :class="
                      category.ref_id
                        ? 'text-theme-500'
                        : 'text-gray-300 dark:text-gray-600'
                    "
                    class="mr-2"
                    :icon="['fal', 'parachute-box']"
                  />
                </template>
                <font-awesome-icon
                  v-tooltip="$t('this category is usable in webshop')"
                  class="mr-2 text-theme-500"
                  :class="
                    category.published
                      ? 'text-theme-500'
                      : 'text-gray-300 dark:text-gray-600'
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

      <!-- <pagination
        class="mt-2"
        @pagination="get_categories({ per_page: 20, page: $event.page })"
      ></pagination> -->
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";

export default {
  name: "PrintProductCategories",
  props: {
    value: {
      type: [Number, String],
      default: null,
    },
  },
  emits: ["input"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { permissions, api, handleError, handleSuccess };
  },
  data() {
    return {
      catType: "normal",

      filter: "",
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.categories,
      pagination: (state) => state.pagination.pagination,
      custom_categories: (state) => state.product.custom_categories,
      active_category: (state) => state.product.active_category,
      active_custom_category: (state) => state.product.active_custom_category,
      boops: (state) => state.product.boops,
      suppliers: (state) => state.suppliers.suppliers,
      assortment_flag: (state) => state.product.assortment_flag,
      component: (state) => state.product.component,
      cart_flag: (state) => state.cart.cart_flag,
    }),
    rawComponent() {
      switch (this.component) {
        case "PrintProductImport":
          return markRaw(PrintProductImport);
        case "CategoryRemoveItem":
          return markRaw(CategoryRemoveItem);
        case "CustomProductCategoryForm":
          return markRaw(CustomProductCategoryForm);
        default:
          return h("div");
      }
    },
  },
  watch: {
   //  filter: _.debounce(function (v) {}, 300),
    active_custom_category(v) {
      return v;
    },
    component() {
    },
    custom_categories() {
    },
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
      get_custom_products: "product/get_custom_products",
    }),
    close() {
      this.set_component("");
    },
  },
};
</script>
