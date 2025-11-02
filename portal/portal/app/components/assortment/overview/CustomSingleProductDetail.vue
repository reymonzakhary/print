<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header class="capitalize">
      {{ product.name }}
    </template>

    <template #modal-body>
      <section
        v-if="Object.keys(product).length > 0"
        class="relative flex w-full min-w-[50vw]"
        style="max-height: calc(100vh - 10rem)"
      >
        <!-- LEFT -->
        <div
          v-if="product && product.media && product.media.length > 0"
          class="sticky top-0 flex flex-col w-1/2"
        >
          <DetailsImage
            disk="assets"
            :file="{ path: bigImage }"
            :size="300"
            class="overflow-hidden rounded"
          />
          <div
            v-if="product.media.length > 1"
            class="flex flex-wrap items-center w-full py-2 space-x-4 overflow-hidden rounded"
          >
            <div
              v-for="(img, i) in product.media"
              :key="`image_${i}`"
              class="object-contain h-full p-2 transition-colors rounded cursor-pointer hover:bg-theme-200"
              @click="bigImage = img"
            >
              <GridThumbnail disk="assets" :file="{ path: img }" />
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div
          class="flex flex-col justify-between w-full p-4"
          :class="{
            'w-1/2': product.media && product.media.length > 0,
          }"
        >
          <div
            class="sticky top-0 z-10 pb-2 bg-white border-b dark:bg-gray-700 dark:border-gray-900"
          >
            <h2 class="font-bold tracking-wide">
              {{ product.name }}
            </h2>
            <p class="text-gray-500">{{ product.description }}</p>
          </div>
          <p class="mt-4 font-mono">
            <span v-if="product.ean" class="text-xs font-bold">EAN:</span>
            {{ product.ean }}
          </p>
          <p class="font-mono">
            <span v-if="product.art_nr" class="text-xs font-bold">
              ART_NR:
            </span>
            {{ product.art_num }}
          </p>

          <ul
            v-if="
              product.properties &&
              product.properties.props &&
              product.properties.props.length > 0
            "
            class="flex flex-col mt-6 text-sm border-b dark:divide-gray-900"
          >
            <p class="font-bold tracking-wide uppercase">
              {{ $t("properties") }}
            </p>

            <li
              v-for="(prop, idx) in product.properties.props"
              :key="'prop_' + prop.key + idx"
              class="flex flex-wrap justify-between w-full py-2"
            >
              <b
                :class="
                  typeof prop.value === 'object'
                    ? 'text-xs sticky top-14 bg-white dark:bg-gray-700 w-full z-0 py-1'
                    : 'w-1/2'
                "
                class="truncate"
              >
                {{ prop.key }}
              </b>

              <span
                v-if="typeof prop.value === 'object'"
                class="w-full divide-y divide-dashed dark:divide-gray-900"
              >
                <div
                  v-for="(value, key, i) in prop.value"
                  :key="'value_' + key"
                  class="flex flex-wrap w-full my-1"
                >
                  <span class="w-1/2 pr-2 lowercase truncate">
                    {{ key }}:
                  </span>
                  <span v-tooltip="value" class="w-1/2 truncate max-w-prose">
                    {{ value }}
                  </span>
                </div>
              </span>

              <span v-else class="w-1/2 truncate">
                {{ prop.value }}
              </span>
            </li>
          </ul>

          <!-- variations -->
          <ul
            v-if="product.variations && product.variations.length > 0"
            class="flex flex-col pb-4 mt-6 space-y-2 text-sm border-b"
          >
            <p class="font-bold tracking-wide uppercase border-b">
              {{ $t("options") }}
            </p>
            <li
              v-for="(variation, i) in product.variations"
              :key="`variation_${variation.name}_${i}`"
            >
              <span class="text-sm font-bold">
                {{ variation.name }}
              </span>

              <div
                v-if="variation.input_type === 'multiple'"
                class="flex flex-col divide-y"
              >
                <span
                  v-for="(opt, i) in variation.options"
                  :key="`option_${opt.name}_${i}`"
                  class="flex flex-wrap items-center p-2 my-2 bg-gray-100 divide-y rounded"
                >
                  <div
                    class="cursor-pointer hover:text-theme-500"
                    @click.stop="addMultiOption(opt, i)"
                  >
                    {{ opt.name }}
                  </div>

                  <template
                    v-if="
                      sendVariations &&
                      sendVariations.length > 0 &&
                      sendVariations.find((option) => option.id === opt.id)
                    "
                  >
                    <div
                      v-for="(validation, i) in opt.properties.validations"
                      :key="validation + '_' + i"
                      class="flex items-center w-full"
                    >
                      <span
                        v-for="(value, key, idx) in validation"
                        :key="value + idx"
                        class="flex items-center w-full my-2"
                      >
                        <b class="w-full md:w-1/3">{{ key }}</b>
                      </span>
                    </div>
                  </template>
                </span>
              </div>

              {{ opt.name }}
            </li>
          </ul>

          <!-- validations -->
          <ul
            v-if="
              product.properties &&
              product.properties.validations &&
              product.properties.validations.length > 0
            "
            class="flex flex-col pb-4 mt-6 space-y-2 text-sm border-b"
          >
            <p class="font-bold tracking-wide uppercase border-b">
              {{ $t("validations") }}
            </p>
            <li
              v-for="(validation, i) in product.properties.validations"
              :key="validation + '_' + i"
              class="flex items-center w-full"
            >
              <span
                v-for="(value, key, idx) in validation"
                :key="value + idx"
                class="flex items-center w-full my-1"
              >
                <b class="w-full md:w-1/3">
                  {{ key }}
                </b>
              </span>
            </li>
          </ul>

          <p
            v-if="product.products && product.products.length > 0"
            class="mt-6 text-sm font-bold tracking-wide uppercase"
          >
            {{ $t("package contents") }}
          </p>
          <ul
            v-if="product.products && product.products.length > 0"
            class="flex flex-wrap pb-4 overflow-auto text-sm border-b divide-y divide-dotted max-h-min"
          >
            <li
              v-for="(prod, i) in product.products"
              :key="prod + '_' + i"
              class="flex items-center w-1/2"
            >
              <div class="flex items-center w-full p-2">
                <Thumbnail
                  disk="assets"
                  :file="{ path: prod.media[0] }"
                  class="mr-2"
                />
                <div>
                  <h2 class="mr-4 font-bold">
                    {{ prod.name }}
                  </h2>
                  <p class="mr-4">
                    {{ prod.description }}
                  </p>
                </div>
              </div>
            </li>
          </ul>

          <ul class="flex flex-col mt-6 mb-auto text-sm">
            <li class="flex justify-between w-full">
              <span class="w-1/2">
                <b>{{ $t("quantity") }}</b>
              </span>
              <span class="w-1/2 text-right">
                <input
                  v-model="quantity"
                  type="number"
                  :disabled="disabled"
                  class="p-0 text-sm text-right input"
                />
              </span>
            </li>

            <li class="flex justify-between w-full">
              <span class="w-1/2">
                <b>{{ $t("price per piece") }}</b></span
              >
              <span class="w-1/2 text-right">
                <span :class="{ 'line-through': product.sale_price }">
                  {{ product.display_price }}</span
                >
                {{ product.sale_price }}</span
              >
            </li>
            <!-- <li class="flex justify-between w-full text-base">
							<span class="w-1/2">
								<b>{{ $t("price total") }}</b></span
							>
							<span class="w-1/2 text-right">
								<b>{{ product.price_total }}</b></span
							>
						</li> -->
          </ul>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <footer
        class="flex items-center justify-between w-1/2 p-4 mt-auto ml-auto text-right"
      >
        <strong class="mr-4">{{ product.display_price }}</strong>
      </footer>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
export default {
  props: {
    prod: Object,
    cart: {
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
      quantity: null,
      disabled: false,
      bigImage: {},
      loading: false,
    };
  },
  watch: {
    quantity(v) {
      this.sendObject.set("quantity", v);
    },
    product: {
      deep: true,
      handler(v) {
        this.bigImage = this.product.media[0];

        return v;
      },
    },

    bigImage(v) {
      return v;
    },
  },
  created() {
    if (this.prod) {
      this.get_custom_product(this.prod.id);
    }
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
      product: (state) => state.product.active_custom_product,
    }),
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
      set_cart_flag: "cart/set_cart_flag",
    }),
    ...mapActions({
      get_custom_product: "product/get_custom_product",
    }),
    // addSingleOption(id, variation) {
    // 	if (id) {
    // 		let position;
    // 		let flag = false;

    // 		for (const option of variation.options) {
    // 			if (this.sendVariations.some((obj) => obj.id == option.id)) {
    // 				position = this.sendVariations.findIndex(
    // 					(variation) => variation.id === id
    // 				);
    // 				this.sendVariations.splice(position, 1);
    // 				break;
    // 			}
    // 		}

    // 		this.sendVariations.push({ id: id });
    // 	}
    // },
    addToCart(product) {
      this.api
        .post("/cart", {
          products: [
            {
              id: product.id,
              product_type: "internal",
              variations: [],
              quantity: 1,
            },
          ],
        })
        .then(() => {
          this.set_cart_flag("view"), (this.$parent.showDetails = false);
          this.$router.push("/cart");
        });
    },
    closeModal() {
      this.$parent.showDetails = false;
    },
    close() {
      this.$parent.showDetails = false;
    },
  },
};
</script>
