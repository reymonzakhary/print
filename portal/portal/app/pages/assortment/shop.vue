<template>
  <div class="h-full p-2">
    <article v-if="category[0] !== null">
      <ShopPricePageHeader
        v-if="true"
        :name="
          selected_category.display_name
            ? $display_name(selected_category.display_name)
            : category[2]
        "
        class="z-10"
        @on-back="deactivateDetails"
        @on-close="deactivateDetails"
      />

      <div class="z-20 mt-4 rounded-b-md p-2 sm:p-4">
        <!-- Boxes & Options -->
        <!-- <transition name="slide"> -->
        <!-- <section> -->
        <section class="item-center mx-auto flex w-auto overflow-x-auto overflow-y-hidden py-2">
          <!-- Supplier info -->
          <!-- <div
                class="items-center justify-center hidden w-full h-auto pb-4 mr-4 border-b sm:flex lg:w-auto lg:border-b-0 dark:border-gray-900"
              >
                <div class="flex flex-col">
                  <figure
                    v-if="supplier"
                    class="flex items-center justify-center flex-shrink-0 w-20 h-20 mx-auto overflow-hidden border rounded-full dark:bg-white"
                  >
                    <img
                      v-if="supplier.name"
                      :src="`/img/suppliers/images/logos/${supplier.name
                        .replace(/\s+/g, '-')
                        .toLowerCase()}.jpg`"
                    />
                    <div v-else>
                      <font-awesome-icon
                        :icon="['fad', 'parachute-box']"
                        class="fa-2xl text-theme-400"
                      />
                    </div>
                  </figure>
                  <p class="font-bold capitalize">{{ category[2] }}</p>
                </div>

                <font-awesome-icon
                  :icon="['fal', 'chevron-right']"
                  class="ml-10 text-6xl text-gray-300"
                />

                <div class="text-sm text-gray-800 dark:text-gray-500 dark:bg-gray-700">
                  <span class="italic">{{ supplier.name }}</span>
                  <a
                    v-for="country in supplier.operating_countries"
                    :key="country.iso_code"
                    class=""
                  >
                    <span class="">
                      <font-awesome-icon :icon="['fal', 'flag']" />
                    </span>
                  </a>
                </div>
              </div> -->
          <!-- Show selected collection of boxes & options -->
          <ShopSelectedBoopsList
            ref="boopsList"
            class="mx-auto w-fit overflow-x-auto text-sm"
            :class="{ 'w-full': boops.boops?.length > 7 }"
            :boops="boops"
            :collection="collection"
            :selected-category="selected_category"
            :active-items="active_items"
          />
        </section>
        <div class="mt-4 flex w-full justify-center">
          <div class="w-full md:w-2/3">
            <ShopPriceTable
              v-if="prices && collection"
              class="sticky top-5 w-full min-w-max"
              :shop-prices="prices"
              :qty="qty"
              :edit="false"
              @on-price-select="selectedPrice = $event"
              @update-qty="qty = $event"
            />
          </div>
        </div>
      </div>
    </article>
  </div>
</template>

<script>
import _ from "lodash";
import { mapState, mapActions, mapMutations, useStore } from "vuex";

export default {
  transition: "slideleftlarge",
  setup() {
    const api = useAPI();
    const calculation = useCalculationRepository();
    const { permissions } = storeToRefs(useAuthStore());
    const store = useStore();
    const eventStore = useEventStore();
    const route = useRoute();
    const { handleError, handleSuccess } = useMessageHandler();

    return {
      api,
      calculation,
      permissions,
      store,
      eventStore,
      route,
      handleError,
      handleSuccess,
    };
  },
  data() {
    return {
      noLocalStorage: false,
      prices: [],
      supplier: {},
      discounts: {},

      qty: 100,
      ProductDetailsTourOptions: {
        useKeyboardNavigation: true,
        labels: {
          buttonSkip: "Skip tour",
          buttonPrevious: "Previous",
          buttonNext: "Next",
          buttonStop: "Finish!",
        },
        highlight: true,
      },
      steps: [
        {
          target: '[data-v-step="0"]',
          content:
            "<b>Welcome to your dashboard!</b><br>This is where you will find your acount info and settings",
          params: {
            placement: "right",
          },
          before: (type) =>
            new Promise((resolve, reject) => {
              // Time-consuming UI/async operation here
            }),
        },
      ],
      selectedPrice: {},
    };
  },
  head() {
    return {
      title: `${this.$t("assortment")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      // vuex product
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      active_items: (state) => state.product.active_items,
      collection: (state) => state.product.collection,

      // vuex productwizard
      selected_category: (state) => state.product_wizard.selected_category,
      selected_boops: (state) => state.product_wizard.selected_boops,
      price_collection: (state) => state.product_wizard.price_collection,
    }),
  },
  watch: {
    selected_category: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
    },
    boops(newVal) {
      return newVal;
    },
    selected_boops(newVal) {
      return newVal;
    },
    prices: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    collection: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    qty: _.debounce(function (v) {
      this.getShopPrices(v);

      return v;
    }, 500),

    price_collection: {
      handler(v) {
        this.getShopPrices(this.qty, v);
      },
      deep: true,
    },
  },
  async mounted() {
    const slug = this.route.query.cat;

    /**
     * There's currently a bug where on a page refresh, or on a
     * HMR reload, the mandatory data for showing price is lost.
     * In order to counter this, I've chosen to save the state in
     * Boops.vue to the localStorage and check here if it's available.
     * If it is, we set the state to the vuex store and continue as normal.
     *
     * This is a temporary solution, preferably we should save the state
     * in the URL for sharability and to avoid this issue. However, because
     * of how to the current flow is set up this is time consuming and complex
     * to implement.
     */

    if (
      this.category.length > 0 &&
      this.boops.boops.length > 0 &&
      this.collection &&
      Object.keys(this.collection).length > 0
    ) {
      return this.getCategory(slug);
    }

    const pricesTime = await JSON.parse(localStorage.getItem("pricesTime"));
    const pricesCategory = await JSON.parse(localStorage.getItem("pricesActiveCategory"));
    const pricesCollection = await JSON.parse(localStorage.getItem("pricesCollection"));
    const pricesActiveObject = await JSON.parse(localStorage.getItem("pricesActiveObject"));
    const pricesActiveItems = await JSON.parse(localStorage.getItem("pricesActiveItems"));
    const pricesBoops = await JSON.parse(localStorage.getItem("pricesBoops"));
    const selectedCategory = await JSON.parse(localStorage.getItem("pricesSelectedCategory"));

    // If none of the localStorage items are set, just act normal.
    if (
      !pricesTime ||
      !pricesCategory ||
      !pricesCollection ||
      !pricesActiveObject ||
      !pricesActiveItems ||
      !pricesBoops ||
      !selectedCategory
    ) {
      this.noLocalStorage = true;
      return this.getCategory(slug);
    }

    // If the localStorage is set, we'll check if the category is the same as the one in the URL.
    if (pricesCategory[1] !== slug) {
      this.noLocalStorage = true;
      return this.getCategory(slug);
    }

    // LocalStorage is set, let's check if it's recent enough to use.
    const time = new Date().getTime();
    const diff = time - pricesTime;

    // If the localStorage is older than 1 hour, we act normal
    if (diff > 3600000) {
      this.noLocalStorage = true;
      return this.getCategory(slug);
    }

    // If the localStorage is recent enough, we'll set the state to the vuex store.
    this.set_active_category(pricesCategory);
    this.set_active_collection(pricesCollection);
    this.set_active_items(pricesActiveItems);
    this.set_selection(pricesActiveObject);
    this.set_selected_category(selectedCategory);
    this.set_boops(pricesBoops);
    this.getCategory(slug);
  },
  beforeUnmount() {
    this.eventStore.off("add_category_printing_method");
  },
  methods: {
    ...mapMutations({
      set_active_collection: "product/set_active_collection",
      set_active_category: "product/set_active_category",
      set_active_items: "product/set_active_items",
      set_selection: "product/set_selection",
      set_boops: "product/set_boops",
      set_selected_category: "product_wizard/set_selected_category",
      set_selected_boops: "product_wizard/set_selected_boops",
      update_calculation_method: "product_wizard/update_calculation_method",
      update_price_build: "product_wizard/update_price_build",
    }),
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),

    async getCategory(slug) {
      this.api
        .get(`categories/${slug}`)
        .then((response) => {
          const category = response.data;
          this.set_selected_category(category);
          this.set_selected_boops(category.boops[0].boops);
          this.set_active_category([category.id, category.slug, category.name]);
          Object.assign(this.selected_category, {
            additional: { machine: null },
          });
          this.$forceUpdate();
          this.getMargins();
          // retrieve prices for the collection of this category
          this.getShopPrices(this.qty, this.collection);
          // this.handleSuccess(this.$t("loaded category from url"));
        })
        .catch((error) => this.handleError(error));
    },

    // Get Supplier by ID
    async getSupplier() {
      // TODO: move this function to vuex store to have it globally accessible
      if (this.boops.tenant_id) {
        await this.api
          .$get(`/suppliers/${this.boops.tenant_id}`)
          .then((response) => (this.supplier = response))
          .catch((error) => this.handleError(error));
      }
    },
    // Obtain and show calculated prices of selected category & product for authenticated Client (reseller)
    async getShopPrices(qty) {
      // if (Object.keys(collection).length > 0) {
      const product = {
        ...this.collection,
      };

      // check if product is empty
      if (
        this.noLocalStorage &&
        Object.keys(product).length === 0 &&
        product.constructor === Object
      ) {
        if (
          this.permissions.includes("print-assortments-categories-access") ||
          this.permissions.includes("custom-assortments-categories-access")
        ) {
          return navigateTo("/assortment");
        } else {
          return navigateTo("/shop");
        }
      }
      await this.calculation
        .get(this.category[1], {
          type: "print",
          product: [...new Set(this.collection)],
          quantity: qty,
          divided: this.selected_category.boops[0].divided,
        })
        .then((response) => {
          this.prices = response;
        })
        .catch((error) => {
          if (error.status === 404) {
            this.prices = [];
          }
          this.handleError(error);
        });
    },

    // Get margin - Category Object of Authenticated Reseller (Seller)
    async getMargins() {
      // TODO: move this function to vuex store to have it globally accessible
      let supplier_url = "";
      if (this.store.state.product.category_ref_id != null) {
        supplier_url = `?supplier_id=${this.store.state.product.category_ref_id}`;
      }
      await this.api
        .get(`/categories/${this.category[1]}/margins${supplier_url}`)
        .then(
          (
            margins, // add result to store
          ) => this.store.commit("margins/store", margins),
        )
        .catch((error) => this.handleError(error));
    },
    // close details and navigate to products overview
    deactivateDetails() {
      // empty the localStorage
      localStorage.setItem("pricesTime", null);
      localStorage.setItem("pricesActiveCategory", null);
      localStorage.setItem("pricesCollection", null);
      localStorage.setItem("pricesActiveObject", null);
      localStorage.setItem("pricesActiveItems", null);
      localStorage.setItem("pricesBoops", null);
      localStorage.setItem("pricesSelectedCategory", null);

      this.$router.push("/assortment");
    },
    handleBoopsListWheel(e) {
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;

      e.preventDefault();
      this.$refs.boopsList.$el.scrollRight += e.deltaY;
    },
  },
};
</script>
