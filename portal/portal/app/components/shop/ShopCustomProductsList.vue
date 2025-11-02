<template>
  <div
    class="w-full h-screen overflow-y-auto"
    :style="'max-height: calc(100vh - 8rem)'"
  >
    <section class="flex flex-wrap w-full">
      <template
        v-if="sorted_custom_products && sorted_custom_products.length > 0"
      >
        <div
          v-for="product in sorted_custom_products"
          :key="product.id"
          class="w-full"
        >
          <div
            class="relative flex items-stretch mx-2 mb-4 bg-white rounded shadow-md dark:bg-gray-700 group"
          >
            <ShopCustomSingleProduct
              :product="product"
              :list="true"
            ></ShopCustomSingleProduct>

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
                    class="w-full max-w-none"
                  />
                </template>
              </ConfirmationModal>
            </transition>

            <transition name="fade">
              <ConfirmationModal v-if="deleteCustomProduct.id === product.id">
                <template #modal-header>
                  <span class="capitalize"
                    >{{ $t("remove") }} {{ product.name }}</span
                  >
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
                <template #modal-header>
                  <span class="capitalize">
                    {{ $t("copy") }} {{ product.name }}
                    {{ $t("to another category") }}
                  </span>
                </template>

                <template #modal-body>
                  {{ $t("copy") }} {{ product.name }}
                  {{ $t("to the following category") }}
                  <section class="flex flex-wrap max-w-lg mt-4">
                    <div
                      v-if="categories && categories.length > 0"
                      class="relative w-full has-icon"
                    >
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

            <footer class="self-center w-1/6 p-4">
              <strong v-if="product.price > 0" class="pt-4">
                {{ product.display_price }}
              </strong>
              <button
                v-if="permissions.includes('cart-access')"
                class="block w-full px-2 py-1 mt-2 text-white transition-colors bg-green-500 rounded hover:bg-green-600"
                @click="
                  addToCart(product),
                    set_cart_flag('view'),
                    $router.push('/cart')
                "
              >
                {{ $t("add to cart") }}
              </button>
            </footer>
          </div>
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
      categories: (state) => state.shop.categories,
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
        .catch((error) => this.handleError(error));
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
    addToCart(product) {
      this.api.post("/cart", {
        products: [
          {
            id: product.id,
            product_type: "internal",
            variations: [],
            quantity: 1,
          },
        ],
      });
    },
    sortArray(array) {
      array.sort((a, b) => a.sort - b.sort);
      return array;
    },
    closeModal() {
      this.editCustomProduct = false;
      this.deleteCustomProduct = false;
      this.copyCustomProduct = false;
    },
  },
};
</script>
