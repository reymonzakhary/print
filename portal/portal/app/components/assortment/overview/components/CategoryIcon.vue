<template>
  <!-- <div class="relative size-6"> -->
  <!-- Selection Indicator -->
  <!-- <div
      v-if="selectingMode"
      class="absolute -right-0.5 -top-0.5 flex size-3.5 items-center justify-center rounded-full bg-theme-500 text-white"
    >
      <font-awesome-icon v-if="category.selected" :icon="['fas', 'check']" class="size-2" />
    </div> -->
  <!-- </div> -->

  <div>
    <div class="mr-1 flex-1 flex-shrink-0">
      <Thumbnail
        v-if="category.media && category.media.length > 0 && category.media[0]"
        :disk="'assets'"
        :file="{ path: category.media[0] }"
        :size="42"
        @click="showDetails = true"
      />
      <font-awesome-icon
        v-else
        v-tooltip="$t('this category has no image')"
        class="text-xs text-gray-300"
        fixed-width
        :icon="['fal', 'image-slash']"
      />
    </div>

    <Teleport to="body">
      <ImageViewer
        v-if="showDetails && category.media && category.media.length > 0 && category.media[0]"
        :disk="'assets'"
        :all-files="category.media.map((image) => ({ path: image }))"
        @close="showDetails = false"
      />
    </Teleport>
  </div>
</template>
<script>
export default {
  name: "CategoryIcon",
  props: {
    category: { type: Object, required: true },
    selectingMode: { type: Boolean, default: false },
  },
  data() {
    return {
      showDetails: false,
    };
  },
  methods: {
    handleSvgError() {
      this.svgError = true;
    },
  },
};
</script>
