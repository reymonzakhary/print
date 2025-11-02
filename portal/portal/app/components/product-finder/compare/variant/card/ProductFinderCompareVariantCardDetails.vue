<template>
  <div>
    <ProductFinderCompareVariantCardDetailsBest
      class="mx-1 p-4"
      :variant="variant"
      :best-price="bestPrice"
      :best-delivery-time="bestDeliveryTime"
      @select-delivery="emit('select-delivery', $event)"
      @select-price="emit('select-price', $event)"
      @close-details="emit('close-details')"
    />
    <Transition name="slide">
      <ProductFinderCompareVariantCardDetailsProducers
        v-if="showProducers"
        :producers="producers"
        class="mx-2"
      />
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const emit = defineEmits(["select-delivery", "select-price", "close-details"]);

const props = defineProps({
  variant: {
    type: Object,
    required: true,
  },
  bestPrice: {
    type: Object,
    required: true,
  },
  bestDeliveryTime: {
    type: Object,
    required: true,
  },
  producers: {
    type: Array,
    default: () => [],
  },
  showDetails: {
    type: Boolean,
    default: false,
  },
});

const showProducers = ref(false);

watch(
  () => props.showDetails,
  (newValue) => {
    // Handle showDetails change
    setTimeout(() => {
      showProducers.value = newValue;
    }, 100);
  },
  { immediate: true },
);
</script>
