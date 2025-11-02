<template>
  <nav class="flex items-center justify-center gap-2">
    <SalesTab
      v-if="permissions.includes('orders-list')"
      data-v-step="2"
      :label="$t('drafts')"
      :icon="['fal', 'pen-to-square']"
      :active="props.activeType === 'drafts'"
      @click="emits('update:activeType', 'drafts')"
    />
    <SalesTab
      v-if="permissions.includes('orders-list')"
      data-v-step="1"
      :label="$t('orders')"
      :icon="['fal', 'file-signature']"
      :active="props.activeType === 'orders'"
      @click="emits('update:activeType', 'orders')"
    />
    <SalesTab
      v-if="permissions.includes('orders-list')"
      data-v-step="3"
      :label="$t('archive')"
      :icon="['fal', 'cabinet-filing']"
      :active="props.activeType === 'archive'"
      @click="emits('update:activeType', 'archive')"
    />
    <UIButton
      v-if="permissions.includes('settings-access')"
      v-tooltip="$t(`To ${activeType} settings`)"
      variant="default"
      class="!bg-transparent !text-base hover:!bg-theme-100"
      :icon="['fal', 'gear']"
      @click="() => navigateTo(`/manage/settings#orders`)"
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
