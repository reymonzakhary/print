<template>
  <div class="w-full mt-4">
    <p class="text-xs font-bold tracking-wide uppercase">
      {{ $t("mode") }}
    </p>
    <div class="flex my-2">
      <button
        class="px-2 py-1 border rounded-l dark:bg-gray-900 dark:border-gray-800"
        :class="{
          'bg-theme-400 hover:bg-theme-500 text-themecontrast-400 border-theme-700 dark:bg-theme-400 dark:hover:bg-theme-500 dark:border-theme-600':
            margin.mode === 'run',
          ' bg-white  hover:bg-gray-100': margin.mode === 'price',
        }"
        @click="updateMode('run')"
      >
        <font-awesome-icon v-if="margin.mode === 'run'" :icon="['fal', 'check']" />
        {{ $t("margin on runs") }}
      </button>

      <button
        class="px-2 py-1 border rounded-r dark:bg-gray-900 dark:border-gray-800"
        :class="{
          'bg-theme-400 hover:bg-theme-500 text-themecontrast-400 border-theme-700 dark:bg-theme-400 dark:hover:bg-theme-500 dark:border-theme-600':
            margin.mode === 'price',
          'bg-white  hover:bg-gray-100': margin.mode === 'run',
        }"
        @click="updateMode('price')"
      >
        <font-awesome-icon v-if="margin.mode === 'price'" :icon="['fal', 'check']" />
        {{ $t("margin on price") }}
      </button>
    </div>
  </div>
</template>

<script>
import marginsMixin from "~/components/settings/margins/marginsMixin";

export default {
  mixins: [marginsMixin],
  props: {
    margin: {
      type: Object,
      required: true,
    },
  },
};
</script>
