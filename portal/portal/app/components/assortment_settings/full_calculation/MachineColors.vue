<template>
  <div>
    <header class="mb-2 flex items-center gap-2">
      <UIButton
        variant="danger"
        :icon="['fa', 'trash']"
        @click="$emit('onRemoveColor', { color: internalItem, i: index })"
      />
      <div class="text-sm font-bold uppercase tracking-widest">
        {{ $display_name(internalItem.display_name) }}
      </div>
    </header>
    <section class="flex w-full flex-wrap items-start">
      <div class="w-2/3 divide-y rounded-md shadow-md dark:divide-gray-900 dark:bg-gray-900">
        <section class="mb-2 grid grid-cols-3 gap-2">
          <div>
            <label
              for="spoilage"
              class="text-xs font-semibold uppercase text-gray-800 dark:text-gray-200"
            >
              {{ $t("Spoilage") }}
            </label>
            <UIInputText
              v-model="internalItem.spoilage"
              type="number"
              name="spoilage"
              placeholder="Spoilage"
              :disabled="!editable"
              class="!cursor-default"
            />
          </div>
          <div>
            <label
              for="mpm"
              class="text-xs font-semibold uppercase text-gray-800 dark:text-gray-200"
            >
              {{ $t("MPM") }}
            </label>
            <UIInputText
              v-model="internalItem.mpm"
              type="number"
              name="mpm"
              placeholder="MPM"
              :disabled="!editable"
              class="!cursor-default"
            />
          </div>
          <div>
            <label
              for="spm"
              class="text-xs font-semibold uppercase text-gray-800 dark:text-gray-200"
            >
              {{ $t("SPM") }}
            </label>
            <UIInputText
              v-model="internalItem.spm"
              type="number"
              name="spm"
              placeholder="SPM"
              :disabled="!editable"
              :class="{ '!cursor-default': !editable }"
            />
          </div>
        </section>
        <div class="bg-white dark:bg-gray-700">
          <header
            class="flex w-full items-center justify-between p-2 text-sm font-bold uppercase tracking-wide"
          >
            <div class="flex-1">
              <font-awesome-icon
                class="mr-2 text-gray-400"
                :icon="['fal', 'print-magnifying-glass']"
              />
              {{ $t("runs") }}
            </div>
            <div class="flex-1">{{ $t("tick per sheet") }}</div>
          </header>

          <div v-for="(sheet_run, i) in internalItem.sheet_runs" :key="i" class="w-full">
            <div v-if="sheet_run.machine === machine">
              <MachineColorsRun
                v-for="(run, idx) in sheet_run.runs"
                :key="`sheet_run_run_${idx}`"
                :run="run"
                :i="idx"
                :editable="editable"
                @on-remove-run="removeQuantity($event)"
              />
            </div>
          </div>
          <div v-if="editable" class="w-full">
            <div
              class="group flex cursor-pointer items-center justify-between rounded-b-md bg-gray-100 p-2 text-center dark:bg-gray-800"
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
        </div>
      </div>
      <div v-if="type === 'printing'" class="w-1/3 p-2">
        <div v-for="(sheet_run, i) in internalItem.sheet_runs" :key="i" class="w-full">
          <machineColorsDlv
            v-if="sheet_run.machine === machine"
            :editable="editable"
            :sheet_run="sheet_run"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
// import vuex mappings
import "vue-select/dist/vue-select.css";
import MachineColorsRun from "./MachineColorsRun.vue";
import MachineColorsDlv from "./MachineColorsDlv.vue";

export default {
  components: { MachineColorsDlv, MachineColorsRun },
  props: {
    item: {
      type: Object,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    machine: {
      type: String,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    editable: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onRemoveColor", "onUpdate"],
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
      internalItem: this.item,
    };
  },
  computed: {
    machineIndex() {
      return this.internalItem.sheet_runs.findIndex((run) => run.machine === this.machine);
    },
  },
  watch: {
    item: {
      deep: true,
      immediate: true,
      handler(v) {
        if (v.name !== this.internalItem.name){
          this.internalItem = v;
        }
        this.updateRuns(v);
        return v;
      },
    },
    internalItem: {
      deep: true,
      handler(v, o) {
        if (v.name === o.name) {
          this.updateRuns(v);
          this.$emit("onUpdate", v);
        }
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
    this.eventStore.on("machine_option_to_value_changed", (e) => {
      let i = 1;
      for (let ind = 0; ind < this.item.sheet_runs[this.machineIndex].runs.length; ind++) {
        if (
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) + Number(i)] !==
            undefined &&
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) + Number(i)].pm ===
            e.pm &&
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) + Number(i)].from <=
            Number(e.value)
        ) {
          // update value
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) + Number(i)].from =
            Number(e.value) + 1;
        } else {
          i++;
        }
      }
    });
    this.eventStore.on("machine_option_from_value_changed", (e) => {
      let i = 1;
      for (let ind = 0; ind < this.internalItem.sheet_runs[this.machineIndex].runs.length; ind++) {
        if (
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) - Number(i)] !==
            undefined &&
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) - Number(i)].to >=
            Number(e.value)
        ) {
          // update value
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index) - Number(i)].to =
            Number(e.value) - 1;
        } else {
          i++;
        }
      }
    });
    this.eventStore.on("machine_option_price_value_changed", (e) => {
      this.$nextTick(() => {
        for (
          let ind = 0;
          ind < this.internalItem.sheet_runs[this.machineIndex].runs.length;
          ind++
        ) {
          // update value
          this.internalItem.sheet_runs[this.machineIndex].runs[Number(e.index)].price = e.value;
        }
      });
    });
  },
  methods: {
    updateRuns(v) {
      v.sheet_runs[this.machineIndex].runs.forEach((run, i) => {
        this.inputUpdate({
          index: i,
          value: run.to,
          price: run.price,
        });
      });
    },
    // update exisiting slot values
    inputUpdate(run) {
      // manipulate data
      if (this.internalItem.sheet_runs[this.machineIndex].runs[run.index]) {
        this.internalItem.sheet_runs[this.machineIndex].runs[run.index].to = parseInt(run.value);
        this.internalItem.sheet_runs[this.machineIndex].runs[run.index].price = parseInt(run.price);
      }

      if (this.internalItem.sheet_runs[this.machineIndex].runs[run.index + 1]) {
        this.internalItem.sheet_runs[this.machineIndex].runs[run.index + 1].from =
          parseInt(run.value) + 1;
      }
    },
    // add new run
    addQuantity(i) {
      let newQuantity = {};
      if (
        this.internalItem.sheet_runs[this.machineIndex].runs[
          this.internalItem.sheet_runs[this.machineIndex].runs.length - 1
        ]
      ) {
        newQuantity = {
          from:
            this.internalItem.sheet_runs[this.machineIndex].runs[
              this.internalItem.sheet_runs[this.machineIndex].runs.length - 1
            ].to + 1,
          to:
            this.internalItem.sheet_runs[this.machineIndex].runs[
              this.internalItem.sheet_runs[this.machineIndex].runs.length - 1
            ].to + 1000,
          price: 10000,
        };
      } else {
        newQuantity = {
          from: 1,
          to: 1000,
          price: 10000,
        };
      }
      this.internalItem.sheet_runs[this.machineIndex].runs.push(newQuantity);
    },
    // remove margin
    removeQuantity(i) {
      this.change++;
      this.internalItem.sheet_runs[this.machineIndex].runs.splice(i, 1);
    },
  },
};
</script>
