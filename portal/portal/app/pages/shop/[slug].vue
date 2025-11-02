<template>
  <main class="flex h-full flex-col">
    <UILoader v-if="isFetchingCategory" class="m-4" />

    <template
      v-else-if="
        (selected_category && Object.keys(selected_category).length > 0) || custom_products
      "
    >
      <header
        class="item-center flex w-full justify-between bg-gray-100 px-4 py-1 dark:bg-gray-800"
      >
        <button class="text-theme-500" @click="navigateTo('/shop')">
          <font-awesome-icon :icon="['fal', 'chevron-left']" />
          <span class="has-text-weight-normal capitalize">
            {{ $t("back") }}
          </span>
        </button>

        <div class="flex max-h-16 items-center text-lg">
          <font-awesome-icon :icon="['fal', 'shop']" class="mr-2" />
          {{ $t("shop") }}

          <template v-if="$route.query.type === 'print'">
            <div class="h-16">
              <ShopImage
                disk="assets"
                :file="{ path: selected_category?.media ? selected_category?.media[0] : '' }"
                class="mx-4"
              />
            </div>
            <b>
              {{
                selected_category?.display_name ? $display_name(selected_category.display_name) : ""
              }}
            </b>
          </template>
          <template v-if="$route.query.type === 'custom'">
            <ShopImage
              disk="assets"
              :file="{ path: active_category?.media ? active_category?.media[0] : '' }"
              class="mx-4"
            />
            <b>
              {{ active_category?.name }}
            </b>
          </template>
        </div>
        <button
          class="card-header-icon ml-auto hidden sm:ml-0 md:block"
          @click="navigateTo('/shop')"
        >
          <font-awesome-icon :icon="['fad', 'circle-xmark']" />
        </button>
      </header>

      <div class="relative flex w-full overflow-x-auto">
        <section class="grid w-full grid-cols-10 gap-x-4 py-4">
          <div
            id="scroll-container"
            class="relative col-span-10 h-full overflow-y-auto md:col-span-5 md:col-start-2 md:pr-4"
            style="scroll-behavior: smooth"
          >
            <ShopBoops
              v-if="$route.query.type === 'print'"
              :type="$route.query.type"
              @price-selected="selectedPrice = $event"
              @item-selected="collection = $event"
            />
          </div>
          <aside
            v-if="$route.query.type === 'print'"
            class="col-span-10 hidden h-auto px-4 md:col-span-3 md:col-start-7 md:block md:pr-4"
          >
            <ShopList :selected-price="selectedPrice" :collection="collection" />
          </aside>
          <!-- <transition name="list" tag="nav"> -->
          <div v-if="$route.query.type === 'custom'" class="col-span-10 w-full">
            <ShopCustomProducts />
            <!-- <ShopCustomProductsList v-if="listview" /> -->
          </div>
        </section>
        <!-- </transition> -->
      </div>
    </template>

    <!-- <div v-else class="flex h-full w-full items-center justify-center p-4 text-2xl">
      {{ $t("category not found") }}
      <UIButton class="ml-2" @click="navigateTo('/shop')">{{ $t("go to shop") }}</UIButton>
    </div> -->
  </main>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
export default {
  name: "ShopCategory",
  layout: "default",
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      isFetchingCategory: true,
      selectedPrice: {},
      collection: [],
    };
  },
  head() {
    return {
      title: `${this.$t("products")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      selected_category: (state) => state.shop.selected_category,
      categories: (state) => state.shop.categories,
      category: (state) => state.shop.category,
      active_category: (state) => state.shop.active_category,
      custom_products: (state) => state.shop.custom_products,
    }),
  },
  watch: {
    selected_category(val) {
      return val;
    },
    custom_products(val) {
      return val;
    },
  },
  async mounted() {

    // fetch print product
    if (
      (this.$route.query.type === "print" && !this.selected_category) ||
      (this.$route.query.type === "print" && Object.keys(this.selected_category)?.length === 0)
    ) {
      await this.api
        .get(`shops/categories/${this.$route.params.slug}?type=${this.$route.query.type}`)
        .then((response) => {
          this.set_selected_category(response.data);
          this.set_active_category(response.data);
          this.set_boops(response.data.boops);
          this.isFetchingCategory = false;
        })
        .catch((error) => this.handleError(error))
        .finally(() => (this.isFetchingCategory = false));
    } else if (this.$route.query.type === "print") {
      this.set_boops(this.selected_category.boops);
      this.isFetchingCategory = false;
    }

    // fetch custom product
    if (
      (this.$route.query.type === "custom" && !this.selected_category) ||
      (this.$route.query.type === "custom" && Object.keys(this.selected_category)?.length === 0)
    ) {
      await this.get_custom_categories();
      if (this.categories?.length > 0) {
        this.categories.forEach((category) => {
          if (category.slug === this.$route.params.slug) {
            this.set_selected_category(category);
            this.set_active_category(category);
          }
        });
      }
      // await this.get_custom_category(this.$route.params.slug);
      if (this.selected_category) {
        await this.get_custom_products({ cat_id: this.selected_category.id });
      }

      this.isFetchingCategory = false;
    } else if (this.$route.query.type === "custom") {
      await this.get_custom_products({ cat_id: this.selected_category.id });
      this.isFetchingCategory = false;
    }

    // const root = document.getElementsByTagName("html")[0];
    // root.classList.add("overflow-hidden");
  },
  methods: {
    ...mapMutations({
      set_selected_category: "shop/set_selected_category",
      set_active_category: "shop/set_active_category",
      set_boops: "shop/set_boops",
    }),
    ...mapActions({
      get_custom_products: "shop/get_custom_products",
      get_custom_category: "shop/get_custom_category",
      get_custom_categories: "shop/get_custom_categories",
    }),
  },
};
</script>
