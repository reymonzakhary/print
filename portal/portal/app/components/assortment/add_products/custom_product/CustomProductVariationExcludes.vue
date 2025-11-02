<template>
  <div id="resultContainer">
    <transition name="fade">
      <SidePanel v-if="editVariation !== null" width="w-full md:w-2/6">
        <template #side-panel-header>
          <h2 class="sticky top-0 p-4 font-bold uppercase tracking-wide text-theme-900">
            <font-awesome-icon :icon="['fal', 'pencil']" class="mr-1" />
            {{ $t("update") }}
            <span class="text-theme-500">
              {{ editVariation.name }}

              <template v-for="product in editVariation.product">
                {{ product.name }}
              </template>
            </span>
          </h2>
        </template>

        <template #side-panel-content>
          <section class="p-4">
            <UploadImage
              v-if="media"
              disk="assets"
              :disk-selector="false"
              :image="{ path: media }"
              @image-changed="media.push($event)"
            />

            <UICurrencyInput
              v-model="price"
              input-class="my-4 w-full text-sm border-green-500 ring-green-200 focus:border-green-500"
            />

            <ValueSwitch
              icon="wave-pulse"
              name="published"
              :set-checked="published"
              @checked-value="published = $event.value"
            />

            <div class="my-4 flex items-center">
              <div class="w-1/2">
                <label class="text-sm font-bold uppercase tracking-wide">
                  {{ $t("EAN-13") }}
                </label>
                <the-mask
                  v-model="ean"
                  mask="# ###### #####"
                  type="text"
                  :masked="false"
                  placeholder="123456789012"
                  class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
                />
              </div>
              <div class="flex w-1/2 items-center">
                <barcode :value="ean" format="EAN13" class="flex w-full justify-center">
                  <div
                    v-if="ean"
                    class="ml-4 flex w-full border px-20 py-16 text-center text-sm italic text-gray-500"
                  >
                    {{ 12 - ean.length }} {{ $t("left") }}
                  </div>
                </barcode>
              </div>
            </div>

            <button
              class="ml-8 rounded-full bg-green-500 px-4 py-1 text-base text-white"
              @click="updateVariation()"
            >
              update
            </button>
          </section>
        </template>
      </SidePanel>
    </transition>

    <AddCustomProductHeader
      v-if="showHeader"
      :step="3"
      to_component="CustomProductForm"
      class="sticky top-0 z-50 bg-gray-100"
    />

    <div
      class="relative m-4 min-w-max rounded bg-white shadow-md shadow-gray-200 dark:shadow-gray-900"
    >
      <h2
        class="sticky top-14 z-10 flex w-full flex-wrap items-center justify-between rounded-t bg-theme-400 p-2 font-bold uppercase tracking-wide text-themecontrast-400"
      >
        {{ $t("variations") }}
        <PaginationTopBar @pagination-changed-top="getVariations" />
      </h2>

      <header
        class="sticky top-16 flex space-x-2 border-b bg-white p-2 text-sm font-bold uppercase tracking-wide shadow-sm dark:border-gray-900"
      >
        <div class="flex flex-1 items-center justify-start text-xs">
          {{ $t("product") }}
        </div>

        <div
          v-for="box in boxes"
          :key="box"
          class="flex flex-1 items-center justify-around text-xs"
        >
          {{ box }}
        </div>

        <div class="flex flex-1 items-center justify-end text-xs">
          {{ $t("actions") }}
        </div>
      </header>

      <section class="divide-y border last:rounded-b">
        <div
          v-for="variation in variations"
          :key="`variation_${variation.id}`"
          class="flex p-1 hover:bg-gray-100"
        >
          <div class="flex w-full flex-1 items-center justify-start">
            {{ variation.name }}
          </div>

          <div
            v-for="product in variation.product"
            :key="`product_${product.id}`"
            class="flex w-full flex-1 items-center justify-around"
          >
            {{ product.name }}
          </div>

          <div class="flex w-full flex-1 items-center justify-end">
            <span class="mr-4 font-mono text-sm text-gray-500">
              {{ variation.ean }}
            </span>
            <span class="mr-4 font-mono text-sm text-gray-500">
              {{ variation.display_price }}
            </span>
            <font-awesome-icon
              :icon="['fal', 'wave-pulse']"
              :class="variation.published ? 'text-theme-500' : 'text-gray-300'"
            />
            <button
              class="ml-auto rounded-full border border-theme-400 px-1 text-theme-400 hover:bg-theme-100"
              @click="editVariation = variation"
            >
              <font-awesome-icon :icon="['fal', 'pencil']" />
            </button>
          </div>
        </div>
      </section>
    </div>

    <div class="sticky bottom-5 flex w-full justify-center">
      <Pagination
        :pagination="pagination"
        class="bottom-2 flex rounded border border-theme-500 shadow-lg"
        @pagination="getVariations"
      />
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import pagination from "~/mixins/pagination";
import VueBarcode from "vue3-barcode";

export default {
  components: {
    barcode: VueBarcode,
  },
  mixins: [pagination],
  props: {
    showHeader: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { handleError, handleSuccess, api };
  },
  data() {
    return {
      variations: [],
      editVariation: null,
      price: null,
      published: true,
      media: [],
      ean: "",
    };
  },
  computed: {
    ...mapState({
      active_custom_product: (state) => state.product.active_custom_product,
    }),
    boxes() {
      const boxes = [];
      if (this.variations && this.variations[0]) {
        const products = this.variations[0].product;
        products.forEach((prod) => boxes.push(prod.box));
      }

      return boxes;
    },
  },
  watch: {
    editVariation(v) {
      if (v) {
        this.price = v.price;
        this.ean = v.ean;
        this.media = v.media;
        this.published = v.published;
      }
    },
  },
  created() {
    this.getVariations({ page: 1 });
  },
  methods: {
    async getVariations(e) {
      if (this.active_custom_product) {
        await this.api
          .get(
            `custom/products/${this.active_custom_product.id}/variations?per_page=${this.pagination.per_page}&page=${e.page}`,
          )
          .then((response) => {
            this.set_pagination(response.meta);
            this.variations = response.data;
            this.set_loader(false);
          });
      }
    },
    updateVariation() {
      this.api
        .put(
          `custom/products/${this.active_custom_product.id}/variations/${this.editVariation.id}`,
          {
            media: this.media,
            price: this.price,
            published: this.published,
            ean: this.ean,
          },
        )
        .then((response) => {
          this.handleSuccess(response);
          this.getVariations({ page: this.pagination.page });
        })
        .catch((error) => this.handleError(error));
    },
    close() {
      this.editVariation = null;
      this.price = 0;
      this.ean = "";
      this.media = [];
      this.published = true;
    },
  },
};
</script>
