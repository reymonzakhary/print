<template>
  <ul class="flex-1 space-y-2 overflow-y-auto">
    <ProductFinderOrderModalPreviewProducersListItem
      v-for="(producer, idx) in producers"
      :key="producer.external_id || idx"
      :producer="producer"
      :is-expanded="expandedProducerIds.has(producer.category.id)"
      :is-selected="
        selectedProducerOption && selectedProducerOption.producerId === producer.external_id
      "
      :is-cheapest="isCheapestProducer(producer.external_id)"
      :is-quickest="isQuickestProducer(producer.external_id)"
      :selected-price="
        selectedProducerOption?.producerId === producer.external_id
          ? selectedProducerOption.price
          : ''
      "
      :selected-delivery-time="
        selectedProducerOption?.producerId === producer.external_id
          ? selectedProducerOption.deliveryTime
          : ''
      "
      @toggle="$emit('toggle-producer', producer.category.id)"
    >
      <div v-if="producer.prices && producer.prices.length > 0">
        <ProductFinderOrderModalPreviewProducersListItemDelivery
          v-for="priceOpt in getPriceOptionsFormatted(producer)"
          :key="priceOpt.id"
          :option="priceOpt"
          :is-selected="selectedProducerOption && selectedProducerOption.id === priceOpt.id"
          @select="$emit('select-option', priceOpt)"
        />
      </div>
      <div
        v-else
        class="rounded-lg bg-gray-50 p-3 text-center text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400"
      >
        {{ $t("No price options available for this producer.") }}
      </div>
    </ProductFinderOrderModalPreviewProducersListItem>
  </ul>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const props = defineProps({
  producers: {
    type: Array,
    default: () => [],
  },
  expandedProducerIds: {
    type: Set,
    default: () => new Set(),
  },
  selectedProducerOption: {
    type: Object,
    default: null,
  },
  priceOptions: {
    type: Array,
    default: () => [],
  },
  cheapestOption: {
    type: Object,
    default: null,
  },
  quickestOption: {
    type: Object,
    default: null,
  },
});

defineEmits(["toggle-producer", "select-option"]);

const getPriceOptionsFormatted = (producer) => {
  return producer.prices?.map((priceOpt) => ({
    id:
      priceOpt.id ||
      `${producer.external_id}-${priceOpt.dlv?.actual_days}-${priceOpt.selling_price_ex}`,
    producerId: producer.external_id,
    producerName: producer.name,
    producerLogo: producer.logo,
    price: priceOpt.display_selling_price_ex,
    priceNumeric: parseFloat(
      String(priceOpt.selling_price_ex)
        .replace(/[^0-9.,]/g, "")
        .replace(",", "."),
    ),
    pricePerUnit: priceOpt.display_ppp || null,
    deliveryTime: parseInt(priceOpt.dlv?.actual_days, 10),
    deliveryDay: priceOpt.dlv?.day,
    deliveryDayName: priceOpt.dlv?.day_name,
    deliveryMonth: priceOpt.dlv?.month,
    originalPriceData: priceOpt,
    originalProducerData: { ...(({ prices, ...rest }) => rest)(producer) },
  }));
};
const { t: $t } = useI18n();

// Helper to check if a producer (by ID) offers the overall cheapest/quickest option
const isCheapestProducer = (producerId) => {
  if (!props.cheapestOption) return false;
  return props.cheapestOption.producerId === producerId;
};

const isQuickestProducer = (producerId) => {
  if (!props.quickestOption) return false;
  return props.quickestOption.producerId === producerId;
};

// Helper to get price options for a specific producer
const getPriceOptionsForProducer = (producerId) => {
  return props.priceOptions.filter((opt) => opt.producerId === producerId);
};
</script>
