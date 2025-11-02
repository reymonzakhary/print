<template>
  <div>
    <section class="pb-20">
      <div class="flex w-full items-stretch justify-between" :class="translate ? 'bg-gray-50' : ''">
        <div class="mx-auto w-full max-w-md bg-white p-4" :class="translate ? 'border-r' : ''">
          <label
            for="category_name"
            class="flex items-center justify-between text-xs font-bold uppercase tracking-wide"
          >
            {{ $t("Name") }}
            <button
              class="rounded-full px-2 text-base text-theme-500 hover:bg-theme-100"
              @click="translate = !translate"
            >
              <font-awesome-icon :icon="['fal', !translate ? 'language' : 'circle-xmark']" />
            </button>
          </label>
          <input
            v-model="name"
            type="text"
            name="option_name"
            class="input border-theme-500"
            @change="setTranslations('name', $event.target.value)"
          />
        </div>

        <transition name="fade">
          <div v-show="translate" class="flex flex-wrap pl-8">
            <template v-for="(lang, i) in languages">
              <div v-if="lang.iso !== language" :key="lang.iso" class="w-full max-w-sm p-4">
                <label
                  :for="`option_name_${lang.iso}`"
                  class="flex text-xs font-bold uppercase tracking-wide"
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
                  :name="`option_name_${lang.iso}`"
                  class="input"
                />
              </div>
            </template>
          </div>
        </transition>
      </div>

      <div
        class="flex w-full items-stretch justify-between border-y"
        :class="translate ? 'bg-gray-50' : ''"
      >
        <div class="mx-auto w-full max-w-md bg-white p-4" :class="translate ? 'border-r' : ''">
          <label
            for="category_description"
            class="flex items-center text-xs font-bold uppercase tracking-wide"
          >
            {{ $t("Description") }}
            <span class="font-normal normal-case italic text-gray-500">
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
          <div v-if="translate" class="flex w-full flex-wrap pl-8">
            <template v-for="(lang, i) in languages">
              <div v-if="lang.iso !== language" :key="'description_' + lang.iso" class="p-4">
                <label
                  :for="`option_description_${lang.iso}`"
                  class="flex text-xs font-bold uppercase tracking-wide"
                >
                  {{ $t("Description") }}
                  <span class="font-normal normal-case italic text-gray-500">
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

      <section class="mx-auto flex max-w-sm flex-col">
        <div v-if="boxes && boxes.length > 0" class="mt-4 w-full">
          <label for="name" class="text-xs font-bold uppercase tracking-wide">
            {{ $t("Related box") }}
          </label>

          <select
            v-model="selectedBox"
            name="boxes"
            class="input max-w-lg border-theme-500 text-sm text-theme-900 dark:text-white"
          >
            <option>{{ $t("select related box") }}</option>
            <option v-for="box in flatBoxes" :key="'option_' + box.id" :value="box">
              {{ box.name }}
            </option>
          </select>
        </div>

        <UploadImage
          v-if="media"
          disk="assets"
          :disk-selector="false"
          :image="{ path: media }"
          class="mx-auto"
          @image-changed="media.push($event)"
        />

        <span class="relative my-4 block pr-2">
          <label for="input_type" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'symbols']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("input type") }}
          </label>
          <select id="input_type" v-model="inputType" name="input_type" class="input w-full p-2">
            <option value="radio">Radio</option>
            <option value="checkbox">Checkbox</option>
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="color">Color</option>
            <option value="date">Date</option>
            <option value="week">Week</option>
            <option value="datetime">Date and time</option>
            <option value="time">Time</option>
            <option value="email">Email</option>
            <option value="file">File</option>
            <option value="image">Image</option>
            <option value="password">Password</option>
            <option value="range">Range</option>
            <option value="tel">Tel.</option>
            <option value="url">Url</option>
            <option value="path">Path</option>
          </select>

          <span class="relative my-4 block">
            <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon :icon="['fal', 'angles-right']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("incremented by") }}
            </label>
            <input
              v-model="incrementalBy"
              type="number"
              name="incremented_by"
              class="input w-full p-2"
            />
          </span>
        </span>

        <span class="my-4 block">
          <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon
              :icon="['fal', 'hourglass-start']"
              class="fa-fw mr-2 text-gray-500"
            />
            {{ $t("price") }}
          </label>
          <span class="relative">
            <UICurrencyInput
              v-model="price"
              input-class="w-full text-sm border-green-500 ring-green-200 focus:border-green-500"
            />
          </span>
        </span>

        <span class="relative my-4 block">
          <label for="width" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon
              :icon="['fal', 'arrows-left-right']"
              class="fa-fw mr-2 text-gray-500"
            />
            {{ $t("width") }}
          </label>
          <input v-model="width" type="number" name="width" class="input w-full p-2" />
        </span>

        <span class="relative my-4 block">
          <label for="height" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'arrows-up-down']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("height") }}
          </label>
          <input v-model="height" type="number" name="height" class="input w-full p-2" />
        </span>

        <span class="relative my-4 block">
          <label for="min" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon
              :icon="['fal', 'arrow-down-to-line']"
              class="fa-fw mr-2 text-gray-500"
            />
            {{ $t("minimum") }}
          </label>
          <input v-model="min" type="number" name="min" class="input w-full p-2" />
        </span>

        <span class="relative my-4 block">
          <label for="max" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon
              :icon="['fal', 'arrow-up-to-line']"
              class="fa-fw mr-2 text-gray-500"
            />
            {{ $t("maximum") }}
          </label>
          <input v-model="max" type="number" name="max" class="input w-full p-2" />
        </span>

        <span v-if="units.length > 0" class="relative my-4 block">
          <label for="unit" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon
              :icon="['fal', 'arrow-up-to-line']"
              class="fa-fw mr-2 text-gray-500"
            />
            {{ $t("unit") }}
          </label>
          <select v-model="unit" class="input">
            <option v-for="un in units" :key="un.short_name" :value="un.short_name">
              {{ un.name }}
            </option>
          </select>
        </span>
      </section>
    </section>

    <div
      class="sticky bottom-0 top-8 flex w-full border-t bg-white/80 px-4 py-2 shadow-md backdrop-blur-md dark:bg-gray-700/80 dark:text-theme-200"
    >
      <button
        v-if="!edit"
        class="mx-auto mt-4 flex items-center rounded-full bg-green-500 p-2 text-white shadow-lg shadow-green-200"
        @click.prevent="addVarOption()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'apple-whole']" class="mr-2" />
        {{ $t("Add option") }}
      </button>
      <button
        v-if="edit"
        class="mx-auto mt-4 flex items-center rounded bg-green-500 p-2 text-white shadow-lg shadow-green-200"
        @click.prevent="updateVarOption()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'apple-whole']" class="mr-2" />
        {{ $t("update option") }}
      </button>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapGetters } from "vuex";

export default {
  props: {
    edit: {
      type: Boolean,
      required: false,
      default: null,
    },
    option: {
      type: Object,
      required: false,
    },
    boxes: {
      type: Array,
      required: true,
    },
  },
  setup() {
    const api = useAPI();
    const eventStore = useEventStore();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleSuccess, handleError, eventStore };
  },
  data() {
    return {
      name: "",
      description: "",
      selectedBox: null,
      inputType: "",
      parentId: null,
      incrementalBy: 1,
      min: 0,
      max: 0,
      width: 0,
      height: 0,
      length: 0,
      unit: "",
      units: [],
      price: 0,
      secure: false,
      languages: [],
      translations: [],
      media: [],
      translate: false,
    };
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

    this.api.get("units").then((response) => {
      this.units = response.data;
    });

    if (this.edit && this.option) {
      this.api.get(`custom/options/${this.option.id}/translations`).then((response) => {
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

    if (Object.keys(this.option).length > 0) {
      this.name = this.option.name;
      this.description = this.option.description;
      this.selectedBox = this.boxes.find((box) => box.id === this.option.box_id);
      this.inputType = this.option.input_type;
      this.incrementalBy = this.option.incremental_by;
      this.parentId = this.option.parent_id;
      this.min = this.option.min;
      this.max = this.option.max;
      this.width = this.option.width;
      this.height = this.option.heigth;
      this.length = this.option.length;
      this.unit = this.option.unit;
      this.price = this.option.price;
      this.secure = this.option.secure;
      this.media = this.option.media;
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
    async addVarOption() {
      this.api
        .post("custom/options", {
          name: this.name,
          description: this.description,
          box_id: this.selectedBox.id,
          input_type: this.inputType,
          incremental_by: this.incrementalBy,
          parent_id: this.parentId,
          min: this.min,
          max: this.max,
          width: this.width,
          height: this.heigth,
          length: this.length,
          unit: this.unit,
          price: this.price,
          secure: this.secure,
          media: this.media,
          translations: this.translations,
          media: this.media,
        })
        .then((response) => {
          this.eventStore.emit("update-boxes");
          this.handleSuccess(response);
          this.set_add_variation(false);
        })
        .catch((error) => this.handleError(error));
    },
    async updateVarOption() {
      this.api
        .put(`custom/options/${this.option.id}`, {
          name: this.name,
          description: this.description,
          box_id: this.selectedBox.id,
          input_type: this.inputType,
          incremental_by: this.incrementalBy,
          parent_id: this.parentId,
          min: this.min,
          max: this.max,
          width: this.width,
          height: this.heigth,
          length: this.length,
          unit: this.unit,
          price: this.price,
          secure: this.secure,
          media: this.media,
          translations: this.translations,
          media: this.media,
        })
        .then((response) => {
          this.eventStore.emit("update-boxes");
          this.handleSuccess(response);
          this.set_add_variation(false);
        });
    },
  },
};
</script>
