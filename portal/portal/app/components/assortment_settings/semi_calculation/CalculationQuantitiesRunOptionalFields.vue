<template>
  <div>
    <div class="w-full">
      <div class="flex items-center text-sm font-bold uppercase tracking-wide">
        <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'print-magnifying-glass']" />
        {{ $t("printing methods") }}
      </div>

      <v-select
        v-model="newRun.pm"
        multiple
        :options="filtered_printing_methods"
        label="name"
        class="input mr-4 rounded bg-white p-1 py-0 text-sm text-theme-900"
      />
    </div>

    <div class="mt-4">
      <div class="flex w-full items-center text-sm font-bold uppercase tracking-wide">
        <font-awesome-icon class="text-gray-400" :icon="['fal', 'turtle']" />
        <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'rabbit-fast']" />
        {{ $t("delivery days") }}
      </div>
      <!-- {{ run }} -->
      <section v-for="(dlv, i) in newRun.dlv_production" :key="'delivery_' + i" class="flex">
        <!-- {{ dlv }} -->
        <v-select
          v-model="dlv.days"
          :options="filtered_delivery_days"
          label="days"
          :reduce="(day) => day.days"
          class="input rounded-l bg-white p-0 text-sm text-theme-900 dark:text-black"
          @change="updateRun(newRun.dlv_production)"
        />

        <button
          class="w-1/2 border border-theme-500 text-theme-500 hover:bg-theme-100"
          :class="{
            'cursor-default bg-theme-400 text-themecontrast-400 hover:bg-theme-400':
              dlv.mode == 'fixed',
          }"
          @click="((dlv.mode = 'fixed'), updateRun(newRun.dlv_production))"
        >
          {{ $t("fixed") }}
        </button>

        <button
          class="w-1/2 border border-l-0 border-theme-500 text-theme-500 hover:bg-theme-100"
          :class="{
            'cursor-default bg-theme-400 text-themecontrast-400 hover:bg-theme-400':
              dlv.mode == '%',
          }"
          @click="((dlv.mode = '%'), updateRun(newRun.dlv_production))"
        >
          {{ $t("percentage") }}
        </button>

        <div class="relative flex w-1/2 items-center">
          <input
            v-model="dlv.value"
            class="input p-1 pl-6"
            type="number"
            placeholder="100"
            step="0.01"
            min="0.00001"
            @change="updateRun(newRun.dlv_production)"
          />
          <font-awesome-icon
            class="absolute left-0 top-0 z-10 ml-2 mt-2 text-gray-400"
            :icon="['fal', 'euro-sign']"
          />
        </div>
      </section>

      <button
        class="ml-auto rounded-full px-2 py-1 text-theme-500 dark:bg-gray-900"
        @click="
          (newRun.dlv_production.push({
            days: null,
            value: 0,
            mode: 'fixed',
          }),
          updateRun(newRun.dlv_production))
        "
      >
        {{ $t("add") }}
      </button>
    </div>

    <div class="flex w-full items-center justify-between pt-2">
      <button
        class="ml-auto rounded-full bg-gray-200 px-2 py-1 dark:bg-gray-900"
        @click="$parent.set_show_prod_days(false)"
      >
        {{ $t("done") }}
      </button>
    </div>
  </div>
</template>

<script>
// import vuex mappings
import { mapMutations, mapGetters } from "vuex";
import "vue-select/dist/vue-select.css";
import _ from "lodash";

export default {
  props: {
    run: {
      type: Object,
      required: true,
    },
    printing_methods: {
      type: Array,
      required: true,
    },
    delivery_days: {
      type: Array,
      required: true,
    },
  },
  setup() {
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    return { addToast, authStore };
  },
  data() {
    return {
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,`
      currency: this.authStore.currencySettings,
      change: 0,
      newRun: {},
      daysArray: [],
    };
  },
  created() {
    this.newRun = _.cloneDeep(this.run);

    if (this.newRun && !this.newRun.dlv_production) {
      this.newRun.dlv_production = [
        {
          days: null,
          value: 0,
          mode: "fixed",
        },
      ];
    }

    if (this.newRun.dlv_production) {
      this.newRun.dlv_production.forEach((day) => this.daysArray.push(day.days));
    }
  },
  computed: {
    ...mapGetters({
      language: "settings/language",
    }),
    // dlv_production() {
    // 	return this.newRun.dlv_production;
    // },
    filtered_printing_methods() {
      return this.printing_methods.filter((el) => !this.run.pm.find((pm) => pm.name === el.name));
    },
    filtered_delivery_days() {
      return this.delivery_days.filter(
        (el) => !this.run.pm.find((dlv) => dlv.days === el.days) && el.iso === this.language,
      );
    },
  },
  watch: {
    // run(v){
    //    return v
    // },
    newRun: {
      handler(v) {
        this.run = v;
        return v;
      },
      deep: true,
      immediate: true,
    },
    dlv_production: {
      handler(v) {
        return v;
      },
      deep: true,
      immediate: true,
    },
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          type: "info",
          message: "You made changes, be sure to save them",
        });
      }
    },
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update_item: "assortmentsettings/update_item",
    }),

    updateRun(dlv_production) {
      this.newRun.dlv_production = dlv_production;
      this.newRun = _.cloneDeep(this.newRun);
    },
  },
};
</script>
