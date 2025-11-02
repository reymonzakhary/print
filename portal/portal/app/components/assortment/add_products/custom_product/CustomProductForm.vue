<template>
  <div>
    <AddCustomProductHeader
      v-if="!edit_mode"
      :step="2"
      to_component="AddCustomProductCategory"
      class="sticky top-0 bg-gray-100 dark:bg-gray-800"
    />

    <section
      :class="{
        'px-4': edit_mode,
        'rounded-md bg-white p-4 shadow-md': !edit_mode,
      }"
      class="flex w-full flex-col shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900 md:justify-evenly lg:flex-row"
    >
      <div class="lg:w-1/2 lg:pr-2">
        <!-- base form -->
        <div class="flex flex-wrap">
          <div class="w-full py-4 pr-4 sm:w-1/2">
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("product name") }}
            </label>
            <input
              v-model="name"
              type="text"
              class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
            />
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("product") }} {{ $t("description") }}
            </label>
            <textarea
              v-model="description"
              class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
            />
          </div>

          <div class="w-full py-4 sm:w-1/2">
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("product image(s)") }}
            </label>
            <!-- <UploadImage
              disk="assets"
              :disk-selector="false"
              :image="{ path: media }"
              @image-changed="updateImage($event)"
            /> -->
            <UIImageSelector
              :selected-image="media[0]"
              disk="assets"
              @on-image-select="updateImage($event)"
              @on-image-remove="updateImage([])"
            />
          </div>
        </div>

        <div class="flex flex-wrap">
          <div class="my-4 w-full sm:w-1/2 sm:pr-2">
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("product price") }}
            </label>
            <UICurrencyInput
              v-model="price"
              input-class="input w-full border-green-500 ring-green-200 focus:border-green-500 text-sm"
            />
          </div>

          <div class="my-4 w-full sm:w-1/2 sm:pl-2">
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("artikelnummer") }}
            </label>
            <input
              v-model="artNumber"
              type="text"
              class="input px-2 py-2 text-sm text-theme-900 dark:text-white"
            />
          </div>
        </div>

        <div class="my-4 flex flex-wrap items-center">
          <div class="w-full sm:w-1/2">
            <label class="text-sm font-bold uppercase tracking-wide">
              {{ $t("EAN-13") }}
            </label>
            <VueTheMask
              v-model="ean"
              mask="# ###### #####"
              type="text"
              placeholder="123456789012"
              class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
            />
          </div>

          <div class="mt-4 flex w-full items-center sm:mt-0 sm:w-1/2">
            <VueBarcode :value="ean" format="EAN13" class="flex w-full justify-center">
              <div
                v-if="ean && ean.length < 12"
                class="ml-4 flex w-full border px-20 py-16 text-center text-sm italic text-gray-500"
              >
                {{ 12 - ean.length }} {{ $t("left") }}
              </div>
            </VueBarcode>
          </div>
        </div>

        <div class="my-8 w-1/2 pr-4">
          <ValueSwitch
            :name="$t('published')"
            icon="wave-pulse"
            :set-checked="published"
            classes="justify-between"
            @checked-value="published = $event.value"
          />
        </div>

        <!-- switches -->
        <div v-if="product" class="flex flex-wrap border-t py-4 dark:border-black">
          <div class="my-2 w-1/2 pr-4">
            <ValueSwitch
              :name="$t('stock product')"
              icon="warehouse-full"
              :set-checked="stockProduct"
              classes="justify-between"
              @checked-value="stockProduct = $event.value"
            />
          </div>
          <div class="my-2 w-1/2 pl-4">
            <div>
              <ValueSwitch
                v-if="!excludes"
                :name="$t('package')"
                icon="box-open-full"
                :set-checked="package"
                :disabled="edit_mode"
                :info="edit_mode ? $t(`can't edit product type`) : ''"
                classes="justify-between"
                @checked-value="package = $event.value"
              />
              <div v-else class="italic text-gray-300">
                <font-awesome-icon :icon="['fal', 'object-exclude']" fixed-width class="mr-1" />
                {{ $t("package") }}
                <font-awesome-icon
                  v-tooltip="
                    //prettier-ignore
                    $t('only usable if not a product with variations in unique combinations')
                  "
                  :icon="['fal', 'circle-info']"
                />
              </div>
            </div>
          </div>
          <div class="my-2 w-1/2 pr-4">
            <ValueSwitch
              :name="$t('product options')"
              icon="rectangle-vertical-history"
              :set-checked="enableVariations"
              classes="justify-between"
              @checked-value="enableVariations = $event.value"
            />
          </div>
          <div class="my-2 w-1/2 pl-4">
            <div>
              <ValueSwitch
                v-if="enableVariations && !package"
                :name="$t('unique combinations')"
                icon="object-exclude"
                :set-checked="excludes"
                :disabled="edit_mode"
                :info="edit_mode ? $t(`can't edit product type`) : ''"
                classes="justify-between"
                @checked-value="
                  ((excludes = $event.value), (stock.qty = null), (lowQtyThreshold = null))
                "
              />
              <div v-else class="italic text-gray-300">
                <font-awesome-icon :icon="['fal', 'object-exclude']" fixed-width class="mr-1" />
                {{ $t("unique combinations") }}
                <font-awesome-icon
                  v-tooltip="$t('only usable with options and if not a package product')"
                  :icon="['fal', 'circle-info']"
                />
              </div>
            </div>
          </div>

          <div class="my-2 w-1/2 pr-4">
            <ValueSwitch
              :name="$t('add validations')"
              icon="files"
              :set-checked="enableValidations"
              classes="justify-between"
              @checked-value="enableValidations = $event.value"
            />
          </div>
          <div class="my-2 w-1/2 pl-4">
            <ValueSwitch
              :name="$t('add templates')"
              icon="files"
              :set-checked="enableTemplates"
              classes="justify-between"
              @checked-value="enableTemplates = $event.value"
            />
          </div>
        </div>
      </div>
      <div class="md:mx-auto md:w-2/3 lg:mx-0 lg:w-1/2 lg:pl-2">
        <!-- TODO: make seperate components -->
        <!-- STOCK -->
        <transition name="slide">
          <div v-if="stockProduct" class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <h3 class="text-sm font-bold uppercase tracking-wide">
              {{ $t("Stock") }}
            </h3>
            <div v-if="!excludes" class="my-4 flex">
              <div class="w-1/2 p-2">
                <label class="text-xs font-bold uppercase tracking-wide">
                  {{ $t("amount") }}
                </label>
                <input
                  v-model="stock.qty"
                  type="number"
                  class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
                  placeholder="1000"
                />
              </div>

              <div class="w-1/2 p-2">
                <label class="text-xs font-bold uppercase tracking-wide">
                  {{ $t("low qty threshold") }}
                  <font-awesome-icon
                    v-tooltip="$t('Will provide a warning when stock hits below the treshold')"
                    :icon="['fal', 'circle-info']"
                  />
                </label>
                <input
                  v-model="lowQtyThreshold"
                  type="number"
                  class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
                  placeholder="100"
                />
              </div>
            </div>
            <transition name="slide">
              <div v-if="excludes" class="italic text-orange-500">
                {{ $t("You will be able to add stock in the next step") }}
              </div>
            </transition>
          </div>
        </transition>

        <!-- PACKAGE -->
        <transition name="slide">
          <div v-if="package" class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <div class="mb-2 flex flex-wrap items-center justify-between">
              <h3 class="w-full text-sm font-bold uppercase tracking-wide">
                {{ $t("package") }}
              </h3>
              <div class="w-full">
                <span class="relative my-4 block">
                  <label for="input_type" class="text-xs font-bold uppercase tracking-widest">
                    {{ $t("products") }}
                  </label>

                  <div class="flex">
                    <input
                      v-model="packageSearch"
                      type="text"
                      :placeholder="$t('search all custom products')"
                      class="input w-full p-2"
                    />
                  </div>

                  <!-- search result -->
                  <div class="flex flex-col divide-y rounded-b bg-white shadow-md">
                    <span
                      v-for="product in result"
                      :key="product.id"
                      class="flex flex-wrap items-center p-1"
                      @click="
                        !product.excludes
                          ? (packageProducts.push({
                              sku_id: product.sku_id,
                            }),
                            (result = []))
                          : ''
                      "
                    >
                      <div class="flex flex-col">
                        <h2 class="mr-4 text-sm font-bold uppercase tracking-wide">
                          {{ product.name }}
                        </h2>

                        <p class="mr-4 text-sm">
                          {{ product.description }}
                        </p>
                      </div>

                      <p class="ml-auto mr-4 font-mono">
                        <span class="text-xs font-bold">EAN:</span>
                        {{ product.ean }}
                      </p>

                      <p v-if="product.art_num" class="mr-4 font-mono">
                        <span class="text-xs font-bold">ART_NR:</span>
                        {{ product.art_num }}
                      </p>

                      <p class="mr-4 font-mono">
                        <span class="text-xs font-bold">Price:</span>
                        {{ product.display_price }}
                      </p>

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
                      <template v-if="product.variations.length > 0">
                        <span
                          v-for="variation in product.variations"
                          :key="`var_${variation.id}`"
                          class="mx-1 flex w-full flex-col text-gray-500"
                          :class="{
                            'border-b last:border-0':
                              variation.product && variation.product.length > 0,
                          }"
                          @click="
                            packageProducts.push({
                              sku_id: variation.product.sku_id,
                            })
                          "
                        >
                          <span v-for="prod in variation.product" :key="`var_prod_${prod.id}`">
                            {{ prod.name }}
                          </span>
                        </span>
                      </template>
                    </span>
                  </div>
                </span>
              </div>

              <div class="flex w-full flex-col items-center">
                <div
                  v-for="(product, i) in packageProducts"
                  :key="`package_product_${product.id}_${i}`"
                  class="m-1 flex w-full items-center rounded border border-theme-500 bg-white p-1 shadow-md"
                >
                  <div class="flex flex-col">
                    <h2
                      v-if="product.sku_id || product.name"
                      class="mr-4 text-sm font-bold uppercase tracking-wide"
                    >
                      {{
                        productList.find((prod) => prod.sku_id === product.sku_id)?.name ??
                        product.name
                      }}
                    </h2>
                    <p class="mr-4 text-sm">
                      {{
                        productList.find((prod) => prod.sku_id === product.sku_id)?.description ??
                        product.description
                      }}
                    </p>
                  </div>

                  <div class="ml-auto flex items-center">
                    <!-- <p class="mr-4 ml-auto font-mono">
                                 <span class="text-xs font-bold">EAN:</span>
                                 {{
                                    productList.find(
                                       (prod) => prod.sku_id === product.sku_id
                                    )?.ean ?? product.ean
                                 }}
                              </p> -->
                    <p
                      v-if="
                        productList.find((prod) => prod.sku_id === product.sku_id)?.art_num ||
                        product.art_num
                      "
                      class="ml-auto mr-4 font-mono"
                    >
                      <span class="text-xs font-bold">ART_NR:</span>
                      {{
                        productList.find((prod) => prod.sku_id === product.sku_id)?.art_num ??
                        product.art_num
                      }}
                    </p>
                    <p class="mr-4 font-mono">
                      <span class="text-xs font-bold">Price:</span>
                      {{
                        productList.find((prod) => prod.sku_id === product.sku_id)?.display_price ??
                        product.display_price
                      }}
                    </p>
                    <font-awesome-icon
                      class="mr-1"
                      :icon="['fal', 'box-full']"
                      :class="
                        productList.find((prod) => prod.sku_id === product.sku_id)?.combination ||
                        product.combination
                          ? 'text-theme-400'
                          : 'text-gray-300'
                      "
                    />
                    <font-awesome-icon
                      class="mr-1"
                      :icon="['fal', 'rectangle-vertical-history']"
                      :class="
                        productList.find((prod) => prod.sku_id === product.sku_id)?.variation ||
                        product.variation
                          ? 'text-theme-400'
                          : 'text-gray-300'
                      "
                    />
                    <font-awesome-icon
                      class="mr-4"
                      :icon="['fal', 'object-exclude']"
                      :class="
                        productList.find((prod) => prod.sku_id === product.sku_id)?.excludes ||
                        product.excludes
                          ? 'text-theme-400'
                          : 'text-gray-300'
                      "
                    />
                    <button class="text-red-500" @click="packageProducts.splice(i, 1)">
                      <font-awesome-icon :icon="['fal', 'circle-xmark']" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>

        <!-- PRODUCT OPTIONS -->
        <transition name="slide">
          <div v-if="enableVariations" class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <div class="mb-2 flex items-center justify-between">
              <h3 class="text-sm font-bold uppercase tracking-wide">
                {{ $t("Variations") }}
              </h3>
              <transition name="slide">
                <div v-if="excludes" class="italic text-orange-500">
                  {{ $t("You will be able to exclude variations in the next step") }}
                </div>
              </transition>
            </div>

            <CustomProductFormBoxes
              v-for="(box, index) in boxes"
              :key="`box_${index}`"
              :box="box"
              :i="0"
              :edit_mode="edit_mode"
            />
          </div>
        </transition>

        <!-- VALIDATIONS -->
        <transition name="slide">
          <div v-if="enableValidations" class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <div class="font-bold">{{ $t("validations") }}</div>
            <div
              class="mb-2 flex h-full items-center rounded border border-gray-300 dark:border-black"
            >
              <input
                v-model="file.name"
                type="text"
                :placeholder="$t('name')"
                class="input ml-1 mr-2 w-1/3 p-1"
              />
              <ValueSwitch
                class="ml-2 w-1/3"
                :name="$t('file required')"
                icon="memo-circle-check"
                :set-checked="file.required"
                @checked-value="file.required = $event.value"
              />
              <v-select
                v-model="file.mime"
                class="input z-50 w-1/3 p-0 text-sm text-theme-900 dark:text-theme-900"
                :options="mimeTypes"
                placeholder="type"
              />
              <button
                class="ml-2 h-full rounded-r bg-theme-500 p-2 text-themecontrast-500"
                @click="addValidation(file)"
              >
                add
              </button>
            </div>
            <transition-group name="slide">
              <div
                v-for="(validation, i) in validations"
                :key="`validation_${i}`"
                class="ml-2 flex w-full items-center justify-between"
              >
                <div
                  v-for="(value, key) in validation"
                  :key="key"
                  class="mr-16 flex w-full justify-between"
                >
                  <b>{{ key }}:</b>
                  <span class="ml-2 italic text-gray-500">{{ value }} </span>
                </div>
                <button
                  class="rounded-full px-2 text-red-500 hover:bg-red-100"
                  @click="removeValidation(i)"
                >
                  <font-awesome-icon :icon="['fal', 'trash']" />
                </button>
              </div>
            </transition-group>
          </div>
        </transition>

        <!-- TEMPLATE -->
        <transition name="slide">
          <div
            v-if="enableTemplates"
            class="flex w-full flex-wrap items-center rounded bg-gray-100 p-2 dark:bg-gray-800"
          >
            <div class="w-full font-bold">{{ $t("templates") }}</div>
            <transition-group name="slide">
              <div
                v-for="template in templates"
                :key="template.id"
                class="my-2 w-full rounded bg-white"
              >
                <div
                  :class="{
                    'bg-theme-500 text-themecontrast-500 hover:bg-theme-600':
                      productTemplate.id === template.id,
                  }"
                  class="flex w-full cursor-pointer items-center justify-between rounded text-sm shadow-md shadow-gray-200 transition-colors duration-75 hover:bg-gray-200 dark:bg-gray-700 dark:shadow-gray-900 dark:hover:bg-gray-700"
                  @click="toggleTemplate(template)"
                >
                  <div class="m-2 h-8 w-8 flex-none overflow-hidden rounded-full">
                    <img
                      v-if="template.image"
                      class="h-full w-full object-cover"
                      :src="template.image"
                    />
                  </div>

                  <div
                    class="w-full flex-auto overflow-hidden truncate px-2 capitalize"
                    :title="template.name"
                  >
                    {{ template.name }}
                  </div>

                  <div class="hidden w-full flex-auto px-2 text-xs lg:flex">
                    {{ moment(template.created_at).format("ddd DD MMM YYYY HH:mm") }}
                  </div>
                  <div class="w-full flex-auto px-2 pr-4 text-right text-xs">
                    <font-awesome-icon
                      v-if="productTemplate.id === template.id"
                      :icon="['fal', 'check']"
                    />
                  </div>
                </div>
              </div>
            </transition-group>
          </div>
        </transition>

        <!-- PROPERTIES -->
        <div class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
          <p class="flex w-full justify-between text-sm font-bold uppercase tracking-wide">
            {{ $t("product") }} {{ $t("properties") }}
            <button
              class="text-sm font-normal text-theme-500 underline"
              @click="properties.push({ key: 'name', value: 'value' })"
            >
              <font-awesome-icon :icon="['fal', 'plus']" />
              {{ $t("add property") }}
            </button>
          </p>
          <div v-if="properties && properties.length > 0" class="flex flex-wrap text-xs">
            <div class="w-1/2 pr-2">
              <label class="text-sm font-bold uppercase tracking-wide">
                {{ $t("name") }}
              </label>
            </div>
            <div class="w-1/2">
              <label class="text-sm font-bold uppercase tracking-wide">
                {{ $t("value") }}
              </label>
            </div>
          </div>
          <div
            v-for="(property, i) in properties"
            :key="'property_' + i"
            class="my-2 flex flex-wrap"
          >
            <div
              v-if="properties[i]"
              class="w-1/2 pr-2"
              :class="{
                'mb-1 pt-2': typeof properties[i].value === 'object',
              }"
            >
              <input
                v-model="properties[i].key"
                type="text"
                class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
              />
            </div>
            <template v-if="typeof properties[i].value === 'object'">
              <div class="flex w-1/2 flex-wrap justify-end text-sm">
                <button class="underline" @click="addPair(properties[i].value)">
                  <font-awesome-icon :icon="['fal', 'plus']" />
                  {{ $t("add property value") }}
                </button>
                <button class="underline" @click="properties[i].value = 'value'">
                  <font-awesome-icon :icon="['fal', 'horizontal-rule']" />
                  {{ $t("make single value") }}
                </button>
              </div>
              <div
                v-for="(value, key, idx) in properties[i].value"
                :key="'value_' + idx"
                class="my-1 ml-4 flex w-full"
              >
                <div class="ml-10 w-1/3 pr-2">
                  <input
                    type="text"
                    class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
                    :value="key"
                    @change="
                      changeKey(properties[i].value[key], properties[i].value, $event.target.value)
                    "
                  />
                </div>
                <div class="flex w-1/3">
                  <input
                    type="text"
                    class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
                    :value="value"
                    @input="properties[i].value[key] = $event.target.value"
                  />
                  <button
                    class="ml-2 rounded-full bg-white px-2 text-red-400 transition-colors hover:bg-red-100"
                    @click="delete properties[i].value[key]"
                  >
                    <font-awesome-icon :icon="['fal', 'trash']" />
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="flex w-1/2">
              <input
                v-model="properties[i].value"
                type="text"
                class="input px-2 py-1 text-sm text-theme-900 dark:text-white"
              />
              <button
                class="ml-2 rounded-full bg-white px-2 text-red-400 transition-colors hover:bg-red-100"
                @click="properties.splice(i, 1)"
              >
                <font-awesome-icon :icon="['fal', 'trash']" />
              </button>
              <button
                v-tooltip="$t('make multiple value')"
                class="ml-2 flex h-8 flex-col rounded-full bg-white px-2 text-sm text-theme-400 transition-colors hover:bg-theme-100"
                @click="properties[i].value = { name: 'value' }"
              >
                <font-awesome-icon :icon="['fal', 'horizontal-rule']" class="mr-3" />
                <font-awesome-icon :icon="['fal', 'line-columns']" class="ml-auto" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="sticky bottom-4 mt-4">
      <button
        v-if="edit_mode"
        class="mx-auto flex rounded-full bg-green-500 px-4 py-1 text-white transition-colors hover:bg-green-600"
        @click="update()"
      >
        {{ $t("Update custom product") }}
      </button>
      <button
        v-else
        class="mx-auto flex rounded-full bg-green-500 px-4 py-1 text-white transition-colors hover:bg-green-600"
        @click="add()"
      >
        {{ $t("Add custom product") }}
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import VueBarcode from "vue3-barcode";
import mixin from "~/components/assortment/add_products/custom_product/mixin";
import moment from "moment";
import _ from "lodash";

export default {
  components: {
    VueBarcode,
  },
  mixins: [mixin],
  props: {
    edit_mode: Boolean,
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      moment: moment,
      productList: [],

      // new product data
      name: "my new product",
      description: "",
      media: [],
      price: 0,
      ean: null,
      artNumber: null,
      properties: [],
      sort: null,
      published: true,

      // switches
      stockProduct: false,
      enableVariations: false,
      enableValidations: false,
      enableTemplates: false,
      excludes: false,
      package: false,

      // stock
      stock: {
        qty: null,
      },
      lowQtyThreshold: null,

      // package
      packageSearch: "",
      result: [],
      packageProducts: [],

      // product options/variations
      boxes: [],
      active_box: null,

      // validations
      validations: [],
      file: [
        {
          name: "",
          required: false,
          mime: "",
        },
      ],

      // templates
      productTemplate: {},
    };
  },
  computed: {
    ...mapState({
      active_custom_category: (state) => state.product.active_custom_category,
      product: (state) => state.product.active_custom_product,
      variations: (state) => state.product.custom_product_variations,
      templates: (state) => state.design.templates,
    }),
  },
  watch: {
    ean(newVal, oldVal) {
      if (newVal === oldVal) return;
      this.ean = newVal.replace(/[^0-9]/g, "");
    },
    categories(newVal) {
      return newVal;
    },
    product: {
      deep: true,
      handler(v) {
        this.fill(v);
      },
    },
    "properties.value": {
      deep: true,
      immediate: true,
      handler(oldVal, newVal) {
        return newVal;
      },
    },
    enableVariations(v) {
      if (v === true) {
        this.api
          .get("custom/boxes")
          .then((response) => {
            this.boxes = response.data;
          })
          .catch((error) => {
            this.handleError(error);
          });
      }
    },
    variations: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    templates: {
      deep: true,
      handler(v) {
        this.$forceUpdate();
        return v;
      },
    },
    packageSearch: _.debounce(function (v) {
      if (v) {
        this.api
          .get(`custom/products/search?q=${v}`)
          .then((response) => (this.result = response.data))
          .catch((error) => this.handleError(error));
      } else {
        this.result = [];
      }
    }, 300),
  },
  mounted() {
    this.get_templates();
    // this.api
    //   .get(`custom/products/search?q=`)
    //   .then((response) => (this.productList = response.data))
    //   .catch((error) => this.handleError(error));
  },
  beforeUnmount() {
    // reset store states
    this.set_active_custom_product({});
    this.set_variations([]);
  },
  methods: {
    ...mapMutations({
      set_variations: "product/set_variations",
      set_component: "product_wizard/set_wizard_component",
      set_active_custom_product: "product/set_active_custom_product",
      update_custom_product: "product/update_custom_product",
    }),
    ...mapActions({
      get_templates: "design/get_templates",
      get_custom_products: "product/get_custom_products",
    }),
    fill(product) {
      this.name = product.name;
      this.description = product.description;
      this.media = product.media;
      this.categoryId = product.category;
      this.price = product.price;
      this.ean = product.ean;
      this.artNumber = product.art_num;
      this.stockProduct = product.stock_product;
      this.validations = product.properties?.validations;
      this.stock.qty = product.stock?.qty ?? null;
      this.lowQtyThreshold = product.low_qty_threshold ?? null;
      this.enableVariations = product.variation;
      this.enableValidations = product.properties?.validations?.length > 0 ? true : false;
      this.package = product.combination;
      this.packageProducts = _.cloneDeep(product.products);
      this.set_variations(product.variations);
      this.properties = product.properties.props ? product.properties.props : [];
      this.enableTemplates = product.properties.template
        ? Object.keys(product.properties.template).length > 0
          ? true
          : false
        : false;
      this.productTemplate = product.properties.template;
      this.sort = product.sort;
      this.published = product.published;
    },
    add() {
      this.api
        .post(`/custom/products`, {
          name: this.name,
          description: this.description,
          media: this.media,
          category_id: this.active_custom_category.id,
          price: this.price,
          ean: this.ean ?? "",
          art_num: this.artNumber,
          stock_product: this.stockProduct,
          stock: { qty: this.stock.qty },
          low_qty_threshold: this.lowQtyThreshold,
          variation: this.enableVariations,
          combination: this.package,
          products: this.packageProducts,
          variations: this.variations,
          excludes: this.excludes,
          properties: {
            props: this.properties,
            validations: this.validations,
            template: this.productTemplate,
          },
          sort: this.sort,
          published: this.published,
        })
        .then((response) => {
          this.handleSuccess(response);

          this.set_active_custom_product(response.data);

          if (this.excludes) {
            this.set_component("CustomProductVariationExcludes");
          } else {
            this.$router.push("/assortment");
          }
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    update() {
      this.variations.forEach((variation) => {
        const options = variation.options;
        options.forEach((option) => {
          if (Object.keys(option).length === 1) {
            Object.assign(option, { option_id: option.id });
          }
        });
      });

      const theTemplate = this.productTemplate ?? [];

      this.api
        .put(`/custom/products/${this.product.id}`, {
          name: this.name,
          description: this.description,
          media: this.media,
          category_id: this.active_custom_category.id,
          price: this.price,
          ean: this.ean ?? "",
          art_num: this.artNumber,
          stock_product: this.stockProduct,
          stock: { qty: this.stock.qty },
          low_qty_threshold: this.lowQtyThreshold,
          combination: this.package,
          products: this.packageProducts,
          variation: this.enableVariations,
          variations: this.variations,
          excludes: this.excludes,
          properties: {
            props: this.properties,
            validations: this.validations,
            template: theTemplate,
          },
          sort: this.sort,
          published: this.published,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.set_active_custom_product(response.data);
          // this.update_custom_product(response.data.data);
          this.get_custom_products({
            cat_id: this.active_custom_category.id,
          });
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    addPair(obj) {
      if (obj[""] !== undefined) {
        this.addToast({
          type: "success",
          message: this.$t("new_object_exists"),
        });
      } else {
        Object.assign(obj, { "": "" });
      }

      this.refresh();
      return obj;
    },
    changeKey(value, obj, new_name) {
      const old_name = Object.keys(obj).find((key) => obj[key] === value);
      Object.assign(obj, { [new_name]: value });
      // eslint-disable-next-line
      delete obj[old_name];
      this.refresh();
    },
    refresh() {
      this.properties = [...this.properties];
      return this.properties;
    },

    addValidation(file) {
      let str = "";

      if (file.required) {
        str += "required|";
      }

      str += "file|";
      str += `mimes:${file.mime}`;

      this.validations.push({ [file.name]: str });
    },
    removeValidation(i) {
      this.validations.splice(i, 1);
    },

    toggleTemplate(template) {
      this.productTemplate = {
        mode: "DesignProviderTemplate",
        id: template.id,
      };
    },

    updateImage(e) {
      if (typeof e.index === "number") {
        this.media.splice(e.index, 1);
      } else {
        // this.media.push(e);
        this.media = [e];
      }
    },
  },
};
</script>
