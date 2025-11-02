<template>
  <div class="flex">
    <assortment-settings-nav class="hidden w-1/5 md:block"></assortment-settings-nav>
    <PrintingMethods class="w-full md:w-4/5"></PrintingMethods>
  </div>
</template>

<script>
import AssortmentSettingsNav from "~/components/assortment_settings/AssortmentSettingsNav.vue";
import PrintingMethods from "~/components/assortment_settings/PrintingMethods.vue";
export default {
  components: {
    AssortmentSettingsNav,
    PrintingMethods,
  },
};
</script>

<style></style>
