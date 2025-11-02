<template>
  <section class="flex items-center">
    <font-awesome-icon
      v-show="isLoading"
      :class="{ 'fa-spin': isLoading }"
      class="mr-2 text-theme-500"
      :icon="['fad', 'spinner-third']"
    />

    <template v-if="assortmentFlag === 'print_product'">
      <template v-if="!category.external">
        <span v-tooltip="$t('this category has no manifest')" class="flex" role="contentinfo">
          <font-awesome-icon
            v-show="!category.has_manifest"
            class="text-amber-500"
            :icon="['fal', 'scroll-old']"
          />
          <font-awesome-icon
            v-show="!category.has_manifest"
            class="mr-2 text-xs text-amber-500"
            :icon="['fas', 'exclamation']"
          />
        </span>
        <span
          v-tooltip="$t('products not generated for this category')"
          class="flex"
          role="contentinfo"
        >
          <font-awesome-icon
            v-show="!category.has_products"
            class="text-amber-500"
            :icon="['fal', 'box-full']"
          />
          <font-awesome-icon
            v-show="!category.has_products"
            class="mr-2 text-xs text-amber-500"
            :icon="['fas', 'exclamation']"
          />
        </span>
      </template>

      <!-- Status icons -->
      <StatusIcons :category="category" />

      <!-- Calculation method -->
      <CalculationMethodIcon
        v-if="assortmentFlag === 'print_product' && !category.price_build.external_calculation"
        :category="category"
        :calc-method="calcMethod"
        :incomplete-calc-refs="incompleteCalcRefs"
        :active-category="activeCategory"
        :necessary-calc-refs="necessaryCalcRefs"
      />
    </template>

    <!-- Menu and edit buttons -->
    <section class="flex items-center">
      <span class="z-10 mr-1 w-6" role="menu">
        <client-only>
          <ItemMenu
            v-if="isActive"
            :menu-items="menuItems"
            menu-icon="ellipsis-h"
            menu-class="w-6 h-6 rounded-full hover:bg-gray-100 dark:hover:bg-black"
            dropdown-class="right-0 border w-44 dark:border-black text-theme-900"
            @item-clicked="$emit('item-clicked', $event)"
          />
        </client-only>
      </span>
      <button
        v-if="!selectingMode"
        class="mr-1 h-6 w-6"
        role="button"
        :title="categoryHasActiveWizard ? $t('Continue wizard') : $t('Edit category')"
        @click.stop.prevent="$emit('edit-item')"
      >
        <font-awesome-icon
          v-if="isActive"
          class="z-50 rounded-full p-1 transition-all hover:bg-theme-100"
          :class="categoryHasActiveWizard ? 'text-green-500' : 'text-theme-500'"
          :icon="['fal', categoryHasActiveWizard ? 'play-circle' : 'pencil']"
        />
      </button>
    </section>
  </section>
</template>

<script>
export default {
  name: "CategoryActions",
  props: {
    category: {
      type: Object,
      required: true,
    },
    assortmentFlag: {
      type: String,
      required: true,
    },
    isActive: {
      type: Boolean,
      required: true,
    },
    selectingMode: {
      type: Boolean,
      required: true,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    calcMethod: {
      type: Object,
      required: true,
    },
    menuItems: {
      type: Array,
      required: true,
    },
    incompleteCalcRefs: {
      type: Boolean,
      required: true,
    },
    activeCategory: {
      type: Array,
      required: true,
    },
    necessaryCalcRefs: {
      type: Object,
      required: true,
    },
    hasActiveWizard: {
      type: Function,
      required: false,
      default: () => false,
    },
  },
  emits: ["item-clicked", "edit-item"],
  computed: {
    categoryHasActiveWizard() {
      return this.hasActiveWizard ? this.hasActiveWizard(this.category) : false;
    },
  },
};
</script>
