<template>
  <header class="flex flex-wrap justify-between p-2 item-center">
    <UIButton
      variant="link"
      class="!text-base capitalize"
      :icon="['fal', 'chevron-left']"
      @click="navigateTo('/assortment')"
    >
      {{ $t("Back") }}
    </UIButton>

    <p class="text-lg">
      <font-awesome-icon :icon="['fal', 'box-full']" />
      {{ $t("Product details") }} -
      <b>{{ props.name }}</b>
      <UIButton
        v-if="
          permissions.includes('print-assortments-margins-access') ||
          permissions.includes('print-assortments-boxes-access') ||
          permissions.includes('print-assortments-options-access') ||
          permissions.includes('print-assortments-machines-access') ||
          permissions.includes('print-assortments-printing-methods-access') ||
          permissions.includes('print-assortments-catalogues-access') ||
          permissions.includes('print-assortments-system-catalogues-access')
        "
        v-tooltip="$t(`To assortment settings`)"
        variant="default"
        class="!bg-transparent hover:!bg-theme-100 !text-lg ml-2"
        :icon="['fal', 'gear']"
        @click="navigateTo('/assortment/settings')"
      />
    </p>

    <div class="flex justify-end">
      <UIButton
        class="!text-base !bg-gray-200 hover:!bg-gray-300 !text-gray-800 hidden ml-auto sm:ml-0 card-header-icon md:block"
        :icon="['fas', 'xmark']"
        @click="emit('on-close')"
      />
    </div>
  </header>
</template>

<script setup>
const { permissions } = storeToRefs(useAuthStore());

const props = defineProps({
  name: {
    type: [String, Number],
    required: true,
  },
});
const emit = defineEmits(["on-close"]);
</script>
