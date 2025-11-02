<template>
  <header class="grid grid-cols-3">
    <div class="flex items-center gap-4">
      <UIButton
        :disabled="!props.prev"
        variant="link"
        class="capitalize"
        :icon="['fal', 'chevron-left']"
        @click="navigateTo(`/orders/${props.prev ?? ''}`)"
      >
        {{ $t("previous") }}
      </UIButton>
      <UIButton
        :disabled="!props.next"
        variant="link"
        class="capitalize"
        :icon="['fal', 'chevron-right']"
        icon-placement="right"
        @click="navigateTo(`/orders/${props.next ?? ''}`)"
      >
        {{ $t("next") }}
      </UIButton>
    </div>

    <div class="text-center">
      <p class="text-lg">
        <font-awesome-icon :icon="['fal', 'file-lines']" />
        {{ $t("order") }} - <b>#{{ props.orderNumber }}</b>
      </p>
    </div>

    <div class="flex justify-end">
      <UIButton
        v-tooltip="props.saving ? $t('saving') : $t('close')"
        class="!bg-gray-200 !text-base !text-gray-800 hover:!bg-gray-300"
        :icon="endIcon"
        :class="{
          'fa-spin': props.saving,
        }"
        :disabled="props.saving"
        @click="emit('on-close')"
      />
    </div>
  </header>
</template>

<script setup>
const props = defineProps({
  orderNumber: {
    type: [String, Number],
    required: true,
  },
  externalId: {
    type: [String, Number, null],
    default: null,
  },
  prev: {
    type: [String, Number, null],
    default: null,
  },
  next: {
    type: [String, Number, null],
    default: null,
  },
  saving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["on-close"]);

const endIcon = computed(() => {
  return props.saving ? ["fad", "spinner"] : ["fad", "xmark"];
});
</script>
