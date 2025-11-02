<template>
  <div>
    <div
      v-if="
        !category_printing_methods ||
        (category_printing_methods && category_printing_methods.length === 0) ||
        addPrintingMethod
      "
      class="my-4 flex justify-between"
    >
      <div v-if="filtered_printing_methods.length > 0" class="flex w-full items-center md:w-1/2">
        <select
          id="pm"
          v-model="pm"
          name="pm"
          class="input w-full rounded-none rounded-l px-2 py-1"
        >
          <option
            v-for="method in filtered_printing_methods"
            :key="`method_${method.id}`"
            :value="method"
          >
            {{ method.name }}
          </option>
        </select>
        <button
          class="rounded-r border border-theme-600 bg-theme-400 px-2 py-1 text-white"
          @click="updatePrintingMethod()"
        >
          {{ $t("add") }}
        </button>
      </div>

      <div v-else class="italic text-gray-500">
        {{ $t("add_more_printing_methods_from_assortment_settings") }}
      </div>
      <button
        v-if="category_printing_methods && category_printing_methods.length > 0"
        class="ml-2 text-theme-500"
        @click="addPrintingMethod = false"
      >
        {{ $t("close") }}
      </button>
    </div>

    <div v-if="category_printing_methods && category_printing_methods.length > 0">
      <div
        v-for="(printing_method, index) in category_printing_methods"
        :key="index"
        class="mb-4 mt-2 flex flex-wrap"
      >
        <CategoryQuantitiesHeader :printing_method="printing_method" :index="index" />

        <section
          class="flex w-full flex-wrap divide-y rounded-md rounded-t-none bg-white shadow-md dark:divide-gray-900 dark:bg-gray-700"
        >
          <header
            class="flex w-full items-center justify-between p-2 text-sm font-bold uppercase tracking-wide"
          >
            <div class="flex-1">
              <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'bow-arrow']" />
              {{ $t("run") }}
            </div>
            <div class="flex-1">
              <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'arrows-h']" />
              {{ $t("interval") }}
            </div>

            <div class="flex-1 text-right">{{ $t("actions") }}</div>
          </header>

          <div v-for="(slot, i) in printing_method.qty_build" :key="i" class="flex w-full flex-col">
            <CategoryQuantitiesRun
              :run="slot"
              :printing_method="printing_method"
              :index="index"
              :i="i"
            />

            <!-- Add qty -->
            <transition name="fade">
              <div
                v-if="
                  i === printing_method.qty_build.length - 1 &&
                  Number(printing_method.qty_build[i].to)
                "
                class="w-full"
              >
                <div
                  class="group mt-2 flex cursor-pointer items-center justify-between rounded-b-md bg-gray-100 p-2 text-center dark:bg-gray-900"
                  @click="addQuantity(index, i)"
                >
                  <p class="text-xm font-bold text-gray-400">
                    {{ $t("add another quantity") }}
                  </p>

                  <button
                    class="rounded-full border border-theme-500 px-2 py-1 text-sm text-theme-500 transition hover:bg-theme-200"
                  >
                    <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
                    {{ $t("add quantity") }}
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </section>
      </div>
    </div>

    <div class="flex justify-end">
      <button
        class="mx-1 rounded-full bg-green-500 px-2 py-1 text-white transition-colors duration-75 hover:bg-green-600"
        @click="save"
      >
        {{ $t("save") }} {{ $t("quantities") }}
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
import "vue-select/dist/vue-select.css";

export default {
  props: {
    category_printing_methods: {
      type: Array,
    },
    type: {
      type: String,
      default: "item",
    },
  },
  setup() {
    const api = useAPI();
    const eventStore = useEventStore();
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    return { eventStore, addToast, api, authStore };
  },
  data() {
    return {
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      mode: 1,
      change: 0,
      show_dlv_pricing: false,
      addPrintingMethod: false,
      pm: {},
      dlv: {},
    };
  },
  computed: {
    ...mapState({
      printing_methods: (state) => state.printing_methods.printing_methods,
      delivery_days: (state) => state.delivery_days.delivery_days,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
    ...mapGetters({
      langauge: "settings/language",
    }),
    filtered_printing_methods() {
      return this.printing_methods.filter(
        (el) => !this.category_printing_methods.find((pm) => pm.name === el.name),
      );
    },
  },
  watch: {
    quantities: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          message: "You made changes, be sure to save them",
          type: "info",
        });
      }
    },
    printing_methods(v) {
      return v;
    },
  },
  created() {
    this.get_printing_methods();
    this.get_delivery_days();

    this.eventStore.on("to_value_changed", (e) => {
      const run = this.category_printing_methods[e.pm_index].qty_build[Number(e.index) + 1];
      // if run exists
      if (run !== undefined) {
        // update value
        run.from = Number(e.value) + 1;
      }
    });

    this.eventStore.on("add_category_printing_method", () => {
      this.addPrintingMethod = true;
    });
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update_printing_method: "product_wizard/update_printing_method",
      update_delivery_days: "product_wizard/update_delivery_days",
    }),
    ...mapActions({
      get_printing_methods: "printing_methods/get_printing_methods",
      get_delivery_days: "delivery_days/get_delivery_days",
      update_selected_category: "product_wizard/update_selected_category",
    }),
    save(event) {
      this.update_selected_category(event);
      this.addToast({
        type: "success",
        message: "Category updated successfully",
      });
    },
    updatePrintingMethod() {
      const printing_method = {
        name: "",
        slug: "",
        from: 1,
        to: 10000,
        dlv_days: [],
        qty_build: [
          {
            from: 1,
            to: 100,
            price: {
              value: 10,
              mode: "fixed",
              on: "price",
            },
            description: "Add cost on every qty",
            incremental_by: 10,
          },
        ],
      };

      printing_method.name = this.pm.name;
      printing_method.slug = this.pm.slug;
      this.category_printing_methods.push(printing_method);
    },

    removePrintingMethod(i) {
      this.category_printing_methods.splice(i, 1);
    },

    // add new margin
    addQuantity(category_printing_methods_index, printing_method_run_index) {
      this.change++;
      // set slot object
      const newQuantity = {
        from:
          Number(
            this.category_printing_methods[category_printing_methods_index].qty_build[
              printing_method_run_index
            ].to,
          ) + 1,
        to:
          Number(
            this.category_printing_methods[category_printing_methods_index].qty_build[
              printing_method_run_index
            ].to,
          ) + 1,
        price: {
          value: 10,
          mode: "fixed",
          on: "price",
        },
        description: "Add cost on every qty",
        incremental_by: 10,
      };

      this.category_printing_methods[category_printing_methods_index].qty_build.push(newQuantity);

      // update store with mutation
      // this.update(data);
    },

    async buffer() {
      this.api
        .post(`/categories/${this.selected_category.slug}/price/buffer`)
        .then((response) => {});
    },
  },
};
</script>
