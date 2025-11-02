<template>
  <header class="flex flex-wrap items-center justify-between w-full text-sm mt-4 pb-1">
    <h2 class="flex mt-4 text-sm font-bold tracking-wide uppercase">
      <span class="text-gray-500 mr-2 font-normal">{{ $t('printing method') }}</span> {{ printing_method.name }}
    </h2>

    <button
      class="px-2 py-1 ml-2 text-right text-red-500 transition bg-red-100 rounded-full hover:bg-red-200 dark:hover:bg-red-900"
      @click="$parent.removePrintingMethod(index)"
    >
      <font-awesome-icon :icon="['fal', 'trash-can']" />
    </button>
  </header>
</template>

<script>
export default {
  name: "CategoryQuantitiesHeader",
  props: {
    printing_method: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      show_dlv_days: true,
    };
  },
};
</script>

<style></style>
