<template>
  <div class="w-full h-screen overflow-y-auto" :style="'max-height: calc(100vh - 8rem)'">
    <section class="flex flex-wrap w-full px-2">
      <template v-if="sorted_custom_products && sorted_custom_products.length > 0">
        <div
          v-for="product in sorted_custom_products"
          :key="product.id"
          class="w-full 2xl:w-1/5 xl:w-1/4 lg:w-1/3 sm:w-1/2"
        >
          <ShopCustomSingleProduct :product="product" />

          <transition name="fade">
            <ConfirmationModal v-if="editCustomProduct.id === product.id">
              <template #modal-header>
                <p class="font-bold tracking-wide uppercase">
                  <font-awesome-icon :icon="['fal', 'shelves']" />
                  {{ $t("edit") }} {{ $t("product") }}
                </p>
              </template>

              <template #modal-body>
                <CustomProductForm
                  :edit_mode="true"
                  :categories="categories"
                  :product="editCustomProduct"
                  class="w-full max-w-screen-md"
                />
              </template>
            </ConfirmationModal>
          </transition>

          <transition name="fade">
            <ConfirmationModal v-if="deleteCustomProduct.id === product.id">
              <template #modal-header class="capitalize">
                {{ $t("remove") }} {{ product.name }}
              </template>

              <template #modal-body>
                <section class="flex flex-wrap max-w-lg">
                  <div class="max-h-screen p-2" style="min-width: 400px">
                    {{ $t("this will remove") }}
                    <b>{{ product.name }}</b
                    >. {{ $t("are you sure") }}
                  </div>
                </section>
              </template>

              <template #confirm-button>
                <button
                  class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
                  @click="deleteProduct()"
                >
                  {{ $t("remove") }}
                </button>
              </template>
            </ConfirmationModal>
          </transition>

          <transition name="fade">
            <ConfirmationModal v-if="copyCustomProduct.id === product.id">
              <template #modal-header class="capitalize">
                {{ $t("copy") }} {{ product.name }}
                {{ $t("to another category") }}
              </template>

              <template #modal-body>
                {{ $t("copy") }} {{ product.name }}
                {{ $t("to the following category") }}
                <section class="flex flex-wrap max-w-lg mt-4">
                  <div v-if="categories && categories.length > 0" class="relative w-full has-icon">
                    <v-select
                      v-model="categoryId"
                      class="p-0 text-sm input text-theme-900 dark:text-white"
                      label="name"
                      :options="categories"
                    >
                    </v-select>
                    <font-awesome-icon
                      class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-400"
                      :icon="['fal', 'user']"
                    />
                  </div>
                </section>
              </template>

              <template #confirm-button>
                <button
                  class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-700"
                  @click="copyProduct()"
                >
                  {{ $t("Copy") }} {{ $t("to") }}
                  {{ categoryId.name }}
                </button>
              </template>
            </ConfirmationModal>
          </transition>
        </div>
      </template>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

export default {
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      editCustomProduct: false,
      deleteCustomProduct: false,
      copyCustomProduct: false,
      categoryId: null,
      sorted_custom_products: [],
    };
  },
  computed: {
    ...mapState({
      custom_products: (state) => state.shop.custom_products,
      pagination: (state) => state.product.pagination,
      categories: (state) => state.product.categories,
      active_category: (state) => state.product.active_category,
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
    custom_products(v) {
      this.sorted_custom_products = _.sortBy(v, "sort");
    },
  },
  methods: {
    ...mapMutations({
      set_cart_flag: "cart/set_cart_flag",
    }),
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
    copyProduct() {
      let catSlug = "";
      let catId = null;

      for (let i = 0; i < this.categories.length; i++) {
        const cat = this.categories[i];
        if (cat.id === this.categoryId.id) {
          catSlug = cat.slug;
          catId = cat.id;
        }
      }

      this.api
        .post(`/products/copy`, {
          category_id: catId,
          name: this.copyCustomProduct.name,
          description: this.copyCustomProduct.description,
          price: this.copyCustomProduct.price,
          ean: this.copyCustomProduct.ean.toString(),
          art_num: this.copyCustomProduct.art_num,
          stock: this.copyCustomProduct.stock,
          properties: this.copyCustomProduct.properties,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    deleteProduct() {
      this.api
        .delete(`/products/${this.deleteCustomProduct.slug}`)
        .then((response) => {
          this.handleSuccess(response);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.editCustomProduct = false;
      this.deleteCustomProduct = false;
      this.copyCustomProduct = false;
    },
  },
};
</script>
