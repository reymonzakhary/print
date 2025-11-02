<template>
  <div>
    <div class="flex flex-wrap">
      <div
        v-for="(product, i) in filteredProducts"
        :key="'product_' + i"
        class="w-full p-2 xl:w-1/2"
      >
        <div
          v-if="product.object && product.prices && product.prices.length > 0"
          class="group flex rounded border-r border-t bg-white hover:bg-gray-100 dark:border-gray-900 dark:bg-gray-700 dark:hover:bg-gray-900"
        >
          <div class="w-8/12 border-l border-r p-4 dark:border-gray-900 sm:block lg:w-6/12">
            <span class="divide-y">
              <p
                v-for="(option, i) in product.object"
                :key="'option_' + i"
                class="my-1 flex justify-between last:border-b-0"
              >
                <small
                  :class="
                    Object.values(selected_options).includes(option.value_link)
                      ? 'text-gray-500'
                      : ''
                  "
                >
                  {{ $display_name(option.display_key) }}
                </small>
                <small
                  :class="
                    Object.values(selected_options).includes(option.value_link)
                      ? 'text-gray-500'
                      : 'font-bold'
                  "
                >
                  {{ $display_name(option.display_value) }}
                </small>
              </p>
            </span>
          </div>

          <div class="w-4/12 p-4 text-right sm:w-6/12">
            <section v-for="(price, index) in product.prices" :key="'price' + price.id">
              <span v-if="index === 0" class="flex w-full flex-wrap">
                <span class="w-full md:w-1/2 md:text-left">
                  <p class="text-xs text-gray-700">Quantity</p>
                  <div class="text-sm font-bold">
                    {{ price.tables.qty }}
                  </div>
                </span>

                <span class="w-full md:w-1/2">
                  <p class="text-xs text-gray-700">Delivery</p>
                  <div class="text-sm font-semibold">
                    <font-awesome-icon :icon="['fal', 'turtle']" />
                    {{ price.tables.dlv.days }} day(s)
                  </div>
                </span>
              </span>

              <span class="flex items-center justify-between">
                <span v-if="index === 0" class="flex w-2/3 flex-wrap items-center px-2">
                  <!-- {{compare_suppliers}} -->
                  <span
                    v-for="supplier in compare_suppliers"
                    :key="supplier._id"
                    class="flex flex-col justify-between rounded bg-white"
                  >
                    <figure v-if="supplier.uuid === price.supplier_id" class="p-2">
                      <img :src="supplier.logo" :alt="supplier.name" class="w-12" />
                    </figure>

                    <p v-if="supplier.uuid === price.supplier_id" class="p-2 text-xs font-bold">
                      {{ supplier.name }}
                    </p>
                  </span>
                </span>

                <span class="w-full lg:w-1/3">
                  <p v-if="index === 0" class="mt-4 text-xs text-gray-700">
                    {{ $t("price") }}
                  </p>
                  <div v-if="index === 0" class="text-lg font-bold">
                    {{ price.tables.display_p }}
                  </div>
                </span>
              </span>

              <!-- show prices -->
              <button
                v-if="!show.includes(product.id) && index === 0"
                class="mt-2 rounded-full px-2 py-1 text-xs text-theme-500 transition-colors duration-75 hover:bg-theme-200 hover:text-theme-700 dark:text-theme-400"
                @click="show.push(product.id)"
              >
                {{ $t("show") }}
                {{ Object.keys(product.prices).length }}
                {{ $t("products") }}
              </button>

              <!-- hide prices -->
              <button
                v-if="show.includes(product.id) && index === 0"
                class="mt-2 rounded-full px-2 py-1 text-xs text-theme-500 transition-colors duration-75 hover:bg-theme-200 hover:text-theme-700 dark:text-theme-400"
                @click="
                  show = show.filter(function (item) {
                    return item !== product.id;
                  })
                "
              >
                {{ $t("hide") }}
                {{ Object.keys(product.prices).length }}
                {{ $t("products") }}
              </button>
              <!-- <button v-if="index === 0" class="px-2 py-1 mt-2 text-xs text-white bg-green-500 rounded-full shadow">Add product</button> -->
            </section>
          </div>
        </div>

        <transition name="slide">
          <div
            v-if="show.includes(product.id)"
            class="flex w-full flex-wrap items-center justify-start rounded-b bg-gray-300 shadow-inner dark:bg-gray-900"
          >
            <section v-for="price in product.prices" :key="'price_' + price.id" class="flex w-full">
              <span class="flex w-full flex-wrap items-center p-2">
                <span class="flex w-2/12 items-center px-2">
                  <span
                    v-for="supplier in compare_suppliers"
                    :key="'supplier' + supplier.uuid"
                    class="flex flex-col justify-between rounded bg-white"
                  >
                    <figure v-if="supplier.uuid === price.supplier_id && supplier.logo" class="p-2">
                      <img :src="'/img/' + supplier.logo" :alt="supplier.name" class="w-16" />
                    </figure>

                    <p
                      v-else-if="supplier.uuid === price.supplier_id"
                      class="p-2 text-xs font-bold"
                    >
                      {{ supplier.name }}
                    </p>
                  </span>
                </span>

                <span class="w-2/12 px-2">
                  <p class="text-xs text-gray-700">
                    {{ $t("quantity") }}
                  </p>
                  <div class="text-sm font-bold">
                    {{ price.tables.qty }}
                  </div>
                </span>

                <span class="w-3/12 px-2">
                  <p class="text-xs text-gray-700">
                    {{ $t("delivery") }}
                  </p>
                  <div class="text-sm">
                    <font-awesome-icon :icon="['fal', 'turtle']" />
                    <span class="font-semibold">
                      {{ price.tables.dlv.days }}
                    </span>
                    <span class="hidden md:inline-block">{{ $t("days") }}</span>
                  </div>
                </span>

                <span class="flex w-3/12 flex-col justify-between">
                  <p class="text-xs text-gray-700">Price</p>
                  <div class="text-lg font-bold">
                    {{ price.tables.display_p }}
                  </div>
                </span>
                <!-- {{product.category}} -->
                <span class="flex w-2/12 flex-col justify-between">
                  <!-- {{ compare_category._id }} -->
                  <!-- <button v-on:click="addProduct(product,price)" class="px-2 py-1 text-sm text-white bg-green-500 rounded-full shadow">Add</button> -->
                  <button
                    class="rounded bg-green-500 px-2 py-1 text-sm text-white shadow"
                    @click="addObj(product.category_id, product.object, price)"
                  >
                    {{
                      flag === "add_product"
                        ? `add to order #${order_id}`
                        : flag === "edit_product"
                          ? `replace item #${item_id} of order #${order_id}`
                          : "create order"
                    }}
                  </button>
                </span>
              </span>
            </section>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, useStore } from "vuex";
export default {
  props: {
    filteredProducts: {
      type: [Array, Function],
      required: true,
    },
    // eslint-disable-next-line vue/prop-name-casing
    compare_category: {
      type: Object,
      required: true,
    },
    // eslint-disable-next-line vue/prop-name-casing
    selected_options: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const api = useAPI();
    const store = useStore();
    const authStore = useAuthStore();
    return { api, store, authStore };
  },
  data() {
    return {
      show: [],
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
    };
  },
  computed: {
    ...mapState({
      flag: (state) => state.compare.flag,
      compare_suppliers: (state) => state.compare.compare_suppliers,
      order_id: (state) => state.orders.active_order,
      item_id: (state) => state.orders.active_order_item,
      active_order_data: (state) => state.orders.active_order_data,
      ordertype: (state) => state.orders.ordertype,
    }),
  },
  methods: {
    // getAssortImgUrl(src) {
    //    var images = require.context('assets/images/assortments_portal/', false, /\.svg$/)
    //    return images('./' + src + ".svg")
    // },
    currencyFormatter(number) {
      if (number) {
        return new Intl.NumberFormat(this.iso, {
          style: "currency",
          currency: this.currency,
        }).format(number);
      }

      return "";
    },
    addObj(id, boops, price) {
      const product = {
        product: {
          category_id: id,
          category_name: this.compare_category.name,
          category_slug: this.compare_category.slug,
          object: boops,
          prices: price,
        },
      };

      // add product to existing order/quotation
      if (this.flag === "add_product") {
        this.api.post(`${this.ordertype}s/${this.order_id}/items`, product);
        this.openDetail();
      }

      // replace product from existing order/quotation
      if (this.flag === "edit_product") {
        this.api.put(`${this.ordertype}s/${this.order_id}/items/${this.item_id}`, product);
        this.openDetail();
      }

      // create new order/quotation with the selected item
      if (this.flag === "compare") {
        this.api.post(`quotations`).then((response) => {
          this.api.post(`quotations/${response.data.id}/items/`, product);
          this.store.commit("orders/set_active_order", response.data.id); // set order id as active order

          // skip the openDetail function as VUEX store is too slow
          const nuxturl = `/orders/order-details?id=${response.data.id}&type=quotation`; // build url

          this.$router.push(nuxturl); // push url
          return;
        });
      }

      // open orderDetail page for current order/quotation
      // this.openDetail();
    },
    async openDetail() {
      const nuxturl = `/orders/order-details?id=${this.order_id}&type=${this.ordertype}`;
      this.$router.push(nuxturl); // push url
    },
  },
};
</script>
<style lang="scss" scoped>
.group {
  box-shadow:
    0 1px 15px rgba(0, 0, 0, 0.04),
    0 1px 6px rgba(0, 0, 0, 0.04),
    0px 5px 0px -1px #eceff1,
    /* 0 1px 15px rgba(0, 0, 0, 0.04), 
								      0 1px 6px rgba(0, 0, 0, 0.04), */ 0px 10px 0px -2px
      #cfd8dc,
    /* 0 1px 15px rgba(0, 0, 0, 0.04), 
								      0 1px 6px rgba(0, 0, 0, 0.04), */ 0px 15px 0px -4px
      #b0bec5;
  // 0 1px 15px rgba(0, 0, 0, 0.04),
  // 0 1px 6px rgba(0, 0, 0, 0.04) !important;
}

.mode-dark {
  .group {
    box-shadow:
      0 1px 15px rgba(0, 0, 0, 0.04),
      0 1px 6px rgba(0, 0, 0, 0.04),
      0px 5px 0px -1px #1a202c,
      /* 0 1px 15px rgba(0, 0, 0, 0.04), 
								            0 1px 6px rgba(0, 0, 0, 0.04), */ 0px 10px
        0px -2px #10141b,
      /* 0 1px 15px rgba(0, 0, 0, 0.04), 
								            0 1px 6px rgba(0, 0, 0, 0.04), */ 0px 15px
        0px -4px #000;
    // 0 1px 15px rgba(0, 0, 0, 0.04),
    // 0 1px 6px rgba(0, 0, 0, 0.04) !important;
  }
}
</style>
