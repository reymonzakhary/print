<template>
  <div class="flex items-center justify-between p-2">
    <section class="flex items-center justify-between flex-1">
      <input
        v-model="run.from"
        disabled
        class="w-full px-2 py-1 text-sm transition bg-gray-200 shadow-none input"
        type="number"
        :min="
          typeof printing_method.qty_build[i - 1] !== 'undefined' &&
          Object.keys(printing_method.qty_build[i - 1]).length > 0
            ? Number(printing_method.qty_build[i - 1].to) + 1
            : Number(printing_method.from)
        "
        :max="Number(printing_method.qty_build[i].to) - 1"
      />

      <div
        class="mx-2 text-xs font-bold tracking-wide text-center text-gray-400 uppercase shrink-0"
      >
        {{ $t("through to") }}
      </div>

      <input
        v-model="run.to"
        class="w-full px-2 py-1 text-sm input"
        type="number"
        :min="Number(printing_method.qty_build[i].from) + 1"
        @input="
          eventStore.emit('to_value_changed', {
            pm_index: index,
            index: i,
            value: run.to,
          })
        "
      />
    </section>

    <section class="flex justify-start flex-1">
      <div class="flex items-center justify-between px-2 ml-1">
        <input v-model="run.incremental_by" class="w-16 px-2 py-1 text-sm input" type="number" />
      </div>
    </section>

    <section class="flex-1 pr-2 text-right">
      <a
        v-if="printing_method.qty_build.length > 1"
        href="#"
        class="px-2 py-1 text-sm text-red-600 rounded-full hover:bg-red-100"
        @click="removeQuantity(i)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
      </a>
    </section>
  </div>
</template>

<script>
import { debounce } from "lodash";
import { mapMutations, mapActions } from "vuex";
export default {
  name: "CategoryQuantitiesrun",
  props: {
    range: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
  },
  setup() {
    const { addToast } = useToastStore();
    const eventStore = useEventStore();
    return {
      eventStore,
      addToast,
    };
  },
  watch: {
    "printing_method.from"(v) {
      if (this.i === 0) {
        this.run.from = Number(v);
      }
    },
    "run.from"(v) {
      if (this.i === 0) {
        this.run.from = Number(this.printing_method.from);
      }
      if (Number(this.run.to) <= Number(v)) {
        this.run.to = Number(v) + 1;
      }
    },
    "run.to": debounce(function (v) {
      if (this.i === 0) {
        this.run.from = Number(this.printing_method.from);
      }
      if (Number(this.run.to) <= Number(this.run.from)) {
        this.run.to = Number(this.run.from) + 1;
      }
    }, 1000),
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          type: "info",
          text: "You made changes, be sure to save them",
        });
      }
    },
  },

  beforeUnmount() {
    this.eventStore.off("to_value_changed");
  },

  methods: {
    ...mapMutations({
      update_printing_method: "product_wizard/update_printing_method",
      update_ranges: "product_wizard/update_ranges",
    }),
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),
    
    removeQuantity(i) {
      this.change++;

      this.printing_method.qty_build.splice(i, 1);
    },
  },
};
</script>
