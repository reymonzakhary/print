<template>
  <div :class="[`pl-${i * 3}`, 'pt-4', { 'border-l border-l-gray-200': i > 0 }]">
    <button
      class="grid items-center w-full gap-4 p-2 text-left bg-white rounded shadow-md category-layout dark:bg-gray-700 shadow-gray-200 group"
      @click.prevent="set_active_custom_category(cat), set_component('CustomProductForm')"
    >
      <div style="grid-area: thumbnail">
        <Thumbnail
          v-if="cat.media && cat.media.length > 0 && cat.media[0]"
          disk="assets"
          :file="{ path: cat.media[0] }"
          :size="80"
        />
      </div>
      <p
        v-tooltip="cat.name"
        class="font-bold truncate group-hover:text-theme-500 max-h-44"
        style="grid-area: name"
      >
        {{ cat.name }}
      </p>
      <span v-tooltip="cat.description" style="grid-area: description" class="truncate max-h-44">
        {{ cat.description }}
      </span>
      <span class="font-mono text-sm text-gray-500" style="grid-area: date">
        {{ moment(cat.timestamp).format("DD MMM YYYY HH:MM") }}
      </span>
      <span class="text-right" style="grid-area: controls">
        <font-awesome-icon
          v-tooltip="cat.published ? 'Published' : 'Unpublished'"
          :icon="['fal', 'wave-pulse']"
          :class="cat.published ? 'text-theme-500' : 'text-gray-500'"
        />
        <UIButton
          :icon="['fal', 'pencil']"
          class="invisible mr-2 group-hover:visible"
          @click.stop="set_active_custom_category(cat), toggle_edit_cat(true)"
        />
      </span>
    </button>

    <CustomProductCategory
      v-for="c in cat.children"
      :key="c.id"
      :cat="c"
      :i="i + 1"
      class="w-full"
    />
  </div>
</template>

<script>
import { mapMutations, mapState } from "vuex";

import moment from "moment";

export default {
  props: {
    cat: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      moment: moment,
    };
  },
  computed: {
    ...mapState({
      edit_cat: (state) => state.product.edit_cat,
    }),
  },
  methods: {
    ...mapMutations({
      toggle_edit_cat: "product/toggle_edit_cat",
      set_active_custom_category: "product/set_active_custom_category",
      set_component: "product_wizard/set_wizard_component",
    }),
  },
};
</script>

<style>
.category-layout {
  grid-template-columns: 2rem 1fr 1fr 1fr auto;
  grid-template-areas: "thumbnail name description date controls";
}
</style>
