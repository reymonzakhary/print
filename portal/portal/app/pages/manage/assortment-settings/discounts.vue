<template>
  <div class="flex">
    <assortment-settings-nav class="hidden w-1/5 md:block" />
    <div class="w-full p-4">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('General Margins')" class="mr-4" />
        </template>
      </UICardHeader>
      <main class="mt-4">
        <DiscountsPage />
      </main>
    </div>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();
</script>
