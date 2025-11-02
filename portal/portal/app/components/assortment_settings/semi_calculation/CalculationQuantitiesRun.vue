<template>
  <div
    v-if="run"
    class="flex justify-between py-1"
    :class="show_prod_days === 'all' ? 'items-start' : 'items-center'"
  >
    <section class="flex-1">
      <form class="flex items-center px-2">
        <section class="relative mr-2 flex w-1/2">
          <input
            v-model="run.from"
            class="input w-full px-2 py-1 text-sm"
            type="number"
            @input="
              eventStore.emit('option_from_value_changed', {
                index: i,
                value: run.from,
                pm: run.pm,
              })
            "
          />
        </section>
        {{ $t("through to") }}
        <section class="relative ml-2 flex w-1/2">
          <input
            v-model="run.to"
            class="input w-full px-2 py-1 text-sm"
            type="number"
            @input="
              eventStore.emit('option_to_value_changed', {
                index: i,
                value: run.to,
                pm: run.pm,
              })
            "
          />
        </section>
      </form>
    </section>

    <section class="flex flex-1">
      <div class="relative flex items-center justify-between">
        <UICurrencyInput
          v-model="run.price"
          input-class="w-24 px-2 py-1 border-green-500 text-sm dark:border-green-500 ring-green-200 focus:border-green-500"
          :options="{
            precision: 5,
          }"
        />
      </div>
    </section>
    <section class="flex flex-1">
      <v-select
        v-model="run.pm"
        :options="printing_methods"
        :reduce="(pm) => pm.slug"
        label="name"
        class="input mr-4 w-auto rounded bg-white p-1 py-0 text-sm text-theme-900"
      />
    </section>

    <section class="flex justify-end" :class="show_prod_days === 'all' ? 'flex-shrink' : 'flex-1'">
      <button
        class="mr-2 rounded-full px-3 text-sm text-red-600 hover:bg-red-100"
        @click="removeQuantity(i)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
      </button>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import "vue-select/dist/vue-select.css";
import _ from "lodash";
import cO from "~/plugins/directives/click-outside";

export default {
  mixins: [cO],
  props: {
    run: {
      type: Object,
      required: true,
    },
    printing_methods: {
      type: Array,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
  },
  setup() {
    const eventStore = useEventStore();
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    return {
      eventStore,
      addToast,
      authStore,
    };
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
      runs: (state) => state.assortmentsettings.runs,
      show_prod_days: (state) => state.assortmentsettings.show_prod_days,
    }),
    daysArray() {
      const arr = [];
      this.run.dlv_production.forEach((day) => arr.push(day.days));

      return arr;
    },
  },
  watch: {
    run: {
      handler(v) {
        this.update_run({
          index: this.i,
          value: this.run,
        });
        return v;
      },
      deep: true,
      immediate: true,
    },
    "run.dlv_production": {
      handler(v) {
        return v;
      },
      deep: true,
      immediate: true,
    },
    "run.from"(v) {
      if (Number(this.run.to) <= Number(v)) {
        this.run.to = Number(v) + 1;
      }
    },
    "run.to": _.debounce(function (v) {
      if (Number(this.run.to) <= Number(this.run.from)) {
        this.run.to = Number(this.run.from) + 1;
      }
    }, 1000),
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          message: this.$t("You made changes, be sure to save them"),
          type: "info",
        });
      }
    },
  },

  beforeUnmount() {
    this.eventStore.off(" option_from_value_changed");
    this.eventStore.off(" option_to_value_changed");
  },
  methods: {
    ...mapMutations({
      update_run: "assortmentsettings/update_run",
      add_run_dlv: "assortmentsettings/add_run_dlv",
      update_run_dlv: "assortmentsettings/update_run_dlv",
      set_show_prod_days: "assortmentsettings/set_show_prod_days",
    }),

    focusDlvPrice(i) {
      const el = document.getElementById(`prod_days_${i}`);
      setTimeout(() => {
        el.focus();
      }, 50);
    },

    hideDlvPrice(e) {
      if (this.show_prod_days !== "all" && !e.currentTarget.contains(e.relatedTarget)) {
        this.set_show_prod_days(false);
      }
    },

    // remove margin
    removeQuantity(i) {
      this.change++;
      this.runs.splice(i, 1);
    },
  },
};
</script>
