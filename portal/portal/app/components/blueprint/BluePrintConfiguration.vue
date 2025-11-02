<template>
  <div class="relative h-full">
    <div
      class="w-full h-full bg-white rounded shadow-md dark:bg-gray-700 shadow-gray-200 dark:shadow-gray-900"
    >
      <header
        class="flex items-center justify-between p-2 rounded-t bg-theme-400 text-themecontrast-400"
      >
        <div class="text-xl">
          {{ selected_blueprint.name }} -
          <span class="text-sm text-gray-200">
            {{ selected_blueprint.ns }}
          </span>
        </div>
        <button
          class="px-2 text-red-500 rounded-full hover:bg-red-100 bg-red-50"
          @click.stop="showRemoveItem = true"
        >
          <font-awesome-icon :icon="['fal', 'trash-can']" />
        </button>
      </header>

      <div class="flex justify-between w-full gap-8">
        <div class="w-full p-2 md:w-2/3">
          <div class="w-full md:w-1/2">
            <div class="flex items-stretch w-full mt-4">
              <span class="w-1/3 px-2 bg-gray-100 rounded-l dark:bg-gray-800">
                {{ $t("name") }}
              </span>
              <input
                v-model="selected_blueprint.name"
                type="text"
                class="w-full p-0 pl-2 rounded-none rounded-r input"
              />
            </div>
            <div class="flex items-stretch w-full mt-2">
              <span class="w-1/3 px-2 bg-gray-100 rounded-l dark:bg-gray-800">
                {{ $t("namespace") }}
              </span>
              <select
                id="bp_namespace"
                v-model="selected_blueprint.ns"
                name="bp_namespace"
                class="w-full p-0 pl-2 rounded-none rounded-r input"
              >
                <option value="workflow_shop">workflow_shop</option>
                <option value="checkout">checkout</option>
                <option value="cart">cart</option>
                <option value="shop">shop</option>
                <option value="orders.mgr">orders.mgr</option>
                <option value="orders.system">orders.system</option>
                <option value="orders.api">orders.api</option>
                <option value="web">web</option>
              </select>
            </div>
            <button
              class="flex px-2 mt-2 ml-auto text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
              @click="updateBlueprint(selected_blueprint)"
            >
              {{ $t("update") }}
            </button>
          </div>

          <ul
            v-if="
              selected_blueprint.ns === 'checkout' ||
              selected_blueprint.ns === 'cart' ||
              selected_blueprint.ns === 'shop'
            "
            role="list"
            class="my-8 divide-y divide-gray-200 dark:divide-gray-800"
          >
            <header class="text-sm font-bold tracking-wide uppercase">
              {{ $t("linked products") }}
            </header>
            <li
              v-for="product in selected_blueprint.products"
              :key="`bp_product_${product.id}`"
              class="flex items-center py-4"
            >
              <CustomSingleProduct
                :product="product"
                :list="false"
                class="pr-2 border-r dark:border-gray-800"
              ></CustomSingleProduct>

              <font-awesome-icon
                v-tooltip="product.queueable ? $t('queueable') : $t('not queueable')"
                :icon="['fal', 'laptop-file']"
                class="pl-4 pr-2 text-gray-300"
                :class="{ 'text-theme-500': product.queueable }"
              />

              <button
                class="px-2 py-1 text-red-500 rounded-full hover:bg-red-100 bg-red-50 dark:bg-red-800 dark:hover:bg-red-900"
                @click.stop="detachProduct(product)"
              >
                <font-awesome-icon :icon="['fal', 'chain-broken']" />
              </button>
            </li>
            <li>
              <button
                v-if="!linkProducts"
                class="ml-auto transition-colors text-theme-500 hover:text-theme-600"
                @click="linkProducts = true"
              >
                <font-awesome-icon :icon="['fal', 'chain']" />
                <font-awesome-icon :icon="['fal', 'plus']" />
                {{ $t("link (another) product") }}
              </button>
              <button
                v-else
                class="ml-auto transition-colors text-theme-500 hover:text-theme-600"
                @click="handleCloseLinkProducts"
              >
                <font-awesome-icon :icon="['fal', 'xmark']" />
                {{ $t("close") }}
              </button>
            </li>
            <li>
              <transition name="slide">
                <div v-if="linkProducts" class="p-2 bg-gray-100 rounded dark:bg-gray-800">
                  <div class="flex flex-wrap items-center justify-between mb-2">
                    <div class="w-full">
                      <span class="relative block">
                        <label for="input_type" class="text-xs font-bold tracking-widest uppercase">
                          {{ $t("add blueprint to product") }}
                        </label>

                        <div class="flex">
                          <input
                            v-model="packageSearch"
                            type="text"
                            :placeholder="$t('search all custom products')"
                            class="w-full p-2 input"
                          />
                        </div>

                        <!-- search result -->
                        <div class="flex flex-col bg-white divide-y rounded-b shadow-md">
                          <span
                            v-for="product in result"
                            :key="product.id"
                            class="flex items-center justify-between p-1 group hover:bg-gray-100"
                          >
                            <div class="flex items-center w-full">
                              <div class="flex flex-col flex-1">
                                <h2 class="mr-4 text-sm font-bold tracking-wide">
                                  {{ product.name }}
                                </h2>
                                <p class="mr-4 text-sm">
                                  {{ product.description }}
                                </p>
                              </div>
                              <p class="mr-4 font-mono">
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
                            </div>

                            <div
                              class="flex items-center justify-end invisible w-full group-hover:visible"
                            >
                              <ValueSwitch
                                name="queueable"
                                :set-checked="queueable"
                                class=""
                                @checked-value="queueable = $event.value"
                              ></ValueSwitch>
                              <button class="mx-2 text-theme-500" @click="attachProduct(product)">
                                {{ $t("link") }}
                              </button>
                            </div>
                          </span>
                        </div>
                      </span>
                    </div>
                  </div>
                </div>
              </transition>
            </li>
          </ul>
        </div>

        <div
          class="flex flex-col justify-around w-full p-4 mx-auto mt-4 mr-4 text-center bg-gray-100 rounded dark:bg-gray-800 md:w-1/3"
        >
          <font-awesome-icon
            :icon="['fad', 'diagram-nested']"
            class="mx-auto text-gray-300 text-7xl"
          />
          <button
            class="flex items-center p-2 mx-auto mt-4 transition-colors rounded-full text-themecontrast-400 bg-theme-400 hover:bg-theme-600"
            @click="showBlueprint = true"
          >
            <font-awesome-icon :icon="['fal', 'pencil']" />
            {{ $t("edit blueprint configuration") }}
          </button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <BluePrint v-if="showBlueprint" class="bg-white rounded" @on-close="showBlueprint = false" />
    </transition>

    <transition name="fade">
      <BluePrintRemoveItem
        v-if="showRemoveItem"
        :item="selected_blueprint"
        :url="url"
        @on-close="showRemoveItem = false"
      />
    </transition>
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
      showRemoveItem: false,
      showBlueprint: false,
      linkProducts: false,
      packageSearch: "",
      result: [],
      queueable: false,
    };
  },
  computed: {
    ...mapState({
      blueprints: (state) => state.blueprint.blueprints,
      selected_blueprint: (state) => state.blueprint.selected_blueprint,
    }),
  },
  watch: {
    selected_blueprint: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    blueprints: {
      deep: true,
      handler(v) {
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
  methods: {
    ...mapMutations({
      populate_blueprints: "blueprint/populate_blueprints",
      select_blueprint: "blueprint/select_blueprint",
    }),
    handleCloseLinkProducts() {
      this.linkProducts = false;
      this.packageSearch = "";
      this.result = [];
    },
    detachProduct(product) {
      this.api
        .post(`/blueprints/${this.selected_blueprint.id}/customs/products/${product.id}/deattach`, {
          queueable: this.queueable,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.api.get(`/blueprints`).then((response) => {
            this.populate_blueprints(response.data);
            this.select_blueprint(this.blueprints.find((x) => x.id === this.selected_blueprint.id));
          });
        })
        .catch((error) => this.handleError(error));
    },
    attachProduct(product) {
      this.api
        .post(`/blueprints/${this.selected_blueprint.id}/customs/products/${product.id}`, {
          queueable: this.queueable,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.api.get(`/blueprints`).then((response) => {
            this.populate_blueprints(response.data);
            this.select_blueprint(this.blueprints.find((x) => x.id === this.selected_blueprint.id));
            this.handleCloseLinkProducts();
          });
        })
        .catch((error) => this.handleError(error));
    },

    updateBlueprint(blueprint) {
      this.api
        .put(`/blueprints/${blueprint.id}`, {
          name: blueprint.name,
          ns: blueprint.ns,
        })
        .then((response) => {
          this.api.get(`/blueprints`).then((response) => {
            this.populate_blueprints(response.data);
          });
          this.handleSuccess(response);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>
