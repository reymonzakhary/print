<template>
  <article class="rounded-md border border-orange-500 bg-white p-4 shadow-md dark:bg-gray-700">
    <div
      class="-m-4 mb-4 rounded-t bg-orange-100 p-2 text-center font-normal normal-case text-orange-500 shadow-orange-200 dark:bg-orange-900 dark:shadow-orange-900"
    >
      <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-2" />
      <b>{{ $t("WARNING") }}!</b> <br />
      <span class="text-sm">
        {{ $t("These fields are edited for every category that uses this box") }}.
      </span>
    </div>

    <section class="flex w-full flex-wrap justify-between pt-2">
      <div class="relative z-10 w-full">
        <label for="display_name" class="text-sm font-bold uppercase tracking-widest">
          <font-awesome-icon :icon="['fal', 'display']" class="fa-fw mr-2 text-gray-500" />
          {{ $t("display name") }}
          <button
            v-tooltip="$t('translate this value')"
            class="rounded-full px-2 text-sm uppercase text-theme-500 hover:bg-theme-100"
            @click="translate = !translate"
          >
            {{ $i18n.locale }}
            <font-awesome-icon :icon="['fal', !translate ? 'language' : 'circle-xmark']" />
          </button>
        </label>

        <template v-for="lang in item.display_name" :key="lang.iso">
          <input
            v-if="lang.iso === $i18n.locale"
            v-model="
              item.display_name[item.display_name.findIndex((name) => name.iso === lang.iso)]
                .display_name
            "
            type="text"
            name="name"
            class="input box-border w-full rounded border-theme-500 p-2"
            @input="update_item({ key: 'display_name', value: item.display_name })"
          />
        </template>

        <transition name="fade">
          <div v-show="translate" class="flex w-full flex-wrap bg-gray-100">
            <template v-for="lang in item.display_name">
              <div v-if="lang.iso !== $i18n.locale" :key="lang.iso" class="p-4">
                <label
                  :for="`category_name_${lang.iso}`"
                  class="flex text-xs font-bold uppercase tracking-wide"
                >
                  {{ $t("Name") }}
                  <span class="ml-auto text-theme-500">
                    <font-awesome-icon :icon="['fal', 'flag']" />
                    {{ lang.iso }}
                  </span>
                </label>
                <input
                  v-model="
                    item.display_name[item.display_name.findIndex((name) => name.iso === lang.iso)]
                      .display_name
                  "
                  type="text"
                  :name="`category_name_${lang.iso}`"
                  class="input"
                  @input="update_item({ key: 'display_name', value: item.display_name })"
                />
              </div>
            </template>
          </div>
        </transition>

        <div class="flex w-full justify-between">
          <small class="text-gray-500">
            {{ $t("original name") }}: <b>{{ item.name }}</b>
          </small>
          <!-- <small class="text-gray-500">
            {{ $t("system key") }}: <b>{{ item.system_key }}</b>
          </small> -->
        </div>
      </div>

      <!-- <span class="relative z-0 -ml-1 mr-2 w-1/2 flex-1">
        <label for="system_key" class="ml-2 text-sm font-bold uppercase tracking-widest">
          <font-awesome-icon :icon="['fal', 'server']" class="fa-fw mr-2 text-gray-500" />
          {{ $t("system key") }}
        </label>
        <input
          type="text"
          :value="item.system_key"
          name="system_key"
          class="input w-full rounded-none rounded-r p-2 pl-4"
          @input="update_item({ key: 'system_key', value: $event.target.value })"
        />
      </span> -->

      <div class="mt-4 grid w-full grid-cols-2 gap-2">
        <div>
          <label for="images" class="text-sm font-bold uppercase tracking-widest">
            {{ $t("images") }}
          </label>
          <UIImageSelector
            disk="assets"
            :selected-image="item.media ? item.media[0] : null"
            @on-image-select="
              update_item({
                key: 'media',
                value: $event,
              })
            "
            @on-image-remove="
              update_item({
                key: 'media',
                value: '',
              })
            "
          />
        </div>
        <div>
          <span class="relative w-full flex-shrink-0 md:ml-2 md:w-1/2 lg:ml-0 lg:mt-4 lg:w-full">
            <label for="description" class="text-sm font-bold uppercase tracking-widest">
              {{ $t("description") }}
            </label>
            <textarea
              :value="item.description"
              name="description"
              class="input w-full p-2"
              rows="3"
              @change="
                update_item({
                  key: 'description',
                  value: $event.target.value,
                })
              "
            />
          </span>
        </div>
      </div>
    </section>

    <section class="mt-4 flex flex-wrap justify-around border-b pb-4 dark:border-gray-900">
      <div
        class="relative mt-4 flex w-full items-center justify-between pb-2 capitalize sm:w-1/2 md:w-full"
      >
        <div>
          <font-awesome-icon :icon="['fal', 'heart-rate']" class="fa-fw mr-2 text-theme-500" />
          {{ $t("published") }}
        </div>
        <div class="flex items-center">
          <div
            class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
            :class="[item.published ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="published"
              class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear dark:bg-gray-700"
              :class="[
                item.published ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
              ]"
            />
            <input
              id="published"
              type="checkbox"
              name="published"
              class="h-full w-full appearance-none focus:outline-none active:outline-none"
              :value="item.published"
              @input="
                update_item({
                  key: 'published',
                  value: $event.target.checked,
                })
              "
            />
          </div>
          <font-awesome-icon
            v-tooltip="
              $t('This item will be available if your category is published on your webshop')
            "
            :icon="['fal', 'circle-info']"
            class="fa-fw ml-auto mr-2 text-theme-500"
          />
        </div>
      </div>
      <!-- TODO: re-enable when backend can handle this in the productfinder
      <div v-if="producer" class="relative mt-2 flex w-full items-center sm:w-1/3 md:w-full">
        <font-awesome-icon :icon="['fal', 'radar']" class="fa-fw mr-2 text-theme-500" />
        {{ $t("shared in finder") }}
        <div
          class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
          :class="[item.shareable ? 'bg-theme-400' : 'bg-gray-300']"
        >
          <label
            for="shareable"
            class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
            :class="[
              item.shareable ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
            ]"
          />
          <input
            id="shareable"
            v-model="item.shareable"
            type="checkbox"
            name="shareable"
            class="h-full w-full appearance-none focus:outline-none active:outline-none"
          />
        </div>
        <font-awesome-icon
          v-tooltip="$t('This will share your category in the product-finder in the Marketplace')"
          :icon="['fal', 'circle-info']"
          class="fa-fw ml-auto mr-2 text-theme-500"
        />
      </div>

      <div v-else class="mt-2 flex w-full flex-wrap items-center text-gray-500 sm:w-1/3 md:w-full">
        <font-awesome-icon :icon="['fal', 'radar']" class="fa-fw mr-2 text-gray-500" />
        {{ $t("shared in finder") }}
        <font-awesome-icon
          v-tooltip="$t('You need to be a producer to share your category in the product-finder')"
          :icon="['fal', 'circle-info']"
          class="fa-fw ml-auto mr-2 text-theme-500"
        />
        <NuxtLink
          v-if="!producer"
          to="/manage/tenant-settings/producer-information"
          class="my-2 flex w-full items-center justify-center rounded-full bg-gradient-to-r from-theme-400 to-pink-500 p-2 text-sm text-white backdrop-opacity-80 transition-all hover:from-theme-500 hover:to-pink-600"
        >
          <font-awesome-icon :icon="['fal', 'industry-windows']" class="fa-fw mr-2" />
          {{ $t("I want to be a producer in the Marketplace") }}
        </NuxtLink>
      </div> -->
    </section>

    <section class="flex">
      <div class="mr-2 w-1/2">
        <span class="relative mt-4 flex w-full items-center justify-between">
          <div>
            <font-awesome-icon :icon="['fal', 'trailer']" class="mr-2 text-theme-500" />
            {{ $t("appendage") }}
          </div>

          <div class="flex items-center">
            <UISwitch
              id="sqm_trailer"
              :value="item.appendage"
              name="appendage"
              @input="
                update_item({
                  key: 'appendage',
                  value: $event,
                })
              "
            />
            <VMenu theme="tooltip">
              <UIButton
                class="ml-2 !p-0 !text-base"
                variant="link"
                :icon="['fal', 'circle-info']"
              />

              <template #popper>
                <div class="flex max-w-80 flex-col p-4">
                  <p>
                    {{
                      // prettier-ignore
                      $t("When enabled, this box will always be treated as an appendage, meaning the options will be extra on top of the product or variation.")
                    }}
                  </p>
                </div>
              </template>
            </VMenu>
          </div>
        </span>

        <span class="relative my-4 block">
          <label for="input_type" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'symbols']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("input type") }}
          </label>
          <select
            id="input_type"
            disabled
            :value="item.input_type"
            name="input_type"
            class="input w-full p-2"
            @change="
              update_item({
                key: 'input_type',
                value: $event.target.value,
              })
            "
          >
            <option value="radio" :checked="item.input_type === 'radio'">Radio</option>
            <option value="checkbox" :checked="item.input_type === 'checkbox'">Checkbox</option>
            <option value="text" :checked="item.input_type === 'text'">Text</option>
            <option value="number" :checked="item.input_type === 'number'">Number</option>
            <option value="select" :checked="item.input_type === 'select'">Select</option>
          </select>
        </span>

        <!-- CALC REF -->
        <span class="relative my-4 block">
          <label for="calc_ref" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'calculator']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("calculation reference") }}
          </label>
          <select
            id="calc_ref"
            :value="item.calc_ref"
            name="calc_ref"
            class="input w-full p-2"
            @change="
              update_item({
                key: 'calc_ref',
                value: $event.target.value,
              })
            "
          >
            <option value="" :checked="item.calc_ref === ''" />
            <option value="other" :checked="item.calc_ref === 'other'">
              {{ $t("none") }}
            </option>
            <option value="format" :checked="item.calc_ref === 'format'">{{ $t("format") }}</option>
            <option value="weight" :checked="item.calc_ref === 'weight'">
              {{ $t("weight") }}
            </option>
            <option value="material" :checked="item.calc_ref === 'material'">
              {{ $t("material") }}
            </option>
            <option value="printing_colors" :checked="item.calc_ref === 'printing_colors'">
              {{ $t("printing colors") }}
            </option>
            <option value="lamination" :checked="item.calc_ref === 'lamination'">
              {{ $t("lamination") }}
            </option>
            <option value="pages" :checked="item.calc_ref === 'pages'">
              {{ $t("pages") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="sides"
              :checked="item.calc_ref === 'sides'"
            >
              {{ $t("sides") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="cover"
              :checked="item.calc_ref === 'cover'"
            >
              {{ $t("cover") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="binding_direction"
              :checked="item.calc_ref === 'binding_direction'"
            >
              {{ $t("binding direction") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="binding_method"
              :checked="item.calc_ref === 'binding_method'"
            >
              {{ $t("binding method") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="binding_color"
              :checked="item.calc_ref === 'binding_color'"
            >
              {{ $t("binding color") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="binding_material"
              :checked="item.calc_ref === 'binding_material'"
            >
              {{ $t("binding material") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="folding"
              :checked="item.calc_ref === 'folding'"
            >
              {{ $t("folding") }}
            </option>
            <option
              v-if="selected_category?.price_build?.full_calculation"
              value="endpapers"
              :checked="item.calc_ref === 'endpapers'"
            >
              {{ $t("endpapers") }}
            </option>
          </select>
        </span>
        <!-- CALC REF END -->
      </div>

      <div class="ml-2 w-1/2">
        <!-- DIVIDER -->
        <!-- <div v-if="type === 'box'" class="p-2 border border-gray-200 rounded">
          <span class="relative block mb-4">
            <label for="divider" class="text-sm font-bold tracking-widest uppercase">
              <font-awesome-icon :icon="['fal', 'split']" class="mr-2 text-gray-500 fa-fw" />
              {{ $t("divider") }}
            </label>
            <UIInputText
              :placeholder="capitalizeFirstLetter($t('box divider'))"
              :model-value="item.divider || ''"
              name="divider"
              @update:model-value="
                update_item({
                  key: 'divider',
                  value: $event,
                })
              "
            />
          </span>
        </div> -->
      </div>
    </section>
  </article>
</template>

<script>
import moment from "moment";
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

export default {
  props: {
    type: {
      type: [String, null],
      default: null,
    },
  },
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      isDynamic: false,
      is3D: this.item && this.item.dimension === "3d",
      moment: moment,
      linked: {},
      filter: "",
      parents: [],
      translate: false,
    };
  },
  computed: {
    ...mapState({
      item: (state) => state.assortmentsettings.item,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
  },
  watch: {
    filter: _.debounce(function (v) {
      this.getAllItems({ page: 1, filter: v });
    }, 300),
    item: {
      deep: true,
      handler(v) {
        return v;
      },
      immediate: true,
    },
  },
  created() {
    if (this.item && this.item.dynamic) {
      this.setIsDynamic(this.item.dynamic);
    }
    if (this.item) {
      this.item.input_type = "radio";
    }
  },
  methods: {
    ...mapMutations({
      update_item: "assortmentsettings/update_item",
    }),
    setIs3D(dimension) {
      this.is3D = dimension === "3d" ? true : false;
      this.update_item({
        key: "dimension",
        value: dimension,
      });
    },
    setIsDynamic(dynamic) {
      this.isDynamic = dynamic;
      this.update_item({
        key: "dynamic",
        value: dynamic,
      });
    },
    async getAllItems(e) {
      let url = "";
      switch (this.type) {
        case "box":
          url = `boxes`;
          break;
        case "option":
          url = `options`;
          break;

        default:
          break;
      }

      await this.api.get(`/${url}?filter=${e.filter ? e.filter : ""}`).then((response) => {
        this.parents = response.data;
      });
    },
  },
};
</script>
