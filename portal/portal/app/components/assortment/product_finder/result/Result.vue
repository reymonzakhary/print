<template>
  <!-- <div  @scroll="scrolledToBottom"> -->
  <div id="resultContainer" class="overflow-y-auto">
    <p class="sticky top-0"></p>
    <section
      class="flex flex-wrap items-center justify-between p-2 m-2 mb-0 mr-4 text-white rounded-t bg-theme-400"
    >
      <span class="text-xs">
        <span class="text-base font-bold capitalize">{{
          compare_category.name
        }}</span>
        <span
          class="px-1 ml-4 text-base font-bold bg-white rounded text-theme-900"
          >{{ total }}</span
        >
        {{ $t("products found") }}
      </span>

      <p class="text-xs t">
        {{ $t("page") }}
        <select
          v-model="page"
          class="text-sm text-white border border-white rounded bg-theme-400"
          @change="
            getFilteredProducts(
              compare_category._id,
              selected_options,
              $event.target.value,
              false,
              'page',
            )
          "
        >
          <option v-for="i in lastPage" :key="i" :value="i">{{ i }}</option>
        </select>
        {{ $t("to") }} <span class="text-sm">{{ lastPage }}</span>
      </p>

      <span class="flex">
        <span class="flex items-center mx-1">
          <p class="mr-2 text-xs">{{ $t("sort by") }}</p>
          <select
            v-model="sort"
            class="text-sm border border-white rounded text-themecontrast-400 bg-theme-400"
            @change="
              (sort = $event.target.value),
                getFilteredProducts(
                  compare_category._id,
                  selected_options,
                  page,
                  false,
                  'page',
                )
            "
          >
            <option value="price">{{ $t("price") }}</option>
            <option value="qty">{{ $t("quantity") }}</option>
            <option value="dlv">{{ $t("delivery") }}</option>
            <option value="supplier">{{ $t("supplier") }}</option>
            <option value="pm">{{ $t("print method") }}</option>
          </select>
        </span>

        <button class="flex items-center px-2 rounded hover:bg-theme-500">
          <font-awesome-icon
            v-if="sortdir === 'asc'"
            :icon="['fad', 'sort-up']"
            @click="
              (sortdir = 'desc'),
                getFilteredProducts(
                  compare_category._id,
                  selected_options,
                  page,
                  false,
                  'page',
                )
            "
          />

          <font-awesome-icon
            v-if="sortdir === 'desc'"
            :icon="['fad', 'sort-down']"
            @click="
              (sortdir = 'asc'),
                getFilteredProducts(
                  compare_category._id,
                  selected_options,
                  page,
                  false,
                  'page',
                )
            "
          />
        </button>

        <span class="flex items-center pl-2 mx-1 border-l">
          <select
            v-model="perPage"
            class="text-sm border border-white rounded text-themecontrast-400 bg-theme-400"
            @change="
              (setperpage = $event.target.value),
                getFilteredProducts(
                  compare_category._id,
                  selected_options,
                  page,
                  false,
                  'page',
                )
            "
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <p class="ml-2 text-xs">{{ $t("per page") }}</p>
        </span>
      </span>
    </section>

    <section
      class="flex px-1 py-2 mx-2 mr-4 rounded-b shadow-md backdrop-blur-md bg-white/80 dark:bg-gray-900/80"
    >
      <span
        v-for="(option, box) in selected_options"
        :key="option"
        class="px-2 py-1 mx-1 text-xs font-bold bg-gray-200 rounded"
      >
        {{ optionName(box, option) }}
      </span>
    </section>

    <div class="pr-2">
      <!-- {{page}}
         {{lastPage}}
         {{endofresults}} -->
      <ProductGrouped
        :filtered-products="filteredProducts"
        :compare_category="compare_category"
        :selected_options="selected_options"
      />

      <p v-if="endofresults" class="mt-4 font-bold text-center">
        <font-awesome-icon :icon="['fad', 'plane-arrival']" />
        {{ $t("products loaded") }}
      </p>

      <!-- <p v-else class="mt-4 mb-20 font-bold text-center">
            <font-awesome-icon 
               :icon="['fad', 'plane-departure']"
            />
            {{ $t("scroll products") }}
         </p> -->
      <Waypoint
        v-else
        :options="{
          active: true,
          callback: scrolledToBottom,
          options: intersectionOptions,
        }"
        tag="div"
        class="flex flex-col items-center justify-center w-full h-40 mx-auto my-4 text-center rounded-md md:w-1/2 bg-gradient-to-b to-theme-300 from-theme-500 text-themecontrast-400"
      >
        <font-awesome-icon
          v-show="loading"
          id="truck"
          :icon="['fad', 'truck']"
        />
        <p class="py-4">Loading next set of products...</p>
        <!-- <span v-if="loading" class="flex justify-center">
               <img src="~/assets/images/loading_loop.svg">
            </span> -->
      </Waypoint>
    </div>
  </div>
</template>

<script>
import { Waypoint } from "vue-waypoint";
import { mapState } from "vuex";

export default {
  components: {
    Waypoint,
  },
  setup() {
    const api = useAPI();
    const loadingIndicator = useLoadingIndicator();
    return { api, loadingIndicator };
  },
  data() {
    return {
      filteredProducts: Array,
      total: 0,
      page: 1,
      perPage: 0,
      lastPage: 0,

      gotopage: 1,
      setperpage: 10,
      sort: "price",
      sortdir: "asc",

      reachedBottom: false,
      endofresults: false,
      waypointActive: false,

      loading: false,

      intersectionOptions: {
        root: null,
        rootMargin: "0px 0px 0px 0px",
        threshold: [0.25, 0.75], // [0.25, 0.75] if you want a 25% offset! (was [0, 1])
      }, // https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
    };
  },
  computed: {
    ...mapState({
      boops: (state) => state.compare.compare_boops,
      selected_options: (state) => state.compare.compare_options,
      compare_category: (state) => state.compare.compare_category,
      compare_suppliers: (state) => state.compare.compare_suppliers,
      compare_selected_suppliers: (state) =>
        state.compare.compare_selected_suppliers,
      compare_qty: (state) => state.compare.compare_qty,
      compare_dlv: (state) => state.compare.compare_dlv,
    }),
  },
  watch: {
    selected_options: {
      handler: function (newVal) {
        this.getFilteredProducts(
          this.compare_category._id,
          newVal,
          1,
          false,
          "page",
        ).catch((error) => {
          this.handleError(error);
        });
      },
      deep: true,
      // immediate: true //disabled due to request with undefined params on pageload
    },
    compare_selected_suppliers() {
      this.getFilteredProducts(
        this.compare_category._id,
        this.selected_options,
        this.page,
        false,
        "page",
      );
    },
    compare_qty() {
      this.getFilteredProducts(
        this.compare_category._id,
        this.selected_options,
        this.page,
        false,
        "page",
      );
    },
    compare_dlv() {
      this.getFilteredProducts(
        this.compare_category._id,
        this.selected_options,
        this.page,
        false,
        "page",
      );
    },
  },
  methods: {
    async getFilteredProducts(cat, v, gotopage, event, loader) {
      // getting new results, so never end of result
      this.endofresults = false;

      if (loader === "page") {
        this.loadingIndicator.start();
      } else {
        // start bottom loader
        this.loading = true;
      }

      // check if some keys contain empty values and delete them
      for (const key in v) {
        if (v[key] === "") {
          delete v[key];
        }
      }

      const suppliers = [...this.compare_selected_suppliers];

      // request category with selected filters
      const body = {
        category_id: cat,
        product: v,
        sortby: this.sort,
        sortdir: this.sortdir,
        qty: `${this.compare_qty[0]}, ${this.compare_qty[2]}`,
        dlv: `${this.compare_dlv}`,
        suppliers: suppliers,
      };

      const filteredProducts = await this.api.post(
        `finder/categories/${this.compare_category.slug}/products?page=${gotopage}&perPage=${this.setperpage}`,
        body,
      );

      // assign data
      this.filteredProducts = filteredProducts.data;

      // assign pagination info
      this.total = filteredProducts.total;
      this.page = filteredProducts.page;
      this.perPage = filteredProducts.per_page;
      this.lastPage = filteredProducts.lastPage;

      if (this.page === this.lastPage) {
        this.endofresults = true;
      }

      // stop the loader
      this.loading = false;
      this.loadingIndicator.finish();
      this.reachedBottom = false;

      if (event) {
        this.toTop(event);
      }
    },
    scrolledToBottom({ el, going, direction }) {
      // define the elemnt
      // var el = event.srcElement;

      // check if not already scrolled to bottom
      if (!this.reachedBottom) {
        // if scrolled to bottom
        //if (el.scrollTop >= el.scrollHeight - el.clientHeight) {
        if (
          going === this.$waypointMap.GOING_IN &&
          direction === this.$waypointMap.DIRECTION_TOP
        ) {
          this.reachedBottom = true; // you reached the bottom of the canion

          // timeout to prevent flywheel scrolling
          setTimeout(() => {
            if (this.page + 1 <= this.lastPage) {
              // real in the next page of results
              this.getFilteredProducts(
                this.compare_category._id,
                this.selected_options,
                Number(this.page) + 1,
                el,
                "bottom",
              );

              if (this.page === this.lastPage) {
                this.endofresults = true;
              }
            } else if (this.page === this.lastPage) {
              this.endofresults = true;
            }
          }, 100);
        }
      }
    },
    toTop(el) {
      // reset and re go!
      this.reachedBottom = false;

      // var el = event.srcElement;
      // scroll to top of the element
      const l = document.getElementById("resultContainer");
      l.scrollTo(0, 0);
    },
    optionName(box, options) {
      if (this.boops && this.boops.length > 0) {
        const boox = this.boops.find((bx) => bx.id === box);
        const optionArray = options.split(",");
        const names = optionArray.map((option) => {
          const nameObj = boox.ops.find((opt) => opt.id === option);
          if (nameObj && nameObj.display_name) {
            return this.$display_name(nameObj.display_name);
          } else {
            return nameObj.name;
          }
        });
        return names.join(", ");
      }
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
																																					         0 1px 6px rgba(0, 0, 0, 0.04), */
      0px 10px 0px -2px #cfd8dc,
    /* 0 1px 15px rgba(0, 0, 0, 0.04), 
																																					         0 1px 6px rgba(0, 0, 0, 0.04), */
      0px 15px 0px -4px #b0bec5;
  // 0 1px 15px rgba(0, 0, 0, 0.04),
  // 0 1px 6px rgba(0, 0, 0, 0.04) !important;
}

// .vb-content {
//    height: calc(100vh - 7rem) !important;
//    padding-right: 1.5rem;
// }

@keyframes yourAnimation {
  0% {
    transform-origin: 0%;
    opacity: 0;
    left: 0;
    transform: scale(1) translate(-20vw);
  }
  100% {
    opacity: 0;
    transform: translate(20vw) scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) translate(0vw);
  }
}

/* Add the animation: property to whichever element you want to animate */
#truck {
  animation: yourAnimation 3s ease 0s infinite normal none;
}
</style>
