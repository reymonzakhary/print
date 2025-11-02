<template>
  <nav class="flex w-full items-center bg-white text-sm dark:bg-gray-700">
    <button
      v-if="namespaceareas.length > 1"
      v-tooltip="{
        content: $t('all') + ' ' + namespace + ' ' + $t('settings'),
        placement: 'top',
      }"
      class="flex-1 items-center truncate border-b-2 px-6 py-2 text-gray-600 hover:text-theme-500 focus:outline-none"
      :class="{
        'border-theme-500 bg-theme-100 font-semibold text-theme-500': area_active === '',
      }"
      @click="
        ($emit('onAreaClick', {
          namespace: namespace,
          area: '',
          page: page,
          per_page: perPage,
          filter: filter,
        }),
        set_area_active(''))
      "
    >
      <font-awesome-icon :icon="['fal', 'ballot-check']" class="mr-1 text-theme-500" />
      {{ $t("all") }} {{ $t("settings") }}
    </button>

    <template v-for="area in namespaceareas">
      <button
        v-if="namespaceareas.length > 1 && area"
        :key="`setting_${area}`"
        class="flex flex-1 items-center justify-center border-b-2 px-6 py-2 capitalize text-gray-600 hover:text-theme-500 focus:outline-none"
        :class="{
          'border-theme-500 bg-theme-100 font-semibold text-theme-500': area_active === area,
        }"
        @click="
          ($emit('onAreaClick', {
            namespace: namespace,
            area: area,
            page: page,
            per_page: perPage,
            filter: filter,
          }),
          set_area_active(area))
        "
      >
        <!-- <font-awesome-icon
					:icon="['fal', 'hand-holding-dollar']"
					class="text-theme-500"
				/> -->
        {{ area }}
      </button>
    </template>
  </nav>
</template>

<script>
import { mapState, mapActions, mapMutations } from "vuex";

export default {
  props: {
    namespace: String,
    settings: [Object, Array],
    page: [Number, String],
    perPage: [Number, String],
    filter: String,
  },
  emits: ["onAreaClick"],
  computed: {
    ...mapState({
      area_active: (state) => state.settings.area_active,
    }),
    namespaceareas() {
      return [...new Set(this.settings.area)];
    },
  },
  methods: {
    ...mapMutations({
      set_area_active: "settings/set_area_active",
    }),
    ...mapActions({
      get_settings: "settings/get_settings",
    }),
  },
};
</script>

<style></style>
