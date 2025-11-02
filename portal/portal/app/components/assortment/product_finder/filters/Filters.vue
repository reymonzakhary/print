<template>
  <div class="w-full">
    <p
      class="sticky top-0 block py-2 pl-4 mr-1 bg-gray-100 rounded-tl-lg dark:bg-gray-800"
      style="z-index: 31"
    >
      <button
        v-if="flag === 'add_product' || flag === 'edit_product'"
        class="mr-8 font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
        @click.stop.prevent="openDetail(store.state.orders.active_order)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
        <font-awesome-icon :icon="['fal', 'file-invoice-dollar']" class="mr-1" />
        {{ $t("back to order") }}
      </button>

      <button class="text-theme-500" @click="back()">
        <font-awesome-icon :icon="['fal', 'chevron-left']" />
        <font-awesome-icon :icon="['fal', 'table-cells']" />
        {{ $t("back") }}
      </button>

      <button
        class="px-2 py-1 m-2 rounded-full text-themecontrast-400 bg-theme-400 lg:hidden"
        @click="showFilters = !showFilters"
      >
        <font-awesome-icon :icon="['fal', 'filter']" />
        {{ $t("toggle filters") }}
      </button>
      <!-- Filter products -->
    </p>

    <div
      class="top-0 flex-wrap lg:flex lg:px-2"
      :class="
        showFilters === false
          ? 'hidden'
          : 'fixed flex z-50 mt-24 bg-white dark:bg-gray-700 h-full overflow-scroll pb-5'
      "
    >
      <section class="w-full pr-4 border-b dark:border-black sm:w-1/3 lg:w-full">
        <RangeSlider />
      </section>

      <section class="w-full pr-1 sm:w-1/3 lg:w-full">
        <label class="px-3 text-xs font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'truck']" class="mr-1" />
          {{ $t("delivery") }}
          <!-- <b class="ml-2 text-base">{{dlv[0]}} - {{dlv[1]}}</b> -->
        </label>

        <div class="flex flex-wrap px-2 border-b dark:border-black">
          <span class="relative flex items-center w-full lg:w-1/2">
            <input
              v-model.number="dlv[0]"
              type="number"
              class="w-full p-1 my-1 text-sm bg-white border rounded dark:border-black dark:bg-gray-700 lg:mr-2 focus:outline-none focus:ring focus:border-theme-300"
            />
            <font-awesome-icon
              :icon="['fal', 'rabbit-fast']"
              class="absolute top-0 right-0 mt-3 mr-4 text-gray-600"
            />
          </span>
          <span class="relative flex items-center w-full lg:w-1/2">
            <input
              v-model.number="dlv[1]"
              type="number"
              class="w-full p-1 my-1 text-sm bg-white border rounded dark:border-black dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            />
            <font-awesome-icon
              :icon="['fal', 'turtle']"
              class="absolute top-0 right-0 mt-3 mr-4 text-gray-600"
            />
          </span>
        </div>
      </section>

      <section class="w-full pr-4 sm:w-1/3 lg:w-full">
        <label class="px-3 text-xs font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'parachute-box']" class="mr-1" />
          {{ $t("producers") }}
          <!-- <b class="ml-2 text-base">{{dlv[0]}} - {{dlv[1]}}</b> -->
        </label>

        <div class="flex flex-wrap border-b dark:border-black">
          <!-- {{suppliers}} -->
          <div
            v-for="(supplier, idex) in suppliers"
            :key="'supplier_' + idex"
            class="flex items-center"
          >
            <label
              class="flex items-start p-2 m-2 text-sm bg-white border rounded cursor-pointer dark:border-black dark:bg-gray-700"
            >
              <div
                class="flex items-center justify-center flex-shrink-0 w-3 h-3 mr-2 bg-white border border-gray-400 rounded-sm dark:border-black dark:bg-gray-700 focus-within:border-theme-500"
              >
                <input
                  type="checkbox"
                  :value="supplier.uuid"
                  class="absolute opacity-0"
                  @change="addSupplier($event.target.checked, $event.target.value)"
                />
                <svg
                  class="hidden w-4 h-4 pointer-events-none fill-current text-theme-500 dark:text-theme-400"
                  viewBox="0 0 20 20"
                >
                  <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                </svg>
              </div>

              <div class="select-none">
                <img :src="`${supplier.logo}`" class="w-auto h-8" :alt="supplier.name" />
                {{ supplier.name }}
              </div>
            </label>
          </div>
        </div>
      </section>

      <transition-group name="fade" tag="div" class="flex flex-wrap h-full">
        <div
          v-for="(box, i) in compareBoops"
          :key="'filter' + i"
          class="w-1/2 py-2 pl-2 sm:w-1/4 lg:w-full xl:w-1/2"
        >
          <div class="h-full p-2 border-b dark:border-black">
            <p class="text-xs font-bold tracking-wide uppercase">
              {{ $display_name(box.display_name) }}
            </p>

            <!-- <p class="relative flex">
                     <input class="w-full px-2 pr-6 bg-white border rounded" type="text" placeholder="Filter..." v-model="search[i]">
                     <span class="absolute right-0 mr-2 text-gray-600">
                        <font-awesome-icon :icon="['fal', 'filter']" />
                     </span>
                  </p> -->

            <div class="field">
              <div v-for="(option, idx) in box.ops" :key="idx" class="flex items-center my-1">
                <label
                  v-if="option.name.toLowerCase().includes(search[i])"
                  class="flex items-center text-sm"
                  :class="[
                    {
                      'text-theme-600 dark:text-theme-400 font-bold':
                        selected[box.id] && selected[box.id].includes(option.id),
                    },
                  ]"
                >
                  <div
                    class="flex items-center justify-center flex-shrink-0 w-3 h-3 mr-2 bg-white border border-gray-400 rounded-sm dark:border-black dark:bg-gray-700 focus-within:border-theme-500"
                  >
                    <input
                      type="checkbox"
                      :value="option.id"
                      class="absolute opacity-0"
                      :checked="
                        selected[box.id] && selected[box.id].includes(option.id) ? true : false
                      "
                      @change="addOption($event.target.checked, $event.target.value, box.id)"
                    />
                    <svg
                      class="hidden w-4 h-4 pointer-events-none fill-current text-theme-500 dark:text-theme-400"
                      viewBox="0 0 20 20"
                    >
                      <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                    </svg>
                  </div>
                  <div v-if="option && option.display_name" class="select-none">
                    <!-- {{option.display_name}} -->
                    {{ $display_name(option.display_name) }}
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, useStore } from "vuex";
import helper from "~/components/sales/legacy/mixins/helper";

export default {
  mixins: [helper],
  setup() {
    const api = useAPI();
    const store = useStore();
    const { handleError } = useMessageHandler();
    return { api, store, handleError };
  },
  data() {
    return {
      showFilters: false,
      compareBoops: {},
      search: [],
      filterableBoxes: [],
      selected: [],
      sendselected: {},
      suppliers: {},
      selectedSuppliers: [],
      dlv: [0, 10],
    };
  },
  computed: {
    ...mapState({
      compare_category: (state) => state.compare.compare_category,
      flag: (state) => state.compare.flag,
      order_id: (state) => state.orders.active_order,
    }),
  },
  watch: {
    dlv: {
      handler: function (val, oldVal) {
        this.set_compare_dlv(this.dlv);
      },
      deep: true,
      immediate: true,
    },
    selected(v) {
      return v;
    },
  },
  mounted() {
    // get boops
    this.getBoops(this.compare_category.slug).catch((error) => {
      this.handleError(error);
    });

    // get suppliers
    this.getSuppliers(this.compare_category._id).catch((error) => {
      this.handleError(error);
    });

    let i = 0;
    for (const box in this.store.state.compare.compare_options) {
      if (Object.prototype.hasOwnProperty.call(this.store.state.compare.compare_options, box)) {
        i++;
        const option = this.store.state.compare.compare_options[box];
        if (!this.selected[box]) {
          // add key: value
          this.selected[box] = [option];
          this.selected = { ...this.selected };
        }

        // some other option in this box has been checked
        if (this.selected[box] && !this.selected[box].includes(option)) {
          // append the current option
          this.selected[box].push(option);
        }
      }
    }
  },
  methods: {
    // vuex mappings
    ...mapMutations({
      set_boops: "compare/set_compare_boops",
      set_compare_options: "compare/set_compare_options",
      set_compare_suppliers: "compare/set_compare_suppliers",
      set_selected_suppliers: "compare/set_selected_suppliers",
      set_compare_dlv: "compare/set_compare_dlv",
    }),

    // filter options
    filterBoxes() {
      this.compareBoops.forEach((box) => {
        this.search.push("");
      });
    },

    // api calls
    // boxes & options to create filters
    async getBoops(cat) {
      const boops = await this.api.get(`/finder/categories/${cat}`);
      this.set_boops(boops.data.boops);
      this.compareBoops = boops.data.boops;

      // for (const box in this.compareBoops) {
      //    if (this.compareBoops.hasOwnProperty(box)) {
      //       const element = this.compareBoops[box];
      //       Object.assign(this.selected, {[element.title]:[]})
      //    }
      // }

      this.filterBoxes();
    },

    // suppliers to select supplier and map supplierinfo to results
    async getSuppliers() {
      const suppliers = await this.api.get(`/suppliers`);
      this.set_compare_suppliers(suppliers.data);
      this.suppliers = suppliers.data;
    },

    // select an option to filter result
    addOption(state, v, box) {
      // if checkbox is checked
      if (state == true) {
        // no option of the specific box is checked
        if (!this.selected[box]) {
          // add key: value
          this.selected[box] = [v];
          this.selected = { ...this.selected };
          this.flattenOption();
        }

        // some other option in this box has been checked
        if (this.selected[box] && !this.selected[box].includes(v)) {
          // append the current option
          this.selected[box].push(v);
          this.flattenOption();
        }
      } else {
        // we unchecked & if the key: value exists in array
        if (this.selected[box] && this.selected[box].includes(v)) {
          const position = this.selected[box].findIndex((item) => item === v);
          // remove the selected option
          this.selected[box].splice(position, 1);

          this.flattenOption();
        }
      }
    },

    // we are going to merge the values array to comma seperated string
    flattenOption() {
      // let's get the keys
      for (const key in this.selected) {
        if (this.selected.hasOwnProperty(key)) {
          const element = this.selected[key];
          // and flatten the values array to comma seperated string
          const string = element.join();
          // set key: newvalue to the array we will send to the microservice to filter
          this.sendselected[key] = string;
        }
      }
      // store in vuex - result will watch changes
      this.set_compare_options(this.sendselected);
    },

    // we filtered on supplier!
    addSupplier(state, id) {
      // we checked an unchecked checkbox
      if (state == true) {
        // add the value to the selected suppliers array
        this.selectedSuppliers.push(id);
      }
      // we unchecked a checked checkbox
      else {
        // remove the id from the array
        this.selectedSuppliers = this.selectedSuppliers.filter(function (item) {
          return item !== id;
        });
      }
      // store in vuex - result will watch changes
      this.set_selected_suppliers(this.selectedSuppliers);
    },

    // Navigation
    back() {
      // load the previous component
      this.store.commit("compare/set_compare_component", "CategoryOverview");
    },
  },
};
</script>

<style lang="scss" scoped>
@media screen and (max-width: 1023px) {
  .vb-content {
    height: auto !important;
    padding-right: 1.5rem;
  }
}

input:checked + svg {
  display: block;
}
</style>
