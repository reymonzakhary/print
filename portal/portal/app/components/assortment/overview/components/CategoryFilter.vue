<template>
  <form v-if="categoriesCount > 10" class="relative my-3 flex w-full" @submit.prevent>
    <label for="category-filter" class="sr-only">{{ $t("Filter categories") }}</label>
    <input
      v-model="filterValue"
      class="mx-2 w-full rounded border bg-white px-2 py-1 focus:border-theme-300 focus:outline-none focus:ring dark:border-gray-900 dark:bg-gray-700"
      type="text"
      placeholder="Filter"
      @input="updateFilter"
    />
    <font-awesome-icon class="absolute right-0 mr-4 mt-2 text-gray-600" :icon="['fal', 'filter']" />
  </form>
</template>

<script>
export default {
  name: "CategoryFilter",
  props: {
    categoriesCount: {
      type: Number,
      required: true,
    },
    modelValue: {
      type: String,
      default: "",
    },
  },
  emits: ["update:modelValue"],
  data() {
    return {
      filterValue: this.modelValue,
    };
  },
  watch: {
    modelValue(newValue) {
      this.filterValue = newValue;
    },
  },
  methods: {
    updateFilter() {
      this.$emit("update:modelValue", this.filterValue);
    },
  },
};
</script>
