<template>
  <section
    class="relative flex flex-col items-stretch mx-2 mb-4 transition-shadow bg-white rounded shadow-md cursor-pointer hover:shadow-xl shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900 group"
    :class="{ 'flex items-center w-full': list }"
    @click="showDetails = true"
  >
    <div class="flex flex-col items-center">
      <div class="w-full overflow-hidden" :class="{ 'w-20 h-20': list, 'h-40 ': !list }">
        <template v-for="(img, i) in product.media" :key="`detail_img_${i}`">
          <transition name="fade">
            <ShopImage
              v-if="product.media && product.media.length > 0"
              v-show="showImage === i"
              :disk="'assets'"
              :file="{ path: img }"
              class="flex w-full overflow-hidden"
            />
          </transition>
        </template>
        <div
          v-if="product.media && product.media.length === 0"
          class="flex flex-col items-center justify-center w-full h-full text-gray-400 bg-gray-600 rounded-t"
        >
          <font-awesome-icon :icon="['fal', 'image-slash']" class="text-5xl" />
          {{ $t("no image available") }}
        </div>
      </div>

      <div
        v-if="!list"
        class="flex"
        :class="product.media && product.media.length > 0 ? 'mt-2' : 'mt-5'"
      >
        <a
          v-for="(img, i) in product.media"
          :key="'link_' + i"
          class="w-3 h-3 mx-1 transition-colors duration-300 border rounded-full shadow-lg cursor-pointer bg-theme-100 border-theme-500 hover:bg-theme-200"
          :class="{ 'bg-theme-400': showImage === i }"
          @click="showImage = i"
        ></a>
      </div>
    </div>

    <div
      class="flex p-4"
      :class="{
        'flex-col justify-between': !list,
        'items-center w-full': list,
      }"
    >
      <h2 class="font-bold tracking-wide" :class="{ 'mr-4': list }">
        {{ product.name }}
      </h2>
      <p class="text-sm" :class="{ 'mr-4': list }">
        {{ product.description }}
      </p>
      <p class="ml-auto font-mono" :class="{ 'mr-4': list }">
        {{ product.display_price }}
      </p>
    </div>

    <ShopCustomSingleProductDetail
      v-if="showDetails"
      :product="product"
    ></ShopCustomSingleProductDetail>
  </section>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  props: {
    product: Object,
    cart: {
      type: Boolean,
      default: false,
    },
    checkout: {
      type: Boolean,
      default: false,
    },
    list: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      quantity: 1,
      disabled: false,
      showDetails: false,
      showImage: 0,
      showProps: false,
    };
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  mounted() {
    if (this.product) {
      this.quantity = this.product.quantity;
    }
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
    }),
    addToCart(product) {
      this.api.post("/cart", {
        products: [
          {
            id: product.id,
            product_type: "internal",
            variations: [],
            quantity: this.quantity,
          },
        ],
      });
    },
    closeModal() {
      this.showDetails = false;
    },
  },
};
</script>
