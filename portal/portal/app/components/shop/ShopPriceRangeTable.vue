<template>
  <section>
    <!-- Price table -->
    <div
      class="mt-2 divide-y overflow-hidden rounded-lg bg-white shadow-md shadow-gray-200 dark:divide-gray-900 dark:bg-gray-700 dark:shadow-gray-900"
    >
      <header
        class="flex justify-between bg-theme-400 p-2 text-themecontrast-400 dark:bg-theme-600"
      >
        <div class="flex-1">quantity</div>
        <div
          v-for="dlv in [
            ...new Map(
              Object.values(shopPricesList.prices)
                .flat()
                .map((price) => [JSON.stringify(price.dlv), price.dlv]),
            ).values(),
          ]"
          :key="dlv.actual_days"
          class="flex-1 cursor-pointer select-none"
        >
          <span class="flex flex-1 flex-col">
            <span>
              <font-awesome-icon
                class
                :icon="['fal', dlv.days > 5 ? 'rabbit' : dlv.days < 3 ? 'dragon' : 'rabbit-fast']"
              />
              {{ dlv?.actual_days ?? "" }}
              {{ $t("days") }}
            </span>
            <small class="text-theme-100">
              {{ dlv.day_name }}
              {{ dlv.day }}
              {{ dlv.month }}
            </small>
          </span>
          <!-- <div class="">{{ dlv.actual_days }} days</div>
          <div class="">{{ dlv.day_name }} {{ dlv.day }} {{ dlv.month }}</div> -->
        </div>
      </header>
      <section
        v-for="qt in [
          ...new Set(
            Object.values(shopPricesList.prices)
              .flat()
              .map((price) => price.qty),
          ),
        ]"
        :key="qt"
        class="flex w-full items-center justify-between bg-white p-2 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-900"
      >
        <div class="flex-1">
          {{ qt }}
        </div>
        {{}}
        <template
          v-for="dlv in [
            ...new Set(
              Object.values(shopPricesList.prices)
                .flat()
                .map((price) => price.dlv.actual_days),
            ),
          ]"
          :key="`${qt}-${dlv}`"
        >
          <span class="flex-1">
            <UIButton
              v-if="reducedData[dlv].find((item) => item.qty === qt)"
              class="flex-1 !text-base"
              :class="{
                '!hover:bg-theme-500 !hover:text-theme-600 !bg-theme-500 !text-themecontrast-500':
                  selected === reducedData[dlv].find((item) => item.qty === qt),
              }"
              @click="
                ($emit('OnPriceSelect', {
                  dlv: reducedData[dlv].find((item) => item.qty === qt),
                  qty: qt,
                }),
                (selected = reducedData[dlv].find((item) => item.qty === qt)))
              "
            >
              {{ reducedData[dlv].find((item) => item.qty === qt)?.display_selling_price_ex ?? "" }}
            </UIButton>
          </span>
        </template>
      </section>
    </div>

    <!-- Free quantity entry for specific price -->
    <div
      class="mt-2 overflow-hidden rounded-lg bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
    >
      <header class="flex w-full items-center bg-theme-500 p-2">
        <UIInputText
          name="rquantity"
          class="mr-2 w-full"
          :prefix="$t('request another quantity')"
          :model-value="theQuantity"
          type="number"
          @input="(e) => handleQuantityChange(e.target.value)"
        />
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
        <div class="flex-1 cursor-pointer select-none">
          {{ $t("quantity") }}
        </div>
        <template v-for="(price, i) in shopPrices.prices" :key="'sorted_price_' + i">
          <p class="flex-1 cursor-pointer select-none">
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
              <small class="text-theme-100">
                {{ price.dlv.day_name }}
                {{ price.dlv.day }}
                {{ price.dlv.month }}
              </small>
            </span>
          </p>
        </template>
      </div>
      <div
        v-if="!loading && shopPrices?.prices?.length > 0"
        class="overflow-initial relative h-full"
      >
        <div
          class="group sticky top-0 flex h-full w-full cursor-pointer justify-between border-b p-2 transition duration-200 last:border-0 hover:bg-gray-200 dark:border-gray-900 dark:hover:bg-gray-900"
          :class="{
            'bg-theme-50 text-theme-500 hover:bg-theme-100 hover:text-theme-600 dark:bg-theme-900 dark:text-theme-200':
              selected === price,
            'shadow-md': priceInfo && i === shopPrices.prices.length - 1,
          }"
        >
          <!-- <div class="flex w-full items-center justify-between"> -->
          <span class="flex-1">
            {{ shopPrices.prices[0].qty }}
          </span>
          <template v-for="(price, i) in shopPrices.prices" :key="'sorted_price_' + i">
            <span class="flex-1">
              <UIButton
                class="flex-1 !text-base"
                :class="{
                  '!hover:bg-theme-500 !hover:text-theme-600 !bg-theme-500 !text-themecontrast-500':
                    selected === price,
                }"
                @click="($emit('OnPriceSelect', { dlv: price }), (selected = price))"
              >
                {{ price.display_selling_price_ex }}
              </UIButton>
            </span>
          </template>
          <!-- </div> -->
        </div>

        <!-- </template> -->
        <Teleport to="body">
          <ShopPriceCalculationInfo
            v-if="priceInfo"
            :calculations="shopPrices.calculation"
            @on-close="priceInfo = false"
          />
        </Teleport>
      </div>

      <div v-if="loading" class="w-full p-4 text-center text-2xl text-theme-500">
        <font-awesome-icon :icon="['fad', 'spinner-third']" spin />
      </div>
    </div>
  </section>
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
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
    shopPrices: {
      type: Object,
      required: true,
    },
    shopPricesList: {
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
      reducedData: {},

      // currency settings
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      selected: {},
      // loading: false,

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
      return this.quantity ? this.quantity : this.qty;
    },
  },
  watch: {
    loading: {
      handler(v) {
        return v;
      },
      immediate: true,
    },
    shopPricesList: {
      handler(newValue) {
        if (newValue && newValue.prices) {
          const list = Array.isArray(newValue.prices)
            ? newValue.prices
            : Object.values(newValue.prices || {}).flat();
          this.reducedData = list.reduce((acc, obj) => {
            const key = obj.dlv?.actual_days;
            if (key === undefined || key === null) {
              console.warn("Price entry missing dlv.actual_days:", obj);
              return acc;
            }
            if (!acc[key]) {
              acc[key] = [];
            }
            acc[key].push(obj);
            return acc;
          }, {});
        } else {
          this.reducedData = {};
        }
      },
      deep: true,
      immediate: true,
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
    },
    togglePriceInfo() {
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
