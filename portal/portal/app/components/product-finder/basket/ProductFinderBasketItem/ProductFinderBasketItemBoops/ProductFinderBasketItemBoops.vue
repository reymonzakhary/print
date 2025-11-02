<template>
  <section class="overflow-hidden">
    <!-- Boops Available -->
    <ul v-if="product && Object.keys(product).length > 0" class="grid grid-cols-2 gap-x-6 gap-y-3">
      <ProductFinderBasketItemBoopsItem
        v-for="(boopValue, boopKey) in product"
        :key="boopValue.key || boopKey"
        :label="getDisplayName(boopValue.key_display_name || boopValue.key)"
        :value="getDisplayName(boopValue.value_display_name || boopValue.value)"
      />
    </ul>

    <!-- No boops available -->
    <ul v-else>
      <ProductFinderBasketItemBoopsItem :label="$t('Details')" :value="$t('Not available')" />
    </ul>
  </section>
</template>

<script setup>
defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const { getDisplayName } = useDisplayName();
</script>
