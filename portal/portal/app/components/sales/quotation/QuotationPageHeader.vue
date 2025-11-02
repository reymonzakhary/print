<template>
  <header class="relative flex justify-between">
    <div class="flex items-center gap-4">
      <UIButton
        :disabled="!props.prev"
        variant="link"
        class="capitalize"
        :icon="['fal', 'chevron-left']"
        @click="navigateTo(`/quotations/${props.prev ?? ''}`)"
      >
        {{ $t("previous") }}
      </UIButton>
      <UIButton
        :disabled="!props.next"
        variant="link"
        class="capitalize"
        :icon="['fal', 'chevron-right']"
        icon-placement="right"
        @click="navigateTo(`/quotations/${props.next ?? ''}`)"
      >
        {{ $t("next") }}
      </UIButton>
    </div>

    <h1 class="absolute left-1/2 -translate-x-1/2 text-lg">
      <template v-if="isExternal">
        <font-awesome-icon :icon="['fal', 'external-link']" />
        {{ $t("external quotation") }} - <b>#{{ props.id }}&nbsp;</b>
        <small v-tooltip="$t('external ID')" class="text-bold text-gray-700">
          #{{ props.externalId }}
        </small>
      </template>
      <template v-else>
        <font-awesome-icon :icon="['fal', 'file-lines']" />
        {{ $t("quotation") }} - <b>#{{ props.id }}</b>
      </template>
    </h1>

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
  id: {
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

const { isExternal } = storeToRefs(useSalesStore());

const endIcon = computed(() => {
  return props.saving ? ["fad", "spinner"] : ["fad", "xmark"];
});
</script>
