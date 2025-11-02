<template>
  <div class="p-0 md:p-2 md:pr-4">
    <!-- <div class="flex items-center justify-between w-full">
      <p class="text-gray-500">{{ $t("select options and quantity") }}</p>
      <div class="flex">
        <button
          class="px-2 py-1 border border-r-0 rounded-l"
          :class="{
            'bg-theme-300 dark:bg-theme-300  text-themecontrast-300 border-theme-400 disabled hover:bg-theme-300':
              view === 'list',
            'bg-white dark:border-gray-900 dark:bg-gray-700 hover:bg-gray-200': view !== 'list',
          }"
          @click="set_view('list')"
        >
          <font-awesome-icon :icon="['fal', 'list']" class="fa-lg" />
        </button>
        <button
          class="px-2 py-1 border border-l-0 rounded-r"
          :class="{
            'bg-theme-300 dark:bg-theme-300 text-themecontrast-300 border-theme-400 disabled hover:bg-theme-300':
              view === 'columns',
            'bg-white dark:border-gray-900 dark:bg-gray-700 hover:bg-gray-200': view !== 'columns',
          }"
          @click="(active = []), set_view('columns')"
        >
          <font-awesome-icon :icon="['fal', 'line-columns']" class="fa-lg" />
        </button>
      </div>
    </div> -->

    <div class="sticky top-4 z-50 flex flex-wrap items-stretch bg-gray-100 dark:bg-gray-800">
      <!-- <label for="price-qty" class="w-full text-sm font-bold tracking-wide uppercase">
        {{ $t("quantity") }}:
      </label>
      <input
        :value="qty"
        type="number"
        name="price-qty"
        class="w-full p-1 text-sm rounded-none rounded-l shadow-md input lg:w-1/3"
        @input="$emit('updateQty', $event.target.value)"
        @keyup.enter="showPrice(), (animated = true)"
      />
      <button
        class="flex items-center justify-between w-full px-2 text-sm transition border rounded-r shadow-md lg:w-1/3 text-themecontrast-400 border-theme-600 bg-theme-400 hover:bg-theme-500"
        :class="{
          animate: animated,
        }"
        @click.prevent="showPrice(), (animated = true)"
        @animationend="animated = false"
      >
        <font-awesome-icon :icon="['fal', 'money-bill-wave']" />
        {{ $t("show prices") }}
        <font-awesome-icon :icon="['fal', 'chevron-right']" />
      </button> -->
      <div class="flex w-1/3 items-center pl-2 align-baseline text-sm text-orange-500">
        <p v-if="collection && collection.length !== boops.boops?.length">
          <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="mr-2" />
          {{ $t("make selection first") }}
        </p>
      </div>
    </div>

    <div v-if="boops === ''" class="px-5">
      <p class="text-xl font-bold text-gray-400">
        {{ $t("no boops") }}
      </p>
      <div class="my-8 flex items-start justify-center">
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
        <font-awesome-icon :icon="['fad', 'rectangle-list']" class="fa-5x my-4 text-gray-400" />
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
      </div>
    </div>

    <div
      v-else
      id="filterContainer"
      name="list"
      tag="nav"
      class="z-0 my-1 flex items-stretch scroll-smooth pb-4 text-sm"
      :class="{
        'w-full overflow-x-scroll': view === 'columns',
        'h-[550px] flex-wrap overflow-y-scroll rounded border': view === 'list',
      }"
    >
      <Boops
        class="h-full"
        :scroll-to-end="scrollToEnd"
        :show-view-prices-button="false"
        @collection-complete="prepareShowPrice($event)"
      />
    </div>

    <OptionsEditPanel v-if="component" :show-runs-panel="true" @on-close="component = false" />
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

/**
 * step one get object from boops service
 * collect the selected data
 * generate a md5 hash from it
 * post it to hash service
 * response with price and quant || add price and quant
 **/
export default {
  props: {
    qty: {
      type: Number,
      required: false,
      default: 100,
    },
  },
  emits: ["updateQty"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    return { permissions, api };
  },
  data() {
    return {
      collection: "",
      animated: false,
      component: false,
    };
  },
  computed: {
    ...mapState({
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      view: (state) => state.product.view,
    }),
  },
  watch: {
    category() {
      this.activeIndex = 0;
      this.getBoops();
    },
    collection(v) {
      return v;
    },
    boops(v) {
      return v;
    },
  },
  created() {
    this.getBoops();
    // if (this.permissions.includes("print-assortments-options-delete")) {
    // 	this.menuItems[0].items[1].show = true;
    // }
  },
  methods: {
    ...mapMutations({
      set_boops: "product/set_boops",
      set_view: "product/set_view",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_active_items: "product/set_active_items",
      // activate_details: "product/activate_details",
      // set_selected_option: "product/set_selected_option",
      set_price_collection: "product_wizard/set_price_collection",
      set_selected_option: "product_wizard/set_selected_option",

      set_item: "assortmentsettings/set_item",
      set_runs: "assortmentsettings/set_runs",
      set_flag: "assortmentsettings/set_flag",
    }),
    async getBoops() {
      if (this.category[1] !== undefined) {
        await this.api
          .get(`/categories/${this.category[1]}`)
          .then((response) => {
            this.set_boops(response.data.boops[0]);
          })
          .catch((err) => {
            this.handleError(err);
            this.set_boops("");
          });
      }

      this.set_loading_boops(false);
    },

    scrollToEnd() {
      const container = this.$el.querySelector("#filterContainer");
      container.scrollTo({
        left: container.scrollWidth,
        top: container.scrollHeight,
      });
    },

    prepareShowPrice(e) {
      this.collection = e;
      this.showPrice();
    },

    showPrice() {
      // the collection of options, seperated by '-' dashes
      // remove the last dash
      // let collection = this.collection.substring(
      // 	0,
      // 	this.collection.length - 1
      // );
      // create an md5 from it
      // let hash = md5(collection);
      // store it in the store
      const priceCollection = [...new Set(this.collection)];
      this.set_price_collection(priceCollection);

      // trigger price calculation
      this.$emit("updateQty", this.qty);

      // store the active options (same as the on from the collection, but with all the data instead of only hashed id's)
      // this.set_active_items(this.activeObject);
    },
  },
};
</script>

<style lang="scss" scoped>
.fade-transition {
  transition: opacity 0.4s ease;
}

.fade-enter,
.fade-leave {
  opacity: 0;
}

.animate {
  position: absolute;
  display: flex;
  width: auto;
  top: 30px;
  // @apply py-0;
  animation: send ease-in 500ms;
}

@keyframes send {
  0% {
    left: 50%;
    opacity: 1;
  }
  // 25%  {background-color:yellow; left:200px; top:0px;}
  // 50%  {background-color:blue; left:200px; top:200px;}
  // 75%  {background-color:green; left:0px; top:200px;}
  100% {
    left: 50vw;
    opacity: 0;
    transform: scaleY(5) scaleX(3);
    background: #fff;
  }
}
</style>
