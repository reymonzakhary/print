<template>
  <section
    class="group relative mb-4 flex cursor-pointer flex-col items-stretch rounded bg-white shadow-gray-200 transition-shadow hover:shadow-xl dark:bg-gray-700 dark:shadow-gray-900"
    :class="{
      'flex w-full items-center': list,
      'shadow-md': !checkout,
      'self-start border border-gray-300 hover:shadow-none dark:border-gray-900': checkout,
    }"
  >
    <div class="flex flex-col items-center">
      <div class="w-full overflow-hidden" :class="{ 'h-20 w-20': list, 'h-40': !list }">
        <template v-for="(img, idx) in product.media" :key="`detail_img_${idx}`">
          <transition name="fade">
            <ShopImage
              v-if="product.media && product.media.length > 0"
              v-show="showImage === idx"
              disk="assets"
              :file="{ path: img.path + '/' + img.name }"
              class="flex w-full overflow-hidden bg-contain"
            />
          </transition>
        </template>

        <div
          v-if="product.media && product.media.length === 0"
          class="flex h-full w-full flex-col items-center justify-center rounded-t bg-gray-600 text-gray-400"
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
          v-for="(img, idx) in product.media"
          :key="'link_' + idx"
          class="mx-1 h-3 w-3 cursor-pointer rounded-full border border-theme-500 bg-theme-100 shadow-lg transition-colors duration-300 hover:bg-theme-200"
          :class="{ 'bg-theme-400': showImage === idx }"
          @click="showImage = idx"
        />
      </div>
    </div>

    <button
      v-tooltip="product.status.description"
      class="sticky z-10 mt-4 block w-full text-center"
      style="top: 2rem"
      :class="[statusColor(product?.status, true, true, false)]"
    >
      {{ product?.status?.name }}
    </button>

    <div
      class="flex px-4 pb-4 pt-2"
      :class="{
        'flex-col justify-between': !list,
        'w-full items-center': list,
      }"
    >
      <!-- custom product -->
      <h2
        v-if="product.variation?.type !== 'print'"
        class="font-bold tracking-wide"
        :class="{ 'mr-4': list }"
      >
        {{ product.name }}
      </h2>
      <!-- print product -->
      <h2 v-else class="font-bold tracking-wide" :class="{ 'mr-4': list }">
        {{ product.variation.category.name }}
      </h2>
      <p class="text-sm" :class="{ 'mr-4': list }">
        {{ product.description }}
      </p>

      <ol>
        <details>
          <li
            v-for="(item, idx) in product.variation?.product"
            :key="`selected_list_item_${idx}`"
            class="flex flex-wrap items-center justify-between border-b py-2 text-sm last:border-0 dark:border-b-gray-800"
          >
            <div
              v-if="product.variation.product[idx - 1]?.divider !== item.divider"
              class="w-full py-2 text-sm font-bold uppercase tracking-wider text-gray-500"
            >
              {{ item.divider }}
            </div>
            <b class="w-1/2">
              {{ item.key }}
            </b>
            <span class="flex w-1/2 items-center">
              <!-- TODO: not returned atm -->
              <!-- <ShopThumbnail 
              v-if="item.media && item.media.length > 0"
              class="w-6"
              disk="assets"
              :file="{ path: item.media[0] }"
            /> -->
              {{ item.value }}
            </span>
          </li>
        </details>
      </ol>

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

      <section class="mt-4 flex items-center justify-between py-1 dark:border-gray-900">
        <span
          v-if="checkout"
          v-tooltip="
            product.reference !== null && product.reference?.length > 25 ? product.reference : ''
          "
          class="flex items-center text-sm"
        >
          <font-awesome-icon v-tooltip="'Reference'" :icon="['fal', 'message']" />
          <span v-if="product.reference && product.reference.length > 0" class="ml-2">
            {{ truncate(product.reference, 25) }}
          </span>
          <span v-else class="ml-2 text-gray-500">
            {{ $t("no reference") }}
          </span>
        </span>

        <span v-else class="relative flex w-full items-center">
          <input
            v-model="reference"
            v-tooltip="
              product.reference !== null && product.reference?.length > 25
                ? product.reference
                : $t('add reference here')
            "
            :disabled="disabled === 'reference' || checkout"
            class="input w-full p-0 pl-6 text-sm shadow-none"
            :class="{ 'bg-gray-200': loading === 'reference' }"
            :placeholder="$t('Reference for the product')"
            @blur="(updateCart(product), (loading = 'reference'))"
          />
          <font-awesome-icon
            :icon="['fal', 'message']"
            class="fa-sm absolute left-0 top-0 mx-1 mt-1 text-gray-500"
          />
          <font-awesome-icon
            v-if="loading === 'reference'"
            :icon="['fas', 'circle-notch']"
            class="fa-spin absolute right-0 top-0 mt-1 text-theme-500"
          />
        </span>
      </section>

      <!-- PROGRESS -->
      <template v-for="p in progress">
        <div
          v-if="
            p.active === product.id ||
            p.signature === progress.find((x) => x.active === product.id)?.signature
          "
          :key="`progress_bar_${p.active}`"
          class="py-2"
        >
          <!-- percentage -->
          <span
            class="p-0.5 text-center text-xs font-bold leading-none text-theme-500 transition-all"
          >
            {{ Math.round(p.current * (100 / p.total)) }}%
          </span>

          <!-- product -->
          <span
            v-if="p.product !== product.name"
            class="p-0.5 text-center text-xs font-medium leading-none text-theme-500 transition-all"
          >
            {{ p.product }}
          </span>

          <!-- blueprint action -->
          <span
            class="truncate text-center text-xs font-medium leading-none text-gray-300 transition-all"
          >
            {{ p.action }}
          </span>

          <!-- progress bar -->
          <div
            class="relative w-full rounded-full bg-gray-200 dark:bg-gray-700"
            :class="p.input === 'request' ? 'h-2' : 'h-4'"
          >
            <!-- steps -->
            <div
              class="absolute flex w-full items-center justify-between px-2"
              :class="p.input === 'request' ? 'h-2' : 'h-4'"
            >
              <div
                v-for="step in p.total"
                :key="`steps_${step}`"
                class="h-1 w-1 rounded-full"
                :class="[
                  step <= p.current ? 'bg-theme-700' : 'bg-white',
                  {
                    'h-2 w-2 border border-white bg-green-400':
                      step === p.current && step === p.total,
                  },
                  {
                    'h-2 w-2 animate-bounce border border-white bg-theme-200':
                      step === p.current && step !== p.total,
                  },
                ]"
              />
            </div>

            <!-- progress bar fill -->
            <div
              class="bg-animate rounded-full bg-gradient-to-r"
              :class="[
                `from-${p.color}-200 to-${p.color}-600 `,
                p.input === 'request' ? 'h-2' : 'h-4',
              ]"
              :style="`width: ${p.current * (100 / p.total)}%`"
            />

            <!-- attempts -->
            <span
              v-if="p.attempt"
              class="text-center text-xs font-medium leading-none text-theme-500 transition-all"
            >
              ({{ p.attempt }})</span
            >
          </div>
        </div>
      </template>

      <!-- necesary for tailwind to incorporate these styles as they are dynamically defined -->
      <div class="from-amber-200 to-amber-600" />
      <div class="from-orange-200 to-orange-600" />
      <div class="from-blue-200 to-blue-600" />

      <!-- ATTCHMENTS -->
      <OrderFiles
        v-if="product.attachments.length > 0"
        class="my-2"
        type="items"
        :object="product"
        :index="i"
        :editable="false"
        :ext_connection="false"
        :order_id="product.id"
      />
    </div>

    <ul class="mb-auto flex flex-col px-4 pb-2 text-sm">
      <li class="my-1 flex w-full flex-wrap justify-between">
        <span class="w-1/2 text-gray-500">
          {{ $t("price per piece") }}
        </span>
        <span class="w-1/2 text-right">
          <span :class="{ 'line-through': product.sale_price }">
            {{ product.display_price }}
          </span>
          <span v-if="product.sale_price">{{ product.sale_price }}</span>
        </span>

        <span class="w-1/2 text-gray-500">
          {{ $t("tax") }}
        </span>
        <span class="w-1/2 text-right">
          <span>
            {{ product.display_tax }}
          </span>
        </span>
      </li>

      <li class="flex w-full justify-between border-y py-1 dark:border-gray-900">
        <span class="w-1/2 text-gray-500">
          {{ $t("quantity") }}
        </span>
        <span class="w-1/2 text-right">
          <input
            v-model="quantity"
            type="number"
            :disabled="checkout"
            class="input p-0 text-right text-sm shadow-none"
            :class="{
              'border-none bg-gray-100 shadow-none hover:bg-gray-100': checkout,
            }"
          />
        </span>
      </li>

      <li class="my-1 flex w-full justify-between text-base">
        <span class="w-1/2">
          {{ $t("total") }}
        </span>
        <span class="w-1/2 text-right">
          <b>{{ product.display_total }}</b>
        </span>
      </li>
    </ul>

    <transition name="fade">
      <CartCustomSingleProductDetail
        v-if="showDetails"
        :product="product"
        @on-set-show-details="showDetails = $event"
      />
    </transition>
  </section>
</template>

<script>
import { mapState, mapMutations, mapGetters } from "vuex";
import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";
import _ from "lodash";
export default {
  mixins: [managerhelper, helper],
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
    i: {
      type: Number,
      rquired: false,
    },
  },
  setup() {
    const api = useAPI();
    const { handleError } = useMessageHandler();
    return { api, handleError };
  },
  data() {
    return {
      quantity: 1,
      reference: "",
      disabled: false,
      showDetails: false,
      showImage: 0,
      showProps: false,
      render: {},
      preview: false,
      loading: false,
    };
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
      progress: (state) => state.cart.progress,
      modalName: (state) => state.fm.modal.modalName,
    }),
    ...mapGetters({
      statusColor: "statuses/statusColor",
    }),
  },
  watch: {
    quantity: _.debounce(function (newVal, oldVal) {
      if (this.cart && newVal !== oldVal) {
        this.updateCart(this.product, newVal);
      }
    }, 300),
    progress: {
      deep: true,
      immediate: true,
      handler(v) {
        return v;
      },
    },
    product: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    modalName(v) {
      return v;
    },
  },
  mounted() {
    if (this.product?.qty) {
      this.quantity = this.product.qty;
    }
    if (this.product?.reference) {
      this.reference = this.product.reference;
    }
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
    }),
    truncate: (str, len) => (str.length > len ? str.substring(0, len) : str),
    convertPrefix(prefix) {
      if (prefix.includes("UploadFileAction")) return "uploaded";
      if (prefix.includes("preview")) return "preview";
    },
    getCart () {
      this.api
              .get("/cart")
              .then((response) => {
                this.disabled = false;
                this.loading = false;
                this.set_cart(response.meta);
                this.set_cart_products(response.data.products);
              })
              .catch((error) => {
                this.handleError(error);
              });
    },
    updateCart(product) {
      this.disabled = true;
      const payload = {
        product_type: "internal",
        quantity: this.quantity,
        variations: [],
      };

      if (this.reference) {
        Object.assign(payload, { reference: this.reference.toString() });
      }

      this.api
        .put(`/cart/${product.id}`, payload)
        .then(()=>{
          this.getCart();
        })
        .catch((error) => {
          if (error.status == 422) {
            this.quantity = this.product.qty
          }
          this.handleError(error)
        });
    },
    closeModal() {
      this.preview = false;
    },
  },
};
</script>

<style>
.bg-animate {
  background-size: 200%;

  -webkit-animation: AnimationName 2s ease-in-out infinite;
  -moz-animation: AnimationName 2s ease-in-out infinite;
  animation: AnimationName 2s ease-in-out infinite;
}

@keyframes AnimationName {
  0%,
  100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}
</style>
