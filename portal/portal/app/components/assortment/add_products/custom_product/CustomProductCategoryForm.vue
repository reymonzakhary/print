<template>
  <div class="mt-2 bg-white rounded shadow-md dark:bg-gray-700 shadow-gray-200">
    <!-- {{ translations }} -->
    <div class="flex items-stretch justify-between w-full bg-gray-50">
      <div class="p-4 bg-white border-r">
        <label
          for="category_name"
          class="flex items-center justify-between text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("Name") }}
          <button
            class="px-2 text-base rounded-full text-theme-500 hover:bg-theme-100"
            @click="translate = !translate"
          >
            <font-awesome-icon :icon="['fal', !translate ? 'language' : 'circle-xmark']" />
          </button>
        </label>
        <input
          v-model="name"
          type="text"
          name="category_name"
          class="input"
          @change="setTranslations('name', $event.target.value)"
        />
      </div>
      <transition name="fade">
        <div v-show="translate" class="flex flex-wrap w-3/4 pl-8">
          <template v-for="(lang, i) in languages">
            <div v-if="lang.iso !== language" :key="lang.iso" class="p-4">
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

    <div class="flex items-stretch justify-between border-y bg-gray-50">
      <div class="p-4 bg-white border-r">
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
          name="category_description"
          class="input min-w-[247px]"
          @change="setTranslations('description', $event.target.value)"
        />
      </div>
      <transition name="fade">
        <div v-if="translate" class="flex flex-wrap w-3/4 pl-8">
          <template v-for="(lang, i) in languages">
            <div v-if="lang.iso !== language" :key="'description_' + lang.iso" class="p-4">
              <label
                :for="`category_description_${lang.iso}`"
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
                :name="`category_description_${lang.iso}`"
                class="input min-w-[247px]"
              />
            </div>
          </template>
        </div>
      </transition>
    </div>

    <div class="p-4">
      <div v-if="categories && categories.length > 1" class="mt-4">
        <label for="parent" class="text-xs font-bold tracking-wide uppercase">
          {{ $t("Parent") }}
          <span class="italic font-normal text-gray-500 normal-case">
            {{ $t("optional") }}
          </span>
        </label>

        <select
          v-model="parentId"
          v-tooltip="$t('coming soon')"
          name="categories"
          disabled
          class="max-w-lg text-sm input text-theme-900 dark:text-white"
        >
          <option :value="null" class="italic text-gray-500">
            {{ $t("select parent") }}
          </option>
          <template v-for="ca in flatCats">
            <option
              v-if="!category || (category && ca.id !== category.id)"
              :key="'ca_' + ca.id"
              :value="ca.id"
            >
              {{ ca.name }}
            </option>
          </template>
        </select>
      </div>

      <!-- <UploadImage
        disk="assets"
        :image="{ path: media }"
        :disk-selector="false"
        @image-changed="media.push($event.value)"
      /> -->

      <UIImageSelector
        :selected-image="media[0]"
        disk="assets"
        @on-image-select="media = [$event]"
        @on-image-remove="media = []"
      />

      <ValueSwitch
        name="published"
        :set-checked="published"
        class="mt-4"
        @checked-value="published = $event.value"
      ></ValueSwitch>

      <button
        v-if="!edit"
        class="flex items-center p-2 mt-4 ml-auto text-white bg-green-500 rounded shadow-lg shadow-green-200"
        @click.prevent="createCustomCategory()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'shelves']" class="mr-2" />
        {{ $t("Save category") }}
      </button>
      <button
        v-if="edit"
        class="flex items-center p-2 mt-4 ml-auto text-white bg-green-500 rounded shadow-lg shadow-green-200"
        @click.prevent="updateCustomCategory()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        <font-awesome-icon :icon="['fal', 'shelves']" class="mr-2" />
        {{ $t("update category") }}
      </button>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapActions, mapState } from "vuex";

export default {
  props: {
    edit: {
      type: Boolean,
      required: false,
      default: null,
    },
    category: {
      type: Object,
      required: false,
      default: null,
    },
  },
  emits: ["on-custom-category-update"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      name: "",
      description: "",
      parentId: null,
      published: true,
      languages: [],
      language: undefined,
      translations: [],
      media: [],
      translate: false,
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.custom_categories,
    }),
    flatCats() {
      return this.flatten(this.categories);
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
    category: {
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

    if (this.edit && this.category) {
      this.api.get(`custom/categories/${this.category.id}/translations`).then((response) => {
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

    if (this.category) {
      this.name = this.category.name;
      this.description = this.category.description;
      this.parentId = this.category.parent_id;
      this.published = this.category.published;
      this.media = this.category.media ?? [];
    }
  },
  methods: {
    ...mapActions({
      get_custom_categories: "product/get_custom_categories",
    }),
    ...mapMutations({
      set_active_custom_category: "product/set_active_custom_category",
      add_custom_category: "product/add_custom_category",
      set_component: "product_wizard/set_wizard_component",
      toggle_edit_cat: "product/toggle_edit_cat",
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
    async createCustomCategory() {
      await this.api
        .post(`/custom/categories`, {
          name: this.name,
          description: this.description,
          parent_id: this.parentId,
          // brand: this.brandId,
          published: this.published,
          translation: this.translations,
          media: this.media,
        })
        .then((res) => {
          this.add_custom_category(res.data);
          this.set_active_custom_category(res.data);
          this.set_component("CustomProductForm");
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    async updateCustomCategory() {
      await this.api
        .put(`/custom/categories/${this.category.id}`, {
          name: this.name,
          description: this.description,
          parent_id: this.parentId,
          // brand: this.brandId,
          published: this.published,
          translation: this.translations,
          media: this.media,
        })
        .then((res) => {
          this.handleSuccess(res);
          this.set_active_custom_category(res.data);
          this.toggle_edit_cat(false);
          this.$emit("on-custom-category-update", res.data);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>
