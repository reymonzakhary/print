<template>
  <nav class="relative flex items-center justify-center gap-2">
    <div class="absolute bottom-1.5 left-0 flex items-center gap-2">
      <StudioQuickButton to="/manage/studio/quotation" />
      <UIButton
        variant="outline"
        class="rounded-full border border-theme-500 px-2 py-1 text-xs text-theme-500 transition-colors hover:bg-theme-50"
        :icon="['fal', 'route']"
        data-step="tour-button"
        @click="$emit('start-tour')"
      >
        {{ $t("tour") }}
      </UIButton>
    </div>
    <SalesTab
      v-if="permissions.includes('quotations-list') && permissions.includes('quotations-create')"
      data-v-step="2"
      :label="$t('drafts')"
      :icon="['fal', 'pen-to-square']"
      :active="props.activeType === 'drafts'"
      @click="emits('update:activeType', 'drafts')"
    />
    <SalesTab
      v-if="permissions.includes('quotations-list')"
      data-v-step="1"
      :label="$t('quotations')"
      :icon="['fal', 'file-signature']"
      :active="props.activeType === 'quotations'"
      @click="emits('update:activeType', 'quotations')"
    />
    <SalesTab
      v-if="
        permissions.includes('quotations-trashed-access') &&
        permissions.includes('quotations-trashed-list')
      "
      data-v-step="3"
      :label="$t('bin')"
      :icon="['fal', 'trash']"
      :active="props.activeType === 'bin'"
      @click="emits('update:activeType', 'bin')"
    />
    <UIButton
      v-if="permissions.includes('settings-access')"
      v-tooltip="$t(`To quotations settings`)"
      variant="default"
      class="!bg-transparent !text-base hover:!bg-theme-100"
      :icon="['fal', 'gear']"
      data-step="quotations-settings"
      @click="() => navigateTo(`/manage/settings#quotations`)"
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
const emits = defineEmits(["update:activeType", "start-tour"]);
</script>
