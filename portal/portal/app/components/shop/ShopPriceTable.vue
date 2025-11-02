<template>
  <div
    class="mt-2 overflow-hidden rounded-lg bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
  >
    <header class="flex w-full items-center bg-theme-500 p-2">
      <div class="relative w-full">
        <div
          v-if="isCallApi"
          class="absolute z-50 flex h-full w-full cursor-not-allowed items-center justify-center rounded bg-gray-200/50 text-theme-500"
        >
          <font-awesome-icon :icon="['fad', 'spinner-third']" spin />
        </div>
        <UIInputText
          name="quantity"
          class="mr-2 w-full"
          :prefix="$t('quantity')"
          :model-value="theQuantity"
          type="number"
          :disabled="isCallApi"
          @input="(e) => handleQuantityChange(e.target.value)"
        />
      </div>
      <UIButton
        v-if="!isShop && selected_category?.price_build?.full_calculation"
        :icon="['fal', 'circle-info']"
        variant="neutral-light"
        icon-placement="right"
        @click="togglePriceInfo"
      >
        {{ $t("check price info") }}
      </UIButton>
    </header>

    <div
      v-if="shopPrices"
      class="flex w-full justify-between bg-theme-400 p-2 text-themecontrast-400 dark:bg-theme-600"
    >
      <p class="flex-1 cursor-pointer select-none">
        {{ $t("delivery") }}
      </p>
      <p class="flex-1 cursor-pointer select-none">
        {{ $t("quantity") }}
      </p>
      <p class="flex-1 cursor-pointer select-none font-bold">
        {{ $t("price") }}
      </p>

      <p v-if="!isShop" class="flex-1 cursor-pointer select-none text-right">
        {{ $t("actions") }}
      </p>
    </div>
    <div v-if="!loading && shopPrices?.prices?.length > 0" class="overflow-initial relative h-full">
      <template v-for="(price, i) in shopPrices.prices" :key="'sorted_price_' + i">
        <div
          class="group sticky top-0 flex h-full w-full cursor-pointer justify-between border-b p-2 transition duration-200 last:border-0 hover:bg-gray-200 dark:border-gray-900 dark:hover:bg-gray-900"
          :class="{
            'bg-theme-50 text-theme-500 hover:bg-theme-100 hover:text-theme-600 dark:bg-theme-900 dark:text-theme-200':
              selected === price,
            'shadow-md': priceInfo && i === shopPrices.prices.length - 1,
          }"
          @click="($emit('OnPriceSelect', price), (selected = price))"
        >
          <div class="flex w-full items-center justify-between">
            <span class="flex flex-1 flex-col">
              <span>
                <font-awesome-icon
                  class
                  :icon="[
                    'fal',
                    price.dlv.days > 5 ? 'rabbit' : price.dlv.days < 3 ? 'dragon' : 'rabbit-fast',
                  ]"
                />
                {{ price.dlv?.actual_days ?? "" }}
                {{ $t("days") }}
              </span>
              <small class="text-gray-600 dark:text-gray-400">
                {{ price.dlv.day_name }}
                {{ price.dlv.day }}
                {{ price.dlv.month }}
              </small>
            </span>

            <span class="flex-1">
              {{ price.qty }}
            </span>

            <span class="flex-1">
              <span>
                {{ price.display_selling_price_ex }}
              </span>
            </span>

            <span v-if="!isShop" class="flex flex-1 justify-end">
              <button
                v-if="flag === 'compare' && selected === price"
                class="mx-1 rounded bg-green-500 px-2 py-1 text-sm text-white shadow transition hover:bg-green-600"
                @click="addObj({ calculation: shopPrices, price }, 'quotations')"
              >
                <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
                {{ $t("quoatation") }}
              </button>
              <button
                v-if="flag === 'compare' && selected === price"
                class="mx-1 rounded bg-green-500 px-2 py-1 text-sm text-white shadow transition hover:bg-green-600"
                @click="addObj({ calculation: shopPrices, price }, 'orders')"
              >
                <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
                {{ $t("order") }}
              </button>

              <button
                v-if="flag && flag !== 'compare' && selected === price"
                class="mx-1 rounded bg-green-500 px-2 py-1 text-sm text-white shadow transition hover:bg-green-600"
                @click="
                  addObj(
                    { calculation: shopPrices, price },
                    ordertype === 'order' ? 'orders' : 'quotations',
                  )
                "
              >
                <font-awesome-icon v-if="flag === 'compare'" class :icon="['fal', 'plus']" />
                {{
                  flag === "add_product"
                    ? // prettier-ignore
                      $t(`add to {ordertype} #{order_id}`, { ordertype: ordertype == "order" ? $t("order") : $t("quotation"), order_id,})
                    : flag === "edit_product"
                      ? // prettier-ignore
                        $t("replace item {item_id} of {ordertype} #{order_id}", {ordertype: ordertype == "order" ? $t("order") : $t("quotation"), item_id, order_id,})
                      : $t("order")
                }}
              </button>
            </span>
          </div>
        </div>
      </template>

      <Teleport to="body">
        <ShopPriceCalculationInfo
          v-if="priceInfo"
          :calculations="shopPrices.calculation"
          @on-close="priceInfo = false"
        />
      </Teleport>
    </div>

    <div v-else-if="!loading && shopPrices?.prices?.length === 0" class="my-4 text-center">
      <span class="italic text-gray-500 dark:text-gray-400"
        >{{ $t("no prices available") }} <font-awesome-icon class :icon="['fal', 'sad-tear']" />
      </span>
      <br />
      <font-awesome-icon class="mt-4" :icon="['fal', 'circle-info']" />
      {{
        selected_category?.price_build?.full_calculation
          ? $t("full calculation")
          : selected_category?.price_build?.semi_calculation
            ? $t("semi calculation")
            : ""
      }}
    </div>

    <div v-else class="w-full p-4 text-center text-2xl text-theme-500">
      <font-awesome-icon :icon="['fad', 'spinner-third']" spin />
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, useStore } from "vuex";

export default {
  props: {
    isShop: {
      type: Boolean,
      required: false,
      default: false,
    },
    isCallApi: {
      type: Boolean,
      required: false,
      default: false,
    },
    shopPrices: {
      type: Object,
      required: true,
    },
    qty: {
      type: Number,
      default: 100,
      required: false,
    },
    quantity: {
      type: Number,
      required: false,
      default: 100,
    },
    discounts: {
      type: Object,
      required: false,
      default: () => {},
    },
    producer: {
      type: Object,
      required: false,
      default: () => {},
    },
  },
  emits: ["OnPriceSelect", "updateQty", "updatePrices", "onQuantityChange"],
  setup() {
    const api = useAPI();
    const store = useStore();
    const quotationRepository = useQuotationRepository();
    const orderRepository = useOrderRepository();
    const { handleError, handleSuccess } = useMessageHandler();
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    return {
      api,
      store,
      handleError,
      handleSuccess,
      addToast,
      quotationRepository,
      orderRepository,
      authStore,
    };
  },
  data() {
    return {
      // displayPrices: this.shopPrices,
      // view settings
      view_settings: {
        selling_price: {
          name: "Selling price",
          value: true,
        },
      },
      colspan: 2,
      sort: "Selling_price",
      desc: false,

      comparePrices: [],

      // currency settings
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      selected: {},
      loading: false,

      priceInfo: false,
    };
  },
  computed: {
    ...mapState({
      price_collection: (state) => state.product_wizard.price_collection,
      // for adding product to order
      flag: (state) => state.compare.flag,
      order_id: (state) => state.orders.active_order,
      item_id: (state) => state.orders.active_order_item,
      collection: (state) => state.product.collection,
      selected_category: (state) => state.product_wizard.selected_category,
      ordertype: (state) => state.orders.ordertype,
      active_items: (state) => state.product.active_items,
    }),
    theQuantity() {
      return this.qty;
    },
  },
  watch: {
    shopPrices() {
      this.loading = false;
    },
  },
  beforeUnmount() {
    this.$emit("updatePrices", []);
    this.comparePrices = [];
  },
  methods: {
    ...mapMutations({
      set_active_collection: "product/set_active_collection",
      set_flag: "compare/set_flag",
    }),
    handleQuantityChange(quantity) {
      this.$emit("updateQty", quantity);
      this.$emit("onQuantityChange", quantity);
      this.loading = true;
    },
    togglePriceInfo(id) {
      this.priceInfo = !this.priceInfo;
      // !priceInfo ? (priceInfo = i) : (priceInfo = false)
      // if (this.priceInfo === id) {
      //   this.priceInfo = false;
      // } else {
      //   this.priceInfo = id;
      // }
    },
    currencyFormatter(number) {
      if (number) {
        return new Intl.NumberFormat(this.iso, {
          style: "currency",
          currency: this.currency,
        }).format(number);
      }

      return "";
    },
    updateColSpan() {
      let i = 0;
      for (const key in this.view_settings) {
        if (Object.prototype.hasOwnProperty.call(this.view_settings, key)) {
          const element = this.view_settings[key];
          if (element.value === true) {
            i++;
          }
        }
      }
      this.colspan = i;
    },
    comparePrice(object) {
      const payload = object;
      const source = {
        collection: this.price_collection,
      };

      Object.assign(payload, source);

      if (this.comparePrices.includes(payload)) {
        const index = this.comparePrices.indexOf(payload);
        this.comparePrices.splice(index, 1);
      } else {
        this.comparePrices.push(payload);
      }
    },
    async addObj({ calculation, price }, type) {
      const calculationWithPrice = { ...calculation }; // copy calculation object
      calculationWithPrice.price = price; // add the price
      delete calculationWithPrice.prices; // remove the unnecesary prices

      // add product to existing order/quotation
      if (this.flag === "add_product") {
        try {
          if (type === "orders") {
            await this.orderRepository.addItemToOrder(this.order_id, calculationWithPrice);
          } else {
            await this.quotationRepository.addItemToQuotation(this.order_id, calculationWithPrice);
          }
          // skip the openDetail function as VUEX store is too slow
          this.store.commit("orders/set_active_order", this.order_id); // set order id as active order

          let nuxturl;
          if (type === "orders") {
            nuxturl = `/orders/${this.order_id}`;
          } else if (type === "quotations") {
            nuxturl = `/quotations/${this.order_id}`;
          }

          this.addToast({
            type: "success",
            message: this.$t("product succesfully added to {ordertype}", {
              ordertype: type === "orders" ? this.$t("order") : this.$t("quotation"),
            }),
          });
          navigateTo(nuxturl);
          return this.set_flag("compare");
        } catch (error) {
          this.handleError(error);
        }
      }

      // replace product from existing order/quotation
      if (this.flag === "edit_product") {
        if (this.ordertype === "order") {
          await this.orderRepository.updateItem({
            orderId: this.order_id,
            itemId: this.item_id,
            data: calculationWithPrice,
          });
        } else {
          await this.quotationRepository.updateItem({
            quotationId: this.order_id,
            itemId: this.item_id,
            data: calculationWithPrice,
          });
        }
      }

      // create new order/quotation with the selected item
      if (this.flag === "compare") {
        try {
          let salesRepository;
          if (type === "orders") {
            salesRepository = this.orderRepository;
          } else {
            salesRepository = this.quotationRepository;
          }
          const data = await salesRepository.create();
          await salesRepository.addItem(data.id, calculationWithPrice);

          this.addToast({
            type: "success",
            message: this.$t("order succesfully created with product"),
          });
          this.store.commit("orders/set_active_order", data.id); // set order id as active order

          let nuxt_url;
          //  skip the openDetail function as VUEX store is too slow
          if (type === "orders") {
            nuxt_url = `/orders/${data.id}`;
          } else {
            nuxt_url = `/quotations/${data.id}`;
          }
          return this.$router.push(nuxt_url); // push url
        } catch (error) {
          this.handleError(error);
        }
      }
      // open orderDetail page for current order/quotation
      this.openDetail();
    },
    async openDetail() {
      let nuxturl;
      if (this.ordertype === "order") {
        nuxturl = `/orders/${this.order_id}`;
      } else if (this.ordertype === "quotation") {
        nuxturl = `/quotations/${this.order_id}`;
      }
      this.$router.push(nuxturl); // push url
    },
  },
};
</script>

<style>
.end-of-table {
  border: 1px solid black !important;
}
</style>
