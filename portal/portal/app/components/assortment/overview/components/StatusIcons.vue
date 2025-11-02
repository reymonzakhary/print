<template>
  <div class="mr-2 flex items-center">
    <!--  -->
    <font-awesome-icon
      v-tooltip="$t('this category is shared to the finder')"
      :class="
        category.shareable ? 'text-gray-500 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'
      "
      fixed-width
      class="mr-2"
      :icon="['fal', 'radar']"
    />
    <font-awesome-icon
      v-tooltip="$t('supplier category')"
      :class="
        category.ref_id ? 'text-gray-500 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'
      "
      class="mr-2"
      fixed-width
      :icon="['fal', 'parachute-box']"
    />
    <!-- Webshop Status Icon -->
    <font-awesome-icon
      v-tooltip="$t('this category is usable in webshop')"
      :class="
        category.published ? 'text-gray-500 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'
      "
      fixed-width
      :icon="['fal', 'heart-rate']"
    />
  </div>
</template>

<script>
export default {
  name: "StatusIcons",
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
};
</script>
