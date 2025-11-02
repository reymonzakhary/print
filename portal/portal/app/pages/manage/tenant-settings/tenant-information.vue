<template>
  <div class="flex h-full items-start gap-3 py-4 lg:w-3/4">
    <div class="max-h-full w-4/12 overflow-y-auto rounded p-2">
      <h3 class="font-semibold px-2">{{ $t("Menu") }}</h3>
      <ul class="mt-3">
        <li
          v-for="tab in tabs"
          :key="tab.name"
          class="cursor-pointer p-4 py-2"
          :class="
            activeTab === tab.name
              ? 'rounded bg-theme-50 text-theme-500'
              : 'hover:bg-gray-100 dark:hover:bg-gray-800'
          "
          @click="activeTab = tab.name"
        >
          <div class="flex items-center gap-3">
            <font-awesome-icon :icon="tab.icon" />
            <span>{{ $t(tab.name) }}</span>
          </div>
        </li>
      </ul>
    </div>
    <UICard class="max-h-full w-full p-4" rounded-full shadow-color="gray">
      <companyTenantInfo
        v-if="activeTab === 'Company info'"
        :dynamic-tenant="dynamicTenant"
        :countries="mainCountries"
      />
      <systemTenantInfo
        v-else-if="activeTab === 'Company settings'"
        :dynamic-tenant="dynamicTenant"
      />
      <contractTenantInfo v-else-if="activeTab === 'Contract'" :dynamic-tenant="dynamicTenant" />
      <countriesTenantInfo
        v-else-if="activeTab === 'Operational Countries'"
        :dynamic-tenant="dynamicTenant"
        :countries="countries"
      />
      <AddressAutoComplete
        v-if="activeTab == 'Address'"
        class="bg-white"
        :api-key="googleMapApi"
        :country-code="'eg'"
        :initial-value="dynamicTenant.address"
        :language="'en'"
        :errors="{}"
        @update:address="updateAddressData"
      />

      <section class="sticky bottom-4 my-8 text-center">
        <UIButton
          variant="success"
          class="px-4 !text-base"
          :loading="saving"
          :disabled="!dynamicTenant.name || !dynamicTenant.company_name || !dynamicTenant.email"
          @click="emit('update:tenant', dynamicTenant)"
        >
          <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
          {{ saving ? $t("Saving") : $t("Save") }}
          <font-awesome-icon v-if="saving" :icon="['fal', 'spinner-third']" fixed-width spin />
        </UIButton>
      </section>
    </UICard>
  </div>
</template>

<script setup>
const props = defineProps({
  tenant: {
    type: Object,
    required: true,
  },
  saving: {
    type: Boolean,
    required: true,
  },
});

const {
  public: { googleMapApiKey },
} = useRuntimeConfig();
const googleMapApi = googleMapApiKey;

const tabs = ref([
  {
    name: "Company info",
    icon: ["fa", "briefcase"],
  },
  {
    name: "Company settings",
    icon: ["fal", "users-crown"],
  },
  {
    name: "Address",
    icon: ["fal", "map-location-dot"],
  },
]);

const activeTab = ref("Company info");

const emit = defineEmits(["update:tenant"]);
const countries = ref([]);
const mainCountries = ref([]);

// Define your refs
const dynamicTenant = ref({});
const tenantRef = toRef(props, "tenant");
watch(
  tenantRef,
  (newVal) => {
    dynamicTenant.value = {
      ...newVal,
      address: {
        ...newVal.address,
        country_name: newVal?.address?.country_id
          ? countries.value.find((country) => country.value === newVal?.address?.country_id)?.label
          : "",
      },
    };
    if (dynamicTenant.value.supplier) {
      tabs.value = [
        {
          name: "Company info",
          icon: ["fa", "briefcase"],
        },
        {
          name: "Company settings",
          icon: ["fal", "users-crown"],
        },
        {
          name: "Contract",
          icon: ["fal", "file-contract"],
        },
        {
          name: "Operational Countries",
          icon: ["fa", "earth-americas"],
        },
        {
          name: "Address",
          icon: ["fal", "map-location-dot"],
        },
      ];
    }
  },
  { immediate: true, deep: true },
);

const api = useAPI();

const getCountries = async () => {
  try {
    const response = await api.get(`/countries`);
    dynamicTenant.value.address.country_name = dynamicTenant.value?.address?.country_id
      ? response.data.find((country) => country.id === dynamicTenant.value?.address?.country_id)
          ?.name
      : "";
    mainCountries.value = response.data;
    countries.value = response.data.map((country) => {
      return {
        label: country.name,
        value: country.id,
      };
    });
  } catch (e) {
    console.log(e);
  }
};

const updateAddressData = (data) => {
  dynamicTenant.value.address.address = data.street;
  dynamicTenant.value.address.number = data.number;
  dynamicTenant.value.address.city = data.city;
  dynamicTenant.value.address.zip_code = data.zip_code;
  dynamicTenant.value.address.country_id = data.country_id;
  dynamicTenant.value.address.country_code = data.country_code;
  dynamicTenant.value.address.region = data.region;
  dynamicTenant.value.address.lat = data.lat;
  dynamicTenant.value.address.lng = data.lng;
  dynamicTenant.value.address.format_address = data.format_address;
  dynamicTenant.value.address.floor = data.floor;
  dynamicTenant.value.address.apartment = data.apartment;
  dynamicTenant.value.address.neighborhood = data.neighborhood;
  dynamicTenant.value.address.landmark = data.landmark;
  dynamicTenant.value.address.country_name = data.country_name;
  if (data.country_code || data.country_name) {
    const countryId = getCountryId(data.country_code || "", data.country_name || "");

    // Use found country ID
    if (countryId !== null) {
      data.country_id = countryId;

      // Update form.country_id if phone country should match address country
      dynamicTenant.value.address.country_id = countryId;
    }
  }
};

const getCountryId = (code, name) => {
  if (mainCountries.value.length === 0) {
    console.warn("No countries available for lookup");
    return null;
  }

  // First try to find by code (more reliable)
  const countryByCode = mainCountries.value.find(
    (country) => country.iso2.toLowerCase() === code.toLowerCase(),
  );

  if (countryByCode) {
    return countryByCode.id;
  }

  // If not found by code, try by name (less reliable due to translations/variations)
  const countryByName = mainCountries.value.find(
    (country) => country.name.toLowerCase() === name.toLowerCase(),
  );

  if (countryByName) {
    return countryByName.id;
  }

  console.warn(`Country not found for code: ${code}, name: ${name}`);
  return null;
};

onMounted(async () => {
  await getCountries();
});
</script>
