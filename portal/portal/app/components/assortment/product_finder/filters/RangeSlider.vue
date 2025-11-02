<template>
  <div>
    <section class="w-full pt-2 pb-6 pl-3 mb-2">
      <form class="flex mb-10">
        <div class="w-2/3">
          <label class="text-xs font-bold tracking-wide uppercase">
            <font-awesome-icon :icon="['fal', 'layer-group']" class="mr-1" />
            {{ $t("quantity") }}
            <!-- <b class="ml-2 text-base">{{newValue[0]}} - {{newValue[2]}}</b> -->
          </label>
          <span class="relative flex">
            <input
              v-model="newValue[1]"
              type="number"
              class="w-full p-1 my-2 mr-2 text-sm bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              :min="min + range / 2"
              :max="max - range / 2"
              @change="updateRangeManually($event.target.value)"
            />
            <font-awesome-icon
              :icon="['fal', 'layer-group']"
              class="absolute top-0 right-0 mt-4 mr-8 text-gray-600"
            />
          </span>
        </div>

        <div class="w-1/3">
          <label class="text-xs font-bold tracking-wide uppercase">
            <!-- <font-awesome-icon :icon="['fal', 'bow-arrow']" /> -->
            {{ $t("range") }}
          </label>
          <span class="relative flex">
            <input
              v-model.number="range"
              type="number"
              class="w-full p-1 my-2 text-sm bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              @change="updateRange()"
            />
            <font-awesome-icon
              :icon="['fal', 'bow-arrow']"
              class="absolute top-0 right-0 mt-4 mr-6 text-gray-600"
            />
          </span>
        </div>
      </form>

      <!-- the slider -->
      <vue-slider
        ref="slider"
        :model-value="value"
        :min="min"
        :max="max"
        :min-range="range / 2"
        :max-range="range / 2"
        :dot-options="dotOptions"
        :marks="marks"
        :lazy="true"
        @change="updateRange()"
      ></vue-slider>
    </section>
  </div>
</template>

<script>
// @ts-nocheck
import { mapMutations } from "vuex";
import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/antd.css";

export default {
  components: {
    VueSlider,
  },
  data() {
    return {
      newValue: [],
      range: 1000,
      dotOptions: [
        {
          tooltip: "always",
        },
        {
          tooltip: "hover",
        },
        {
          tooltip: "always",
        },
      ],
    };
  },
  computed: {
    min() {
      return 0;
    },
    max() {
      return 3500;
    },
    value() {
      return [
        (this.max - this.min) / 2 - this.range / 2,
        (this.max - this.min) / 2,
        (this.max - this.min) / 2 + this.range / 2,
      ];
    },
    marks() {
      return [this.min, this.max];
    },
  },
  mounted() {
    this.updateRange();
  },
  methods: {
    // vuex mappings
    ...mapMutations({
      set_compare_qty: "compare/set_compare_qty",
    }),
    updateRangeManually(v) {
      this.$refs.slider.setValue([
        Number(v) - this.range / 2,
        Number(v),
        Number(v) + this.range / 2,
      ]);
      this.updateRange();
    },
    updateRange() {
      this.newValue = this.$refs.slider.getValue();
      // store in vuex - result will watch changes
      this.set_compare_qty(this.newValue);
    },
  },
};
</script>
