<template>
  <div class="grid grid-cols-[11rem_auto] gap-x-6">
    <ProductFinderUICategoryImage :image="image" :title="title" :loading="loading" size="full" />
    <div>
      <!-- Product Information in Two Columns -->
      <div class="mb-4 rounded p-2">
        <div class="grid grid-cols-2 gap-x-6 gap-y-3">
          <ProductFinderOrderModalTitle :title="title" />
          <div class="flex items-center gap-2">
            <font-awesome-icon :icon="['fal', 'box']" class="text-theme-500" />
            <span class="text-sm font-bold text-black dark:text-white">
              {{ quantity ? `${quantity} ${$t("pcs")}` : "N/A" }}
            </span>
          </div>
          <!-- <div class="flex items-center gap-2">
            <font-awesome-icon :icon="['fal', 'truck']" class="text-theme-500" />
            <span class="text-sm font-bold text-black dark:text-white">
              {{
                selectedDeliveryTime
                  ? `${selectedDeliveryTime} ${$t("days")}`
                  : $t("Select producer")
              }}
            </span>
          </div> -->

          <Separator class="col-span-2" />

          <ProductFinderOrderModalBoopsList class="col-span-2" :product="productProperties" />
        </div>
      </div>

      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  image: {
    type: String,
    default: null,
  },
  title: {
    type: String,
    default: "",
  },
  loading: {
    type: Boolean,
    default: false,
  },
  quantity: {
    type: [Number, String],
    default: null,
  },
  selectedDeliveryTime: {
    type: [Number, String],
    default: null,
  },
  productProperties: {
    type: Object,
    default: () => ({}),
  },
});

// Import functions
const { getDisplayName } = useDisplayName();
const { t: $t } = useI18n();
</script>
