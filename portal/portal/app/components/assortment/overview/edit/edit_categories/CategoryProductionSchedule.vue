<template>
  <div>
    <div class="flex justify-between px-4">
      <div class="text-sm font-bold tracking-wide uppercase">
        {{ $t("produce on") }}
      </div>
      <div class="text-sm font-bold tracking-wide uppercase">
        {{ $t("if ordered before") }}
      </div>
    </div>

    <div
      v-for="(day, i) in productionDays"
      :key="$display_name(day.day)"
      class="flex items-center w-full px-4 py-2 transition justify-evenly hover:bg-gray-100 dark:hover:bg-gray-800"
    >
      <div class="flex items-center flex-1 justify around">
        <font-awesome-icon :icon="['fal', 'calendar-day']" class="mr-2" />
        <span class="flex-1">{{ day.day }}</span>
        <UISwitch
          :key="`pdys_${i}`"
          :value="day.active"
          :name="`pdys_${i}`"
          @input="changeActive($event, i)"
        ></UISwitch>
      </div>

      <div class="flex items-center justify-end flex-1">
        <font-awesome-icon :icon="['fal', 'clock']" class="mx-2" />
        <input
          v-model="day.deliver_before"
          type="time"
          :disabled="!day.active"
          class="w-auto p-0 px-2 input"
        />

        <!-- WARNING: did not read the original values and with the new html5 time input became redundant -->
        <!-- <VueTheMask
          v-if="!supportsTouch"
          v-model="day.deliver_before"
          type="time"
          :masked="false"
          :disabled="!day.active"
          class="w-auto p-0 px-2 input"
        ></VueTheMask> -->
        <vue-time-picker
          v-if="supportsTouch"
          v-model="day.deliver_before"
          input-class="w-auto p-0 px-2 input"
          :active-color="theme_colors['--theme-500']"
          :close-on-overlay="true"
          :close-on-esc="true"
          :done-text="$t('floppy-disk')"
          :cancel-text="$t('cancel')"
          :required="true"
        ></vue-time-picker>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import VueTimePicker from "vue3-timepicker";
import "vue3-timepicker/dist/VueTimepicker.css";

export default {
  components: {
    VueTimePicker,
  },
  props: {
    productionDays: {
      required: true,
      type: Array,
    },
  },
  emits: ["onUpdateProductionDays"],
  data() {
    return {
      value: "",
    };
  },
  computed: {
    ...mapState({
      themecontrast_colors: (state) => state.theme.text_colors,
      theme_colors: (state) => state.theme.theme_colors,
    }),
    supportsTouch() {
      if (
        "ontouchstart" in window ||
        navigator.MaxTouchPoints > 0 ||
        navigator.msMaxTouchPoints > 0
      ) {
        return true;
      } else {
        return false;
      }
    },
  },
  watch: {
    productionDays: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  methods: {
    changeActive(e, i) {
      this.$emit("onUpdateProductionDays", { e, i });
      // this.productionDays[i].active = e.value;
    },
  },
};
</script>

<style></style>
