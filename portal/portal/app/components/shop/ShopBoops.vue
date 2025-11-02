<template>
  <div class="relative w-full overflow-y-auto">
    <div
      v-if="boops === ''"
      class="justify- dark:bg-gray-black flex h-full w-full flex-col flex-wrap items-center rounded bg-gray-200 p-4 text-center dark:bg-gray-900"
    >
      <p class="text-xl font-bold text-gray-400">
        {{ $t("category is empty") }}
      </p>

      <div class="my-8 flex items-start justify-center">
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x m-4 text-gray-300" />
        <font-awesome-icon :icon="['fad', 'bars']" class="fa-5x my-4 text-gray-400" />

        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
      </div>
    </div>

    <transition-group v-else name="list" tag="nav" class="relative flex w-full flex-col text-sm">
      <template v-for="(box, index) in boops[0].boops" :key="index">
        <div
          v-if="
            ((boops[0]?.boops[index - 1]?.divider &&
              boops[0]?.boops[index - 1]?.divider !== box.divider) ||
              (index === 0 && boops[0].divided)) &&
            index <= activeIndex
          "
          class="sticky top-0 z-50 mb-6 rounded-t bg-theme-500 text-sm font-bold uppercase tracking-wider text-themecontrast-500 shadow dark:bg-theme-600"
        >
          <h3 class="p-2">{{ box.divider }}</h3>
        </div>
        <section
          v-if="index <= activeIndex"
          class="mb-8 w-full flex-shrink-0 rounded bg-white shadow-md shadow-gray-200 dark:bg-gray-800 dark:shadow-gray-900"
          :style="'z-index:' + (28 - index)"
        >
          <p
            class="border-b bg-white p-2 text-sm font-bold uppercase tracking-wide dark:border-gray-900 dark:bg-gray-700"
          >
            {{ $display_name(box.display_name) }}
          </p>

          <section
            class="grid w-full grid-cols-4 items-center justify-start gap-4 p-4 dark:bg-gray-700"
          >
            <template v-for="(item, i) in box.ops">
              <div
                v-if="hasValue(index, i)"
                :key="'warning_' + i"
                class="flex items-center justify-between rounded border-amber-500 bg-amber-100 p-2 text-amber-500 dark:bg-amber-800"
              >
                {{ $t("select a value first") }}
                <font-awesome-icon :icon="['fal', 'arrow-up']" class="" />
              </div>
              <button
                v-else-if="checkExclude(item, box.divider) === true"
                :key="'boop_' + i"
                class="flex h-full flex-col items-center justify-between rounded border bg-white p-2 transition-colors duration-75 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800"
                :class="{
                  'border-theme-500 !bg-theme-100 text-theme-500 dark:!bg-theme-900': isSelected(
                    box,
                    item,
                    index,
                  ),
                }"
                @click="setActiveOption(box, item, index + 1)"
              >
                <div class="w-full">
                  <div
                    v-tooltip="
                      $display_name(item.display_name).length > 30
                        ? $display_name(item.display_name)
                        : ''
                    "
                    class="h-24 truncate"
                  >
                    <ShopImage disk="assets" :file="{ path: item.media[0] }" />
                  </div>
                  <h2 v-tooltip="$display_name(item.display_name)" class="w-full truncate pt-2">
                    {{ $display_name(item.display_name) }}
                  </h2>
                </div>

                <form
                  v-if="
                    collection.find(
                      (activeItem) =>
                        activeItem?.value === item.slug && activeItem.divider === box.divider,
                    ) && item.dynamic
                  "
                  class="flex w-full items-center"
                >
                  <BoopsDynamicOption
                    :item="item"
                    :selected="collection[index]"
                    :active-items="activeItems"
                    @on-pages-updated="setPages($event, index)"
                    @on-sides-updated="setSides($event, index)"
                    @on-format-width-updated="setFormat($event, 'width', index)"
                    @on-format-height-updated="setFormat($event, 'height', index)"
                  />
                  <!-- <div v-else>
                    {{ $display_name(item.display_name) }}
                  </div> -->
                </form>
              </button>

              <button
                v-else
                :key="item.id"
                disabled
                class="cursor-not-allowed truncate rounded border border-gray-100 p-2 text-center text-gray-300 dark:text-gray-700"
              >
                <div
                  v-tooltip="
                    $display_name(item.display_name).length > 30
                      ? $display_name(item.display_name)
                      : ''
                  "
                  class="h-24 truncate"
                >
                  <ShopImage disk="tenancy" :file="{ path: item.media[0] }" />
                </div>
                <h2 class="w-full truncate pt-2">
                  {{ $display_name(item.display_name) }}
                </h2>
              </button>
            </template>
          </section>
        </section>
      </template>
    </transition-group>
    <!-- category:{{ (selected_category, category) }} -->
    <!-- <UILoader v-if="isFetchingPrices" /> -->

    <div
      v-if="isFetchingPrices && !priceList"
      class="w-full p-4 text-center text-2xl text-theme-500"
    >
      <font-awesome-icon :icon="['fad', 'spinner-third']" spin />
    </div>
    <ShopPriceRangeTable
      v-if="
        (prices && Object.keys(prices).length > 0) ||
        (priceList && Object.keys(priceList).length > 0)
      "
      class="sticky top-5 w-full min-w-max overflow-x-auto"
      :loading="isFetchingPrices"
      :shop-prices="prices"
      :shop-prices-list="priceList"
      :edit="false"
      is-shop
      :quantity="Number(quantity)"
      @on-price-select="((selectedPrice = $event.dlv), (qty = $event.qty), scrollToBottom())"
      @on-quantity-change="quantity = $event"
    />
    <div
      v-else-if="hasPricesFetched && prices && prices.length == 0"
      class="mx-auto mb-6 text-center"
    >
      {{ $t("We've found no prices with this combination") }}...
    </div>
    <div
      v-if="
        activeItems.length === boops[0]?.boops?.length &&
        (prices || priceList) &&
        Object.keys(selectedPrice).length > 0
      "
      v-tooltip="
        !me.ctx.find((item) => item.member === true) && $t(`Only members can add items to the cart`)
      "
      class="mt-4 flex justify-center"
    >
      <button
        v-if="(prices || priceList) && Object.keys(selectedPrice).length > 0"
        class="mx-auto mb-40 w-full rounded-full border-2 p-2 text-xl font-bold uppercase tracking-wide text-themecontrast-400 transition dark:border-theme-600 lg:w-1/2"
        :class="
          !me.ctx.find((item) => item.member === true)
            ? 'border-gray-500 bg-gray-400'
            : 'border-theme-300 bg-theme-400 hover:bg-theme-500'
        "
        :disabled="!me.ctx.find((item) => item.member === true)"
        @click="handleAddToCartClick(prices ? prices : priceList, activeItems)"
      >
        <font-awesome-icon :icon="['fad', 'cart-arrow-down']" class="mr-2" />
        {{ $t("add to cart") }}
      </button>
    </div>

    <!-- <OptionsEditPanel v-if="component" @on-close="component = false" /> -->
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

const images = import.meta.glob("assets/images/assortments_portal/en/*.svg", {
  eager: true,
});

export default {
  props: {
    type: {
      type: String,
      default: "print",
    },
  },
  emits: ["closeShopBoops", "priceSelected", "itemSelected"],
  setup() {
    const { objectToFormData } = useUtilities();
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const calculation = useCalculationRepository();
    const { handleError, handleSuccess } = useMessageHandler();
    const { addToast } = useToastStore();
    return {
      objectToFormData,
      api,
      calculation,
      permissions,
      handleError,
      handleSuccess,
      addToast,
    };
  },
  data() {
    return {
      activeIndex: "",
      activeObject: {},
      activeItems: [],
      collection: [],
      exclude: {},
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
          ],
        },
      ],
      component: false,
      isFetchingPrices: false,
      prices: [],
      priceList: [],
      selectedPrice: {},
      selectedDivider: "",
      lastQty: 1,
      quantity: 1,
      qty: null,
      hasPricesFetched: false,
    };
  },
  computed: {
    ...mapState({
      category: (state) => state.shop.active_category,
      me: (state) => state.settings.me,
      selected_category: (state) => state.shop.selected_category,
      boops: (state) => state.shop.boops,
      active_collection: (state) => state.shop.active_collection,
      active_items: (state) => state.shop.active_items,
    }),
  },
  watch: {
    category: {
      deep: true,
      handler() {
        this.activeIndex = 0;
        this.getBoops();
      },
    },
    activeItems: {
      deep: true,
      handler(v) {
        this.set_active_items(v);
        if (
          this.boops &&
          (v.length === this.boops[0].boops.length ||
            this.activeIndex === this.boops[0].boops.length)
        ) {
          if (!v[v.length - 1].dynamic) {
            this.priceList = null;
            this.getPriceList();
          }
        } else {
          this.prices = null;
          this.priceList = null;
        }
        this.scrollToBottom();
      },
    },
    "prices, priceList": {
      deep: true,
      handler() {
        setTimeout(() => {
          this.scrollToBottom();
        }, 200);
      },
    },
    quantity: {
      handler() {
        this.getPrices();
      },
    },
    selectedPrice(v) {
      this.$emit("priceSelected", v);
    },
  },
  created() {
    if (
      this.permissions.includes("print-assortments-options-update") &&
      this.permissions.includes("print-assortments-options-list")
    ) {
      this.menuItems[0].items[0].show = true;
    }

    // this.getCategory();
  },
  unmounted() {
    this.set_active_items(null);
  },
  methods: {
    ...mapMutations({
      set_boops: "shop/set_boops",
      set_loading_boops: "shop/set_loading_boops",
      set_active_collection: "shop/set_active_collection",
      set_selected_category: "shop/set_selected_category",
      set_active_items: "shop/set_active_items",
      set_selected_option: "product_wizard/set_selected_option",
      set_item: "assortmentsettings/set_item",
      set_runs: "assortmentsettings/set_runs",
      set_flag: "assortmentsettings/set_flag",
      set_cart_flag: "cart/set_cart_flag",
    }),
    scrollToBottom() {
      this.$nextTick(() => {
        const optionsList = document.getElementById("scroll-container");
        optionsList.scrollTo({
          top: optionsList.scrollHeight,
        });
        // optionsList.scroll(optionsList.scrollHeight);
        // console.log(optionsList.scrollHeight);
      });
    },
    // async getCategory() {
    //   if (this.type === "print") return;

    //   if (this.category[1] !== undefined) {
    //     await this.api
    //       .get(`shops/categories/${this.category[1]}`)
    //       .then((response) => {
    //         this.set_selected_category(response.data);
    //       })
    //       .catch((error) => {
    //         this.handleError(error);
    //       });
    //   }

    //   this.set_loading_boops(false);
    // },
    // async getBoops() {
    //   console.log(this.selected_category.slug);
    //   console.log(this.category);
    //   if (this.category[1] !== undefined) {
    //     await this.api
    //       .post(`shops/categories/${this.category[1]}`, {
    //         type: `print`,
    //       })
    //       .then((response) => {
    //         this.set_boops(response.data);
    //         this.set_loading_boops(false);
    //       })
    //       .catch((error) => {
    //         this.handleError(error);
    //         this.set_boops("");
    //       });
    //   }

    //   this.set_loading_boops(false);
    // },
    setFormat(val, dim, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }

      if (dim === "height") {
        this.collection[index]._.height = parseInt(val);
      } else if (dim === "width") {
        this.collection[index]._.width = parseInt(val);
      }

      if (this.activeItems.length === this.boops[0].boops.length) {
        if (this.activeItems[this.activeItems.length - 1].dynamic) {
          if (
            this.collection[this.collection.length - 1]._.height &&
            this.collection[this.collection.length - 1]._.width
          ) {
            this.priceList = null;
            this.getPriceList();
          }
        }
      }
    },
    setPages(val, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }
      Object.assign(this.collection[index]._, { pages: parseInt(val) });
      this.$emit("itemSelected", this.collection);
      //  Object.assign(this.collection[index]._, { pages: parseInt(val) });
      if (this.activeItems.length === this.boops[0].boops.length) {
        this.priceList = null;
        this.getPriceList();
      }
    },
    setSides(val, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }
      Object.assign(this.collection[index]._, { sides: parseInt(val) });
      if (this.activeItems.length === this.boops[0].boops.length) {
        this.priceList = null;
        this.getPriceList();
      }
      //  Object.assign(this.collection[index]._, { pages: parseInt(val) });
    },
    hasValue(index) {
      // check if previous dynamic option has value inputted
      return (
        this.activeItems[index - 1]?.dynamic &&
        this.collection[index - 1]?._ &&
        Object.keys(this.collection[index - 1]?._).length === 0
      );
    },
    isSelected(box, item, index) {
      // check if item is selected using helper functions for clarity
      return (
        this.isMatchingBox(index, box, item) && this.hasActiveItems() && this.isItemActive(item)
      );
    },
    isMatchingBox(index, box, item) {
      return (
        this.collection[index]?.key === box.slug &&
        this.collection[index]?.value === item.slug &&
        this.collection[index]?.divider === box.divider &&
        this.collection[index]?.dynamic === item.dynamic
      );
    },
    hasActiveItems() {
      return this.activeItems.length > 0;
    },
    isItemActive(item) {
      return this.activeItems.findIndex((x) => x.slug === item.slug) > -1;
    },
    checkAllOptionsExclude(box, divider = "") {
      return box.ops.every((option) => {
        return !this.checkExclude(option, divider);
      });
    },
    setActiveOption(box, item, index) {
      /** @activeIndex int hold the box position **/
      this.activeIndex = index;
      this.activeItems.length = index - 1;
      this.activeItems.splice(index, 1);
      this.activeItems.push(item);
      this.selectedDivider = box.divider;
      // this.collection += item.id + "-";
      if (
        index <= this.boops[0].boops.length - 1 &&
        this.checkAllOptionsExclude(this.boops[0].boops[index], box.divider)
      ) {
        this.activeIndex = index + 1;
      } else {
        this.activeIndex = index;
      }

      /** @exclude array: reset the array **/
      for (let i = index; i <= Object.keys(this.exclude).length && i >= index; i - 1) {
        delete this.exclude[Object.keys(this.exclude)[i - 1]];
      }

      this.collection[index - 1] = {
        key: box.slug,
        key_id: box.id,
        source_key: box.source_slug,
        value: item.slug,
        source_value: item.source_slug,
        value_id: item.id,
        divider: box.divider,
        dynamic: item.dynamic,
        _: {}, // -->> dynamic options (like width, height or any other free input on the option)
      };

      /** @activeObject Object follow the current steps **/
      Object.values(this.activeObject).forEach((v, k) => {
        if (k > index - 1) {
          delete this.activeObject[k];
        }
      });

      /** Updating the current active object **/
      this.activeObject = Object.assign(this.activeObject, {
        [index - 1]: {
          index: index - 1,
          name: item.name,
          exclude: item.excludes,

          box_id: box.id,
          option_id: item.id,
          display_key: box.name,
          display_value: item.name,
          key: box.slug,
          value: item.slug,
          key_link: box.linked,
          value_link: item.linked,
        },
      });

      // clone the object
      const excl = { ...this.exclude };

      /** add the selected excludes to exclude object by box **/
      Object.values(this.activeObject).forEach((v) => {
        // add 'singles' & 'exclude with' object
        excl[item.slug] = { singles: [], with: [], exclude: [] };

        // add excludes
        if (v.exclude && v.exclude.filter((a) => a.length === 1).length > 0) {
          excl[item.slug].singles = _.cloneDeep(v.exclude);
        }

        // if excludes with other option combination: add the option itself
        if (v.exclude && v.exclude.filter((a) => a.length > 1).length > 0) {
          const withPocket = excl[item.slug].with;
          const exclPocket = excl[item.slug].exclude;

          // add all values to 'with object'
          for (let idx = 0; idx < v.exclude.length; idx++) {
            const excl = v.exclude[idx];

            withPocket.push(_.clone(excl));
          }

          withPocket.forEach((element) => {
            // add the item itself for better comparing
            element.unshift(item.id);

            // pop the excluded (last value)
            element.pop();
          });

          // add the exlcuded (last value) to excluded array
          for (let indx = 0; indx < v.exclude.length; indx++) {
            const element = v.exclude[indx];
            exclPocket.push(_.clone(element[element.length - 1]));
          }
        }
      });

      // reassign the object
      this.exclude = excl;

      this.$emit("itemSelected", this.collection);
    },
    checkExclude(item, divider) {
      /**
       * if item matches any exclude return false
       * to disable the option in the selection
       */

      if (!Array.isArray(item.excludes)) {
        this.handleError(
          this.$t("This category has problems, please delete this and create a new one"),
        );
        return true;
      }

      // current selection to compare to the combination
      const actives = [];
      let flag = false;

      // set active items in flat id's array
      if (divider) {
        for (let i = 0; i < this.collection.length; i++) {
          for (let j = 0; j < this.activeItems.length; j++) {
            if (
              this.collection[i].divider === divider &&
              this.collection[i].value === this.activeItems[j].slug
            ) {
              const active = this.activeItems[j];
              actives.push(active.id);
            }
          }
        }
      } else {
        for (let i = 0; i < this.activeItems.length; i++) {
          const active = this.activeItems[i];
          actives.push(active.id);
        }
      }

      item.excludes.forEach((exclude) => {
        if (exclude.every((singleExclude) => actives.includes(singleExclude))) {
          flag = true;
        }
      });

      // read the flag and exclude if flag is true
      if (flag) {
        return false;
      }

      // item does not match any excludes, so continue
      return true;
    },
    async menuItemClicked(event, option) {
      switch (event) {
        case "edit":
          await this.api.get(`/options?per_page=999999`).then((response) => {
            const opt = response.data.find((op) => op.slug === option.slug);
            if (opt) {
              this.set_item(opt);
              this.set_runs(opt.runs);
              this.set_flag("from_boops");
              this.component = true;
            }
          });
          break;

        default:
          break;
      }
    },
    getPrices: _.debounce(async function (qty) {
      this.isFetchingPrices = true;
      const product = {
        ...this.collection,
      };

      // make this work when landing direct on page and from category overview select
      const category = this.selected_category ?? this.category ?? "";

      // check if product is empty
      if (Object.keys(product).length === 0 && product.constructor === Object) {
        return navigateTo("/shop");
      }

      await this.calculation
        .get(category.slug, {
          type: "print",
          product: [...new Set(this.collection)],
          quantity: this.quantity,
          divided: this.boops[0].divided,
        })
        .then((response) => {
          this.prices = response;
          this.lastQty = this.quantity;
        })
        .catch((error) => {
          if (error.status === 404) {
            this.prices = [];
          }
          this.handleError(error);
          this.quantity = this.lastQty ? this.lastQty : this.qty;
        })
        .finally(() => {
          this.isFetchingPrices = false;
          this.hasPricesFetched = true;
        });
    }, 300),
    async getPriceList() {
      this.isFetchingPrices = true;

      const product = {
        ...this.collection,
      };

      // make this work when landing direct on page and from category overview select
      const category = this.selected_category ?? this.category ?? "";

      // check if product is empty
      if (Object.keys(product).length === 0 && product.constructor === Object) {
        return navigateTo("/shop");
      }

      await this.calculation
        .getList(category.slug, {
          type: "print",
          product: [...new Set(this.collection)],
          quantity: this.quantity,
          divided: this.boops[0].divided,
        })
        .then((response) => {
          this.priceList = response;
        })
        .catch((error) => {
          if (error.status === 404) {
            this.prices = [];
          }
          this.handleError(error);
        })
        .finally(() => {
          this.isFetchingPrices = false;
          this.hasPricesFetched = true;
        });
    },
    getImgUrl(src) {
      const newsrc = src.split(" ");
      for (let i = 0; i < newsrc.length; i++) {
        const imgsrc = newsrc[i];
        try {
          if (images[`/assets/images/assortments_portal/en/${imgsrc}.svg`]) {
            return images[`/assets/images/assortments_portal/en/${imgsrc}.svg`].default;
          }
        } catch (err) {
          console.error(err);
        }
      }
    },
    addToCart(calculation, product, legacy = false) {
      const calculationWithPrice = { ...calculation, mode: calculation.type }; // copy calculation object
      calculationWithPrice.price = this.selectedPrice; // add the price
      delete calculationWithPrice.prices; // remove the unnecesary prices

      // make this work when landing direct on page and from category overview select
      const category = this.selected_category ?? this.category ?? "";

      // CUSTOM
      if (this.type === "custom") {
        const products = [
          {
            id: null,
            product_type: "internal",
            variations: null,
            quantity: this.quantity,
          },
        ];

        if (legacy) {
          products[0].id = product.id;
          products[0].variations = [];
        } else {
          products[0].id = this.category[0];
          products[0].variations = product;
        }

        this.api.post("/cart", {
          products: products,
        });
      } else {
        // PRINT

        // DEBUG
        // console.log("calculation");
        // console.log(calculation);
        // console.log("product");
        // console.log(product);
        // console.log("calculationWithPrice");
        // console.log(calculationWithPrice);
        // console.log("selectedPrice");
        // console.log(this.selectedPrice);

        // OLD object
        const theProduct = {
          mode: "print",
          category_name: category.name,
          category_id: category.id,
          category_slug: category.slug,
          divided: this.boops[0].divided ?? false,
          product: [...new Set(this.collection)],
          price: this.selectedPrice,
          quantity: this.qty ?? this.quantity, // use qty from pricelist if available else use quantity from single price
        };
        const formData = this.objectToFormData(theProduct);

        // NEW object
        // const formData = objectToFormData(calculationWithPrice);

        this.api
          .post("/cart", formData, { isFormData: true })
          .then(() => navigateTo("/cart"))
          .catch((error) => this.handleError(error));
      }
    },
    handleAddToCartClick(calculation, activeItems) {
      const isLegacy = !!activeItems?.product?.id;
      this.addToCart(calculation, activeItems, isLegacy);
    },
  },
};
</script>
