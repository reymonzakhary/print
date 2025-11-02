<template>
  <div class="h-screen w-full overflow-y-auto" :style="'max-height: calc(100vh - 6rem)'">
    <section class="flex w-full flex-wrap items-center">
      <div
        v-if="sorted_custom_products && sorted_custom_products.length > 0"
        class="flex w-full justify-between px-4 pb-4"
      >
        <button
          v-if="!drag"
          class="mx-1 text-sm capitalize text-theme-500 hover:text-theme-600"
          @click="drag = !drag"
        >
          <font-awesome-icon :icon="['fal', 'shuffle']" />
          {{ $t("reorder") }}
        </button>
        <button v-if="drag" class="mx-1 text-sm capitalize text-gray-500" @click="drag = !drag">
          <font-awesome-icon :icon="['fal', 'check']" />
          {{ $t("done") }}
        </button>

        <nuxt-link
          v-if="
            ((permissions.includes('print-assortments-categories-create') &&
              cart_flag === 'view') ||
              permissions.includes('custom-assortments-categories-create')) &&
            !drag
          "
          :to="'/assortment/add-products'"
          class="mx-1 text-sm text-theme-500 transition-colors duration-75 hover:text-theme-700"
          @click="activateModal"
        >
          <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
          {{ $t("new") }}
        </nuxt-link>
      </div>

      <draggable
        v-if="drag"
        :list="reorder_custom_products"
        item-key="reorderCustomProductsList"
        @change="saveReorder"
      >
        <template #item="{ element }">
          <div
            class="group relative mx-2 mb-2 flex items-stretch rounded bg-white shadow-md dark:bg-gray-700"
          >
            <div class="grid place-items-center px-2">
              <font-awesome-icon
                :icon="['fal', 'grip-vertical']"
                class="cursor-move text-gray-500"
              />
            </div>
            <CustomSingleProduct :product="element" :list="true" />
          </div>
        </template>
      </draggable>

      <template v-if="sorted_custom_products && sorted_custom_products.length > 0 && !drag">
        <div v-for="product in sorted_custom_products" :key="product.id" class="w-full">
          <div
            class="group relative mx-2 mb-2 flex items-stretch rounded bg-white shadow-md dark:bg-gray-700"
          >
            <CustomSingleProduct :product="product" :list="false" />
          </div>
        </div>
      </template>
    </section>

    <div class="sticky bottom-0 flex w-full justify-center">
      <Pagination
        v-if="pagination.last_page > 1"
        :pagination="pagination"
        class="flex rounded border border-theme-500 shadow-lg"
        @pagination="
          get_custom_products({
            cat_id: active_custom_category.id,
            page: $event.page,
          })
        "
      />
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";
import pagination from "~/mixins/pagination";

export default {
  mixins: [pagination],
  setup() {
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const { handleError, handleSuccess } = useMessageHandler();
    return { permissions, api, handleError, handleSuccess };
  },
  data() {
    return {
      reorder_custom_products: [],
      sorted_custom_products: [],
      drag: false,
    };
  },
  computed: {
    ...mapState({
      active_custom_category: (state) => state.product.active_custom_category,
      custom_products: (state) => state.product.custom_products,
      pagination: (state) => state.pagination.pagination,

      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  watch: {
    pagination: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
    },
    custom_products: {
      deep: true,
      handler(v) {
        this.reorder_custom_products = _.sortBy(v, "sort");
        this.sorted_custom_products = _.sortBy(v, "sort");
      },
    },
  },
  mounted() {
    if (this.active_custom_category) {
      this.get_custom_products({ cat_id: this.active_custom_category.id });
    }
  },
  methods: {
    ...mapMutations({
      set_cart_flag: "cart/set_cart_flag",
      set_wizard_type: "product_wizard/set_wizard_type",
      set_wizard_component: "product_wizard/set_wizard_component",
    }),
    ...mapActions({
      get_custom_products: "product/get_custom_products",
    }),
    sortArray(array) {
      array.sort((a, b) => a.sort - b.sort);
      return array;
    },
    async saveReorder() {
      this.reorder_custom_products.forEach(async (item, index) => {
        try {
          await this.api.put(`/custom/products/${item.id}`, { sort: index });
          this.get_custom_products({
            cat_id: this.active_custom_category.id,
            page: this.pagination.current_page,
          });
          this.handleSuccess(this.$t("reorder success"));
        } catch (error) {
          this.handleError(error);
        }
      });
    },
    decodeUnicode(str) {
      // Going backwards: from bytestream, to percent-encoding, to original string.
      return decodeURIComponent(
        atob(str)
          .split("")
          .map(function (c) {
            return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
          })
          .join(""),
      );
    },
    activateModal() {
      this.set_wizard_type("");
      this.set_wizard_component("AddProductOverview");
    },
  },
};
</script>

<style></style>
