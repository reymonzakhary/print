<template>
  <main class="flex h-full flex-col">
    <VTour ref="assortmentTour" name="assortmentTour" :steps="steps" highlight />
    <header class="item-center grid w-full grid-cols-3 px-4 py-2">
      <div class="flex items-center">
        <button
          class="rounded-full border border-theme-500 px-2 text-sm text-theme-500"
          @click="startTour"
        >
          <font-awesome-icon :icon="['fal', 'route']" />
          {{ $t("start tour") }}
        </button>
        <div class="ml-4 hidden text-sm md:block">
          <span
            class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-900"
          >
            <font-awesome-icon :icon="['fal', 'up']" fixed-width />
            Shift
          </span>
          +
          <span
            class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-900"
          >
            <font-awesome-icon :icon="['fal', 'computer-mouse-scrollwheel']" fixed-width />
            scroll
            <font-awesome-icon :icon="['fal', 'sort']" fixed-width />
          </span>
          <span class="text-gray-500">{{ $t("for horizontal scrolling") }}</span>
        </div>
      </div>

      <p class="text-center text-lg" data-step="assortment">
        <font-awesome-icon :icon="['fal', 'box-full']" />
        {{ $t("assortment") }}
      </p>

      <div class="text-right">
        <nuxt-link
          v-if="
            hasPermission('print-assortments-margins-access') ||
            hasPermission('print-assortments-boxes-access') ||
            hasPermission('print-assortments-options-access') ||
            hasPermission('print-assortments-machines-access') ||
            hasPermission('print-assortments-printing-methods-access') ||
            hasPermission('print-assortments-catalogues-access') ||
            hasPermission('print-assortments-system-catalogues-access')
          "
          to="/manage/assortment-settings"
          class="font-normal text-theme-500 transition-colors duration-75 hover:text-theme-700 dark:text-theme-300 dark:hover:text-theme-500"
          data-step="assortment-settings"
        >
          <font-awesome-icon :icon="['fal', 'gear']" class="" />
          {{ $t("settings") }}
        </nuxt-link>
      </div>
    </header>

    <div
      v-if="compare_flag !== 'compare'"
      class="border-1 align-center m-4 flex justify-between rounded border border-blue-500 bg-blue-100 px-2 py-1 text-blue-500"
    >
      <div class="flex items-center">
        <font-awesome-icon :icon="['fad', 'circle-info']" class="mr-2" />
        {{ $t("Adding a product") }}
        <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-1 mt-0.5" />
        <strong class="mr-1">{{ ordertype === "order" ? $t("order") : $t("quotation") }}</strong>
        <div class="pb-0.5">
          <span class="rounded bg-blue-500 px-2 py-0.5 font-mono text-xs font-bold text-blue-50"
            >#{{ order_id }}</span
          >
        </div>
      </div>
      <UIButton
        v-tooltip.bottom-end="$t('Cancel')"
        variant="link"
        class="!text-lg !text-blue-500 hover:!bg-blue-200"
        :icon="['fal', 'cancel']"
        @click="(set_flag('compare'), $router.push(`${ordertype}s/${order_id}`))"
      />
    </div>

    <div
      id="container"
      class="relative flex h-full grid-cols-12 overflow-x-auto"
      style="scroll-behavior: smooth"
    >
      <aside class="left-0 z-10 pb-2 pl-4 xl:sticky" data-step="categories">
        <Categories
          :selecting-mode="selectingMode"
          @on-missing-materials-in-catalogue-identified="missingMaterialsInCatalogue = $event"
          @on-missing-grs-in-catalogue-identified="missingGrsInCatalogue = $event"
          @on-missing-options-in-machines-identified="missingInMachine = $event"
          @on-category-selected="(updateUrl($event), (selectingMode = false), (prices = []))"
          @assortment-flag-changed="((showPrices = false), (prices = {}), set_collection($event))"
          @on-category-delete="handleCategoryDelete"
        />
      </aside>

      <transition name="list">
        <section
          v-if="
            assortment_flag === 'print_product' ||
            (!hasPermission('custom-assortments-products-access') &&
              hasPermission('print-assortments-products-access'))
          "
          class="z-0 col-span-10 pb-4"
          :class="{ '2xl:pr-[20%]': !prices || Object.keys(prices).length === 0 }"
        >
          <Boops
            v-if="boops?.boops?.length > 0"
            :scroll-to-end="scrollToEnd"
            :show-view-prices-button="showPrices"
            :missing-materials-in-catalogue="missingMaterialsInCatalogue"
            :missing-grs-in-catalogue="missingGrsInCatalogue"
            :missing-in-machine="missingInMachine"
            :qty="qty"
            @update-qty="qty = parseInt($event)"
            @collection-complete="
              (updateUrl($event), getShopPrices(qty, collection), (showPrices = true), scrollToEnd)
            "
            @collection-updated="
              (updateUrl($event),
              ((showPrices = false),
              (prices = {}),
              set_collection($event),
              $event.length > 2 ? (selectingMode = true) : (selectingMode = false)))
            "
            @reset:prices="handleResetEdit"
          />
        </section>
      </transition>

      <div
        v-if="showPrices && prices && Object.keys(prices).length > 0"
        class="block w-full min-w-fit pl-8 pr-4 md:min-w-[800px] 2xl:mr-[20%]"
      >
        <UIButton
          v-if="hasPermission('print-assortments-margins-access') && !me.external"
          @click.prevent.stop="showPriceDetails = !showPriceDetails"
        >
          {{ showPriceDetails ? $t("show shop price") : $t("show price details") }}
        </UIButton>

        <ShopPriceTable
          v-if="!showPriceDetails"
          class="w-full"
          :shop-prices="prices"
          :is-call-api="isCallApi"
          :qty="parseInt(qty)"
          :edit="false"
          @on-price-select="selectedPrice = $event"
          @update-qty="((qty = parseInt($event)), getShopPrices($event, collection))"
        />
        <PriceTable
          v-else
          :prices="prices"
          :qty="parseInt(qty)"
          :edit="false"
          @on-price-select="selectedPrice = $event"
          @update-qty="qty = parseInt($event)"
        />
      </div>
      <div v-else-if="showPrices">
        <UIInputText
          name="quantity"
          class="mr-2 w-72"
          :prefix="$t('quantity')"
          :model-value="qty"
          type="number"
          @input="(e) => (qty = e.target.value)"
        />
      </div>

      <transition name="list" tag="nav">
        <!-- If custom product is selected OR if the user has no access to print assortments but does to custom. -->
        <div
          v-if="
            (assortment_flag === 'custom_product' &&
              Object.keys(active_custom_category).length > 0) ||
            (hasPermission('custom-assortments-products-access') &&
              !hasPermission('print-assortments-products-access'))
          "
          class="col-span-10 w-full"
        >
          <CustomProductsList />
        </div>
      </transition>
    </div>
  </main>
</template>

<script>
import { mapState, mapMutations, useStore } from "vuex";
import _ from "lodash";

export default {
  name: "Products",
  layout: "default",
  setup() {
    const api = useAPI();
    const calculation = useCalculationRepository();
    const { permissions, hasPermission, hasAnyPermissions, hasPermissionGroup } = usePermissions();
    const { handleError } = useMessageHandler();
    const { capitalizeFirstLetter } = useUtilities();
    const store = useStore();
    return {
      permissions,
      hasPermission,
      hasAnyPermissions,
      hasPermissionGroup,
      api,
      calculation,
      store,
      handleError,
      capitalizeFirstLetter,
    };
  },
  data() {
    return {
      containerWidth: 0,
      listview: false,
      showPrices: false,
      showPriceDetails: false,
      missingMaterialsInCatalogue: {},
      missingGrsInCatalogue: {},
      missingInMachine: {},
      prices: [],
      selectingMode: false,
      isCallApi: false,
      // supplier: {},
      // discounts: {},

      qty: 100,
      lastQty: this.qty,
      steps: [
        {
          target: "[data-step='assortment']",
          title: this.capitalizeFirstLetter(this.$t("welcome to your assortment!")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is where you manage the categories that you want to add to your shop for selling and, if you are a producer, share on the Product Finder"),
          ),
          // popperConfig: {
          //   placement: "inside",
          // },
        },
        {
          target: "[data-step='categories']",
          title: this.capitalizeFirstLetter(this.$t("welcome to your categories!")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is where you will find a list of your categories. You can watch what boxes and options it contains, edit it's details, check the prices and quickly browse a category to add it to a quotation or order."),
          ),
          popperConfig: {
            placement: "right",
          },
        },
        {
          target: "[data-step='assortment-settings']",
          title: this.capitalizeFirstLetter(this.$t("The settings that reflect your assortment")),
          body: this.capitalizeFirstLetter(
            // prettier-ignore
            this.$t("This is where you will find settings that work with your assortment. Things like your printmachines and catalog for calculating assortment prices. An overview of used boxes and options. Printing methods and more."),
          ),
          popperConfig: {
            placement: "left",
          },
        },
      ],
    };
  },
  head() {
    return {
      title: `${this.$t("assortment")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.categories,
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      assortment_flag: (state) => state.product.assortment_flag,
      compare_flag: (state) => state.compare.flag,
      order_id: (state) => state.orders.active_order,
      ordertype: (state) => state.orders.ordertype,
      collection: (state) => state.product.collection,
      price_collection: (state) => state.product_wizard.price_collection,
      selected_category: (state) => state.product_wizard.selected_category,
      active_custom_category: (state) => state.product.active_custom_category,
      me: (state) => state.settings.me,
    }),
  },
  watch: {
    categories: {
      handler(val) {
        return val;
      },
      deep: true,
    },
    category() {
      this.prices = {};
    },
    processedBoops: {
      handler(val) {
        return val;
      },
      deep: true,
    },
    prices() {
      this.$nextTick(this.scrollToEnd);
    },
    qty: _.debounce(function (v) {
      if (
        (Array.isArray(v) && v.length > 0) ||
        this.collection.length === this.boops.boops.length
      ) {
        if (this.showPriceDetails) {
          this.getPrices(v, this.collection);
        } else {
          this.getShopPrices(v, this.collection);
        }
      }
    }, 500),

    // get prices from other endpoint too show discounts and margins in price table
    // instead of shop prices in shop price table or vice versa
    showPriceDetails(v) {
      if (v) {
        this.getPrices(this.qty, this.collection);
      } else {
        this.getShopPrices(this.qty, this.collection);
      }
    },
    collection: {
      immediate: true,
      handler(v) {
        // if (
        //   this.selected_category?.boops?.[0]?.boops?.length &&
        //   Object.keys(v).length === this.selected_category.boops[0].boops.length
        // ) {
        //   // this.getShopPrices(this.qty, v);
        // }
        this.selectingMode = Object.keys(v).length > 1;
      },
      deep: true,
    },
  },
  async created() {
    await this.fetchCategories();
  },
  beforeUnmount() {
    this.prices = [];
    this.set_collection({});
  },
  fetchOnServer: true,
  methods: {
    ...mapMutations({
      set_assortment_flag: "product/set_assortment_flag",
      set_boops: "product/set_boops",
      set_collection: "product/set_active_collection",
      set_flag: "compare/set_flag",
    }),
    handleResetEdit() {
      this.prices = {};
      this.showPrices = false;
      const url = new URL(window.location.href);

      // Keep only the 'cat' parameter
      const catValue = url.searchParams.get("cat");
      url.search = ""; // Clear all
      if (catValue) {
        url.searchParams.set("cat", catValue);
      }

      // Update URL without reloading the page
      window.history.replaceState({}, "", url);
    },
    /*
     * @param {object} e
     * @description updateUrl function to update the URL based on the selected category or collection.
     */
    updateUrl(e) {
      if (!e) return;

      const route = useRoute();
      const router = useRouter();

      // Handle category selection
      if (e.type === "category" && !e.fromUrl) {
        router.push({
          path: "/assortment",
          query: {
            ...route.query,
            cat: e.cat.slug,
            c: undefined,
          },
        });
        this.qty = 100;
        return;
      }

      // Handle collection array
      if (Array.isArray(e) && e.length > 0) {
        const urlParts = e.map((item) => {
          const { key, value, divider = "", dynamic, _ = {} } = item;
          let part = `${key}:${value}:${divider}`;

          if (dynamic && Object.keys(_).length > 0) {
            const dynamicProps = Object.entries(_)
              .map(([k, v]) => `${k}=${v}`)
              .join(",");
            if (dynamicProps) {
              part += `:${dynamicProps}`;
            }
          }

          return part;
        });

        router.replace({
          query: {
            ...route.query,
            c: urlParts.join("|"),
          },
        });
      }
    },
    handleCategoryDelete(category) {
      const categories = this.$store.state.product.categories;
      const categoryIndex = categories.findIndex((cat) => cat.id === category.id);
      if (categoryIndex !== -1) {
        categories.splice(categoryIndex, 1);
        this.$store.commit("product/set_categories", categories);
        return;
      }

      // Check and remove from custom_categories
      const customCategories = this.$store.state.product.custom_categories;
      const customCategoryIndex = customCategories.findIndex((cat) => cat.id === category.id);
      if (customCategoryIndex !== -1) {
        customCategories.splice(customCategoryIndex, 1);
        this.$store.commit("product/set_custom_categories", customCategories);
      }

      this.set_boops("");
    },
    scrollToEnd() {
      const container = this.$el.querySelector("#container");
      container.scrollTo({
        left: container.scrollWidth,
      });
    },
    async fetchCategories() {
      let custom = "";
      if (
        (this.assortment_flag && this.assortment_flag === "custom_product") ||
        (this.hasPermission("custom-assortments-categories-access") &&
          !this.hasPermission("print-assortments-products-access"))
      ) {
        custom = "custom/";
        this.set_assortment_flag("custom_product");
      }
      await this.api
        .get(`/${custom}categories?per_page=100&page=1`)
        .then((response) => {
          if (custom === "custom/") {
            this.store.commit("product/set_custom_categories", response.data);
          } else {
            this.store.commit("product/set_categories", response.data);
          }
          this.store.commit("pagination/set_pagination", response.meta);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    // Obtain and show calculated prices of selected category & product for authenticated Client (reseller)
    getShopPrices: _.debounce(async function (qty, collection) {
      this.isCallApi = true;
      let product = {};
      if (collection && Object.keys(collection).length > 0) {
        product = {
          ...collection,
        };
      }

      // check if product is empty
      if (Object.keys(product).length === 0 && product.constructor === Object) {
        return navigateTo("/assortment");
      }

      await this.calculation
        .get(this.category[1], {
          type: "print",
          product: product ?? [...new Set(this.collection)],
          quantity: qty ?? this.qty,
          divided: this.selected_category.boops[0].divided ?? false,
        })
        .then((response) => {
          this.prices = response;
          this.lastQty = qty ?? this.qty;
        })
        .catch((error) => {
          this.prices = [];
          this.qty = this.lastQty ? this.lastQty : this.qty;
          this.handleError(error);
        }).finally(() => {
          this.isCallApi = false;
        });
    }, 600),

    async getPrices(qty, collection) {
      let product = {};
      if (collection && Object.keys(collection).length > 0) {
        product = {
          ...collection,
        };
      }

      await this.api
        .post(`/categories/${this.category[1]}/products/calculate/prices`, {
          type: "print",
          product: product ?? [...new Set(this.collection)],
          quantity: qty ?? this.qty,
          divided: this.selected_category.boops[0].divided,
        })
        .then((response) => {
          this.prices = response.data;
          this.lastQty = qty ?? this.qty;
        })
        .catch((error) => {
          if (error.status === 404) {
            this.prices = [];
          }
          this.qty = this.lastQty;
          this.handleError(error);
        });
      // }
    },

    startTour() {
      this.$refs.assortmentTour.resetTour();
      this.$refs.assortmentTour.startTour();
    },
  },
};
</script>
