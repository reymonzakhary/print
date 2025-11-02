<template>
  <section class="">
    <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("contract information") }}</h3>
    <div class="my-4 grid grid-cols-3 gap-4">
      <template
        v-for="run in getSpecificContract(dynamicTenant.contracts)?.custom_fields?.runs || []"
        :key="run"
      >
        <span class="col-span-3 sm:col-span-1">
          <label
            class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
            for="contract-from"
          >
            {{ $t("from") }}
          </label>
          <UIInputText
            name="tenant_fqdn"
            :placeholder="'Form'"
            disabled
            affix="€"
            :model-value="run.from"
          />
        </span>

        <span class="col-span-2 sm:col-span-1">
          <label
            class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
            for="name"
          >
            {{ $t("to") }}
          </label>
          <UIInputText
            name="tenant_fqdn"
            :placeholder="'to'"
            disabled
            affix="€"
            :model-value="run.to"
          />
        </span>

        <span class="col-span-2 sm:col-span-1">
          <label
            class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
            for="name"
          >
            {{ $t("percentage") }}
          </label>
          <UIInputText
            name="tenant_fqdn"
            :placeholder="'to'"
            disabled
            affix="%"
            :model-value="run.percentage"
          />
        </span>
      </template>
    </div>
    <div class="my-4 grid grid-cols-2 gap-4">
      <span class="col-span-3 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="contract-from"
        >
          {{ $t("payment terms") }}
        </label>
        <UIInputText
          name="tenant_payment_terms"
          :placeholder="'Payment Terms'"
          disabled
          :model-value="
            (getSpecificContract(dynamicTenant.contracts)?.custom_fields?.payment_terms || 'N/A') +
            ' days'
          "
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

const getSpecificContract = (contracts) => {
  return (
    contracts.find((contract) => {
      return contract.manager_contract;
    }) || { custom_fields: { runs: [], payment_terms: 0 } }
  );
};
</script>

<style lang="scss" scoped></style>
