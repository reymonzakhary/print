<template>
  <section class="flex items-center w-full group">
    <div v-if="product.media && product.media.length > 0" class="flex flex-col items-center">
      <div class="overflow-hidden">
        <transition name="fade">
          <Thumbnail
            disk="assets"
            :file="{ path: product.media[0] }"
            class="flex mx-1 border rounded"
            :size="40"
          />
        </transition>
      </div>
    </div>

    <div class="flex items-center w-full p-2">
      <h2 class="mr-4 text-sm font-bold tracking-wide uppercase">
        {{ product.name }}
      </h2>

      <p class="hidden mr-4 text-sm text-gray-500 lg:block">
        {{ product.description }}
      </p>

      <!-- <p class="ml-auto mr-4 font-mono">
				<span class="text-xs font-bold">EAN:</span>
				{{ product.ean }}
			</p>

			<p class="mr-4 font-mono">
				<span class="text-xs font-bold">ART_NR:</span>
				{{ product.art_num }}
			</p> -->

      <p class="ml-auto mr-4 font-mono">
        {{ product.display_price }}
      </p>

      <div class="items-center hidden md:flex">
        <font-awesome-icon
          class="mr-1"
          :icon="['fal', 'wave-pulse']"
          :class="product.published ? 'text-theme-400' : 'text-gray-300'"
        />
        <font-awesome-icon
          class="mr-1"
          :icon="['fal', 'box-full']"
          :class="product.combination ? 'text-theme-400' : 'text-gray-300'"
        />
        <font-awesome-icon
          class="mr-1"
          :icon="['fal', 'rectangle-vertical-history']"
          :class="product.variation ? 'text-theme-400' : 'text-gray-300'"
        />
        <font-awesome-icon
          class="mr-4"
          :icon="['fal', 'object-exclude']"
          :class="product.excludes ? 'text-theme-400' : 'text-gray-300'"
        />
      </div>

      <span class="z-10 invisible mr-1 group-hover:visible">
        <!-- <client-only> -->
        <ItemMenu
          :menu-items="menuItems"
          menu-icon="ellipsis-h"
          menu-class="w-6 h-6 rounded-full hover:bg-gray-100 dark:hover:bg-black"
          dropdown-class="right-0 border w-44 dark:border-black text-theme-900"
          @item-clicked="menuItemClicked($event, product)"
        />
        <!-- </client-only> -->
      </span>

      <button
        class="px-2 py-1 text-xs transition-colors border rounded-full border-theme-500 text-theme-500 hover:bg-theme-100"
        @click="editProduct(product)"
      >
        <font-awesome-icon class="" :icon="['fal', 'pencil']" />
        <!-- {{ $t("view product") }} -->
      </button>
    </div>

    <CustomSingleProductDetail
      v-if="showDetails"
      :prod="product"
      :cart="cart"
    ></CustomSingleProductDetail>

    <!-- EDIT single -->
    <!-- <transition-group name="fade"> -->
    <SidePanel
      v-if="editCustomProduct"
      width="w-full md:w-11/12 xl:w-10/12"
      @on-close="editCustomProduct = false"
    >
      <template #side-panel-header>
        <p class="p-2 font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'shelves']" />
          {{ $t("edit") }} {{ $t("product") }}
        </p>
      </template>

      <template #side-panel-content>
        <CustomProductForm
          :edit_mode="true"
          :categories="categories"
          :product="editCustomProduct"
          class="w-full max-w-none"
        />
      </template>
    </SidePanel>
    <!-- </transition-group> -->

    <!-- EDIT Combinations -->
    <!-- <transition-group name="fade"> -->
    <SidePanel
      v-if="
        editCustomProductVariations &&
        active_custom_product &&
        editCustomProductVariations.id === active_custom_product.id
      "
      width="w-full "
    >
      <template #side-panel-header>
        <p class="p-2 font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'shelves']" />
          {{ $t("edit") }} {{ $t("product") }}
        </p>
      </template>

      <template #side-panel-content>
        <div class="flex items-start">
          <CustomProductForm
            :edit_mode="true"
            :categories="categories"
            :product="editCustomProductVariations"
            class="w-1/4 max-w-none"
          />
          <CustomProductVariationExcludes class="w-3/4 max-w-none" :show-header="false" />
        </div>
      </template>
    </SidePanel>
    <!-- </transition-group> -->

    <!-- DELETE -->
    <transition name="fade">
      <ConfirmationModal v-if="deleteCustomProduct" classes="w-auto">
        <template #modal-header class="capitalize">
          {{ $t("remove") }} {{ product.name }}
        </template>

        <template #modal-body>
          <section class="flex flex-wrap max-w-lg">
            <div class="max-h-screen p-2" style="min-width: 400px">
              {{ $t("This will remove") }}
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

    <!-- COPY -->
    <transition name="fade">
      <ConfirmationModal v-if="copyCustomProduct.id === product.id" @on-close="closeModal">
        <template #modal-header class="capitalize">
          {{ $t("copy") }} {{ product.name }}
          {{ $t("to another category") }}
        </template>

        <template #modal-body>
          <!-- {{ copyCustomProduct }} -->
          {{ $t("copy") }} {{ copyCustomProduct.name }}
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
            v-if="categoryId"
            class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-700"
            @click="copyProduct()"
          >
            {{ $t("Copy") }} {{ $t("to") }}
            {{ categoryId.name }}
          </button>
        </template>
      </ConfirmationModal>
    </transition>
  </section>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

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
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, permissions };
  },
  data() {
    return {
      quantity: null,
      disabled: false,
      showDetails: false,
      showImage: 0,
      showProps: false,
      editCustomProduct: false,
      editCustomProductVariations: false,
      deleteCustomProduct: false,
      copyCustomProduct: false,
      categoryId: null,
      menuItems: [
        {
          items: [
            {
              action: "clone",
              icon: "clone",
              title: this.$t("copy"),
              classes: "",
              show: false,
            },
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit"),
              classes: "",
              show: false,
            },
            {
              action: "delete",
              icon: "trash",
              title: this.$t("delete"),
              classes: "text-red-500",
              show: false,
            },
          ],
        },
      ],
    };
  },
  watch: {
    // quantity: _.debounce(function (val) {
    // 	if (this.cart) {
    // 		this.updateCart(this.product, val);
    // 	}
    // }, 300),
    deleteCustomProduct(v) {
      return v;
    },
  },
  created() {
    if (this.permissions.includes("custom-assortments-products-update")) {
      this.menuItems[0].items[0].show = true;
      this.menuItems[0].items[1].show = true;
    }
    if (this.permissions.includes("custom-assortments-products-delete")) {
      this.menuItems[0].items[2].show = true;
    }
  },
  mounted() {
    if (this.product) {
      this.quantity = this.product.quantity;
    }
  },
  computed: {
    ...mapState({
      cart_flag: (state) => state.cart.cart_flag,
      categories: (state) => state.product.custom_categories,
      active_custom_product: (state) => state.product.active_custom_product,
    }),
  },
  methods: {
    ...mapMutations({
      set_cart: "cart/set_cart",
      set_cart_products: "cart/set_cart_products",
      set_active_custom_product: "product/set_active_custom_product",
      remove_custom_product: "product/remove_custom_product",
    }),
    ...mapActions({
      get_custom_product: "product/get_custom_product",
    }),
    menuItemClicked(event, product) {
      switch (event) {
        case "clone":
          this.copyCustomProduct = product;
          this.categoryId = product.category;
          break;

        case "edit":
          this.editProduct(product);
          break;

        case "delete":
          this.deleteCustomProduct = product.id;
          break;

        default:
          break;
      }
    },
    editProduct(product) {
      if (!product.excludes) {
        this.get_custom_product(product.id);
        this.editCustomProduct = true;
      } else {
        this.get_custom_product(product.id);
        this.editCustomProductVariations = product;
      }
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
        .post(`custom/products/copy`, {
          category_id: catId,
          name: this.copyCustomProduct.name,
          description: this.copyCustomProduct.description,
          price: this.copyCustomProduct.price,
          ean: this.copyCustomProduct.ean?.toString(),
          art_num: this.copyCustomProduct.art_num,
          properties: this.copyCustomProduct.properties,
          media: this.copyCustomProduct.media,
          stockProduct: this.copyCustomProduct.stock_product,
          stock: this.copyCustomProduct.stock,
          lowQtyThreshold: this.copyCustomProduct.low_qty_threshold ?? null,
          enableVariations: this.copyCustomProduct.variation ?? false,
          package: this.copyCustomProduct.package ?? false,
          variations: this.copyCustomProduct.variations,
          properties: this.copyCustomProduct.properties.props
            ? this.copyCustomProduct.properties.props
            : [],
        })
        .then((response) => {
          this.handleSuccess(response);
          this.closeModal();
        })
        .catch((error) => this.handleError(error));
    },
    deleteProduct() {
      const id = this.deleteCustomProduct;
      this.deleteCustomProduct = false;

      this.api
        .delete(`custom/products/${id}`)
        .then((response) => {
          this.remove_custom_product(id);
          this.handleSuccess(response);

          this.closeModal();
          this.close();
        })
        .catch((error) => {
          this.deleteCustomProduct = id;
          this.handleError(error);
        });
    },
    closeModal() {
      this.editCustomProduct = false;
      this.deleteCustomProduct = false;
      this.copyCustomProduct = false;
    },
    close() {
      this.editCustomProduct = false;
      this.editCustomProductVariations = false;
      this.deleteCustomProduct = false;
      this.copyCustomProduct = false;
    },
  },
};
</script>
