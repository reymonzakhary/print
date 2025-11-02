<template>
  <div v-if="run" class="flex w-full justify-between py-1">
    <section class="flex flex-1 items-center justify-between">
      <input
        v-if="editable"
        v-model="run.from"
        class="input ml-2 w-full px-2 py-1 text-sm"
        type="number"
        @input="
          eventStore.emit('machine_option_from_value_changed', {
            index: i,
            value: run.from,
          })
        "
      />
      <div v-else class="w-1/3 pl-2 text-right font-mono">{{ run.from }}</div>
      <div
        class="mx-2 shrink-0 text-center text-xs font-bold uppercase tracking-wide text-gray-400"
      >
        {{ $t("through to") }}
      </div>
      <input
        v-if="editable"
        v-model="run.to"
        class="input mr-2 w-full px-2 py-1 text-sm"
        type="number"
        @input="
          eventStore.emit('machine_option_to_value_changed', {
            index: i,
            value: run.to,
          })
        "
      />
      <div v-else class="w-48 pr-8 text-end font-mono">{{ run.to }}</div>
    </section>

    <section class="flex flex-1">
      <div class="relative flex items-center justify-between">
        <UICurrencyInput
          v-if="editable"
          v-model="run.price"
          input-class="ml-6 w-24 text-sm border-green-500 ring-green-200 focus:border-green-500 px-2 py-1 dark:border-green-500"
          :options="{
            precision: 5,
          }"
        />
        <div v-else class="font-mono">{{ run.display_price }}</div>
      </div>
    </section>

    <section v-if="editable" class="flex flex-shrink justify-end">
      <button
        class="mr-2 rounded-full px-3 text-sm text-red-600 hover:bg-red-100"
        @click="$emit('onRemoveRun', i)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
      </button>
    </section>
  </div>
</template>

<script>
// import vuex mappings
import "vue-select/dist/vue-select.css";

import cO from "~/plugins/directives/click-outside";
import _ from "lodash";

export default {
  mixins: [cO],
  props: {
    run: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
    editable: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onRemoveRun"],
  setup() {
    const eventStore = useEventStore();
    const { addToast } = useToastStore();
    return {
      eventStore,
      addToast,
    };
  },
  data() {
    return {
      // run: { ...this.run },
      mode: 1,
      change: 0,
    };
  },
  watch: {
    run: {
      handler(v) {
        return v;
      },
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
    this.eventStore.off(" machine_option_from_value_changed");
    this.eventStore.off(" machine_option_to_value_changed");
  },
  methods: {
    // update exisiting run values
    inputUpdate(run) {
      // console.log(run.index, run.value);
      // manipulate data
      this.runs[run.index].to = parseInt(run.value);
      this.runs[run.index + 1].from = parseInt(run.value) + 1;
    },
    // remove margin
    removeQuantity(i) {
      this.change++;
      this.runs.splice(i, 1);
      // update store with mutation
    },
  },
};
</script>
