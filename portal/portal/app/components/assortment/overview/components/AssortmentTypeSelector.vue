<template>
  <div
    v-if="
      hasPermission('print-assortments-products-access') &&
      hasPermission('custom-assortments-products-access')
    "
    class="relative my-3 flex w-full px-2"
  >
    <button
      class="w-1/2 rounded-l border border-r-0 px-2 py-1 text-xs"
      :class="{
        'border-theme-600 bg-theme-400 text-themecontrast-400': assortmentFlag === 'print_product',
      }"
      @click="switchAssortmentType('print_product')"
    >
      {{ $t("print product") }}
    </button>

    <button
      v-tooltip="$t('Order via shop')"
      :disabled="compareFlag !== 'compare'"
      class="w-1/2 rounded-r border border-l-0 px-2 py-1 text-xs"
      :class="{
        'border-theme-600 bg-theme-400 text-themecontrast-400': assortmentFlag === 'custom_product',
        'cursor-not-allowed opacity-50': compareFlag !== 'compare',
      }"
      @click="switchAssortmentType('custom_product')"
    >
      {{ $t("custom product") }}
    </button>
  </div>
</template>

<script>
export default {
  name: "AssortmentTypeSelector",
  props: {
    assortmentFlag: {
      type: String,
      required: true,
    },
    compareFlag: {
      type: String,
      required: true,
    },
  },
  emits: ["switch-assortment-type"],
  setup() {
    const { hasPermission } = usePermissions();
    return { hasPermission };
  },
  methods: {
    switchAssortmentType(type) {
      this.$emit("switch-assortment-type", type);
    },
  },
};
</script>
