<template>
  <transition name="fade">
    <component
      :is="rawComponent"
      v-if="component !== 'CustomProductCategoryForm'"
      :my-cats="categories"
      :cat="activeCategory"
      @on-category-delete="$emit('on-category-delete', $event)"
      @on-close="$emit('close-panel')"
    />

    <SidePanel v-else @on-close="$emit('close-panel')">
      <template #side-panel-header>
        <h2 class="p-4 font-bold uppercase tracking-wide text-theme-900">
          <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" />
          <span class="text-gray-500" role="presentation">Edit</span>
        </h2>
      </template>

      <template #side-panel-content>
        <component
          :is="rawComponent"
          :category="activeCustomCategory"
          :edit="true"
          @on-category-delete="$emit('on-category-delete', $event)"
          @on-custom-category-update="$emit('on-custom-category-update')"
          @on-close="$emit('close-panel')"
        />
      </template>
    </SidePanel>
  </transition>
</template>

<script>
export default {
  name: "CategorySidePanel",
  props: {
    component: {
      type: String,
      default: "",
    },
    categories: {
      type: Array,
      default: () => [],
    },
    activeCategory: {
      type: Array,
      required: true,
    },
    activeCustomCategory: {
      type: Object,
      required: true,
    },
    rawComponent: {
      type: [Object, Function],
      required: true,
    },
  },
  emits: ["on-category-delete", "on-custom-category-update", "close-panel"],
};
</script>
