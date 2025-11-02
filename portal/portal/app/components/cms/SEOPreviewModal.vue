<template>
  <SlideInModal
    :icon="['fab', 'searchengin']"
    title="SEO Preview"
    :show="show"
    @on-close="$emit('onClose', $event)"
    @on-backdrop-click="$emit('onBackdropClick', $event)"
  >
    <div class="md:w-[33vw] p-4">
      <SEOPreview variant="google" :title="title" :description="description" :url="url" />

      <SEOPreview
        variant="facebook"
        :title="title"
        :description="description"
        :url="url"
        :image="image"
        class="mt-8"
      />

      <SEOPreview
        variant="x"
        :title="title"
        :description="description"
        :url="url"
        :image="image"
        class="mt-8"
      />
    </div>
  </SlideInModal>
</template>

<script>
export default {
  name: "SEOPreviewModal",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    url: {
      type: String,
      required: true,
    },
    image: {
      type: String,
      required: false,
      default: null,
    },
  },
  emits: ["onClose", "onBackdropClick"],
};
</script>
