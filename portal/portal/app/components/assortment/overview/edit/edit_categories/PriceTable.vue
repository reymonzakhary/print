<template>
  <div class="mt-2 rounded-lg bg-white shadow-md dark:bg-gray-700">
    <header class="flex w-full items-center bg-theme-500 p-2">
      <UIInputText
        name="quantity"
        class="mr-2 w-full"
        :prefix="$t('quantity')"
        :model-value="qty ?? 100"
        :placeholder="$t('desired quantity')"
        type="number"
        min="1"
        @input="(e) => handleQuantityChange(e.target.value)"
      />
      <UIButton
        v-if="prices.calculation_type == 'full_calculation'"
        :icon="['fal', 'circle-info']"
        variant="neutral-light"
        icon-placement="right"
        @click="priceInfo = !priceInfo"
      >
        {{ $t("check price info") }}
      </UIButton>
    </header>

    <div
      v-if="prices"
      class="grid justify-between bg-theme-400 p-2 text-themecontrast-400 dark:bg-theme-600 md:grid-cols-6"
    >
      <p
        class="flex-1 cursor-pointer select-none"
        :class="{ 'font-bold': sort === 'dlv' }"
        @click="((sort = 'dlv'), (desc = !desc))"
      >
        {{ $t("delivery") }}
        <font-awesome-icon v-if="sort === 'dlv'" :icon="['fad', desc ? 'sort-down' : 'sort-up']" />
      </p>

      <p
        class="flex-1 cursor-pointer select-none"
        :class="{ 'font-bold': sort === 'qty' }"
        @click="((sort = 'qty'), (desc = !desc))"
      >
        {{ $t("quantity") }}
        <font-awesome-icon v-if="sort === 'qty'" :icon="['fad', desc ? 'sort-down' : 'sort-up']" />
      </p>

      <div
        v-for="(setting, key) in view_settings"
        :key="setting.id"
        class="flex flex-1 items-center justify-between"
      >
        <div class="flex">
          <span class="mr-2 flex self-center rounded-sm border border-theme-200">
            <input
              :id="key"
              v-model="setting.value"
              type="checkbox"
              :name="key"
              @change="updateColSpan()"
            />
          </span>
          <p
            v-tooltip="
              setting.name === 'Selling price'
                ? `gross price + margin`
                : setting.name === 'Buying price'
                  ? 'gross_price - discount'
                  : ''
            "
            class="cursor-pointer select-none whitespace-nowrap"
            :class="{
              'font-bold': sort === setting.name.replace(' ', '_'),
            }"
            @click="((sort = setting.name.replace(' ', '_')), (desc = !desc))"
          >
            {{ setting.name }}
            <font-awesome-icon
              v-if="sort === setting.name.replace(' ', '_')"
              :icon="['fad', desc ? 'sort-down' : 'sort-up']"
            />
          </p>
        </div>
      </div>

      <p class="flex-1 cursor-pointer select-none text-right">
        {{ $t("actions") }}
      </p>
    </div>

    <div class="my-4">
      <p v-if="comparePrices.length > 0" class="pl-2 text-sm font-bold uppercase tracking-wide">
        {{ $t("compare") }} {{ $t("prices") }}
      </p>
      <!-- {{ comparePrices }} -->
      <template v-for="(price, i) in comparePrices" :key="'compare_price_' + i">
        <!-- {{ price }} -->
        <div
          class="flex w-full flex-wrap justify-between border-b border-theme-300 bg-theme-100 p-2 last:border-b-2 hover:bg-theme-200 dark:bg-theme-700"
        >
          <div class="grid w-full grid-cols-6 items-center justify-between">
            <div class="flex flex-1 flex-col truncate pr-4">
              <span>
                <font-awesome-icon
                  class
                  :icon="[
                    'fal',
                    price.dlv.days > 5 ? 'rabbit' : price.dlv.days < 3 ? 'dragon' : 'rabbit-fast',
                  ]"
                />
                {{ price.dlv.actual_days }}
                {{ $t("days") }}
              </span>
              <small
                v-tooltip="
                  `${price.dlv.day_name} ${price.dlv.day} ${price.dlv.month} ${price.dlv.year}`
                "
                class="truncate text-gray-600"
              >
                {{ price.dlv.day_name }}
                {{ price.dlv.day }}
                {{ price.dlv.month }}
                {{ price.dlv.year }}
              </small>
            </div>

            <span class="flex-1">{{ price.qty }}</span>

            <span class="flex-1 font-mono">
              <span v-if="view_settings.gross_price.value">
                {{ price.display_gross_price }}
              </span>
            </span>

            <!-- <span class="flex-1 font-mono">
              <span v-if="view_settings.buying_price.value">
                {{ price.display_buying_price }}
                <small
                  v-if="price.discount.value"
                  v-tooltip="`discount`"
                  class="px-1 text-white bg-orange-500 rounded-full"
                >
                  -{{ price.discount.value }}
                  {{ price.discount.type === "percentage" ? "%" : iso.currency }}
                </small>
              </span>
            </span> -->

            <span class="flex flex-1 items-center font-mono">
              <span v-if="view_settings.selling_price.value" class="flex">
                {{ price.display_selling_price_ex }}
              </span>
            </span>
            <span class="flex-1 font-mono">
              <span v-if="view_settings.profit.value" class="flex text-green-600">
                {{ price.display_profit }}
                <small
                  v-if="price.margins.value"
                  v-tooltip="`margin`"
                  class="mx-1 flex items-center whitespace-nowrap rounded-full bg-green-500 px-1 text-white"
                >
                  +{{ price.margins.value }}
                  {{ price.margins.type === "percentage" ? "%" : iso.currency }}
                </small>
              </span>
            </span>
            <span class="flex flex-1 justify-end">
              <span
                v-tooltip
                class="h-6 w-6 cursor-pointer rounded-full text-center text-red-500 hover:bg-red-200"
                @click="comparePrice(price)"
              >
                <font-awesome-icon :icon="['fal', 'not-equal']" />
              </span>
            </span>

            <!-- <VDropdown>
              <button class="flex-1 text-theme-500 dark:text-theme-300">
                <font-awesome-icon :icon="['fad', 'circle-info']" />
              </button>

              <template #popper>
                <div class="flex justify-between items-center p-2 w-full text-sm">
                  <div v-for="(option, box) in price.collection" :key="option" class="flex-1">
                    <div class="font-bold">{{ box }}</div>
                    {{ option }}
                  </div>
                </div>
              </template>
            </VDropdown> -->
          </div>
        </div>
      </template>
    </div>

    <div v-if="sortedPrice.length > 0 && !loading">
      <template v-for="(price, i) in sortedPrice" :key="'sorted_price_' + i">
        <div
          class="group flex w-full grid-cols-7 justify-between border-b p-2 last:border-0 hover:bg-gray-200 dark:border-gray-900 dark:hover:bg-gray-900"
          :class="{ 'shadow-md': priceInfo && i === sortedPrice.length - 1 }"
          @click="$emit('OnPriceSelect', $event)"
        >
          <div class="grid w-full grid-cols-6 items-center justify-between">
            <div class="flex flex-1 flex-col truncate pr-4">
              <span>
                <font-awesome-icon
                  class
                  :icon="[
                    'fal',
                    price.dlv.days > 5 ? 'rabbit' : price.dlv.days < 3 ? 'dragon' : 'rabbit-fast',
                  ]"
                />
                {{ price.dlv.actual_days }}
                {{ $t("days") }}
              </span>
              <small
                v-tooltip="
                  `${price.dlv.day_name} ${price.dlv.day} ${price.dlv.month} ${price.dlv.year}`
                "
                class="truncate text-gray-600"
              >
                {{ price.dlv.day_name }}
                {{ price.dlv.day }}
                {{ price.dlv.month }}
                {{ price.dlv.year }}
              </small>
            </div>

            <span class="flex-1">
              {{ price.qty }}
            </span>

            <span class="flex-1 font-mono">
              <span v-if="view_settings.gross_price.value">
                {{ price.display_gross_price }}
              </span>
            </span>

            <!-- <span class="flex-1 font-mono">
              <span v-if="view_settings.buying_price.value">
                {{ price.display_buying_price }}
                <small
                  v-if="price.discount.value"
                  v-tooltip="`discount`"
                  class="px-1 text-white bg-orange-500 rounded-full"
                >
                  -{{ price.discount.value }}
                  {{ price.discount.type === "percentage" ? "%" : iso.currency }}
                </small>
              </span>
            </span> -->

            <div class="flex-1 font-mono">
              <div v-if="view_settings.selling_price.value">
                <div class="flex">
                  <b class="flex-1 whitespace-nowrap">{{ price.display_selling_price_ex }}</b>
                </div>
                <!--  <div class="flex text-xs font-bold tracking-tighter text-gray-500 uppercase">
                <div class="flex-1">ex vat</div>
                <div class="flex-1 mx-4">vat</div>
                <div class="flex-1">inc vat</div>
              </div>
              <div class="flex">
                <div class="flex-1">{{ price.display_selling_price_ex }}</div>
                <span class="mx-2 text-gray-500">
                  {{ price.display_vat_p }}
                </span>
                <b class="flex-1">{{ price.display_selling_price_inc }}</b>
                <small
                  v-if="price.margins.value"
                  v-tooltip="`margin`"
                  class="px-1 text-white bg-green-500 rounded-full"
                >
                  +{{ price.margins.value }}
                  {{ price.margins.type === "percentage" ? "%" : iso.currency }}
                </small>
              </div> -->
              </div>
            </div>

            <!-- <span class="flex-1"> </span> -->

            <span class="flex-1 font-mono">
              <span
                v-if="view_settings.profit.value"
                class="flex whitespace-nowrap font-bold text-green-600"
              >
                {{ price.display_profit }}
                <small
                  v-if="price.margins.value"
                  v-tooltip="`margin`"
                  class="mx-1 flex items-center whitespace-nowrap rounded-full bg-green-500 px-1 text-white"
                >
                  +
                  {{ price.margins.display_value }}
                  {{ price.margins.type === "percentage" ? "%" : "" }}
                </small>
              </span>
            </span>

            <span class="flex flex-1 justify-end">
              <span
                v-if="!comparePrices.includes(price)"
                v-tooltip="'Add to compare'"
                class="invisible h-6 w-6 cursor-pointer rounded-full border border-theme-200 text-center text-theme-500 hover:bg-theme-200 group-hover:visible"
                @click="comparePrice(price)"
              >
                <font-awesome-icon :icon="['fal', 'equals']" />
              </span>
              <span v-else class="invisible text-center text-sm text-gray-600 group-hover:visible">
                Already in compare
              </span>
            </span>
          </div>
        </div>
      </template>
    </div>

    <div v-else-if="loading" class="w-full p-4 text-center text-2xl text-theme-500">
      <font-awesome-icon :icon="['fad', 'spinner-third']" spin />
    </div>

    <div
      v-else
      class="flex w-full items-center p-2 text-center align-baseline text-sm text-amber-500"
    >
      <p>
        <font-awesome-icon :icon="['fal', 'arrow-left']" class="mr-2" />
        <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="mr-2" />
        {{ $t("make selection first") }}
      </p>
    </div>

    <Teleport to="body">
      <ShopPriceCalculationInfo
        v-if="priceInfo"
        :calculations="prices.calculation"
        :price-info="priceInfo"
        @on-close="priceInfo = false"
      />
    </Teleport>
  </div>
</template>

<script>
import _ from "lodash";
import { mapState } from "vuex";

export default {
  //
  props: {
    qty: {
      type: Number,
      required: false,
      default: 100,
    },
    prices: {
      type: Object,
      required: false,
      default: () => {},
    },
    discounts: {
      type: Object,
      requiered: true,
      default: null,
    },
    producer: {
      type: Object,
      required: false,
      default: null,
    },
  },
  emits: ["updateQty", "onQuantityChange", "OnPriceSelect", "clearPrices", "update-qty"],
  setup() {
    const authStore = useAuthStore();
    const { addToast } = useToastStore();
    return {
      addToast,
      authStore,
    };
  },
  data() {
    return {
      // displayPrices: this.prices,
      // view settings
      view_settings: {
        gross_price: {
          name: "Gross price",
          value: true,
        },
        // buying_price: {
        //   name: "Buying price",
        //   value: true,
        // },
        selling_price: {
          name: "Selling price",
          value: true,
        },
        profit: {
          name: "Profit",
          value: true,
        },
      },
      colspan: 2,
      sort: "Selling_price",
      desc: false,
      loading: false,

      comparePrices: [],

      priceInfo: false,

      // currency settings
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
    };
  },
  computed: {
    ...mapState({
      // price_collection: (state) => state.product_wizard.price_collection,
      // for adding product to order
      flag: (state) => state.compare.flag,
      order_id: (state) => state.orders.active_order,
      item_id: (state) => state.orders.active_order_item,
      // collection: (state) => state.product.collection,
      selected_category: (state) => state.product_wizard.selected_category,
      ordertype: (state) => state.orders.ordertype,
    }),
    sortedPrice() {
      const sort = "tables." + this.sort.toLowerCase();
      const direction = this.desc ? "desc" : "asc";
      const newprices = _.orderBy(this.prices.prices, sort, direction);
      return newprices;
    },
  },
  watch: {
    prices: {
      deep: true,
      handler(v) {
        // console.log("watched price: ");
        // console.log(v);
        this.loading = false;
        return v;
      },
    },
  },
  beforeUnmount() {
    this.$emit("clearPrices");
    this.comparePrices = [];
  },
  methods: {
    handleQuantityChange(quantity) {
      if (quantity > 0) {
        this.$emit("update-qty", quantity);
        this.$emit("onQuantityChange", quantity);
        this.loading = true;
      } else {
        this.addToast({
          type: "info",
          message: this.$t("quantity should be greater than 0"),
        });
      }
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
      // const source = {
      //   collection: { ...this.price_collection },
      // };

      // Object.assign(payload, source);

      if (this.comparePrices.includes(payload)) {
        const index = this.comparePrices.indexOf(payload);
        this.comparePrices.splice(index, 1);
      } else {
        this.comparePrices.push(payload);
      }
    },
  },
};
</script>

<style>
.end-of-table {
  border: 1px solid black !important;
}
</style>
