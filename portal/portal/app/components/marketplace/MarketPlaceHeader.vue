<template>
  <div>
    <UICardHeader class="flex md:h-[42px]" :use-tabs="true" rounded-full>
      <template #left>
        <div class="flex items-center">
          <UIPrindustryBox class="mr-2 h-8 w-8" />
          <UICardHeaderTitle :title="$t('marketplace')" />
        </div>
      </template>
      <template #center>
        <div class="mx-auto flex">
          <UICardHeaderTab
            :icon="['fal', 'radar']"
            :label="$t('product finder')"
            :active="activeTab === 'productFinder'"
            @click="activeTab = 'productFinder'"
          />
          <UICardHeaderTab
            :icon="['fal', 'parachute-box']"
            :label="$t('producer finder')"
            :active="activeTab === 'producerFinder'"
            @click="activeTab = 'producerFinder'"
          />
        </div>
      </template>

      <template #right>
        <div class="flex items-center justify-end gap-3">
          <UIButton
            v-if="
              (activeTab === 'producerFinder' &&
                permissions.includes('suppliers-settings-access')) ||
              (activeTab === 'productFinder' &&
                permissions.includes('print-assortments-categories-access'))
            "
            icon="gear"
            to="/manage/tenant-settings/producer-information"
            variant="outline"
          >
            {{ $t("settings") }}
          </UIButton>
          <ProductFinderOrder />
        </div>
      </template>
    </UICardHeader>
    <div
      v-if="activeSales"
      class="border-1 align-center flex justify-between rounded border border-blue-500 bg-blue-100 px-2 py-1 text-blue-500"
    >
      <div class="flex items-center">
        <font-awesome-icon :icon="['fad', 'circle-info']" class="mr-2" />
        {{ $t("Adding a product") }}
        <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-1 mt-0.5" />
        <strong class="mr-1">
          {{ store.activeQuotation ? $t("quotation") : $t("order") }}
        </strong>
        <div class="pb-0.5">
          <span class="rounded bg-blue-500 px-2 py-0.5 font-mono text-xs font-bold text-blue-50"
            >#{{ activeSales }}</span
          >
        </div>
      </div>
      <UIButton
        v-tooltip.bottom-end="$t('Cancel')"
        variant="link"
        class="!text-lg !text-blue-500 hover:!bg-blue-200"
        :icon="['fal', 'cancel']"
        @click="
          ((activeSales = null),
          $router.push(`/${store.activeQuotation ? 'quotations' : 'orders'}/${activeSales}`))
        "
      />
    </div>
  </div>
</template>

<script setup>
const { permissions } = storeToRefs(useAuthStore());

const store = useProductFinderStore();

const activeSales = computed(() => store.activeQuotation || store.activeOrder);

const activeTab = defineModel("activeTab", {
  type: String,
  required: true,
  validator: (value) => ["productFinder", "producerFinder"].includes(value),
});
</script>
