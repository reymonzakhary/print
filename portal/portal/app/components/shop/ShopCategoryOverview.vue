<template>
  <div id="categoryOverview" class="w-full h-full overflow-y-auto">
    <nav
      class="sticky top-0 flex items-center w-full px-2 bg-white rounded-t shadow-lg shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
    >
      <button
        v-if="flag === 'add_product'"
        class="flex items-center mr-8 font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
        @click="navigateTo(`/orders/order-details/?id=${order_id}&type=${ordertype}`)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
        {{ $t("back") }}
      </button>
      <div class="flex justify-center w-full mx-auto md:w-1/4">
        <input
          ref="catSearch"
          v-model="search"
          class="m-2 input md:w-auto"
          type="text"
          placeholder="Filter category"
        />
      </div>
    </nav>
    <div
      v-if="categories && searchCategories(categories).length > 0"
      class="flex flex-wrap w-full px-2 mt-4"
    >
      <div
        v-for="(category, i) in searchCategories(categories)"
        :key="`${i}_${category.id}`"
        class="w-full p-2 sm:w-1/2 lg:w-1/3 xl:w-1/4"
      >
        <button
          class="flex items-center w-full h-32 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 hover:shadow-lg hover:border hover:border-theme-500"
          @click="handleCategoryClick(category)"
        >
          <figure class="w-32 h-32 p-2">
            <img
              v-if="getImgUrl(category.name.toLowerCase())"
              :src="getImgUrl(category.name.toLowerCase())"
              :alt="category.name"
            />
            <ShopImage
              v-else-if="category.media && category.media.length > 0 && category.media[0]"
              disk="assets"
              :file="{ path: category.media[0] }"
              class="w-full"
            />
            <img v-else src="~/assets/images/Prindustry-box_3.svg" :alt="category.name" />
          </figure>
          <div class="w-2/3 p-2 text-left first-letter:capitalize">
            <h3
              v-tooltip="
                category.display_name ? $display_name(category.display_name) : category.name
              "
              class="truncate"
            >
              {{ category.display_name ? $display_name(category.display_name) : category.name }}
            </h3>
            <p v-tooltip="category.description" class="text-sm text-gray-500 truncate">
              {{ category.description }}
            </p>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";

export default {
  emits: ["onTypeChange", "onSelectCatChange"],
  data() {
    return {
      countData: [],
      cats: [],
      search: "",
      numberKey: 0,
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.shop.categories,
      order_id: (state) => state.orders.active_order.id,
      ordertype: (state) => state.orders.ordertype,
    }),
    flag() {
      return this.$store.state.compare.flag;
    },
    groupedCategories() {
      return this.groupCategories(this.categories);
    },
    count() {
      return this.countData;
    },
  },
  watch: {
    categories() {
      this.groupCategories(this.categories);
    },
  },
  mounted() {
    this.$refs.catSearch.focus();
  },
  methods: {
    ...mapMutations({
      set_active_category: "shop/set_active_category",
      set_active_custom_category: "shop/set_active_custom_category",
      set_loading_boops: "shop/set_loading_boops",
      set_selected_category: "shop/set_selected_category",
      set_boops: "shop/set_boops",
    }),
    ...mapActions({
      get_suppliers: "suppliers/get_suppliers",
      get_categories: "shop/get_categories",
      get_custom_categories: "shop/get_custom_categories",
      get_custom_products: "shop/get_custom_products",
    }),
    handleCategoryClick(category) {
      const isPrintProduct = category.tenant_id ? true : false;
      let type = String;
      if (isPrintProduct) {
        this.selectCategory(category);
        type = "print";
        this.$emit("onTypeChange", type);
      } else {
        type = "custom";
        this.selectCustomCategory(category);
        this.$emit("onTypeChange", type);
      }
      // this.$emit("onSelectCatChange", false);
      return navigateTo(`/shop/${category.slug}?type=${type}`);
    },
    searchCategories(cats) {
      const array = [];
      for (let i = 0; i < cats.length; i++) {
        const cat = cats[i];
        if (cat.name.toLowerCase().includes(this.search)) {
          array.push(cat);
        }
      }
      return array;
    },
    groupCategories(cats) {
      if (cats.length) {
        // Sort alphabetically
        const ordered_categories = _.orderBy(cats, ["name"], ["asc"]);
        // group by first letter of tyhe name
        const groupedCategories = _.groupBy(ordered_categories, function (category) {
          if (category.name) {
            return category.name.toLowerCase().substr(0, 1);
          }
        });
        // return grouped objectArray
        return groupedCategories;
      }
    },
    selectCategory(category) {
      this.set_active_category([category.id, category.slug, category.name]);
      this.set_boops(category.boops);
      this.set_selected_category(category);
      this.set_loading_boops(true);
    },
    async selectCustomCategory(category) {
      // await this.get_custom_products({ cat_id: category.id });
      this.set_active_category(category);
      this.set_selected_category(category);
    },
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

<style lang="css" scoped>
#categoryOverview {
  scroll-behavior: smooth;
}
</style>
