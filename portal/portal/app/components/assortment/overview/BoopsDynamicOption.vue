<template>
  <div class="w-full">
    <!-- FORMAT -->
    <fieldset
      v-if="
        item.dynamic_type === 'format' &&
        activeItems.length > 0 &&
        activeItems.findIndex((x) => x.slug === item.slug) > -1
      "
      class="flex w-full flex-col items-center"
    >
      <legend class="sr-only">{{ $t("item dimensions") }}</legend>
      <label :for="`height-${item.slug}`" class="sr-only">
        {{ $t("height") }}
      </label>
      <UIInputText
        :prefix="`${$t('height')} ${item.unit}`"
        v-model="selected._.height"
        :min="item.minimum_height"
        :max="item.maximum_height"
        :step="item.incremental_by"
        :placeholder="item.minimum_height"
        type="number"
        name="optionHeight"
        class="text-nowrap p-0 text-xs"
        @click.stop=""
        @input="$emit('onFormatHeightUpdated', $event.target.value)"
      />

      <div class="mx-2" aria-hidden="true" role="presentation">x</div>

      <label :for="`width-${item.slug}`" class="sr-only"> {{ $t("width") }}</label>
      <UIInputText
        :prefix="`${$t('width')} ${item.unit}`"
        v-model="selected._.width"
        :min="item.minimum_width"
        :max="item.maximum_width"
        :step="item.incremental_by"
        :placeholder="item.width"
        type="number"
        name="optionWidth"
        class="text-nowrap p-0 text-xs"
        @click.stop=""
        @input="$emit('onFormatWidthUpdated', $event.target.value)"
      />
    </fieldset>

    <!-- PAGES with input -->
    <fieldset
      v-if="
        item.dynamic_type === 'pages' &&
        !item.generate &&
        activeItems.length > 0 &&
        activeItems.findIndex((x) => x.slug === item.slug) > -1
      "
      class="flex w-full items-center"
    >
      <legend class="sr-only">{{ $t("item pages") }}</legend>
      <label :for="`optionPages-${item.slug}`" class="sr-only">
        {{ $t("pages") }}
      </label>
      <UIInputText
        v-model="selected._.pages"
        :prefix="`${$t('pages')}`"
        :min="item.start_on"
        :max="item.end_on"
        :step="item.incremental_by"
        :placeholder="item.start_on"
        type="number"
        :name="`optionPages-${item.slug}`"
        class="w-full text-nowrap p-0 text-xs"
        @click.stop=""
        @input="
          roundToStep($event.target.value, item.start_on, item.end_on, item.incremental_by, 'pages')
        "
      />
    </fieldset>

    <!-- Genrated PAGES -->
    <section
      v-if="
        item.dynamic_type === 'pages' &&
        item.generate &&
        activeItems.length > 0 &&
        activeItems.findIndex((x) => x.slug === item.slug) > -1
      "
      class="w-full"
    >
      <ol class="max-h-[60dvh] w-full overflow-y-auto">
        <template v-for="(option, i) in Number(item.end_on)" :key="`dynamic_pages_option_${i}`">
          <li
            v-if="option % item.incremental_by === 0 && option >= item.start_on"
            class="my-1 block w-full rounded bg-white px-2 py-1 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800"
            :class="{
              '!bg-theme-200 !text-theme-600 dark:!bg-theme-700 dark:!text-theme-300':
                option === pages,
            }"
            @click.stop.prevent="((pages = parseInt(option)), $emit('onPagesUpdated', option))"
          >
            {{ option }}
          </li>
        </template>
      </ol>
    </section>

    <!-- sides -->
    <fieldset
      v-if="
        item.dynamic_type === 'sides' &&
        !item.generate &&
        activeItems.length > 0 &&
        activeItems.findIndex((x) => x.slug === item.slug) > -1
      "
      class="flex w-full items-center"
    >
      <legend class="sr-only">{{ $t("item pages") }}</legend>
      <label :for="`optionPages-${item.slug}`" class="sr-only">
        {{ $t("sides") }}
      </label>
      <UIInputText
        v-model="selected._.sides"
        :prefix="`${$t('sides')}`"
        :min="item.start_on"
        :max="item.end_on"
        :step="item.incremental_by"
        :placeholder="item.start_on"
        type="number"
        :name="`optionPages-${item.slug}`"
        class="w-full text-nowrap p-0 text-xs"
        @click.stop=""
        @input="
          roundToStep($event.target.value, item.start_on, item.end_on, item.incremental_by, 'sides')
        "
      />
    </fieldset>

    <!-- Genrated SIDES -->
    <section
      v-if="
        item.dynamic_type === 'sides' &&
        item.generate &&
        activeItems.length > 0 &&
        activeItems.findIndex((x) => x.slug === item.slug) > -1
      "
      class="w-full"
    >
      <ol class="max-h-[60dvh] w-full overflow-y-auto">
        <template v-for="(option, i) in Number(item.end_on)" :key="`dynamic_pages_option_${i}`">
          <li
            v-if="option % item.incremental_by === 0 && option >= item.start_on"
            class="my-1 block w-full rounded bg-white px-2 py-1 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800"
            :class="{ '!bg-theme-200 !text-theme-600 dark:!bg-theme-800': option === sides }"
            @click.stop.prevent="((sides = parseInt(option)), $emit('onSidesUpdated', option))"
          >
            {{ option }}
          </li>
        </template>
      </ol>
    </section>
  </div>
</template>

<script>
import _ from "lodash";
export default {
  props: {
    item: {
      type: Object,
      required: true,
      default: () => {},
    },
    selected: {
      type: Object,
      required: true,
      default: () => {
        return {
          _: {
            pages: 0,
            sides: 0,
            height: 0,
            width: 0,
          },
        };
      },
    },
    activeItems: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  emits: ["onPagesUpdated", "onFormatWidthUpdated", "onFormatHeightUpdated", "onSidesUpdated"],
  data() {
    return {
      internalItem: this.item,
      pages: 0,
      sides: 0,
    };
  },
  created() {
    this.roundToStep = this.roundToStep.bind(this);
  },
  methods: {
    roundToStep: _.debounce(function (value, min, max, step, type) {
      if (!value || isNaN(value)) {
        value = min;
      }

      const roundedValue =
        step === 0
          ? Math.min(Math.max(min, value), max)
          : Math.min(Math.max(min, Math.round(value / step) * step), max);

      if (type === "pages") {
        this.$emit("onPagesUpdated", roundedValue);
        this.pages = roundedValue;
      }

      if (type === "sides") {
        this.$emit("onSidesUpdated", roundedValue);
        this.sides = roundedValue;
      }
    }, 500),
  },
};
</script>
