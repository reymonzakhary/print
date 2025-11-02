<template>
  <section class="flex w-full h-full">
    <transition
      name="fade"
      tag="div"
      class="w-full"
      :class="{ 'h-full': selectCat, 'h-auto': !selectCat }"
    >
      <UILoader v-if="isFetchingCategories" />
      <ShopCategoryOverview
        v-else-if="selectCat"
        @on-select-cat-change="(selectedCat) => (selectCat = selectedCat)"
        @on-type-change="(selectedType) => (type = selectedType)"
      />
    </transition>
  </section>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  name: "Products",
  layout: "default",
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      containerWidth: 0,
      listview: false,
      selectCat: true,
      type: "",
      isFetchingCategories: false,
    };
  },
  head() {
    return {
      title: `${this.$t("products")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.shop.categories,
      category: (state) => state.shop.category,
      selected_category: (state) => state.shop.selected_category,
      active_items: (state) => state.product.active_items,
      boops: (state) => state.shop.boops,
      workflow: (state) => state.shop.workflow,
      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  watch: {
    categories(val) {
      return val;
    },
    category(val) {
      return val;
    },
  },
  async mounted() {
    this.isFetchingCategories = true;
    if (!this.categories.length) {
      try {
        const [customProducts, printProducts] = await Promise.all([
          this.api.get(`shops/categories?per_page=999999999`),
          this.api.get(`/shops/categories?per_page=999999999&type=print`),
        ]);
        this.$store.commit("shop/set_categories", customProducts.data);
        this.$store.commit("shop/add_categories", printProducts.data);
      } catch (error) {
        this.handleError(error);
      } finally {
        this.isFetchingCategories = false;
      }
    } else {
      this.isFetchingCategories = false;
    }
  },
  methods: {
    ...mapMutations({
      set_assortment_flag: "product/set_assortment_flag",
    }),
    // scrollToEnd() {
    //   const container = this.$el.querySelector("#container");
    //   container.scrollTo({
    //     top: container.scrollHeight,
    //   });
    // },
    getImgUrl(src) {
      const newsrc = src.split(" ");
      for (let i = 0; i < newsrc.length; i++) {
        const imgsrc = newsrc[i];
        try {
          const images = require.context(
            "assets/images/assortments_portal/en/",
            false,
            /\.(png|jpe?g|svg)$/,
          );
          return images("./" + imgsrc + ".svg");
        } catch (error) {
          /* empty */
        }
      }
    },
  },
};
</script>
