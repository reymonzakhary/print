<template>
  <div>
    <section
      class="flex w-full flex-wrap divide-y rounded-md bg-white shadow-md dark:divide-gray-900 dark:bg-gray-700"
    >
      <header
        class="flex w-full items-center justify-between p-2 text-sm font-bold uppercase tracking-wide"
      >
        <div class="flex-1">
          <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'bow-arrow']" />
          {{ $t("run") }}
        </div>
        <div class="flex-1">{{ $t("price per piece") }}</div>
        <div class="flex-1">
          <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'print-magnifying-glass']" />
          {{ $t("printing method") }}
        </div>
        <!-- <div class="flex-1">
          <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'print-magnifying-glass']" />
          {{ $t("production days") }}
        </div>
        <div class="flex-1 text-right">
          <button
            class="p-2 ml-auto text-theme-500"
            @click="
              show_prod_days === false ? set_show_prod_days('all') : set_show_prod_days(false)
            "
          >
            {{ $t("show all production days") }}
            <font-awesome-icon :icon="['fal', 'angle-down']" />
          </button>
        </div> -->
      </header>
      <div v-for="(run, i) in runs" :key="i" class="w-full">
        <CalculationQuantitiesRun
          v-if="printing_methods"
          :run="run"
          :printing_methods="printing_methods"
          :production_days="production_days"
          :i="i"
        />
      </div>
      <div class="w-full">
        <div
          class="group flex cursor-pointer items-center justify-between rounded-b-md bg-gray-100 p-2 text-center dark:bg-gray-900"
        >
          <p class="text-xm font-bold text-gray-400">
            {{ $t("add another quantity") }}
          </p>

          <button
            class="rounded-full border border-theme-500 px-2 py-1 text-sm text-theme-500 transition hover:bg-theme-200"
            @click="addQuantity()"
          >
            <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
            {{ $t("add quantity") }}
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import "vue-select/dist/vue-select.css";

export default {
  props: {
    type: {
      type: String,
      default: "option",
    },
  },
  setup() {
    const eventStore = useEventStore();
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    return { eventStore, addToast, authStore };
  },
  data() {
    return {
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      mode: 1,
      change: 0,
    };
  },
  computed: {
    ...mapState({
      item: (state) => state.assortmentsettings.item,
      runs: (state) => state.assortmentsettings.runs,
      show_prod_days: (state) => state.assortmentsettings.show_prod_days,
      printing_methods: (state) => state.printing_methods.printing_methods,
      production_days: (state) => state.production_days.production_days,
    }),
    showAddQuantity() {
      if (this.item.runs.length === 0) {
        return true;
      } else {
        return false;
      }
    },
  },
  watch: {
    runs: {
      handler(v) {
        this.update_item({
          key: "runs",
          value: this.runs,
        });

        // setTimeout(() => {
        this.$nextTick(() => {
          if (this.show_prod_days === "all") {
            this.set_show_prod_days(false);
            this.set_show_prod_days("all");
          }
        });

        return v;
      },
    },
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          message: this.$t("You made changes, be sure to save them"),
          type: "info",
        });
      }
    },
    printing_methods(v) {
      return v;
    },
    production_days(v) {
      return v;
    },
  },
  created() {
    this.get_printing_methods();
    this.get_production_days();

    this.eventStore.on("option_to_value_changed", (e) => {
      let i = 1;
      for (let ind = 0; ind < this.runs.length; ind++) {
        if (
          this.runs[Number(e.index) + Number(i)] !== undefined &&
          this.runs[Number(e.index) + Number(i)].pm === e.pm
        ) {
          // update value
          this.runs[Number(e.index) + Number(i)].from = Number(e.value) + 1;
        } else {
          i++;
        }
      }
    });
    this.eventStore.on("option_from_value_changed", (e) => {
      let i = 1;
      for (let ind = 0; ind < this.runs.length; ind++) {
        if (
          this.runs[Number(e.index) - Number(i)] !== undefined &&
          this.runs[Number(e.index) - Number(i)]?.pm === e?.pm
        ) {
          // update value
          this.runs[Number(e.index) - Number(i)].to = Number(e.value) - 1;
        } else {
          i++;
        }
      }
    });
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update_item: "assortmentsettings/update_item",
      add_run: "assortmentsettings/add_run",
      update_run: "assortmentsettings/update_run",
      set_show_prod_days: "assortmentsettings/set_show_prod_days",
    }),
    ...mapActions({
      get_printing_methods: "printing_methods/get_printing_methods",
      get_production_days: "production_days/get_production_days",
    }),

    // add new margin
    addQuantity() {
      const i = this.runs.length - 1;
      const run = this.runs[i];

      this.change++;
      // set slot object
      if (this.printing_methods.length > 0) {
        const newQuantity = {
          from: 1,
          to: 100,
          price: 10,
          pm: this.printing_methods[0].slug,
          dlv_production: run && run.dlv_production ? run.dlv_production : [],
        };
        // if (slot === 0) {
        this.add_run(newQuantity);
        // }

        this.update_run({
          index: i,
          run: newQuantity,
        });
      } else {
        this.addToast({
          message: this.$t("You should add printing methods first"),
          type: "warning",
        });
      }
    },
  },
};
</script>
