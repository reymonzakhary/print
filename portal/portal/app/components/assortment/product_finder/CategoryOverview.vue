<template>
  <div id="categoryOverview" class="w-full h-full overflow-y-auto">
    <nav
      class="sticky top-0 flex items-center w-full px-2 bg-white rounded-t shadow-lg shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
    >
      <button
        v-if="flag === 'add_product' || flag === 'edit_product'"
        class="flex items-center mr-8 font-normal transition-colors duration-75 text-theme-500 hover:text-theme-700"
        @click="
          navigateTo(`/orders/order-details/?id=${order_id}&type=${ordertype}`)
        "
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
        {{ $t("back") }}
      </button>

      <!-- First Character List -->
      <ul v-if="groupedCategories" class="hidden w-3/4 py-4 md:flex">
        <li
          v-for="(_, char) in groupedCategories"
          :key="char"
          class="transition-colors duration-75 hover:text-gray-600"
        >
          <a
            class="p-1 capitalize cursor-pointer"
            @click="scrollToChar(char)"
            >{{ char }}</a
          >
        </li>
      </ul>

      <div class="flex justify-end w-full md:w-1/4">
        <input
          :value="search"
          class="m-2 input md:w-auto"
          type="text"
          placeholder="Filter category"
          @input="search = `${$event.target.value}`.toLowerCase()"
        />
      </div>
    </nav>

    <div
      v-for="(categories, key) in groupedCategories"
      :key="key"
      class="w-full"
    >
      <div
        v-show="search.length === 0 || key == search.charAt(0)"
        :id="key"
        class="w-full py-4 mt-12 text-center border-t dark:border-black"
      >
        <p class="text-4xl capitalize">
          {{ key }}
        </p>
      </div>

      <div v-if="categories && searchCategories(categories).length > 0">
        <transition-group
          name="slidedownslow"
          class="flex flex-col flex-wrap sm:flex-row"
          tag="div"
        >
          <span
            v-for="(category, i) in searchCategories(categories)"
            :key="`${i} ${category.id}`"
            class="flex w-full p-2 sm:w-1/2 lg:w-1/3 xl:w-1/4"
          >
            <button
              class="flex w-full h-32 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 hover:shadow-lg hover:border hover:border-theme-500"
              @click="compare(category)"
            >
              <figure class="w-32 h-32 p-2">
                <img
                  v-if="getImgUrl(category.name.toLowerCase())"
                  :src="getImgUrl(category.name.toLowerCase())"
                  :alt="category.name"
                />
                <img
                  v-else
                  src="~/assets/images/Prindustry-box_3.svg"
                  :alt="category.name"
                />
              </figure>

              <div class="w-2/3 p-2 text-left capitalize">
                <p>{{ category.name }}</p>
              </div>
            </button>
          </span>
        </transition-group>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, useStore } from "vuex";
import _ from "lodash";
const images = import.meta.glob("assets/images/assortments_portal/en/*.svg", {
  eager: true,
});

export default {
  setup() {
    const store = useStore();
    return { store };
  },
  data() {
    return {
      categories: {},
      countData: [],
      cats: [],
      search: "",
      numberKey: 0,
    };
  },
  computed: {
    ...mapState({
      compare_categories: (state) => state.compare.compare_categories,
      selected_producer_categories: (state) =>
        state.product.selected_producer_categories,
      order_id: (state) => state.orders.active_order.id,
      ordertype: (state) => state.orders.ordertype,
    }),
    flag() {
      return this.store.state.compare.flag;
    },
    groupedCategories() {
      if (
        this.flag === "compare" ||
        this.flag === "add_product" ||
        this.flag === "edit_product"
      ) {
        return this.groupCategories(this.compare_categories);
      } else {
        return this.groupCategories(this.selected_producer_categories);
      }
    },
    count() {
      return this.countData;
    },
  },
  watch: {
    compare_categories() {
      this.groupCategories(this.compare_categories);
    },
    selected_producer_categories() {
      this.groupCategories(this.selected_producer_categories);
    },
  },
  mounted() {
    if (this.compare_categories) {
      if (this.flag === "compare" || this.flag === "add_product") {
        setTimeout(() => {
          this.categories = this.compare_categories;
        }, 500);
      } else {
        this.categories = this.compare_categories;
      }
    } else {
      this.categories = this.selected_producer_categories;
    }
  },
  methods: {
    scrollToChar(char) {
      const element = document.getElementById(char);
      if (element) {
        element.scrollIntoView({
          block: "start",
          inline: "nearest",
          behavior: "auto",
        });
      }
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
      if (cats && cats.length) {
        // Sort alphabetically
        const ordered_categories = _.orderBy(cats, ["name"], ["asc"]);

        // group by first letter of tyhe name
        const groupedCategories = _.groupBy(
          ordered_categories,
          function (category) {
            return category.name.toLowerCase().substr(0, 1);
          },
        );

        // return grouped objectArray
        return groupedCategories;
      }
    },
    compare(category) {
      this.store.commit("compare/set_compare_category", category);
      if (this.flag === "compare" || this.flag === "add_product") {
        this.store.commit("compare/set_compare_component", "CompareProducts");
      }
    },
    getImgUrl(categories) {
      const categoriesInArray = categories.split(" ");
      let imgUrl = "";

      categoriesInArray.forEach((category) => {
        const src = category.toLowerCase();
        if (images[`/assets/images/assortments_portal/en/${src}.svg`]) {
          imgUrl =
            images[`/assets/images/assortments_portal/en/${src}.svg`].default;
        }
      });

      return imgUrl;
    },
  },
};
</script>

<style lang="css" scoped>
#categoryOverview {
  scroll-behavior: smooth;
}
</style>
