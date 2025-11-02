<template>
  <nav class="flex items-center justify-center gap-2">
    <SalesTab
      v-if="permissions.includes('quotations-list')"
      :label="$t('quotations')"
      :icon="['fal', 'file-signature']"
      :active="props.activeType === 'quotations'"
      @click="emits('update:activeType', 'quotations')"
    />
    <SalesTab
      v-if="permissions.includes('orders-list')"
      :label="$t('orders')"
      :icon="['fal', 'file-invoice-dollar']"
      :active="props.activeType === 'orders'"
      @click="emits('update:activeType', 'orders')"
    />
    <SalesTab
      v-if="permissions.includes('orders-list')"
      :label="$t('archive')"
      :icon="['fal', 'box-archive']"
      :active="props.activeType === 'archive'"
      @click="emits('update:activeType', 'archive')"
    />
    <UIButton
      v-if="permissions.includes('settings-access')"
      v-tooltip="$t(`To ${activeType} settings`)"
      variant="default"
      class="bg-transparent !text-base"
      :icon="['fal', 'gear']"
      @click="() => navigateTo(`/manage/settings#${props.activeType}`)"
    />
  </nav>
</template>

<script setup>
const { permissions } = storeToRefs(useAuthStore());
const props = defineProps({
  activeType: {
    type: String,
    required: true,
  },
});
const emits = defineEmits(["update:activeType"]);
</script>
