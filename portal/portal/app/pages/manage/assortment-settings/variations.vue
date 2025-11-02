<template>
  <div class="flex">
    <assortment-settings-nav class="hidden w-1/5 md:block"></assortment-settings-nav>
    <Variations class="w-full md:w-4/5"></Variations>
  </div>
</template>

<script>
import AssortmentSettingsNav from "~/components/assortment_settings/AssortmentSettingsNav.vue";
import Variations from "~/components/assortment_settings/custom_products/Variations.vue";
export default {
  components: {
    AssortmentSettingsNav,
    Variations,
  },
};
</script>
