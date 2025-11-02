<template>
  <div>
    <section class="pb-20">
      <div
        class="flex items-stretch justify-between w-full"
        :class="translate ? 'bg-gray-50' : ''"
      >
        <div
          class="w-full max-w-md p-4 mx-auto bg-white"
          :class="translate ? 'border-r' : ''"
        >
          <label
            for="category_name"
            class="flex items-center justify-between text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("Name") }}
            <button
              class="px-2 text-base rounded-full text-theme-500 hover:bg-theme-100"
              @click="translate = !translate"
            >
              <font-awesome-icon
                :icon="['fal', !translate ? 'language' : 'circle-xmark']"
              />
            </button>
          </label>
          <input
            v-model="name"
            type="text"
            name="category_name"
            class="input border-theme-500"
            @change="setTranslations('name', $event.target.value)"
          />
        </div>

        <transition name="fade">
          <div v-show="translate" class="flex flex-wrap w-full pl-8">
            <template v-for="(lang, i) in languages">
              <div
                v-if="lang.iso !== language"
                :key="lang.iso"
                class="w-full p-4"
              >
                <label
                  :for="`category_name_${lang.iso}`"
                  class="flex text-xs font-bold tracking-wide uppercase"
                >
                  {{ $t("Name") }}
                  <span class="ml-auto text-theme-500">
                    <font-awesome-icon :icon="['fal', 'flag']" />
                    {{ lang.name }}
                  </span>
                </label>
                <input
                  v-model="translations[i].name"
                  type="text"
                  :name="`category_name_${lang.iso}`"
                  class="input"
                />
              </div>
            </template>
          </div>
        </transition>
      </div>

      <div
        class="flex items-stretch justify-between border-y"
        :class="translate ? 'bg-gray-50' : ''"
      >
        <div
          class="w-full max-w-md p-4 mx-auto bg-white"
          :class="translate ? 'border-r' : ''"
        >
          <label
            for="category_description"
            class="flex items-center text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("Description") }}
            <span class="italic font-normal text-gray-500 normal-case">
              {{ $t("optional") }}
            </span>
          </label>
          <textarea
            v-model="description"
            type="text"
            name="option_description"
            class="input min-w-[247px]"
            @change="setTranslations('description', $event.target.value)"
          />
        </div>
        <transition name="fade">
          <div v-if="translate" class="flex flex-wrap w-full pl-8">
            <template v-for="(lang, i) in languages">
              <div
                v-if="lang.iso !== language"
                :key="'description_' + lang.iso"
                class="p-4"
              >
                <label
                  :for="`option_description_${lang.iso}`"
                  class="flex text-xs font-bold tracking-wide uppercase"
                >
                  {{ $t("Description") }}
                  <span class="italic font-normal text-gray-500 normal-case">
                    {{ $t("optional") }}
                  </span>
                  <span class="ml-auto text-theme-500">
                    <font-awesome-icon :icon="['fal', 'flag']" />
                    {{ lang.name }}
                  </span>
                </label>
                <textarea
                  v-if="lang.iso !== language"
                  v-model="translations[i].description"
                  type="text"
                  :name="`option_description_${lang.iso}`"
                  class="input min-w-[247px]"
                />
              </div>
            </template>
          </div>
        </transition>
      </div>

      <section class="max-w-sm mx-auto">
        <UploadImage
          v-if="media"
          disk="assets"
          :disk-selector="false"
          :image="{ path: media }"
          @image-changed="media.push($event)"
        />
        <ValueSwitch
          icon="list-check"
          name="multiSelect"
          :set-checked="multiSelect"
          class="w-full py-2 mt-4 border-b"
          @checked-value="multiSelect = $event.value"
        ></ValueSwitch>
        <ValueSwitch
          icon="layer-plus"
          name="incremental"
          :set-checked="incremental"
          class="w-full py-2 border-b"
          @checked-value="incremental = $event.value"
        ></ValueSwitch>
        <ValueSwitch
          icon="trailer"
          name="appendage"
          :set-checked="appendage"
          class="w-full py-2 border-b"
          info="When enabled, this box will always be treated as an appendage, meaning the options will be extra opn top of the product or variation just like accessoires"
          @checked-value="appendage = $event.value"
        ></ValueSwitch>
        <ValueSwitch
          icon="square-full"
          name="square meter calculation"
          :set-checked="sqm"
          class="w-full py-2"
          @checked-value="sqm = $event.value"
        ></ValueSwitch>
        <div
          v-if="multiSelect"
          class="flex items-center justify-between w-full mt-4"
        >
          <label
            for="select_limit"
            class="flex items-center justify-between text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("Select limit") }}
            <span class="ml-2 font-normal text-gray-500 normal-case">
              optional
            </span>
          </label>
          <input
            v-model="selectLimit"
            type="number"
            name="select_limit"
            class="w-1/2 input"
          />
        </div>
        <div class="flex items-center justify-between w-full mt-4">
          <label
            for="select_limit"
            class="flex items-center justify-between text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("Option limit") }}
            <span class="ml-2 font-normal text-gray-500 normal-case">
              optional
            </span>
          </label>
          <input
            v-model="optionLimit"
            type="number"
            name="option_limit"
            class="w-1/2 input"
          />
        </div>
      </section>
    </section>

    <div
      class="sticky bottom-0 flex w-full px-4 py-2 border-t shadow-md backdrop-blur-md bg-white/80 dark:bg-gray-700/80 top-8 dark:text-theme-200"
    >
      <button
        v-if="!edit"
        class="flex items-center p-2 mx-auto mt-4 text-white bg-green-500 rounded-full shadow-lg shadow-green-200"
        @click.prevent="addVarBox()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'apple-crate']" class="mr-2" />
        {{ $t("Add box") }}
      </button>
      <button
        v-if="edit"
        class="flex items-center p-2 mx-auto mt-4 text-white bg-green-500 rounded shadow-lg shadow-green-200"
        @click.prevent="updateVarBox()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'apple-crate']" class="mr-2" />
        {{ $t("update box") }}
      </button>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapGetters } from "vuex";
import _ from "lodash";

export default {
  props: {
    edit: {
      type: Boolean,
      required: false,
      default: null,
    },
    box: {
      type: Object,
      required: false,
    },
    boxes: {
      type: Array,
      required: true,
    },
  },
  emits: ["on-new-box", "on-update-box"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleSuccess, handleError };
  },
  data() {
    return {
      name: "",
      description: "",
      multiSelect: false,
      incremental: false,
      appendage: false,
      parentId: null,
      sqm: false,
      selectLimit: 0,
      optionLimit: 0,

      languages: [],
      translations: [],
      media: [],
      translate: false,
    };
  },
  computed: {
    ...mapState({
      // boxes: (state) => state.product.custom_categories,
    }),
    ...mapGetters({
      language: "settings/language",
    }),
    flatBoxes() {
      return this.flatten(this.boxes);
    },
  },
  watch: {
    languages(v) {
      return v;
    },
    translations: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  created() {
    this.api.get("languages").then((response) => {
      this.languages = response.data;
      this.languages.forEach((lang) => {
        this.translations.push({
          iso: lang.iso,
          name: "",
          description: "",
        });
      });
    });

    if (this.edit && this.box) {
      this.api
        .get(`custom/boxes/${this.box.id}/translations`)
        .then((response) => {
          this.translations.forEach((trans) => {
            response.data.forEach((resp) => {
              if (trans.iso === resp.iso) {
                trans.name = resp.name;
                trans.description = resp.description;
              }
            });
          });
        });
    }

    if (Object.keys(this.box).length > 0) {
      this.name = this.box.name;
      this.multiSelect = this.box.input_type === "single" ? false : true;
      this.incremental = this.box.incremental;
      this.appendage = this.box.appendage;
      this.selectLimit = this.box.select_limit;
      this.optionLimit = this.box.option_limit;
      this.parentId = this.box.parent_id;
      this.sqm = this.box.sqm;
      this.media = this.box.media;
    }
  },
  mounted() {
    setTimeout(() => {
      if (this.$refs.varGroupName) {
        this.$refs.varGroupName.focus();
      }
    }, 100);
  },
  beforeUnmount() {
    this.set_active_box({});
    this.set_active_option({});
    this.set_add_variation(false);
    this.set_type("");
    this.set_edit(false);
  },
  methods: {
    ...mapMutations({
      set_active_box: "assortmentsettings/set_active_box",
      set_active_option: "assortmentsettings/set_active_option",
      set_add_variation: "assortmentsettings/set_add_variation",
      set_type: "assortmentsettings/set_type",
      set_edit: "assortmentsettings/set_edit",
    }),
    flatten(array) {
      const self = this;
      let result = [];
      array.forEach(function (a) {
        result.push(a);
        if (Array.isArray(a.children)) {
          result = result.concat(self.flatten(a.children));
        }
      });
      return result;
    },
    setTranslations(type, value) {
      this.translations.forEach((trans) => {
        if (trans[type].length == 0) {
          trans[type] = value;
        }
      });
    },
    async addVarBox() {
      this.api
        .post("custom/boxes", {
          name: this.name,
          input_type: this.multiSelect ? "multiple" : "single",
          incremental: this.incremental,
          appendage: this.appendage,
          select_limit: this.selectLimit,
          option_limit: this.optionLimit,
          parent_id: this.parentId,
          sqm: this.sqm,
          translations: this.translations,
          media: this.media.length > 0 ? [this.media[0].value] : [],
        })
        .then((response) => {
          this.$emit("on-new-box", response.data);
          this.handleSuccess(response);
          this.set_add_variation(false);
        })
        .catch((error) => this.handleError(error));
    },
    async updateVarBox() {
      this.api
        .put(`custom/boxes/${this.box.id}`, {
          name: this.name,
          input_type: this.multiSelect ? "multiple" : "single",
          incremental: this.incremental,
          appendage: this.appendage,
          select_limit: this.selectLimit,
          option_limit: this.optionLimit,
          parent_id: this.parentId,
          sqm: this.sqm,
          translations: this.translations,
          media: this.media,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.$emit("on-update-box", response.data);
          this.set_add_variation(false);
        });
    },
  },
};
</script>
