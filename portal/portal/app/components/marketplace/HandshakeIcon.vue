<template>
  <div>
    <div
      v-if="producer.handshake === 'accepted' || producer.contract?.st === 320"
      class="flex items-center space-x-2 pr-2 text-base lg:justify-end"
    >
      <div
        v-if="maxDiscountSlot.type === 'percentage'"
        class="ml-2 shrink-0 -skew-x-12 rounded bg-green-100 text-green-500"
      >
        <span class="skew-x-12 px-4 text-xs font-bold">
          -{{ maxDiscountSlot.value }}
          <span class="font-mono">%</span>
        </span>
      </div>
      <div
        v-if="maxDiscountSlot.type === 'fixed'"
        class="ml-2 shrink-0 -skew-x-12 rounded bg-green-100 text-green-500"
      >
        <span class="skew-x-12 px-4 text-xs font-bold">
          -{{ formatCurrency(maxDiscountSlot.value / 1000 || 0) }}
        </span>
      </div>
      <font-awesome-icon :icon="['fal', 'handshake']" class="mr-2 text-green-500" />
    </div>

    <div
      v-else-if="
        producer.handshake === 'pending' ||
        producer.handshake === 'suspended' ||
        producer.contract?.st === 301 ||
        producer.contract?.st === 327
      "
      class="flex flex-1 items-center text-base font-bold lg:justify-end"
      :class="{
        'text-blue-500': producer.handshake === 'pending' || producer.contract?.st === 301,
        'text-amber-500': producer.handshake === 'suspended' || producer.contract?.st === 327,
      }"
    >
      <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-auto mr-2" />
    </div>

    <div
      v-else-if="producer.handshake === 'rejected' || producer.contract?.st === 319"
      class="flex flex-1 items-center text-base font-bold text-red-500 lg:justify-end"
    >
      <font-awesome-icon :icon="['fal', 'handshake-slash']" class="ml-auto mr-2" />
    </div>

    <div v-else class="w-full pr-2 text-right text-base">
      <!-- <UIButton class="!text-base"> -->
      <!-- {{ $t("Request handshake") }} -->
      <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="" />
      <!-- </UIButton> -->
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  producer: {
    type: Object,
    required: true,
  },
});

const { formatCurrency } = useMoney();

// Computed property to get the maximum discount slot
const maxDiscountSlot = computed(() => {
  const slots = props.producer.contract?.custom_fields?.contract?.discount?.general?.slots || [];
  if (slots.length === 0) return { value: 0, type: null };

  return slots.reduce((max, slot) => (slot.value > max.value ? slot : max));
});
</script>
