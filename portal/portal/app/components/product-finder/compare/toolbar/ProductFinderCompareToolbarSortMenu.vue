<template>
  <div class="relative">
    <span v-if="title" class="mb-1.5 ml-1 block text-xs font-medium">
      <font-awesome-icon
        :icon="['fas', 'bars-sort']"
        class="mr-1.5 inline-block size-3 text-theme-500"
      />
      {{ title }}
    </span>
    <UIVSelect
      v-model="sortBy"
      :options="options"
      :clearable="false"
      :filterable="false"
      :searchable="false"
      :reduce="(option) => option.value"
      class="w-56"
      :icon="['fas', 'sort']"
      :disabled="disabled"
    />
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const sortBy = defineModel({ type: String, default: "delivery-asc" });

const { t: $t } = useI18n();

const options = [
  {
    label: $t("Delivery time: shortest first"),
    value: "delivery-asc",
    icon: ["fas", "truck-fast"],
  },
  {
    label: $t("Delivery time: longest first"),
    value: "delivery-desc",
    icon: ["fas", "truck-flatbed"],
  },
  { label: $t("Price: low to high"), value: "price-asc", icon: ["fas", "sort-amount-down"] },
  { label: $t("Price: high to low"), value: "price-desc", icon: ["fas", "sort-amount-up"] },
  {
    label: $t("Producers: least to most"),
    value: "producers-asc",
    icon: ["fas", "user"],
  },
  { label: $t("Producers: most to least"), value: "producers-desc", icon: ["fas", "users"] },
  { label: $t("Boops: least to most"), value: "boops-asc", icon: ["fas", "sort-amount-down"] },
  { label: $t("Boops: most to least"), value: "boops-desc", icon: ["fas", "sort-amount-up"] },
];
</script>
