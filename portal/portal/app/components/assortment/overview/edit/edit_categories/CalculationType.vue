<template>
  <div>
    <div v-for="(value, key) in sortedPriceBuild" :key="key">
      <button
        class="my-2 flex w-full items-center justify-between rounded border p-2 shadow-md transition duration-150 hover:shadow-lg dark:border-gray-900 dark:hover:shadow-gray-900"
        :class="{
          'bg-theme-400 font-bold text-themecontrast-400 dark:bg-theme-500': value === true,
          'bg-white dark:bg-gray-700': value === false,
        }"
        @click="update(key)"
      >
        <font-awesome-icon
          :icon="[
            'fas',
            key === 'collection'
              ? 'album-collection'
              : key === 'semi_calculation'
                ? 'calculator-simple'
                : 'calculator',
          ]"
          class="fa-fw mr-2 text-xl"
        />

        <div class="">
          {{
            key === "full_calculation"
              ? $t("full calculation")
              : key === "semi_calculation"
                ? $t("semi calculation")
                : $t("collection price")
          }}
        </div>

        <font-awesome-icon
          v-tooltip="
            key === 'full_calculation'
              ? $t(`this is an automated price calculation based on machine and catalog parameters`)
              : key === 'semi_calculation'
                ? //prettier-ignore
                  $t(`this is an automated price calculation based on a price and calculation parameters on an option`)
                : //prettier-ignore
                  $t(`setting the price manually on each possible combination, like you would do in a product excel`)
          "
          :icon="['fas', 'circle-info']"
        />
      </button>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapActions } from "vuex";
export default {
  props: {
    priceBuild: {
      required: true,
      type: Object,
    },
  },
  emits: ["update_price_build"],
  setup() {
    const { addToast } = useToastStore();
    return { addToast };
  },
  computed: {
    sortedPriceBuild() {
      const sorted = {
        full_calculation: this.priceBuild.full_calculation,

        semi_calculation: this.priceBuild.semi_calculation,
        // collection: this.priceBuild.collection,
      };
      return sorted;
    },
  },
  watch: {
    priceBuild(v) {
      return v;
    },
  },
  methods: {
    ...mapMutations({
      update_price_build: "product_wizard/update_price_build",
    }),
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),

    update(type) {
      const price_build = { ...this.priceBuild };
      Object.entries(price_build).forEach(([key, val]) => {
        price_build[key] = false;
        if (key === type) {
          price_build[key] = true;
        }
      });
      this.$emit("update_price_build", price_build);
    },
  },
};
</script>
