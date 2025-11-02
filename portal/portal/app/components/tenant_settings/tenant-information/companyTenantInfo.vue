<template>
  <section class="mb-8">
    <h3 class="mb-4 text-sm font-bold uppercase tracking-wide">{{ $t("company") }}</h3>
    <div class="mt-4 grid grid-cols-2 gap-4">
      <span class="col-span-2 mx-auto text-center">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon
            :icon="['fal', 'industry-windows']"
            fixed-width
            class="text-gray-500"
          />
          <font-awesome-icon :icon="['fal', 'copyright']" fixed-width class="text-gray-500" />
          {{ $t("company logo") }}
        </label>
        <UIImageSelector
          class="w-full"
          :selected-image="dynamicTenant.logo"
          disk="assets"
          @on-image-select="dynamicTenant.logo = $event"
          @on-image-remove="dynamicTenant.logo = ''"
        />
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon :icon="['fal', 'user']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("name") }}
        </label>
        <UIInputText
          name="tenant_name"
          :placeholder="$t('John')"
          :model-value="dynamicTenant.name"
          @update:model-value="dynamicTenant.name = $event"
        />
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="gender"
        >
          <font-awesome-icon
            :icon="['fal', 'person-half-dress']"
            fixed-width
            class="text-gray-500"
          />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("gender") }}
        </label>
        <UIVSelect
          name="tenant_gender"
          :options="genderOptions"
          :model-value="dynamicTenant.gender"
          @update:model-value="dynamicTenant.gender = $event.value"
        />
      </span>

      <!-- <span>
        <label class="text-xs font-bold tracking-wide text-gray-600 dark:text-gray-400 uppercase" for="name">
          <font-awesome-icon :icon="['fal', 'user']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("last name") }}
        </label>
        <UIInputText :placeholder="$t('Doe')" />
      </span> -->
    </div>
    <div class="mt-4 grid grid-cols-2 gap-4">
      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon :icon="['fal', 'building']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("company name") }}
        </label>
        <UIInputText
          name="tenant_company_name"
          :placeholder="$t('my company')"
          :model-value="dynamicTenant.company_name"
          @update:model-value="dynamicTenant.company_name = $event"
        />
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon
            :icon="['fal', 'industry-windows']"
            fixed-width
            class="text-gray-500"
          />
          {{ $t("Request producer status") }}
        </label>
        <NuxtLink
          v-if="!dynamicTenant.supplier"
          to="/manage/tenant-settings/producer-information"
          class="flex w-full items-center justify-center rounded-full bg-gradient-to-r from-theme-500 to-pink-600 p-2 text-sm text-white backdrop-opacity-80 transition-all hover:backdrop-opacity-100"
        >
          <font-awesome-icon :icon="['fal', 'industry-windows']" class="fa-fw bg-o mr-2" />
          {{ $t("I want to be a producer in the Marketplace") }}
        </NuxtLink>
        <div v-else class="">
          <div class="flex items-center text-green-500">
            <UIPrindustryBox line="text-green-600" class="w-5" />
            <font-awesome-icon :icon="['fas', 'handshake']" fixed-width class="mx-2" />
            <font-awesome-icon :icon="['fal', 'industry-windows']" fixed-width class="" />
          </div>
          <p class="text-sm">{{ $t("You are a producer in the marketplace") }}!</p>
        </div>
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon :icon="['fal', 'building']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("VAT number") }}
        </label>
        <UIInputText
          name="tenant_tax_number"
          :placeholder="$t('VAT-1234567890')"
          :model-value="dynamicTenant.tax_nr"
          @update:model-value="dynamicTenant.tax_nr = $event"
        />
      </span>

      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="name"
        >
          <font-awesome-icon :icon="['fal', 'building']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("chamber of commerce (optional)") }}
        </label>
        <UIInputText
          name="tenant_coc"
          :placeholder="$t('coc-1234567890')"
          :model-value="dynamicTenant.coc"
          type="text"
          pattern="[0-9]*"
          @update:model-value="dynamicTenant.coc = $event"
        />
      </span>
      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="currency"
        >
          <font-awesome-icon :icon="['far', 'money-bill']" fixed-width class="text-gray-500" />
          <font-awesome-icon :icon="['fal', 'tag']" fixed-width class="text-gray-500" />
          {{ $t("Currency") }}
        </label>
        <UIVSelect
          name="tenant_currency"
          :options="currencyOptions"
          :model-value="dynamicTenant.currency"
          @update:model-value="dynamicTenant.currency = $event.value"
        />
      </span>
      <span class="col-span-2 sm:col-span-1">
        <label
          class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
          for="currency"
        >
          {{ $t("phone") }}
        </label>
        <UIPhoneInput
          v-model="tempPhoneObject"
          name="phone"
          placeholder=""
          required
          :countries="countries"
          autocomplete="off"
        />
      </span>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  dynamicTenant: {
    type: Object,
    required: true,
  },
  countries: {
    type: Array,
    required: true,
  },
});

const { handleError } = useMessageHandler();

const tempPhoneObject = ref({
  dial_code: props.dynamicTenant.dial_code,
  phone: props.dynamicTenant.phone,
});

watch(
  tempPhoneObject,
  (newValue) => {
    props.dynamicTenant.dial_code = newValue.dial_code;
    props.dynamicTenant.phone = newValue.phone;
  },
  { deep: true },
);

const api = useAPI();
const { t: $t } = useI18n();

const genderOptions = ref([
  { label: $t("Male"), value: "male" },
  { label: $t("Female"), value: "female" },
  { label: $t("Other"), value: "other" },
]);

const currencyOptions = ref([]);

const getCurrency = async () => {
  try {
    const response = await api.get(`/currencies`);
    for (const key in response.data) {
      currencyOptions.value.push({
        label: `${key} ( ${response.data[key]} )`,
        value: key,
      });
    }
  } catch (error) {
    handleError(error);
  }
};

onMounted(() => {
  getCurrency();
});
</script>

<style lang="scss" scoped></style>
