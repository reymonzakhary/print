<template>
  <div
    class="flex flex-wrap items-center justify-between h-full px-2 py-1 overflow-visible transition-all border-t first:border-t-0 group dark:hover:bg-black dark:border-black"
    :class="[
      `ml-${i * 3} hover:bg-gray-${i + 2}00`,
      isChecked(option.id) && validationSettings
        ? `rounded bg-gray-${i + 2}00 my-1`
        : '',
    ]"
  >
    <div
      class="flex items-center justify-between w-full cursor-pointer"
      @click="toggleCheck(option)"
    >
      <span>
        <input
          :id="option.name"
          type="checkbox"
          :name="option.name"
          :checked="isChecked(option.id)"
        />
        {{ option.name }}
      </span>
      <span class="flex items-center">
        <small
          v-tooltip="$t('system key')"
          class="ml-2 font-mono text-gray-500"
        >
          {{ option.display_price }}
        </small>
        <button
          class="text-theme-500"
          @click.stop="validationSettings = !validationSettings"
        >
          <font-awesome-icon
            :icon="['fal', 'gear']"
            class="p-2 ml-2 rounded-full hover:bg-theme-100"
          />
        </button>
      </span>
    </div>
    <span
      v-show="isChecked(option.id) && validationSettings"
      class="w-full h-full p-2 bg-white rounded"
    >
      <div class="mt-4 font-bold">{{ $t("validations") }}</div>
      <div class="flex items-center h-full border border-gray-300 rounded">
        <input
          v-model="optionFile.name"
          type="text"
          :placeholder="$t('name')"
          class="w-1/3 p-1 mx-2 input"
          :class="{ 'border-red-500': checkName }"
        />
        <ValueSwitch
          class="w-1/3 ml-2"
          :name="$t('file required')"
          icon="memo-circle-check"
          :set-checked="optionFile.required"
          @checked-value="optionFile.required = $event.value"
        />
        <v-select
          v-model="optionFile.mime"
          class="z-50 w-1/3 p-0 text-sm input text-theme-900 dark:text-white"
          :class="{ 'border-red-500': checkMime }"
          :options="mimeTypes"
          placeholder="type"
        >
        </v-select>
        <button
          class="h-full p-2 ml-2 rounded-r bg-theme-500 text-themecontrast-500"
          @click="addValidation(optionFile)"
        >
          add
        </button>
      </div>
      <transition-group name="slide">
        <div
          v-for="(validation, i) in validations"
          :key="`validation_${i}`"
          class="divide-y"
        >
          <div
            v-for="(value, key) in validation"
            :key="key"
            class="flex justify-between"
          >
            <b>{{ key }}:</b>
            <span class="ml-2 italic text-gray-500">{{ value }} </span>
          </div>
        </div>
      </transition-group>

      <div class="mt-4 font-bold">{{ $t("templates") }}</div>
      <div
        v-if="templates"
        class="flex items-center w-full h-full p-2 bg-gray-100 border rounded"
      >
        <transition-group name="slide">
          <div
            v-for="template in templates"
            :key="template.id"
            class="flex w-full rounded"
          >
            <div
              :class="{
                'bg-theme-500':
                  isChecked(option.id) &&
                  isChecked(option.id).properties &&
                  isChecked(option.id).properties.template &&
                  isChecked(option.id).properties.template.id === template.id,
              }"
              class="flex items-center justify-between w-full text-sm transition-colors duration-75 bg-white rounded shadow-md cursor-pointer shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
              @click="
                add_variation_option_property_template({
                  box_id: box_id,
                  option: option,
                  template: {
                    mode: 'DesignProviderTemplate',
                    id: template.id,
                  },
                })
              "
            >
              <div class="flex-none w-8 h-8 m-2 overflow-hidden rounded-full">
                <img
                  v-if="template.image"
                  class="object-cover w-full h-full"
                  :src="template.image"
                />
              </div>

              <div
                class="flex-auto w-full px-2 overflow-hidden capitalize truncate"
                :title="template.name"
              >
                {{ template.name }}
              </div>

              <div class="flex-auto hidden w-full px-2 text-xs lg:flex">
                {{
                  moment(template.created_at).format("ddd DD MMM YYYY HH:mm")
                }}
              </div>
            </div>
          </div>
        </transition-group>
      </div>
    </span>

    <CustomProductFormOptions
      v-for="theOption in option.children"
      :key="'option_' + theOption.slug"
      :option="theOption"
      :box_id="box_id"
      :i="i + 1"
      class="w-full"
    />
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import mixin from "~/components/assortment/add_products/custom_product/mixin";
import moment from "moment";

export default {
  mixins: [mixin],
  props: {
    option: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
    box_id: {
      type: Number,
      required: true,
    },
    edit_mode: {
      type: Boolean,
      default: false,
    },
  },
  async fetch() {
    this.get_templates();
  },
  computed: {
    ...mapState({
      variations: (state) => state.product.custom_product_variations,
      templates: (state) => state.design.templates,
    }),
    validations() {
      const options = this.variations.find(
        (box) => box.id === this.box_id,
      ).options;

      if (options.length > 0) {
        const option = options.find((opt) => opt.id === this.option.id);
        if (option?.properties?.validations) {
          return option.properties.validations;
        }
      }
    },
  },
  watch: {
    variations: {
      deep: true,
      handler(v) {
        this.$forceUpdate();
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
  },
  data() {
    return {
      optionFile: [
        {
          name: "",
          required: false,
          mime: "",
        },
      ],
      checkName: false,
      checkMime: false,
      moment: moment,
      validationSettings: false,
    };
    // this.toggleCheck(this.option);
  },
  methods: {
    ...mapMutations({
      add_variation_option: "product/add_variation_option",
      remove_variation_option: "product/remove_variation_option",
      add_variation_option_property_validation:
        "product/add_variation_option_property_validation",
      add_variation_option_property_template:
        "product/add_variation_option_property_template",
    }),
    ...mapActions({
      get_templates: "design/get_templates",
    }),
    isChecked(id) {
      if (this.variations?.length > 0) {
        let i;
        // if (this.edit_mode) {
        // 	i = this.variations.findIndex(
        // 		(box) => box.row_id === this.box_id
        // 	);
        // } else {
        i = this.variations.findIndex((box) => box.id === this.box_id);
        // }
        return this.variations[i]?.options.find((op) => op.id === id);
      }
    },
    toggleCheck(option) {
      if (!this.isChecked(option.id)) {
        this.add_variation_option({ box_id: this.box_id, option: option });
      } else {
        this.remove_variation_option({
          box_id: this.box_id,
          option: option,
        });
      }
    },
    addValidation(file) {
      if (file.name && file.mime) {
        let str = "";

        if (file.required) {
          str += "required|";
        }

        str += "file|";
        str += `mimes:${file.mime}`;

        this.add_variation_option_property_validation({
          box_id: this.box_id,
          option: this.option,
          validation: {
            [file.name]: str,
          },
        });

        this.optionFile = [
          {
            name: "",
            required: false,
            mime: "",
          },
        ];

        this.checkName = false;
        this.checkMime = false;

        setTimeout(() => {
          this.$forceUpdate();
        }, 500);
      } else if (!file.name) {
        this.checkName = true;
      } else if (!file.mime) {
        this.checkMime = true;
      }
    },
    filtered_templates() {
      if (this.filter.length > 0) {
        return this.templates.filter((template) => {
          return Object.values(template).some((val) => {
            if (val !== null) {
              return val
                .toString()
                .toLowerCase()
                .includes(this.filter.toLowerCase());
            }
          });
        });
      }
      return this.templates;
    },
  },
};
</script>

<style></style>
