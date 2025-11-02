<template>
  <div class="h-full">
    <header
      class="relative flex justify-center p-4 pb-0 bg-white dark:bg-gray-700"
    >
      <button class="mr-auto text-theme-500 hover:text-theme-700" @click="back">
        <font-awesome-icon :icon="['fal', 'chevron-left']" />
        {{ $t("back") }}
      </button>
      <div class="mr-auto">
        <font-awesome-icon :icon="['fal', 'box-full']" />
        {{ $t("adding new category") }}
      </div>
    </header>

    <!-- general category overview component -->
    <div class="flex h-full pb-4">
      <CategoryOverview />
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
  computed: {
    ...mapState({
      compare_category: (state) => state.compare.compare_category,
      flag: (state) => state.compare.flag,
    }),
  },
  watch: {
    compare_category: {
      handler(newValue) {
        this.next(newValue);
      },
      deep: true,
    },
  },
  methods: {
    //  Vuex mappings
    ...mapMutations({
      set_type: "product_wizard/set_wizard_type",
      set_component: "product_wizard/set_wizard_component",
      set_selected_category: "product_wizard/set_selected_category",
    }),

    // Navigation
    back() {
      this.set_type("");
      this.set_component("PrintProductFromProducer");
    },
    next(category) {
      this.set_selected_category(category);
      this.set_component("PrintProductEditBoops");
    },
  },
};
</script>
