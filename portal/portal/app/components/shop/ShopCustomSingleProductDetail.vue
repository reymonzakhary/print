<template>
  <ConfirmationModal v-if="Object.keys(product).length > 0" classes="w-auto" @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ product.name }}</span>
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
          class="sticky top-0 flex w-1/2 flex-col"
        >
          <DetailsImage disk="assets" :file="{ path: bigImage }" class="overflow-hidden rounded" />
          <div
            v-if="product.media.length > 1"
            class="flex w-full flex-wrap items-center space-x-4 overflow-hidden rounded py-2"
          >
            <div
              v-for="(img, i) in product.media"
              :key="`image_${i}`"
              class="my-2 cursor-pointer rounded-md border-2 object-contain transition-colors hover:border-theme-200"
              @click="bigImage = img"
            >
              <GridThumbnail disk="assets" :file="{ path: img }" />
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div
          class="flex w-full flex-col justify-between p-4"
          :class="{
            'w-1/2': product.media && product.media.length > 0,
          }"
        >
          <div
            class="sticky top-0 z-10 border-b bg-white pb-2 dark:border-gray-900 dark:bg-gray-700"
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
            <span v-if="product.art_nr" class="text-xs font-bold"> ART_NR: </span>
            {{ product.art_num }}
          </p>

          <ul
            v-if="
              product.properties && product.properties.props && product.properties.props.length > 0
            "
            class="mt-6 flex flex-col border-b text-sm dark:divide-gray-900"
          >
            <p class="font-bold uppercase tracking-wide">
              {{ $t("properties") }}
            </p>

            <li
              v-for="(prop, idx) in product.properties.props"
              :key="'prop_' + prop.key + idx"
              class="flex w-full flex-wrap justify-between py-2"
            >
              <b
                :class="
                  typeof prop.value === 'object'
                    ? 'sticky top-14 z-0 w-full bg-white py-1 text-xs dark:bg-gray-700'
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
                <template v-for="(value, key) in prop.value" :key="'value_' + key">
                  <div class="my-1 flex w-full flex-wrap">
                    <span class="w-1/2 truncate pr-2 lowercase"> {{ key }}: </span>
                    <span v-tooltip="value" class="w-1/2 max-w-prose truncate">
                      {{ value }}
                    </span>
                  </div>
                </template>
              </span>

              <span v-else class="w-1/2 truncate"> {{ prop.value }}</span>
            </li>
          </ul>
          <!-- variations -->
          <ul
            v-if="product.variations && product.variations.length > 0"
            class="mt-6 flex flex-col space-y-2 border-b pb-4 text-sm"
          >
            <p class="border-b font-bold uppercase tracking-wide">
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
                v-if="
                  variation.input_type === 'multiple' ||
                  (variation.input_type === 'single' && variation.options.length === 1)
                "
                class="flex flex-col divide-y"
              >
                <span
                  v-for="(opt, i) in variation.options"
                  :key="`option_${opt.name}_${i}`"
                  class="my-2 flex flex-wrap items-center divide-y rounded bg-gray-100 p-2"
                >
                  <div
                    class="cursor-pointer hover:text-theme-500"
                    @click.stop="addMultiOption(opt, i)"
                  >
                    <input
                      type="checkbox"
                      :name="opt.name"
                      :value="i"
                      :checked="
                        sendVariations &&
                        sendVariations.length > 0 &&
                        sendVariations.find((option) => option.id === opt.id)
                          ? true
                          : false
                      "
                    />
                    {{ opt.name }}
                  </div>

                  <template
                    v-if="
                      sendVariations &&
                      sendVariations.length > 0 &&
                      sendVariations.find((option) => option.id === opt.id) &&
                      opt.properties &&
                      opt.properties.validations
                    "
                  >
                    <div
                      v-for="(validation, i) in opt.properties.validations"
                      :key="validation + '_' + i"
                      class="flex w-full items-center"
                    >
                      <span
                        v-for="(value, key, idx) in validation"
                        :key="value + idx"
                        class="my-2 flex w-full items-center"
                      >
                        <b class="w-full md:w-1/3">{{ key }}</b>
                        <ShopCustomProductVariationFileUpload
                          :optie="opt"
                          :option="
                            sendVariations && sendVariations.length > 0
                              ? sendVariations.find((option) => option.id === opt.id)
                              : {}
                          "
                          :validation="{
                            key: key,
                            value: value,
                          }"
                          :index="i"
                        />
                      </span>
                    </div>
                  </template>
                </span>
              </div>

              <select
                v-if="variation.input_type === 'single' && variation.options.length > 1"
                class="input"
                @change="addSingleOption($event.target.value, variation, i)"
              >
                <!-- <option :value="null">
									{{ $t("select option") }}
								</option> -->
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

          <!-- validations -->
          <ul
            v-if="
              product.properties &&
              product.properties.validations &&
              product.properties.validations.length > 0
            "
            class="mt-6 flex flex-col space-y-2 border-b pb-4 text-sm"
          >
            <p class="border-b font-bold uppercase tracking-wide">
              {{ $t("validations") }}
            </p>
            <li
              v-for="(validation, i) in product.properties.validations"
              :key="validation + '_' + i"
              class="flex w-full items-center"
            >
              <span
                v-for="(value, key, idx) in validation"
                :key="value + idx"
                class="my-1 flex w-full items-center"
              >
                <b class="w-full md:w-1/3">
                  {{ key }}
                </b>
                <ShopCustomProductFileUpload
                  :product="product"
                  :validation="{
                    key: key,
                    value: value,
                  }"
                  :index="i"
                  @update:file="sendObject.set($event.key, $event.file)"
                />
              </span>
            </li>
          </ul>

          <p
            v-if="product.products && product.products.length > 0"
            class="mt-6 text-sm font-bold uppercase tracking-wide"
          >
            {{ $t("package contents") }}
          </p>
          <ul
            v-if="product.products && product.products.length > 0"
            class="flex max-h-min flex-wrap divide-y divide-dotted overflow-auto border-b pb-4 text-sm"
          >
            <li
              v-for="(prod, i) in product.products"
              :key="prod + '_' + i"
              class="flex w-1/2 items-center"
            >
              <div class="flex w-full items-center p-2">
                <Thumbnail disk="assets" :file="{ path: prod.media[0] }" class="mr-2" />
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

          <ul class="mb-auto mt-6 flex flex-col text-sm">
            <li v-if="!product.hasBlueprint" class="my-1 flex w-full justify-between">
              <span class="w-1/2">
                <b>{{ $t("quantity") }}</b>
              </span>
              <span class="w-1/2 text-right">
                <input
                  v-model="quantity"
                  type="number"
                  :disabled="disabled"
                  class="input p-0 text-right text-sm"
                />
              </span>
            </li>

            <li class="my-1 flex w-full justify-between">
              <span class="w-1/2">
                <b>{{ $t("price per piece") }}</b>
              </span>
              <span class="w-1/2 text-right">
                <span :class="{ 'line-through': product.sale_price }">
                  {{ product.display_price }}
                </span>
                <span v-if="product.sale_price">
                  {{ product.sale_price }}
                </span>
              </span>
            </li>

            <li class="my-1 flex w-full justify-between text-base">
              <span class="w-1/2">
                <b>{{ $t("price total") }}</b></span
              >
              <span class="w-1/2 text-right">
                <b>
                  {{ currencyFormatter(product.price * quantity) }}
                </b>
              </span>
            </li>
          </ul>
        </div>

        <!-- <pre>{{ workflow }}</pre> -->
      </section>

      <ConfirmationModal v-if="preview" :close-button="false" classes="w-auto">
        <template #modal-header>
          <h2 class="capitalize">{{ product.name }}</h2>
        </template>
        <template #modal-body>
          <div style="height: 80vh; width: 90vw">
            <iframe
              :src="$store.state.fm.filemanager.PDFtoShow"
              allowfullscreen
              height="100%"
              width="100%"
            />
          </div>
        </template>
        <template #cancel-button>
          <button
            class="ml-auto mr-2 flex items-center rounded bg-red-600 px-2 py-1 text-white shadow-md shadow-red-300 transition-colors hover:bg-red-700"
            :class="{ 'animate-pulse': loading }"
            @click="rejectPreviews()"
          >
            <font-awesome-icon :icon="['fas', 'xmark']" class="mr-2 text-lg" />
            {{ $t("reject preview(s)") }}
          </button>
        </template>
        <template #confirm-button>
          <button
            class="ml-2 mr-auto flex items-center rounded bg-green-600 px-2 py-1 text-white shadow-md shadow-green-300 transition-colors hover:bg-green-700"
            :class="{ 'animate-pulse': loading }"
            @click="approvePreviews()"
          >
            <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-lg" />
            {{ $t("approve preview(s)") }}
          </button>
        </template>
      </ConfirmationModal>
    </template>

    <template #confirm-button>
      <footer class="ml-auto mt-auto flex w-1/2 items-center justify-between p-4 text-right">
        <button
          v-tooltip="
            !me.ctx.find((item) => item.member === true) &&
            $t(`Only members can add items to the cart`)
          "
          class="w-full rounded-full border px-2 py-1 text-themecontrast-500 transition-colors"
          :class="
            !me.ctx.find((item) => item.member === true)
              ? 'border-gray-500 bg-gray-400'
              : 'border-theme-300 bg-theme-500 hover:bg-theme-600'
          "
          :disabled="!me.ctx.find((item) => item.member === true)"
          @click="!loading ? addCustomToCart('cart') : ''"
        >
          <span v-if="!loading">
            {{ $t("add to cart") }}
          </span>
          <span v-if="loading">
            {{ $t("addding to cart") }}
          </span>
        </button>

        <button
          v-if="
            workflow &&
            workflow.find((wf) => wf.stage.blueprint_ns === 'shop') &&
            ns === 'shop' &&
            product.hasBlueprint
          "
          class="w-full rounded-full border border-theme-500 px-2 py-1 text-theme-500 transition-colors hover:bg-theme-100"
          @click="!loading ? addCustomToCart('shop') : ''"
        >
          {{
            //prettier-ignore
            $t(workflow.find((wf) => wf.stage.blueprint_ns === "shop").stage.button)
          }}

          <transition name="slide">
            <div
              v-if="loading && ns === 'shop'"
              class="fixed bottom-0 left-0 right-0 top-0 z-50 flex flex-wrap items-center justify-center bg-gradient-to-br from-gray-700 via-gray-900 to-gray-700 text-lg font-bold text-white drop-shadow"
            >
              <div class="flex w-full justify-center">
                <font-awesome-icon
                  :icon="['fad', 'gear']"
                  class="animate-spin-slow opacity-60"
                  size="10x"
                />
                <font-awesome-icon
                  :icon="['fad', 'gear']"
                  class="-ml-4 -mt-6 animate-spin-reverse-slow"
                  size="8x"
                />
              </div>
              <span v-if="ns === 'shop'">
                {{ $t("generating preview(s)") }}
              </span>
            </div>
          </transition>
        </button>
      </footer>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
export default {
  props: {
    product: Object,
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    const authStore = useAuthStore();
    return { api, handleError, handleSuccess, authStore };
  },
  data() {
    return {
      quantity: 1,
      ns: "shop",
      disabled: false,
      bigImage: {},
      sendProduct: {}, // NOTE: not used
      sendVariations: [],
      sendObject: new FormData(),
      preview: false,
      loading: false,

      // currency settings
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
    };
  },
  watch: {
    /**
     * Watching changes in quantity data to add to payload
     * @param {Number} v
     */
    quantity(v) {
      this.sendObject.set("quantity", v);
    },
    product: {
      deep: true,
      handler(v) {
        this.bigImage = this.product.media[0];

        this.sendObject.append("product", v.sku_id);
        this.sendObject.append("quantity", this.quantity);
        this.sendObject.append("ns", this.ns);

        return v;
      },
    },
    sendVariations: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    sendObject: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    bigImage(v) {
      return v;
    },
  },
  created() {
    // Set the namespace data to toggle dynamic parts of the interface
    this.ns =
      this.workflow?.find((wf) => wf.stage.blueprint_ns === "shop")?.stage.blueprint_ns ?? "cart";

    // Build the payload
    if (this.product) {
      this.bigImage = this.product.media[0];

      this.sendObject.append("product", this.product.sku_id);
      this.sendObject.append("quantity", this.quantity);
      this.sendObject.append("ns", this.ns);
      if (this.product.variations.length > 0) {
        for (let i = 0; i < this.product.variations.length; i++) {
          const variation = this.product.variations[i];
          if (variation.input_type === "single") {
            this.sendVariations.push({ id: variation.options[0].id });
            this.sendObject.set(`variations[${i}][id]`, variation.options[0].id);
          }
        }
      }
      // this.get_custom_product(this.prod.id);
    }
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
      workflow: (state) => state.shop.workflow,
      me: (state) => state.settings.me,
      // product: (state) => state.shop.active_custom_product,
    }),
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
      set_cart_flag: "cart/set_cart_flag",
    }),
    ...mapActions({
      get_custom_product: "shop/get_custom_product",
    }),

    /**
     * Add single option to payload
     * @param {Number} id
     * @param {Array} variation
     * @param {Number} i
     * @param {Object} opt
     */
    addSingleOption(id, variation, i, opt) {
      if (id) {
        let position;
        const flag = false;

        for (const option of variation.options) {
          if (this.sendVariations.some((obj) => obj.id == option.id)) {
            position = this.sendVariations.findIndex((variation) => variation.id === id);
            this.sendVariations.splice(position, 1);
            this.sendObject.delete(`variations[${i}][id]`);
            break;
          }
        }

        this.sendVariations.push({ id: id });
        this.sendObject.set(`variations[${i}][id]`, id);
      }
    },

    /**
     * Add multiple options to payload
     * @param {Array} opt
     * @param {Number} i
     */
    addMultiOption(opt, i) {
      let position;
      if (!this.sendVariations.find((option) => option.id === opt.id)) {
        const obj = { id: opt.id };
        if (opt.validations) {
          Object.assign(obj, opt.validations);
        }
        this.sendVariations.push(obj);

        this.sendObject.set(`variations[${i}][id]`, opt.id);
      } else {
        position = this.sendVariations.findIndex((variation) => variation.id === opt.id);
        this.sendVariations.splice(position, 1);
        this.sendObject.delete(`variations[${i}][id]`);
      }
    },

    /**
     * Adding a custom product to the cart
     * @param {String} [ns] Optional namespace which indicates where the blueprint should be handled: shop or cart.
     */
    addCustomToCart(ns) {
      this.loading = true;

      // update the namespace based on clicked (shop/cart)
      if (ns) {
        this.ns = ns;
        this.sendObject.set("ns", ns);
      }

      const object = this.sendObject;

      this.api
        .post("/cart", object, { isFormData: true })
        .then((response) => {
          // If the namespace is shop, we want to handle the blueprint in the shop before heading to the cart
          if (this.ns === "shop") {
            this.loading = false;
            this.preview = true;

            if (response.data.cart_variation) {
              this.sendObject.append("cart_variation", response.data.cart_variation);
            }
            this.$store.commit("fm/filemanager/setPDF", response.data.url);
          } else {
            // If the namespace is cart, we want to handle the blueprint in the que while heading to cart
            this.set_cart_flag("view");
            this.$parent.showDetails = false;

            this.$router.push("/cart");

            this.loading = false;
          }
        })
        .catch((error) => {
          this.handleError(error);
          this.loading = false;
        });
    },

    // TODO: Very KJE specific and unused, try to make more general.... so sad
    approvePreviews() {
      this.ns = "cart"; // approve files
      this.sendObject.set("ns", "cart");
      this.preview = false; // toggle preview modal
    },
    rejectPreviews() {
      this.preview = false; // toggle preview modal
    },

    /**
     * Format number to currency
     * @param {Number} number
     */
    currencyFormatter(number) {
      if (number) {
        return new Intl.NumberFormat(this.iso, {
          style: "currency",
          currency: this.currency,
        }).format(number);
      }

      return "";
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
