<template>
  <header class="sticky top-0 z-40 bg-white dark:bg-gray-700">
    <div
      class="flex items-center justify-between border-b px-2 py-1 text-sm font-bold uppercase tracking-wide dark:border-gray-900"
    >
      <nuxt-link
        v-if="hasPermission('cart-access') && cartFlag === 'add'"
        to="assortment/add-categories"
        class="text-sm font-normal text-theme-500 transition-colors duration-75 hover:text-theme-700 dark:text-theme-200 dark:hover:text-theme-500"
        @click="
          $emit('set-cart-flag', 'view');
          $router.push('/cart');
        "
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
        {{ $t("back") }}
      </nuxt-link>

      {{ $t("categories") }}

      <section class="flex space-x-4">
        <nuxt-link
          v-if="
            (hasPermission('print-assortments-categories-create') && cartFlag === 'view') ||
            hasPermission('custom-assortments-categories-create')
          "
          :to="'/assortment/add-products'"
          class="text-sm font-normal normal-case text-theme-500 transition-colors duration-75 hover:text-theme-700 dark:text-theme-300 dark:hover:text-theme-500"
          @click="$emit('activate-modal')"
        >
          <font-awesome-icon :icon="['fal', 'plus']" class="" />
          {{ assortmentFlag === "print_product" ? $t("print product") : $t("custom product") }}
        </nuxt-link>
      </section>
    </div>
  </header>
</template>

<script>
export default {
  name: "CategoryHeader",
  props: {
    cartFlag: {
      type: String,
      required: true,
    },
    assortmentFlag: {
      type: String,
      required: true,
    },
  },
  emits: ["set-cart-flag", "activate-modal"],
  setup() {
    const { hasPermission } = usePermissions();
    return { hasPermission };
  },
};
</script>
