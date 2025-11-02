<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{ product.name }}</span>
    </template>

    <template #modal-body>
      <section
        class="relative flex w-full min-w-[50vw]"
        style="max-height: calc(100vh - 10rem)"
      >
        <div
          v-if="product.media && product.media.length > 0"
          class="sticky top-0 flex flex-col w-1/2"
        >
          <DetailsImage
            disk="assets"
            :file="{ path: bigImage }"
            :size="300"
            class="overflow-hidden rounded"
          />
          <div
            class="flex flex-wrap items-center w-full py-2 space-x-4 overflow-hidden rounded"
          >
            <div
              v-for="(img, i) in product.media"
              :key="`image_${i}`"
              class="object-contain my-2 transition-colors border-2 rounded-md cursor-pointer hover:border-theme-200"
              @click="bigImage = img"
            >
              <GridThumbnail disk="assets" :file="{ path: img }" />
            </div>
          </div>
        </div>

        <div
          class="flex flex-col justify-between w-full p-4"
          :class="{ 'w-1/2': product.media && product.media.length > 0 }"
        >
          <div
            class="sticky top-0 z-10 pb-2 bg-white border-b dark:bg-gray-700 dark:border-gray-900"
          >
            <h2 class="font-bold tracking-wide uppercase">
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

          <ul class="flex flex-col mt-6 text-sm border-b dark:divide-gray-900">
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
                <template
                  v-for="(value, key, i) in prop.value"
                  :key="'value_' + key"
                >
                  <div class="flex flex-wrap w-full my-1">
                    <span class="w-1/2 pr-2 lowercase truncate">
                      {{ key }}:
                    </span>
                    <span v-tooltip="value" class="w-1/2 truncate max-w-prose">
                      {{ value }}
                    </span>
                  </div>
                </template>
              </span>
              <span v-else class="w-1/2 truncate"> {{ prop.value }}</span>
            </li>
          </ul>

          <ul
            v-if="product.variations"
            class="flex flex-col pb-4 mt-6 space-y-2 text-sm border-b"
          >
            <p class="font-bold tracking-wide uppercase">
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
                class="flex flex-col"
              >
                <span
                  v-for="(opt, i) in variation.options"
                  :key="`option_${opt.name}_${i}`"
                >
                  <input
                    type="checkbox"
                    :name="opt.name"
                    :value="i"
                    @click="addMultiOption(opt.id, $event.target.checked)"
                  />
                  <label :for="opt.name"> {{ opt.name }}</label>
                </span>
              </div>

              <select
                v-if="variation.input_type === 'single'"
                class="input"
                @change="addSingleOption($event.target.value, variation)"
              >
                <option :value="null">{{ $t("select option") }}</option>
                <option
                  v-for="(opt, i) in variation.options"
                  :key="`option_${opt.name}_${i}`"
                  :value="opt.id"
                >
                  {{ opt.name }}
                </option>
              </select>
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
                  {{ product.display_price }}
                  {{ product.display_tax }}
                </span>
                {{ product.sale_price }}
              </span>
            </li>
            <li class="flex justify-between w-full text-base">
              <span class="w-1/2">
                <b>{{ $t("price total") }}</b>
              </span>
              <span class="w-1/2 text-right">
                <b>{{ product.display_total }}</b>
              </span>
            </li>
          </ul>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <footer
        class="flex items-center justify-between w-1/2 p-4 mt-auto ml-auto text-right"
      >
        <strong class="mr-4">{{ product.display_price }}</strong>

        <button
          class="w-full px-2 py-1 text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
          @click="addCustomToCart(product)"
        >
          {{ $t("add to cart") }}
        </button>
      </footer>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  props: {
    product: Object,
  },
emits: ['onSetShowDetails'],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      quantity: 1,
      disabled: false,
      bigImage: {},
      sendProduct: {},
      sendVariations: [],
    };
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
    }),
  },
  watch: {
    bigImage(v) {
      return v;
    },
    product: {
      deep: true,
      handler(v) {
        this.bigImage = this.product.media[0];
      },
    },
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
      set_cart_flag: "cart/set_cart_flag",
    }),
    addSingleOption(id, variation) {
      if (id) {
        let position;
        const flag = false;

        for (const option of variation.options) {
          if (this.sendVariations.some((obj) => obj.id == option.id)) {
            position = this.sendVariations.findIndex(
              (variation) => variation.id === id,
            );
            this.sendVariations.splice(position, 1);
            break;
          }
        }

        this.sendVariations.push({ id: id });
      }
    },
    addMultiOption(id, check) {
      let position;
      if (check === true) {
        this.sendVariations.push({ id: id });
      } else {
        position = this.sendVariations.findIndex(
          (variation) => variation.id === id,
        );
        this.sendVariations.splice(position, 1);
      }
    },
    addPrintToCart(product) {
      this.api
        .post("/cart", { data: [this.sendProduct] })
        .then(() => {
          this.api
            .put("/cart", {
              product: [
                {
                  quantity: this.quantity,
                  product_type: "internal",
                  product_id: this.sendProduct.id,
                  variations: [this.sendVariations],
                },
              ],
            })
            .then(() => {
              this.set_cart_flag("view"), this.$emit("onSetShowDetails", false);
              this.$router.push("/cart");
            })
            .catch((error) => this.handleError(error));
        })
        .catch((error) => this.handleError(error));
    },
    addCustomToCart(product) {
      this.api
        .post("/cart", {
          quantity: this.quantity,
          product: product.id,
          vairations: this.sendVariations,
        })
        .then(() => {
          this.set_cart_flag("view"), this.$emit("onSetShowDetails", false);
          this.$router.push("/cart");
        })
        .catch((error) => this.handleError(error));
    },
    closeModal() {
      this.$emit("onSetShowDetails", false)
    },
  },
};
</script>
