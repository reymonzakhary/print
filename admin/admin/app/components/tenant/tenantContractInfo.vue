<template>
  <div>
    <fieldset class="flex flex-col gap-4 p-4 rounded-md border bg-white">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Contract Information</legend>
      <p class="italic text-gray-600">
        Select the price tiers and the percentages that should be paid on them
      </p>

      <div v-for="(run, index) in tenant.contract.runs" :key="run.id" class="flex items-end gap-3">
        <div class="px-1 w-4/12">
          <label
            :for="`run-from-${run.id}`"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            From:
          </label>
          <span class="flex items-center">
            <span class="bg-gray-100 rounded-l p-1 border border-r-0">€</span>
            <UIInputText
              v-model="run.from"
              class="rounded-none rounded-r"
              :name="`run-from-${run.id}`"
              placeholder=""
              required
              :disabled="isSupplier"
              type="number"
              autocomplete="off"
            />
          </span>
        </div>
        <div class="px-1 w-4/12">
          <label
            :for="`run-to-${run.id}`"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            To:
          </label>
          <span class="flex items-center">
            <span class="bg-gray-100 rounded-l p-1 border border-r-0">€</span>
            <UIInputText
              v-model="run.to"
              class="rounded-none rounded-r"
              :name="`run-to-${run.id}`"
              placeholder=""
              required
              :disabled="isSupplier"
              type="number"
              autocomplete="off"
            />
          </span>
        </div>
        <div class="px-1 w-4/12">
          <label
            :for="`run-percenage-${run.id}`"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Percentage:
          </label>
          <span class="flex items-center">
            <UIInputText
              v-model="run.percentage"
              class="rounded-none rounded-l"
              :name="`run-percenage-${run.id}`"
              placeholder=""
              required
              :disabled="isSupplier"
              type="number"
              autocomplete="off"
            />
            <span class="bg-gray-100 rounded-r p-1 border border-l-0">%</span>
          </span>
        </div>
        <div
          v-if="tenant.contract.runs.length > 1 && index !== tenant.contract.runs.length - 1"
          class="text-red-500 mb-2"
          @click="removeRun(index)"
        >
          <font-awesome-icon :icon="['fal', 'trash']" />
        </div>
        <div class="flex items-end">
          <div
            v-if="index === tenant.contract.runs.length - 1"
            class="p-2 bg-theme-500 cursor-pointer text-white rounded text-sm"
            @click.stop="addRun"
          >
            <font-awesome-icon :icon="['fal', 'plus']" />
          </div>
        </div>
      </div>
    </fieldset>
    <fieldset class="flex flex-col gap-4 p-4 mt-3 rounded-md border bg-white">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Sales Information</legend>
      <div>
        <label
          for="payment-terms"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          <FontAwesomeIcon :icon="['fal', 'calendar']" class="mr-2" />
          Payment Terms:
        </label>
        <UISelector
          v-model="tenant.contract.payment_terms"
          name="payment-terms"
          :disabled="isSupplier"
          :options="options"
        />
      </div>
      <div class="col-span-2">
        <label
          for="zones"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Operation Zones:
        </label>
        <MultiSelect
          v-model="props.tenant.operation_countries"
          :options="countries"
          name="zones"
          display-property="name"
          option-value="id"
        />
      </div>
      <div class="">
        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">
          Can request quotation
        </label>
        <div class="flex relative items-center">
          No
          <div
            class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
            :class="[tenant.can_request_quotation ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="is-supplier"
              class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
              :class="[
                tenant.can_request_quotation
                  ? 'translate-x-6 border-theme-500'
                  : 'translate-x-0 border-gray-300',
              ]"
            />
            <input
              id="is-supplier"
              v-model="tenant.can_request_quotation"
              type="checkbox"
              name="toggle"
              class="w-full h-full appearance-none active:outline-none focus:outline-none"
              @click="tenant.can_request_quotation = !tenant.can_request_quotation"
            />
          </div>
          Yes
        </div>
      </div>
    </fieldset>
    <fieldset
      v-tooltip="!tenant.currency && 'Please select a currency first'"
      class="flex flex-col gap-4 p-4 mt-3 rounded-md border"
      :class="!tenant.currency ? 'bg-gray-300 cursor-not-allowed' : 'bg-white'"
    >
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Currency Exchange</legend>
      <p class="italic text-gray-600">
        Select the exchange rates that should be used for the currency
        <span v-if="tenant.currency" class="px-2 py-1 bg-theme-500 rounded text-white">{{
          tenant.currency ? getCurrencySign(tenant.currency) : ""
        }}</span>
      </p>

      <div v-for="(exchange, index) in exchangeArr" :key="exchange.id" class="flex items-end gap-3">
        <div class="px-1 w-4/12">
          <label
            :for="`exchange from ${exchange.id}`"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            From:
          </label>
          <span class="flex items-center">
            <UISelector
              v-model="exchange.from"
              :name="`exchange from ${exchange.id}`"
              :options="localCurrencies"
              :disabled="!tenant.currency"
            />
          </span>
        </div>
        <div class="px-1 w-4/12">
          <label
            :for="`exchange-rate-${exchange.id}`"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Percentage:
          </label>
          <span class="flex items-center">
            <UIInputText
              v-model="exchange.rate"
              class="rounded-none rounded-l"
              :name="`exchange-rate-${exchange.id}`"
              placeholder=""
              required
              :disabled="!tenant.currency"
              type="number"
              step="0.1"
              autocomplete="off"
            />
          </span>
        </div>
        <div v-if="exchangeArr.length > 1" class="text-red-500 mb-2" @click="removeExchange(index)">
          <font-awesome-icon :icon="['fal', 'trash']" />
        </div>
        <div class="flex items-end">
          <div
            v-if="index === exchangeArr.length - 1"
            class="p-2 bg-theme-500 cursor-pointer text-white rounded text-sm"
            @click.stop="addExchange"
          >
            <font-awesome-icon :icon="['fal', 'plus']" />
          </div>
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import MultiSelect from "~/components/global/ui/MultiSelect.vue";

const props = defineProps({
  tenant: Object,
  currencies: Array,
  countries: Array,
  exchangeArr: Array,
  isSupplier: {
    type: Boolean,
    default: false,
  },
});

const getCurrencySign = (currency) => {
  return props.currencies.find((c) => c.value === currency)?.label || "";
};
const localCurrencies = ref([...props.currencies]);
localCurrencies.value.splice(
  localCurrencies.value.findIndex((c) => c.value === props.tenant.currency),
  1,
);
const options = ref([
  {
    label: "15 days",
    value: 15,
  },
  {
    label: "30 days",
    value: 30,
  },
  {
    label: "60 days",
    value: 60,
  },
]);

const addRun = () => {
  props.tenant.contract.runs.push({
    id: props.tenant.contract.runs[props.tenant.contract.runs.length - 1].id + 1,
    from: null,
    to: null,
    percenage: null,
  });
};

const addExchange = () => {
  if (!props.tenant.currency) return;
  props.exchangeArr.push({
    id: props.exchangeArr[props.exchangeArr.length - 1].id + 1,
    from: null,
    rate: null,
  });
};

const removeRun = (index) => {
  props.tenant.contract.runs.splice(index, 1);
};

const removeExchange = (index) => {
  props.exchangeArr.splice(index, 1);
};
</script>

<style scoped></style>
