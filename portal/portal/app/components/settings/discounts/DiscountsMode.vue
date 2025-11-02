<template>
  <div class="mt-4 w-full">
    <p class="text-xs font-bold uppercase tracking-wide">
      {{ $t("mode") }}
    </p>
    <div class="my-2 flex">
      <button
        class="rounded-l border px-2 py-1 dark:border-gray-800 dark:bg-gray-900"
        :class="{
          'border-theme-700 bg-theme-400 text-themecontrast-400 hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
            discount.mode === 'run',
          'bg-white hover:bg-gray-100': discount.mode === 'price',
        }"
        @click="updateMode('run')"
      >
        <font-awesome-icon v-if="discount.mode === 'run'" :icon="['fal', 'check']" />
        {{ $t("discount on runs") }}
      </button>

      <button
        class="rounded-r border px-2 py-1 dark:border-gray-800 dark:bg-gray-900"
        :class="{
          'border-theme-700 bg-theme-400 text-themecontrast-400 hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
            discount.mode === 'price',
          'bg-white hover:bg-gray-100': discount.mode === 'run',
        }"
        @click="updateMode('price')"
      >
        <font-awesome-icon v-if="discount.mode === 'price'" :icon="['fal', 'check']" />
        {{ $t("discount on price") }}
      </button>
    </div>
  </div>
</template>

<script>
import discountsMixin from "~/components/settings/discounts/discountsMixin";

export default {
  mixins: [discountsMixin],
  props: {
    discount: {
      type: Object,
      required: true,
    },
  },
};
</script>
