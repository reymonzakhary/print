<template>
  <article id="resultContainer" class="h-full overflow-y-auto">
    <!-- id="resultContainer" used for pagination -->
    <header class="item-center sticky top-0 z-20 flex justify-between bg-gray-100 p-4 pb-2">
      <button class="text-theme-500" @click="deactivateOverview()">
        <font-awesome-icon :icon="['fal', 'chevron-left']" />
        <span class="has-text-weight-normal capitalize">
          {{ $t("back") }}
        </span>
      </button>

      <p>
        <font-awesome-icon :icon="['fal', 'box-full']" />
        {{ $t("Product combinations") }} -
        {{ category[2] }}
      </p>

      <button class="card-header-icon" @click="deactivateOverview()">
        <span class="icon">
          <font-awesome-icon :icon="['fad', 'circle-xmark']" />
        </span>
      </button>
    </header>

    <!-- {{ products }} -->

    <section
      v-if="products && products.length > 0"
      class="relative h-full p-2"
      style="height: calc(100vh - 10rem)"
    >
      <div class="min-w-max rounded shadow-md shadow-gray-200 dark:shadow-gray-900">
        <div
          class="sticky top-12 z-10 flex w-full flex-wrap items-center justify-center rounded-t bg-theme-400 p-2 text-xs"
        >
          <PaginationTopBar @pagination-changed-top="getProducts" />
        </div>

        <!-- table header -->
        <header
          class="sticky flex border-b bg-white p-2 text-xs font-bold uppercase tracking-wide shadow-sm dark:border-gray-900 sm:text-sm md:text-base"
          style="top: 86px"
        >
          <div
            v-for="(box, i) in boops.boops"
            :key="'box_' + box.id"
            class="flex flex-1 items-center justify-around text-xs"
            @click="((sort = box.slug), (desc = !desc))"
          >
            {{ box.display_name ? $display_name(box.display_name) : box.name }}
            <font-awesome-icon
              v-if="sort === box.slug"
              :icon="['fad', desc ? 'sort-down' : 'sort-up']"
            />
            <font-awesome-icon
              v-if="i < boops.boops.length - 1"
              :icon="['fal', 'caret-right']"
              class="text-theme-500"
            />
          </div>
          <div class="mr-2 text-xs">
            {{ $t("actions") }}
          </div>
        </header>

        <client-only>
          <PaginationStart @pagination-changed-start="getProducts" />
        </client-only>

        <section
          v-for="productspage in products"
          :key="`page_${productspage.id}`"
          class="divide-y border"
        >
          <div
            v-for="(product, idx) in productspage"
            :key="'combination_' + product.id"
            class="hover:bg-thmee-100 group flex w-full justify-between bg-white px-2 py-1 last:rounded-b-md dark:bg-gray-700 dark:hover:bg-gray-900"
          >
            <div
              v-for="(obj, index) in product.object"
              :key="obj.value"
              class="flex w-full flex-1 items-center justify-around"
            >
              {{ $display_name(obj.display_value) }}
              <font-awesome-icon
                v-if="index < product.object.length - 1"
                :icon="['fal', 'caret-right']"
                class="text-sm text-theme-500"
              />
            </div>

            <button
              v-if="
                permissions.includes('print-assortments-categories-read') &&
                permissions.includes('print-assortments-categories-update')
              "
              class="transition:color mr-2 rounded-full border border-theme-500 px-1 text-xs text-theme-500 duration-150 hover:bg-theme-400 hover:text-white sm:px-2 sm:py-1"
              @click="activateDetails(product.object)"
            >
              {{ $t("open") }}
            </button>
          </div>
        </section>

        <PaginationEnd @pagination-changed-bottom="getProducts" />
      </div>
      <Pagination
        :pagination="pagination"
        class="fixed bottom-5 right-5 rounded border border-theme-500 text-center shadow-lg"
        @pagination="getProducts"
      />
    </section>

    <section
      v-if="Array.isArray(products) && products.length"
      class="-mt-10 flex h-full flex-col items-center justify-center p-4"
    >
      <p class="text-gray-500">
        {{ $t("No products generated for this category yet") }}
      </p>
      <button
        class="mt-10 rounded-full bg-theme-400 px-4 py-2 text-lg font-bold text-themecontrast-400 transition-colors hover:bg-theme-500"
        @click="generateManifest"
      >
        <font-awesome-icon :icon="['fad', 'box-full']" class="mr-2" />
        {{ capitalizeFirstLetter($t("build product combination")) }}
      </button>
    </section>
  </article>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import PaginationTopBar from "../../components/global/pagination/PaginationTopBar.vue";
import pagination from "~/mixins/pagination";

export default {
  components: { PaginationTopBar },
  mixins: [pagination],
  transition: "slideleftlarge",
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const { handleError, handleSuccess } = useMessageHandler();

    return { api, permissions, handleError, handleSuccess, capitalizeFirstLetter };
  },
  data() {
    return {
      supplier: {},
      products: [],
      sort: "",
      desc: "asc",
    };
  },
  head() {
    return {
      title: `${this.$t("assortment")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      selected_category: (state) => state.product_wizard.selected_category,
      selected_boops: (state) => state.product_wizard.selected_boops,
      active_items: (state) => state.product.active_items,
      collection: (state) => state.product.collection,
    }),
  },
  watch: {
    boops(newVal) {
      return newVal;
    },
    selected_boops(newVal) {
      return newVal;
    },
    sort() {
      this.getProducts({ page: 1 });
    },
    desc() {
      this.getProducts({ page: 1 });
    },
  },
  mounted() {
    this.getProducts({ page: 1, type: "set" }); // type: set means setting the first results as opposed to appending next result
  },
  methods: {
    ...mapMutations({
      set_boops: "product/set_boops",
      set_selected_boops: "product_wizard/set_selected_boops",
      set_active_collection: "product/set_active_collection",
      set_active_items: "product/set_active_items",
      // activate_details: "product/activate_details",
    }),

    async getProducts(e) {
      // make sure it fires just once for every page change, exception for first load
      // if (
      // 	e.page !== this.pagination.current_page ||
      // 	(this.pagination.current_page === 1 && e.page === 1 && e.type === 'set')
      // ) {

      this.climbTheCanion();
      // get the results
      await this.api
        .get(
          `categories/${this.category[1]}/products?per_page=${this.pagination.per_page}&page=${e.page}&sort=${this.sort}&dir=${this.desc}`,
        )
        .then((response) => {
          // set pagination settings
          this.set_pagination(response.meta);
          this.set_loader(false);

          // add to the result (infinyte scrollinh) or refresh the result (pagination params change)
          if (e.type === "addEnd") {
            this.products.push(response.data);
            this.products.shift();
            // reset and re go!
            this.climbTheCanion();
          } else if (e.type === "addStart") {
            this.products.unshift(response.data);
            this.products.pop();
            // reset and re go!
            this.downTheCanion();
          } else {
            this.products = [response.data];
          }
        })
        .catch((error) => this.handleError(error));
      // }
    },

    async generateManifest() {
      this.loading = true;
      this.api
        .post(`categories/${this.selected_category.slug}/products/combinations/generate`, {
          id: this.selected_category.id,
          name: this.selected_category.name,
          slug: this.selected_category.slug,
          boops: this.selected_boops,
        })
        .then((response) => {
          this.handleSuccess(response);
        })
        .catch((error) => this.handleError(error));
    },

    activateDetails(combination) {
      let collection = {};
      let items = {};

      combination.forEach((combi) => {
        collection = Object.assign(collection, {
          [combi.key]: combi.value,
        });
        items = Object.assign(items, {
          [combi.display_key]: combi.display_value,
        });
      });

      // store it in the store
      this.set_active_collection(collection);
      this.set_active_items(items);

      // trigger the details view to be loaded
      // this.activate_details(true);

      this.$router.push(`/assortment/details?cat=${this.selected_category.slug}`);
    },

    // close product combinations overview and navigate to categories overview
    deactivateOverview() {
      this.$router.push("/assortment");
    },
  },
};
</script>
