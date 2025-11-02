<template>
  <section class="">
    <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("system") }}</h3>
    <div class="my-4 grid grid-cols-2 gap-4">
      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="live-domain"
        >
          <font-awesome-icon :icon="['fal', 'earth-europe']" fixed-width class="text-gray-500" />
          {{ $t("live domain") }}
        </label>
        <UIInputText
          name="tenant_fqdn"
          :placeholder="'mycompany'"
          disabled
          prefix="https://"
          affix="prindustry.io"
          :model-value="dynamicTenant?.fqdn?.split('.')[0]"
        />
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon :icon="['fal', 'at']" fixed-width class="text-gray-500" />
          {{ $t("email") }}
        </label>
        <UIInputText
          v-model="dynamicTenant.email"
          name="tenant_email"
          type="email"
          :placeholder="$t('john.doe@company.com')"
        />
      </span>
      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="language"
        >
          <font-awesome-icon :icon="['far', 'money-bill']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("language") }}
        </label>
        <UIVSelect
          name="tenant_language"
          :options="languages"
          :model-value="dynamicTenant.manager_language"
          :get-option-label="
            (option) => {
              return languages.find((l) => l.value === option)?.label;
            }
          "
          @update:model-value="dynamicTenant.manager_language = $event.value"
        />
      </span>
    </div>
  </section>
</template>

<script setup>
defineProps({
  dynamicTenant: {
    type: Object,
    required: true,
  },
});

const languages = ref([
  {
    label: "English ( en )",
    value: "EN",
  },
  {
    label: "French ( fr )",
    value: "FR",
  },
  {
    label: "Netherlands ( nl )",
    value: "NL",
  },
  {
    label: "German ( de )",
    value: "DE",
  },
]);
</script>

<style lang="scss" scoped></style>
