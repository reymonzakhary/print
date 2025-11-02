<template>
  <div class="container">
    <h2 class="text-sm font-bold tracking-wide uppercase">
      {{ $t("assortment") }} {{ $t("settings") }}
    </h2>

    <nav class="flex items-center w-full my-4">
      <button
        class="block px-6 py-2 text-gray-600 border-b-2 hover:text-theme-500 focus:outline-none"
        :class="{
          'text-theme-500 font-semibold border-theme-500 bg-theme-100':
            component == 'Margins',
        }"
        @click="component = 'Margins'"
      >
        <font-awesome-icon
          :icon="['fal', 'hand-holding-dollar']"
          class="text-theme-500"
        />
        {{ $t("margins") }}
      </button>

      <button
        class="block px-6 py-2 text-gray-600 border-b-2 hover:text-theme-500 focus:outline-none"
        :class="{
          'text-theme-500 font-semibold border-theme-500 bg-theme-100':
            component == 'Discounts',
        }"
        @click="component = 'Discounts'"
      >
        <font-awesome-icon
          :icon="['fal', 'percentage']"
          class="text-theme-500"
        />
        {{ $t("discounts") }}
      </button>
    </nav>

    <transition name="fade" class="flex flex-wrap w-full">
      <!-- Inactive components will be cached! -->
      <!-- <keep-alive> -->
      <component :is="component" class="w-full"></component>
      <!-- </keep-alive> -->
    </transition>
  </div>
</template>

<script>
import Margins from "~/components/settings/margins/Margins";

import { mapMutations } from "vuex";

export default {
  transition: "disable",
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      component: "Margins",
    };
  },
  methods: {
    ...mapMutations({
      set_me: "settings/set_me",
    }),
    async saveMargins(object) {
      const updateMargin = await this.api.put("/margins/general", {
        margin: object,
      });
      this.$store.dispatch("toast/set", {
        text: updateMargin.message,
        status: updateMargin.status,
      });
    },
  },
  components: {
    Margins,
  },
};
</script>

<style></style>
