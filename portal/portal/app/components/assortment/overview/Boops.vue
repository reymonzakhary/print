<!-- eslint-disable vue/no-useless-template-attributes -->
<template>
  <section class="h-full">
    <section
      v-if="!boops || boops.boops.length === 0"
      class="ml-2 hidden h-full w-full flex-col flex-wrap items-center rounded bg-gray-200 p-4 text-center dark:bg-gray-900 md:flex"
    >
      <h2 class="text-xl font-bold text-gray-400">
        {{ $t("no boops available for this category") }}
      </h2>
      <figure class="my-8 flex items-start justify-center">
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x m-4 text-gray-300" />
        <font-awesome-icon :icon="['fad', 'bars']" class="fa-5x my-4 text-gray-400" />
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
      </figure>
    </section>

    <transition-group
      v-else
      name="list"
      tag="nav"
      class="relative flex h-full w-full pr-4 pt-12 text-sm lg:pt-0"
    >
      <nav class="mx-1 h-full min-h-24 w-80 flex-shrink-0 overflow-y-auto">
        <header class="sticky top-0 z-10 rounded-t bg-white dark:bg-gray-700">
          <div
            class="relative flex items-center border-b px-2 py-1 pr-8 text-sm font-bold uppercase tracking-wide dark:border-gray-900"
          >
            <div class="w-full overflow-hidden truncate">
              {{ $t("quantity") }}
            </div>
          </div>
        </header>
        <div
          class="rounded-b bg-white p-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
        >
          <UIInputText
            name="quantity"
            class="mr-2 w-full"
            :prefix="$t('quantity')"
            :model-value="qty"
            type="number"
            @input="(e) => handleQuantityChange(e.target.value)"
          />
        </div>
      </nav>
      <template v-if="qty">
        <template
          v-for="(box, index) in boops.boops"
          :key="`${box.slug}_index`"
          class="h-full overflow-y-auto"
        >
          <section
            v-if="index <= activeIndex && !checkAllOptionsExclude(box, box.divider)"
            class="relative"
            :class="{
              '!border-gray-200':
                box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
              'mx-1 mt-2 flex flex-col rounded border-2 border-gray-300 pb-4 pl-8 pr-2 pt-4 dark:border-gray-500':
                boops.divided && index <= activeIndex,
              'ml-0 rounded-l-none !border-l-0 !pl-0':
                boops.divided && boops?.boops[index - 1]?.divider === box.divider,
              'mr-0 rounded-r-none !border-r-0 !pr-0':
                boops.divided &&
                boops?.boops[index + 1]?.divider === box.divider &&
                index !== activeIndex,
            }"
          >
            <div
              v-if="
                boops.divided &&
                (boops?.boops[index - 1]?.divider !== box.divider ||
                  (index === 0 && boops.divided)) &&
                index <= activeIndex
              "
              class="absolute mx-auto -mt-7 bg-gray-100 px-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-800"
              :class="{
                '!text-gray-300':
                  box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
              }"
            >
              {{
                box.divider === "null" || box.divider === "undefined" || box.divider === ""
                  ? $t("not divided")
                  : box.divider
              }}
            </div>

            <div
              v-show="
                box.divider !== 'null' &&
                box.divider !== 'undefined' &&
                box.divider !== '' &&
                boops.divided &&
                ((boops?.boops[index - 1]?.divider !== box.divider && activeIndex >= index) ||
                  (index === 0 && box.divider?.length > 0))
              "
              class="absolute left-0 my-auto ml-2 flex h-full flex-col"
            >
              <font-awesome-icon
                :icon="['fad', 'calculator']"
                class="fa-lg text-gray-300 dark:text-gray-500"
              />
              <span
                class="mt-2 h-full truncate pb-4 text-gray-500 [writing-mode:vertical-rl]"
                :title="$t('calculation')"
              >
                {{ $t("calculation") }}
              </span>
            </div>

            <nav
              class="mx-1 h-full min-h-24 w-80 flex-shrink-0 overflow-y-auto"
              :style="'z-index:' + (28 - index)"
            >
              <header class="sticky top-0 z-10 rounded-t bg-white dark:bg-gray-700">
                <div
                  class="relative flex items-center border-b px-2 py-1 pr-8 text-sm font-bold uppercase tracking-wide dark:border-gray-900"
                >
                  <div
                    v-tooltip="
                      box.display_name && $display_name(box.display_name).length > 20
                        ? $display_name(box.display_name)
                        : ''
                    "
                    class="w-full overflow-hidden truncate"
                  >
                    {{ $display_name(box.display_name) }}
                  </div>
                  <div v-if="permissions.includes('print-assortments-boxes-update')" class="flex">
                    <font-awesome-icon
                      v-if="!box.linked"
                      v-tooltip="$t('this option is not standardized by Prindustry')"
                      class="ml-1 text-xs"
                      fixed-with
                      :icon="['fas', 'link-slash']"
                      :class="['text-amber-500']"
                    />
                    <UIButton
                      v-tooltip="
                        box?.calc_ref?.length > 0
                          ? $t('this box has calculation reference:') + ' ' + box.calc_ref
                          : $t('this box has no calculation reference! :(')
                      "
                      variant="inverted-neutral"
                    >
                      <font-awesome-icon
                        class="ml-1"
                        fixed-width
                        :icon="[
                          'fal',
                          box?.calc_ref?.length > 0 ? calcRef(box.calc_ref) : 'calculator',
                        ]"
                        :class="[box?.calc_ref?.length > 0 ? 'text-green-500' : 'text-amber-500']"
                      />
                      <font-awesome-icon
                        v-if="!box.calc_ref"
                        class="ml-1 text-xs"
                        fixed-with
                        :icon="['fas', 'exclamation']"
                        :class="['text-amber-500']"
                      />
                    </UIButton>
                  </div>
                  <span class="absolute right-0 top-0 z-10 mr-1" role="menu">
                    <client-only>
                      <ItemMenu
                        :menu-items="boxMenuItems"
                        menu-icon="ellipsis-h"
                        menu-class="rounded-full hover:bg-gray-100 h-6 w-6 mt-[2px]"
                        dropdown-class="right-0 border w-44 text-theme-900"
                        @item-clicked="menuItemClicked($event, box)"
                      />
                    </client-only>
                  </span>
                </div>
              </header>
              <div
                v-if="hasValue(index)"
                class="rounded bg-white p-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
              >
                <div
                  :key="'warning_' + index"
                  class="z-0 flex items-center rounded border-amber-500 bg-amber-100 px-2 py-4 text-amber-500"
                >
                  <font-awesome-icon :icon="['fal', 'arrow-left']" class="mr-2" />
                  {{ $t("select a value first") }}
                </div>
              </div>
              <nav
                v-else
                class="rounded-b bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
              >
                <ul>
                  <li
                    v-for="(item, i) in box.ops"
                    :key="`option_${i}`"
                    class="last:rounded-b last-of-type:rounded-b"
                    :class="{
                      'bg-theme-50 text-theme-500 hover:bg-theme-50 dark:bg-theme-900 dark:text-theme-200 dark:hover:bg-theme-900':
                        isSelected(box, item, index),
                    }"
                  >
                    <button
                      v-if="checkExclude(item, box.divider) === true"
                      class="group relative flex w-full flex-wrap items-center justify-between px-2 py-2 text-left hover:bg-gray-200 dark:hover:bg-gray-900"
                      @click="setActiveOption(box, item, index + 1)"
                    >
                      <div
                        v-tooltip="
                          $display_name(item.display_name).length > 30
                            ? $display_name(item.display_name)
                            : ''
                        "
                        class="flex items-center truncate pr-8"
                      >
                        <CategoryIcon
                          v-if="item?.media?.length > 0 && item?.media[0]"
                          :category="item"
                          class="mr-1 flex items-center"
                        />
                        <font-awesome-icon
                          v-else
                          v-tooltip="$t('this option has no image')"
                          class="mr-1 text-base text-gray-300"
                          fw
                          :icon="['fal', 'image-slash']"
                        />
                        {{ $display_name(item.display_name) }}
                      </div>

                      <div
                        v-if="
                          collection[index]?.key === box.slug &&
                          collection[index]?.value === item.slug &&
                          collection[index]?.divider === box.divider &&
                          item.dynamic
                        "
                        role="group"
                        class="mt-2 flex items-center justify-center pr-8"
                        @click.stop
                        @mousedown.stop
                      >
                        <BoopsDynamicOption
                          :item="item"
                          :selected="collection[index]"
                          :active-items="activeItems"
                          @on-pages-updated="setPages($event, index)"
                          @on-sides-updated="setSides($event, index)"
                          @on-format-width-updated="setFormat($event, 'width', index)"
                          @on-format-height-updated="setFormat($event, 'height', index)"
                        />
                      </div>
                      <div class="mr-8">
                        <span
                          v-if="
                            missingMaterialsInCatalogue &&
                            missingMaterialsInCatalogue.length > 0 &&
                            missingMaterialsInCatalogue.find(
                              (material) => material.optionName === item.name,
                            )
                          "
                          v-tooltip="$t('this material is missing in the catalogue')"
                        >
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'file']"
                            :class="['text-amber-500']"
                          />
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'exclamation']"
                            :class="['text-amber-500']"
                          />
                        </span>

                        <span
                          v-if="
                            missingGrsInCatalogue &&
                            missingGrsInCatalogue.length > 0 &&
                            missingGrsInCatalogue.find((grs) => grs.optionName === item.name)
                          "
                          v-tooltip="
                            // prettier-ignore
                            $t('this weight is missing in the catalogue.')
                          "
                        >
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'weight-hanging']"
                            :class="['text-amber-500']"
                          />
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'exclamation']"
                            :class="['text-amber-500']"
                          />
                        </span>
                        <span
                          v-if="
                            missingInMachine &&
                            missingInMachine.length > 0 &&
                            missingInMachine.find((color) => color.optionName === item.name)
                          "
                          v-tooltip="$t('this color is missing in the machine(s)')"
                        >
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'print']"
                            :class="['text-amber-500']"
                          />
                          <font-awesome-icon
                            fixed-with
                            :icon="['fal', 'exclamation']"
                            :class="['text-amber-500']"
                          />
                        </span>

                        <font-awesome-icon
                          v-if="!item.linked"
                          v-tooltip="$t('this option is not standardized by Prindustry')"
                          class="ml-1 text-xs"
                          fixed-with
                          :icon="['fas', 'link-slash']"
                          :class="['text-amber-500']"
                        />

                        <font-awesome-icon
                          v-if="
                            item.additional?.calc_ref && item.additional?.calc_ref !== box.calc_ref
                          "
                          v-tooltip="
                            // prettier-ignore
                            $t('this option has a different calculation reference then the box. The calculation will not work for this option.') +
                            ' ' +
                            item.additional.calc_ref
                          "
                          class="ml-1 text-sm text-amber-500"
                          fixed-with
                          :icon="['fal', calcRef(item.additional?.calc_ref)]"
                        />
                        <font-awesome-icon
                          v-if="
                            item.additional?.calc_ref && item.additional?.calc_ref === box.calc_ref
                          "
                          class="mx-1 text-xs text-green-500"
                          fixed-with
                          :icon="['fal', calcRef(item.additional?.calc_ref)]"
                        />
                      </div>

                      <span
                        class="invisible absolute right-0 top-0 z-10 mr-1 flex group-hover:visible"
                        role="menu"
                      >
                        <client-only>
                          <ItemMenu
                            :menu-items="menuItems"
                            menu-icon="ellipsis-h"
                            menu-class="rounded-full hover:bg-gray-100 h-8 w-8 mt-[2px]"
                            dropdown-class="right-0 border w-44 text-theme-900"
                            @item-clicked="menuItemClicked($event, item)"
                          />
                        </client-only>
                      </span>
                    </button>
                    <button
                      v-else
                      disabled
                      class="flex w-full cursor-not-allowed items-center truncate px-2 py-2 text-left text-gray-300 dark:text-gray-700"
                    >
                      {{ $display_name(item.display_name) }}
                      <VDropdown @click="setSelected(box, item, box.divider ? box.divider : null)">
                        <font-awesome-icon
                          v-tooltip="
                            item.excludes?.filter((a) => a.length > 0 && a.length < 2).length > 0
                              ? `Has ${
                                  item.excludes.filter((a) => a.length > 0 && a.length < 2).length
                                } excludes`
                              : 'has no excludes'
                          "
                          class="ml-1"
                          fixed-with
                          :icon="[
                            item.excludes?.filter((a) => a.length > 0 && a.length < 2).length > 0
                              ? 'fad'
                              : 'fal',
                            'circle-info',
                          ]"
                          :class="[
                            item.excludes?.filter((a) => a.length > 0 && a.length < 2).length > 0
                              ? 'cursor-pointer text-theme-500'
                              : 'text-gray-300',
                          ]"
                        />
                        <template
                          v-if="
                            item.excludes?.filter((a) => a.length > 0 && a.length < 2).length > 0
                          "
                          #popper
                        >
                          <section class="rounded border-theme-400 p-4 shadow-md">
                            <div
                              v-for="(excl, idx) in item.excludes"
                              :key="excl + '_' + idx"
                              class=""
                            >
                              <template v-if="excl.length === 1">
                                <div v-if="idInfo(excl[0])">
                                  {{
                                    //prettier-ignore
                                    $t("this option can only be selected if you alter the following option: ")
                                  }}
                                  <span class="font-bold" role="presentation">
                                    {{ $display_name(idInfo(excl[0]).display_name) }}
                                  </span>
                                </div>
                              </template>
                            </div>
                          </section>
                        </template>
                      </VDropdown>
                    </button>
                  </li>
                </ul>
              </nav>
            </nav>
          </section>
        </template>

        <nav
          v-if="!showViewPricesButton"
          class="mx-1 flex-1"
          :class="{
            'mb-5 ml-2 lg:mt-7': boops.divided,
            'absolute right-4 top-0 w-72 lg:relative lg:right-auto lg:top-auto lg:block':
              activeItems?.length !== boops.boops?.length,
          }"
        >
          <!-- :style="'z-index:' + (28 - index)" -->
          <section
            class="rounded bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
          >
            <div
              class="relative hidden items-center border-b px-2 py-1 pr-8 text-sm font-bold uppercase tracking-wide dark:border-gray-900 lg:flex"
            >
              <div class="w-full overflow-hidden truncate">
                {{ $t("collection actions") }}
              </div>
            </div>
            <div class="p-2 lg:p-4">
              <div
                v-if="
                  !hasValue(activeItems?.length) &&
                  activeItems?.length === boops.boops?.length &&
                  boops.boops?.length > 0 &&
                  permissions.includes('print-assortments-categories-update')
                "
                @click.prevent="activateDetails()"
              >
                <span class="italic text-gray-500 dark:text-gray-400">
                  {{ $t("no prices available") }}
                  <font-awesome-icon class :icon="['fal', 'sad-tear']" />
                </span>
              </div>
              <div
                v-if="
                  !hasValue(activeItems?.length) &&
                  activeItems?.length !== boops.boops?.length &&
                  boops.boops?.length > 0
                "
                class="lg:flex-no-wrap flex w-full flex-wrap items-center justify-between text-sm text-amber-500"
              >
                <span class="flex">
                  <font-awesome-icon :icon="['fal', 'arrow-left']" class="mr-2" />
                  <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="mr-2" />
                </span>
                <div class="lg:py-2">{{ $t("make selection complete first") }}</div>
                <div>
                  <b>{{ activeItems.length + "/" + boops.boops?.length }}</b>
                </div>
              </div>
              <div
                v-if="
                  hasValue(activeItems.length) &&
                  activeItems?.length === boops.boops?.length &&
                  boops.boops?.length > 0
                "
                :key="'warning_' + i"
                class="m-2 flex items-center rounded border-amber-500 bg-amber-100 px-2 py-4 text-amber-500"
              >
                <font-awesome-icon :icon="['fal', 'arrow-left']" class="mr-2" />
                {{ $t("select a value first") }}
              </div>
              <div
                v-if="
                  hasValue(activeItems.length) &&
                  activeItems?.length === boops.boops?.length &&
                  boops.boops?.length > 0 &&
                  (!someOptionsHaveWidth || !someOptionsHaveHeight)
                "
                :key="'warning_' + i"
                class="m-2 flex items-center rounded border-amber-500 bg-amber-100 px-2 py-4 text-amber-500"
              >
                <font-awesome-icon :icon="['fal', 'arrow-left']" class="mr-2" />
                {{
                  // prettier-ignore
                  $t("It seems like some of your options do not have a {dimension}. At least one option needs a width and a height set to calculate the price in a semi-calculation context. Do you want to continue?", {dimension: (!someOptionsHaveWidth)? 'width' : (!someOptionsHaveHeight)? 'heigth': 'width and height' })
                }}
              </div>
            </div>
          </section>
        </nav>
      </template>
    </transition-group>
    <Teleport to="body">
      <div v-if="component === 'OptionsEditPanel'">
        <OptionsEditPanel :show-runs-panel="true" @on-close="handleClose" />
      </div>

      <div v-if="component === 'BoxesEditPanel'">
        <BoxesEditPanel @on-close="handleClose" />
      </div>
    </Teleport>
  </section>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

/**
 * step one get object from boops service
 * collect the selected data
 * generate a md5 hash from it
 * post it to hash service
 * response with price and quant || add price and quant
 **/
export default {
  props: {
    scrollToEnd: {
      type: Function,
      required: true,
    },
    showViewPricesButton: {
      type: Boolean,
      default: true,
    },
    missingMaterialsInCatalogue: {
      type: Object,
      default: () => {},
      required: false,
    },
    missingGrsInCatalogue: {
      type: Object,
      default: () => {},
      required: false,
    },
    missingInMachine: {
      type: Object,
      default: () => {},
      required: false,
    },
    qty: {
      type: Number,
      default: 0,
      required: true,
    },
  },
  emits: [
    "collectionComplete",
    "boopsProcessed",
    "onShowPrices",
    "collectionUpdated",
    "updateQty",
    "reset:prices",
  ],
  setup() {
    const { confirm } = useConfirmation();
    const { permissions } = storeToRefs(useAuthStore());
    const { handleError } = useMessageHandler();

    const api = useAPI();
    return { permissions, api, confirm, handleError };
  },
  data() {
    return {
      someOptionsHaveHeight: false,
      someOptionsHaveWidth: false,
      activeIndex: 0,
      activeObject: {},
      activeItems: [],
      collection: [],
      exclude: {},
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("Edit"),
              classes: "",
              show: false,
            },
          ],
        },
      ],
      boxMenuItems: [
        {
          items: [
            {
              action: "editBox",
              icon: "pencil",
              title: this.$t("Edit"),
              classes: "",
              show: false,
            },
          ],
        },
      ],
      component: false,
      // excludes feedback
      flatOptions: [],
    };
  },
  computed: {
    ...mapState({
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
  },
  watch: {
    category: {
      immediate: true,
      handler() {
        this.resetState();
        // this.getBoops(); -->> category carries boops already
      },
    },
    selected_category: {
      immediate: true,
      handler() {
        this.resetState();
        // this.getBoops(); -->> category carries boops already
      },
    },
    boops() {
      // flat options on boops change to be able to read the options for the excludes information
      this.flattenOptions();
    },
    collection: {
      deep: true,
      handler(v) {
        const boop = this.boops.boops.find((boop) => boop.system_key === v[v.length - 1]?.key);
        if (!boop) return v;
        const option = boop.ops.find((op) => op.slug === v[v.length - 1].value);
        if (!option) return v;
        const hasHeight = !!option.height;
        const hasWidth = !!option.width;
        this.someOptionsHaveHeight = this.someOptionsHaveHeight || hasHeight;
        this.someOptionsHaveWidth = this.someOptionsHaveWidth || hasWidth;
        return v;
      },
    },
  },
  created() {
    if (
      this.permissions.includes("print-assortments-options-update") &&
      this.permissions.includes("print-assortments-options-list")
    ) {
      this.menuItems[0].items[0].show = true;
    }
    if (
      this.permissions.includes("print-assortments-boxes-update") &&
      this.permissions.includes("print-assortments-boxes-list")
    ) {
      this.boxMenuItems[0].items[0].show = true;
    }
  },
  mounted() {
    if (this.boops?.boops) {
      this.flattenOptions();
      this.buildCollectionFromUrl();
    }
  },
  methods: {
    ...mapMutations({
      set_boops: "product/set_boops",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_active_items: "product/set_active_items",
      set_active_detail: "product_wizard/set_active_detail",
      set_selected_option: "product_wizard/set_selected_option",
      set_selected_divider: "product_wizard/set_selected_divider",
      set_selected_box: "product_wizard/set_selected_box",

      set_item: "assortmentsettings/set_item",
      set_runs: "assortmentsettings/set_runs",
      set_flag: "assortmentsettings/set_flag",
    }),
    handleClose() {
      this.resetState();
      this.component = false;
      this.$emit("reset:prices");
    },
    handleQuantityChange(quantity) {
      this.$emit("updateQty", quantity);
    },
    setSelected(box, item, divider) {
      this.set_selected_box(box);
      this.set_selected_option(item);
      if (divider) {
        this.set_selected_divider(divider);
      }
    },
    resetState() {
      this.activeIndex = 0;
      this.activeItems = [];
      this.collection = [];
      this.activeObject = {};
      this.exclude = {};
    },
    calcRef(calc_ref) {
      switch (calc_ref) {
        case "format":
          return "ruler-combined";
        case "material":
          return "file";
        case "weight":
          return "weight-hanging";
        case "printing_colors":
          return "circles-overlap-3";
        default:
          return "check";
      }
    },
    buildCollectionFromUrl() {
      if (this.boops?.boops) {
        // Get url param c
        const route = useRoute();
        const collectionParam = route.query.c;

        if (collectionParam) {
          // console.log(collectionParam);
          // Split boxes by |
          const boxes = collectionParam.split("|");

          boxes.forEach((box, index) => {
            // Split box params by :
            const [key, value, divider, dynamicProps] = box.split(":");

            // Find matching box and option
            const matchingBox = this.boops.boops[index];
            if (!matchingBox) return;

            const matchingOption = matchingBox.ops.find((op) => op.slug === value);
            if (!matchingOption) return;

            // Build collection item
            const collectionItem = {
              key,
              key_id: matchingBox.id,
              source_key: matchingBox?.source_slug,
              value,
              value_id: matchingOption.id,
              source_value: matchingOption?.source_slug,
              divider,
              dynamic: matchingOption.dynamic,
              _: {},
            };

            // Add dynamic props if they exist
            if (dynamicProps) {
              const props = dynamicProps.split(",");
              props.forEach((prop) => {
                const [key, value] = prop.split("=");
                collectionItem._[key] = parseInt(value);
              });
            }

            // Add to collection and activeItems
            this.collection[index] = collectionItem;
            this.activeItems.push(matchingOption);
            if (
              index + 1 <= this.boops.boops.length - 1 &&
              this.checkAllOptionsExclude(this.boops.boops[index + 1], divider)
            ) {
              this.activeIndex = index + 1;
            } else {
              this.activeIndex = index;
            }
          });

          this.set_active_collection(this.collection);

          // If collection is complete, get prices
          if (
            this.activeItems.length === this.boops.boops.length ||
            this.activeIndex >= this.boops.boops.length - 1
          ) {
            this.$emit("collectionComplete", this.collection);
          }
        }
      }
    },
    setFormat(val, dim, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }

      if (dim === "height") {
        this.collection[index]._.height = parseInt(val);
      } else if (dim === "width") {
        this.collection[index]._.width = parseInt(val);
      }

      /** collection complete will not be emitted when _ object has no value
       * to prevent wrong price from being fetched
       * Se we emit it here when the value is set and it is the last option
       * in the collection
       *  */
      if (this.activeItems.length === this.boops.boops.length) {
        if (this.activeItems[this.activeItems.length - 1].dynamic) {
          if (
            this.collection[this.collection.length - 1]._.height &&
            this.collection[this.collection.length - 1]._.width
          ) {
            this.$emit("collectionComplete", this.collection);
          } else {
            this.$emit("collectionUpdated", this.collection);
          }
        } else {
          this.$emit("collectionComplete", this.collection);
        }
      }
    },
    setPages(val, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }

      Object.assign(this.collection[index]._, { pages: parseInt(val) });

      /** collection complete will not be emitted when _ object has no value
       * to prevent wrong price from being fetched
       * Se we emit it here when the value is set and it is the last option
       * in the collection
       *  */
      if (this.activeItems.length === this.boops.boops.length) {
        this.$emit("collectionComplete", this.collection);
      } else {
        this.$emit("collectionUpdated", this.collection);
      }
    },
    setSides(val, index) {
      if (val == undefined || isNaN(val) || val == "") {
        val = 0;
      }

      Object.assign(this.collection[index]._, { sides: parseInt(val) });

      /** collection complete will not be emitted when _ object has no value
       * to prevent wrong price from being fetched
       * Se we emit it here when the value is set and it is the last option
       * in the collection
       *  */
      if (this.activeItems.length === this.boops.boops.length) {
        this.$emit("collectionComplete", this.collection);
      } else {
        this.$emit("collectionUpdated", this.collection);
      }
    },
    hasValue(index) {
      // check if previous dynamic option has value inputted
      if (this.activeItems[index - 1]?.dynamic) {
        // console.log(Object.keys(this.collection[index - 1]?._).length);
        // console.log(this.collection[index - 1]?._);
      }
      if (index !== 0) {
        return this.activeItems[index - 1]?.dynamic &&
          this.collection[index - 1]?._ &&
          this.activeItems[index - 1]?.dynamic
          ? this.activeItems[index - 1]?.dynamic_type === "format"
            ? Object.keys(this.collection[index - 1]?._)?.length < 2
            : Object.keys(this.collection[index - 1]?._)?.length < 1
          : false;
      }
    },
    isSelected(box, item, index) {
      // check if item is selected
      return !!this.collection.find(
        (collect) => collect?.value_id === item.id && collect?.divider === box.divider,
      );
    },
    idInfo(id) {
      for (let i = 0; i < this.flatOptions.length; i++) {
        const option = this.flatOptions[i];
        if (option.id === id) {
          return option;
        }
      }
    },
    flattenOptions() {
      const arr = [];
      for (let i = 0; i < this.boops?.boops?.length; i++) {
        const box = this.boops?.boops[i];
        arr.push(box.ops);
      }
      this.flatOptions = [].concat(...arr);
    },
    checkAllOptionsExclude(box, divider = "") {
      return box.ops.every((option) => {
        return !this.checkExclude(option, divider);
      });
    },
    setActiveOption(box, item, index) {
      /** @activeIndex int hold the box position **/
      this.activeItems.length = index - 1;
      this.activeItems.splice(index, 1);
      this.activeItems.push(item);

      /** @exclude array: reset the array **/
      this.exclude = {};
      const targetIndex = index - 1;
      this.collection[targetIndex] = {
        key: box.slug,
        source_key: box?.source_slug ?? null,
        key_id: box.id,
        value: item.slug,
        source_value: item?.source_slug ?? null,
        value_id: item.id,
        divider: box.divider,
        dynamic: item.dynamic,
        _: {},
      };
      // Trim any downstream selections; they’re no longer valid after this choice.
      if (this.collection.length > targetIndex + 1) {
        this.collection.splice(targetIndex + 1);
      }

      /** @activeObject Object follow the current steps **/
      Object.values(this.activeObject).forEach((v, k) => {
        if (k > index - 1) {
          delete this.activeObject[k];
        }
      });

      /** Updating the current active object **/
      this.activeObject = Object.assign(this.activeObject, {
        [index - 1]: {
          index: index - 1,
          name: item.name,
          exclude: item.excludes,

          box_id: box.id,
          option_id: item.id,
          display_key: box.name,
          display_value: item.name,
          key: box.slug,
          value: item.slug,
          key_link: box.linked,
          value_link: item.linked,
        },
      });

      // clone the object
      const excl = { ...this.exclude };

      /** add the selected excludes to exclude object by box **/
      Object.values(this.activeObject).forEach((v) => {
        // add 'singles' & 'exclude with' object
        excl[item.slug] = { singles: [], with: [], exclude: [] };

        // add excludes
        if (v.exclude && v.exclude.filter((a) => a.length === 1).length > 0) {
          excl[item.slug].singles = _.cloneDeep(v.exclude);
        }

        // if excludes with other option combination: add the option itself
        if (v.exclude && v.exclude.filter((a) => a.length > 1).length > 0) {
          const withPocket = excl[item.slug].with;
          const exclPocket = excl[item.slug].exclude;

          // add all values to 'with object'
          for (let idx = 0; idx < v.exclude.length; idx++) {
            const excl = v.exclude[idx];

            withPocket.push(_.clone(excl));
          }

          withPocket.forEach((element) => {
            // add the item itself for better comparing
            element.unshift(item.id);

            // pop the excluded (last value)
            element.pop();
          });

          // add the exlcuded (last value) to excluded array
          for (let indx = 0; indx < v.exclude.length; indx++) {
            const element = v.exclude[indx];
            exclPocket.push(_.clone(element[element.length - 1]));
          }
        }
      });

      // reassign the object
      this.exclude = excl;

      this.set_active_collection(this.collection);
      if (
        index <= this.boops.boops.length - 1 &&
        this.checkAllOptionsExclude(this.boops.boops[index], box.divider)
      ) {
        this.activeIndex = index + 1;
      } else {
        this.activeIndex = index;
      }
      // emit to notify that a complete collection is selected
      if (
        (this.activeItems.length === this.boops.boops.length ||
          this.activeIndex === this.boops.boops.length) &&
        !this.hasValue(index)
      ) {
        this.$emit("collectionComplete", this.collection);
      } else {
        this.$emit("collectionUpdated", this.collection);

        // check if url params match boops length
        // TODO: this gives weird behaviour, but would enable selecting from half way. Need to fix different
        // setTimeout(() => {
        //   const route = useRoute();
        //   const collectionParam = route.query.c;
        //   if (collectionParam && collectionParam.split("|").length === this.boops.boops.length) {
        //     this.buildCollectionFromUrl();
        //     return;
        //   }
        // }, 100); // Add 100ms delay
      }

      // Scroll the view automatically to the last box
      setTimeout(() => {
        this.scrollToEnd();
      }, 200);
    },
    checkExclude(item, divider = "") {
      /**
       * if item matches any exclude return false
       * to disable the option in the selection
       */

      if (!Array.isArray(item.excludes)) {
        this.handleError(
          this.$t("This category has problems, please delete this and create a new one"),
        );
        return true;
      }

      // current selection to compare to the combination
      let actives = [];
      let flag = false;
      // set active items in flat id's array
      actives = this.collection.map((collect) => collect.value_id);
      item.excludes.forEach((exclude) => {
        if (exclude.every((singleExclude) => actives.includes(singleExclude))) {
          flag = true;
        }
      });

      // read the flag and exclude if flag is true
      if (flag) {
        return false;
      }

      // item does not match any excludes, so continue
      return true;
    },
    async menuItemClicked(event, item) {
      switch (event) {
        case "edit":
          await this.api
            .get(`categories/${this.selected_category.id}/options/${item.id}`)
            .then((response) => {
              this.set_item(response.data);
              this.set_runs(response.data.sheet_runs);
              this.set_runs(response.data.runs);
              this.set_flag("from_boops");
              this.component = "OptionsEditPanel";
            });
          break;

        case "editBox":
          await this.set_item(item);
          this.set_flag("from_boops");
          this.component = "BoxesEditPanel";
          break;

        default:
          break;
      }
    },
    async activateDetails() {
      // Store the data in localStorage
      // localStorage.setItem("pricesTime", JSON.stringify(new Date().getTime()));
      // localStorage.setItem("pricesActiveCategory", JSON.stringify(this.category));
      // localStorage.setItem("pricesCollection", JSON.stringify(this.collection));
      // localStorage.setItem("pricesActiveObject", JSON.stringify(this.activeObject));
      // localStorage.setItem("pricesActiveItems", JSON.stringify(this.activeItems));
      // localStorage.setItem("pricesBoops", JSON.stringify(this.boops));
      // localStorage.setItem("pricesSelectedCategory", JSON.stringify(this.selected_category));

      this.set_active_collection(this.collection);
      // store the active options (same as the on from the collection, but with all the data instead of only hashed id's)
      this.set_active_items(this.activeObject);
    },
  },
};
</script>
