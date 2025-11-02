<template>
  <section class="flex p-4">
    <section class="flex w-full flex-wrap space-x-4">
      <div
        v-for="(product, i) in cart_products"
        :key="product.id"
        class="md:1/3 group flex w-full flex-col items-stretch rounded sm:w-1/2 xl:w-2/12"
      >
        <CartCustomSingleProduct
          v-if="!checkout || product.status.code === 302"
          :product="product"
          :cart="true"
          :checkout="checkout"
          :i="i"
          class="w-full"
        />

        <footer v-if="!checkout" class="mt-auto w-full p-4 text-right">
          <button
            class="mt-2 block w-full rounded border border-red-500 px-2 py-1 text-sm text-red-500 transition-colors hover:bg-red-100"
            @click="remove(product)"
          >
            {{ $t("remove") }}
          </button>
        </footer>
      </div>

      <transition name="slide">
        <div v-if="!checkout" class="md:1/3 w-full sm:w-1/2 xl:w-2/12">
          <div
            class="flex h-full flex-wrap items-center rounded bg-gray-200 py-4 text-center dark:bg-gray-900"
          >
            <p
              v-if="$store.state.orders.active_order_data.items !== 'undefined'"
              class="my-2 w-full text-xl font-bold text-gray-600"
            >
              {{ $t("add another") }}
            </p>

            <font-awesome-icon
              :icon="['fad', 'people-carry-box']"
              class="fa-4x mx-auto my-2 w-48 text-gray-600"
            />

            <div class="my-2 w-full">
              <button
                class="mx-auto rounded-full bg-theme-400 px-4 py-2 text-themecontrast-400 transition-colors duration-75 hover:bg-theme-500"
                @click="$router.push('/shop')"
              >
                <font-awesome-icon :icon="['fal', 'box']" class="mr-2" />
                {{ $t("add product") }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </section>

    <div class="z-20 ml-auto w-full sm:w-4/6 xl:w-1/6">
      <transition name="slide">
        <CartPrice v-if="!checkout && cart_products && cart_products.length > 0" />
      </transition>

      <transition name="slide">
        <CartCheckout v-if="checkout" />
      </transition>
    </div>

    <!-- modals -->
    <transition name="fade">
      <ConfirmationModal v-if="thanks" :cancel-button="false">
        <template #modal-header>
          {{ $t("Order succesfully created") }}
        </template>

        <template #modal-body>
          {{ $t("Order succesfully created") }}
        </template>

        <template #confirm-button>
          <button
            class="mx-auto rounded-full bg-green-500 px-2 py-1 text-sm text-white transition-colors duration-75 hover:bg-green-600"
            @click="(set_checkout(false), (thanks = false))"
          >
            <font-awesome-icon :icon="['fal', 'thumbs-up']" class="mr-2" />
            {{ $t("thank you") }}
          </button>
        </template>
      </ConfirmationModal>
    </transition>

    <transition name="fade">
      <component :is="modalName" :user="me.id" />
    </transition>
  </section>
</template>

<script>
import { mapState, mapMutations } from "vuex";
// modal views
import NewFile from "~/components/filemanager/views/NewFile.vue";
import NewFolder from "~/components/filemanager/views/NewFolder.vue";
import Upload from "~/components/filemanager/views/FileUpload.vue";
import Delete from "~/components/filemanager/views/Delete.vue";
import Clipboard from "~/components/filemanager/views/Clipboard.vue";
import Status from "~/components/filemanager/views/Status.vue";
import Rename from "~/components/filemanager/views/Rename.vue";
import Properties from "~/components/filemanager/views/Properties.vue";
import Preview from "~/components/filemanager/views/Preview.vue";
import TextEdit from "~/components/filemanager/views/TextEdit.vue";
import AudioPlayer from "~/components/filemanager/views/AudioPlayer.vue";
import PDFViewer from "~/components/filemanager/views/PDFViewer.vue";
import VideoPlayer from "~/components/filemanager/views/VideoPlayer.vue";
import Zip from "~/components/filemanager/views/Zip.vue";
import Unzip from "~/components/filemanager/views/Unzip.vue";
import PreviewLoader from "~/components/filemanager/views/PreviewLoader.vue";

export default {
  components: {
    NewFile,
    NewFolder,
    Upload,
    Delete,
    Clipboard,
    Status,
    Rename,
    Properties,
    Preview,
    TextEdit,
    AudioPlayer,
    PDFViewer,
    VideoPlayer,
    Zip,
    Unzip,
    PreviewLoader,
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      thanks: false,
    };
  },
  head() {
    return {
      title: `${this.$t("cart")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      cart: (state) => state.cart.cart,
      cart_products: (state) => state.cart.cart_products,
      cart_render: (state) => state.cart.cart_render,
      custom_product_variations: (state) => state.shop.custom_product_variations,
      checkout: (state) => state.cart.checkout,
      modalName: (state) => state.fm.modal.modalName,
      me: (state) => state.settings.me,
    }),
  },
  watch: {
    cart(newVal) {
      return newVal;
    },
    cart_products: {
      deep: true,
      handler(newVal) {
        return newVal;
      },
    },
    thanks(v) {
      if (v === true) {
        setTimeout(() => {
          this.set_checkout(false);
          this.thanks = false;
        }, 2000);
      }
    },
  },
  created() {
    this.api
      .get("/cart")
      .then((response) => {
        this.set_cart(response.meta);
        this.set_cart_products(response.data.products);
      })
      .catch((error) => {
        this.handleError(error);
      });
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
      set_cart_flag: "cart/set_cart_flag",
      set_checkout: "cart/set_checkout",
      set_cart_render: "cart/set_cart_render",
    }),
    remove(product) {
      this.api.delete(`/cart/${product.id}`).then((response) => {
        this.handleSuccess(response);

        this.api
          .get("/cart")
          .then((response) => {
            this.set_cart(response.meta);
            this.set_cart_products(response.data.products);
            this.set_cart_render(this.cart_render - 1);
          })
          .catch((error) => {
            this.handleError(error);
          });
      });
    },
  },
};
</script>
