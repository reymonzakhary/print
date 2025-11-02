<template>
  <div class="p-2">
    <Form
      ref="createUserForm"
      autocomplete="off"
      @submit.stop="handleSubmit"
    >
      <UICard
        rounded-full
        class="flex flex-col items-center p-4 col-span-2 relative overflow-hidden min-h-[calc(100vh-8rem)] z-0"
        :class="isLoading ? 'justify-center' : ''"
      >
        <PrindustryLogo
          class="absolute -right-20 -bottom-20 text-blue-50 dark:text-gray-900 -z-10 h-[450px]"
        />
        <UILoader v-if="isLoading" />
        <template v-else>
          <div class="flex my-5">
            <header class="flex justify-between w-full">
              <div class="flex items-center mr-4 -mt-4">
                <UICardHeaderTitle v-if="isEditing" title="Update tenant" :icon="['fas', 'edit']" />
                <UICardHeaderTitle v-else title="Create tenant" :icon="['fal', 'user-plus']" />
                <FontAwesomeIcon
                  v-tooltip="'Please fill all fields'"
                  :icon="['fal', 'chevron-right']"
                  class="ml-4 text-2xl"
                />
              </div>

              <div
                v-for="(tab, i) in tabs"
                :key="tab"
                class="px-6 py-2 mb-5 flex items-center border-b-2 cursor-pointer transition-color duration-300"
                :class="
                  activeTab == tab
                    ? checkAppear(tab)
                      ? 'border-red-500 border-b-2'
                      : 'border-theme-500 border-b-2'
                    : ''
                "
                @click="activeTab = tab"
              >
                <span :class="activeTab == tab && checkAppear(tab) ? 'text-red-500' : ''">
                  <span
                    class="inline-block w-5 mr-2 text-sm text-center h-5 rounded-full bg-blue-400 text-white"
                  >
                    {{ i + 1 }}
                  </span>
                  {{ tab }}
                </span>
                <font-awesome-icon
                  v-if="checkAppear(tab)"
                  v-tooltip="'please fill all fields'"
                  :icon="['fal', 'circle-exclamation']"
                  class="ml-1 text-red-500 text-sm"
                />
              </div>
            </header>
          </div>
          <tenant-personal-info
            v-if="activeTab == 'Personal'"
            :tenant="tenant"
            :countries="countries"
            :temp-phone-object="tempPhoneObject"
            :init-supplier-flag="initSupplierFlag"
            :is-editing="isEditing"
            class="max-w-max"
            @update:temp-phone-object="tempPhoneObject = $event"
          />
          <tenant-company-info
            v-if="activeTab == 'Company'"
            :tenant="tenant"
            class="max-w-max"
            :is-editing="isEditing"
            :currencies="currencies"
            @update:image="newFile = $event"
          />
          <tenant-system-info
            v-if="activeTab == 'Tenant'"
            :tenant="tenant"
            class="max-w-max"
            :is-editing="isEditing"
          />
          <div v-if="activeTab == 'Contract'">
            <tenant-contract-info
              :tenant="tenant"
              :countries="countries"
              :currencies="currencies"
              :exchange-arr="exchangeArr"
            />
          </div>
          <div v-if="activeTab == 'Configure'" class="w-8/12">
            <label
              for="configure"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              Configure Json:
            </label>
            <UITextArea
              v-model="tenant.external_configure"
              name="Configure"
              label="Configure"
              :rows="18"
              placeholder="Configure"
              class="mb-2 w-full !whitespace-normal"
            />
          </div>
          <AddressAutoComplete
            v-if="activeTab == 'Address'"
            class="bg-white"
            :api-key="'AIzaSyBOp0kmoa4L7L1pMxHs5C9ScnX7xS75J9c'"
            :country-code="'eg'"
            :initial-value="tenant"
            :language="'en'"
            :errors="{}"
            @update:address="updateAddressData"
          />
          <div class="flex justify-center w-full py-3">
            <UIModalButton
              v-if="activeTab !== 'Address'"
              class="!text-xl"
              variant="blue"
              :disabled="loading"
              @click.prevent="nextTab(activeTab)"
            >
              Next
              <FontAwesomeIcon :icon="['fal', 'arrow-right']" class="ml-2" />
            </UIModalButton>
            <UIModalButton
              v-if="activeTab === 'Address'"
              class="!text-2xl"
              variant="success"
              :disabled="loading"
              @click="handleSubmit"
            >
              <FontAwesomeIcon :icon="['fal', 'check']" class="mr-2" />
              {{ isEditing ? "Update Tenant" : "Create Tenant" }}
            </UIModalButton>
          </div>
        </template>
      </UICard>
    </Form>
  </div>
</template>

<script setup>
import * as yup from "yup";
import AddressAutoComplete from "../../components/tenant/AddressAutoComplete.vue";
import { ref } from "vue";
import { createFormData } from "~/composables/createFormdata.js";
import TenantCompanyInfo from "~/components/tenant/tenantCompanyInfo.vue";
// import OperationZones from "~/components/tenant/modal/operationZones.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const props = defineProps({
  isEditing: {
    type: Boolean,
    default: () => false,
  },
});

const newFile = ref(null);

const tabs = computed(() => {
  if (tenant.value.is_external) {
    return ["Personal", "Company", "Tenant", "Contract", "Configure", "Address"];
  } else if (tenant.value.supplier) {
    return ["Personal", "Company", "Tenant", "Contract", "Address"];
  } else {
    return ["Personal", "Company", "Tenant", "Address"];
  }
});

const activeTab = ref("Personal");
const exchangeArr = ref([
  {
    id: 1,
    from: "",
    rate: null,
  },
]);

const tempPhoneObject = ref({ dial_code: "", phone: "" });

const checkValidator = () => {
  return (
    tenant.value.company_name === "" ||
    (tenant.value.supplier ? tenant.value.company_coc === "" : false) ||
    tenant.value.tax_nr === "" ||
    tenant.value.email === "" ||
    (!props.isEditing
      ? tenant.value.password === "" || tenant.value.password_confirmation === ""
      : false) ||
    (tenant.value.supplier
      ? tenant.value.contract?.payment_terms === "" ||
        tenant.value.contract?.currency === "" ||
        tenant.value.contract?.runs?.length === 0 ||
        tenant.value.operation_countries.length === 0
      : false) ||
    tenant.value.address === "" ||
    tenant.value.number === "" ||
    tenant.value.city === "" ||
    tenant.value.zip_code === "" ||
    tenant.value.country_id === ""
  );
};
const nextTab = (currentTab) => {
  if (checkAppear(currentTab)) {
    return;
  }
  const currentIndex = tabs.value.indexOf(currentTab);
  if (currentIndex < tabs.value.length - 1) {
    activeTab.value = tabs.value[currentIndex + 1];
  }
};
const checkAppear = (name) => {
  if (name === "Personal") {
    return (
      (tenant.value.gender === "" ||
        tenant.value.first_name === "" ||
        tenant.value.last_name === "") &&
      showErrors.value
    );
  } else if (name === "Company") {
    return (
      (tenant.value.company_name === "" ||
        (tenant.value.supplier ? tenant.value.company_coc === "" : false) ||
        tenant.value.tax_nr === "") &&
      showErrors.value
    );
  } else if (name === "Tenant") {
    return (
      (tenant.value.email === "" ||
        tenant.value.password === "" ||
        tenant.value.password_confirmation === "") &&
      showErrors.value
    );
  } else if (name === "Contract") {
    return (
      (tenant.value.contract?.payment_terms === "" ||
        tenant.value.contract?.currency === "" ||
        tenant.value.contract?.runs?.length === 0 ||
        tenant.value.operation_countries.length === 0) &&
      showErrors.value
    );
  } else if (name === "Address") {
    return (
      (tenant.value.address === "" ||
        tenant.value.number === "" ||
        tenant.value.city === "" ||
        tenant.value.zip_code === "" ||
        tenant.value.country_id === "") &&
      showErrors.value
    );
  }
};

const tenantRepository = useTenantRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { $api } = useNuxtApp();

const loading = ref(false);
const createUserForm = ref(null);

const tenant = ref({
  gender: "male",
  first_name: "",
  last_name: "",
  company_name: "",
  fqdn: "",
  email: "",
  password: "",
  password_confirmation: "",
  is_external: false,
  external_configure: "",
  logo: null,
  address: "",
  number: "",
  city: "",
  zip_code: "",
  country_id: "",
  country_name: "",
  region: "",
  lat: "",
  lng: "",
  format_address: "",
  supplier: false,
  floor: "",
  can_request_quotation: false,
  company_coc: "",
  tax_nr: "",
  phone: "",
  dial_code: "",
  apartment: "",
  neighborhood: "",
  currency: "",
  manager_language: "",
  landmark: "",
  contract: {
    payment_terms: "",
    runs: [
      {
        id: 1,
        from: null,
        to: null,
        percentage: null,
      },
    ],
    exchange_rate: {},
  },
  operation_countries: [],
});

// const delivery_zone = ref([]);

const countries = ref([]);
const currencies = ref([]);

const getCountries = async () => {
  const { data } = await $api("/countries");
  countries.value = data;
};

const getCurrencies = async () => {
  const { data } = await $api("/currencies");
  for (const key in data) {
    currencies.value.push({
      label: `${key} ( ${data[key]} )`,
      value: key,
    });
  }
};

const updateAddressData = (data) => {
  tenant.value.address = data.street;
  tenant.value.number = data.number;
  tenant.value.city = data.city;
  tenant.value.zip_code = data.zip_code;
  tenant.value.country_id = data.country_id;
  tenant.value.country_code = data.country_code;
  tenant.value.region = data.region;
  tenant.value.lat = data.lat;
  tenant.value.lng = data.lng;
  tenant.value.format_address = data.format_address;
  tenant.value.floor = data.floor;
  tenant.value.apartment = data.apartment;
  tenant.value.neighborhood = data.neighborhood;
  tenant.value.landmark = data.landmark;
  tenant.value.country_name = data.country_name;
  if (data.country_code || data.country_name) {
    const countryId = getCountryId(data.country_code || "", data.country_name || "");

    // Use found country ID
    if (countryId !== null) {
      data.country_id = countryId;

      // Update form.country_id if phone country should match address country
      tenant.value.country_id = countryId;
    }
  }
};

// const handleUpdateZones = (data) => {
//   delivery_zone.value = data;
// };

const getCountryId = (code, name) => {
  if (countries.value.length === 0) {
    console.warn("No countries available for lookup");
    return null;
  }

  // First try to find by code (more reliable)
  const countryByCode = countries.value.find(
    (country) => country.iso2.toLowerCase() === code.toLowerCase(),
  );

  if (countryByCode) {
    return countryByCode.id;
  }

  // If not found by code, try by name (less reliable due to translations/variations)
  const countryByName = countries.value.find(
    (country) => country.name.toLowerCase() === name.toLowerCase(),
  );

  if (countryByName) {
    return countryByName.id;
  }

  console.warn(`Country not found for code: ${code}, name: ${name}`);
  return null;
};
const initSupplierFlag = ref(false);
const isLoading = ref(false);
const id = useRoute().params.id;
const getTenant = async () => {
  isLoading.value = true;
  const { data } = await $api(`/tenants/${id}`);
  tenant.value.gender = data.gender;
  tenant.value.first_name = data.name.split(" ")[0];
  tenant.value.last_name = data.name.split(" ")[1];
  tenant.value.company_name = data.company_name;
  tenant.value.email = data.email;
  tenant.value.manager_language = data?.manager_language
    ? data?.manager_language.toLowerCase()
    : "";
  tenant.value.supplier = data.supplier;
  initSupplierFlag.value = data.supplier;
  tenant.value.company_coc = data?.coc;
  tenant.value.tax_nr = data?.tax_nr;
  tenant.value.operation_countries =
    data.operation_countries && data.operation_countries.length > 0
      ? data.operation_countries.map((country) => country.id)
      : [];
  tenant.value.logo = data.logo;
  tenant.value.fqdn = data?.fqdn?.split(".")[0];
  tenant.value.address = data?.address?.address;
  tenant.value.apartment = data?.address?.apartment;
  tenant.value.city = data?.address?.city;
  tenant.value.external_configure = data.external_configure
    ? JSON.stringify(data.external_configure)
    : "";
  tenant.value.is_external = data.external;
  tenant.value.country_id = data?.address?.country_id;
  tenant.value.country_name = data?.address?.country_name;
  const contract = data.contracts.find((contract) => contract.manager_contract) ?? {};
  tenant.value.can_request_quotation = contract?.can_request_quotation ?? false;
  tenant.value.currency = data?.currency;
  if (
    contract?.custom_fields?.exchange_rate &&
    Object.keys(contract?.custom_fields?.exchange_rate).length > 0
  ) {
    exchangeArr.value = [];
    for (const key in contract?.custom_fields?.exchange_rate) {
      exchangeArr.value.push({
        id: exchangeArr.value.length + 1,
        from: key,
        rate: contract?.custom_fields?.exchange_rate[key],
      });
    }
  }
  const runs = contract?.custom_fields?.runs;
  if (runs?.length > 0) {
    for (let i = 0; i < runs.length; i++) {
      tenant.value.contract.runs[i].from = runs[i].from;
      tenant.value.contract.runs[i].to = runs[i].to;
      tenant.value.contract.runs[i].percentage = runs[i].percentage;
      tenant.value.contract.runs[i].id = i + 1;
    }
  }
  tenant.value.contract.payment_terms = contract?.custom_fields?.payment_terms;
  tenant.value.floor = data?.address?.floor;
  tenant.value.format_address = data?.address?.format_address;
  tenant.value.landmark = data?.address?.landmark;
  tenant.value.lat = data?.address?.lat;
  tenant.value.lng = data?.address?.lng;
  tenant.value.neighborhood = data?.address?.neighborhood;
  tenant.value.number = data?.address?.number;
  tenant.value.region = data?.address?.region;
  tenant.value.zip_code = data?.address?.zip_code;
  tempPhoneObject.value.dial_code = data.dial_code;
  tempPhoneObject.value.phone = data.phone;
  tenant.value.country_name = data?.address?.country_id
    ? countries.value.find((country) => country.id === data.address.country_id)?.name
    : "";
  isLoading.value = false;
  console.log(tenant.value);
};

onMounted(() => {
  getCountries();
  getCurrencies();
  if (props.isEditing) {
    getTenant();
  }
});

const showErrors = ref(false);
async function handleSubmit() {
  try {
    loading.value = true;
    exchangeArr.value
      .filter((item) => item.from !== "" && item.rate !== null)
      .forEach((item) => {
        tenant.value.contract.exchange_rate[item.from] = item.rate;
      });
    tenant.value.phone = tempPhoneObject.value.phone;
    tenant.value.dial_code = tempPhoneObject.value.dial_code;
    if (checkValidator()) {
      showErrors.value = true;
      return;
    }
    if (props.isEditing) {
      await updateTenant();
    } else {
      await createTenant();
    }
    useRouter().push("/tenants");
  } catch (error) {
    tenant.value.external_configure = JSON.stringify(tenant.value.external_configure);
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function createTenant() {
  if (tenant.value.external_configure) {
    tenant.value.external_configure = JSON.parse(tenant.value.external_configure);
  } else {
    delete tenant.value.external_configure;
  }
  const finalTenantData = {
    ...tenant.value,
  };
  if (!finalTenantData.supplier) {
    delete finalTenantData.contract;
  }
  const data = createFormData(finalTenantData);

  await tenantRepository.createTenant(data);
  // if (res && tenant.value.supplier) {
  //   await attachZones(res);
  // }
  addToast({
    message: "Creating tenant. You will be notified once the tenant has been successfully created.",
    type: "info",
  });
}

async function updateTenant() {
  if (!tenant.value.supplier && !initSupplierFlag.value) {
    delete tenant.value.contract;
  }
  if (tenant.value.external_configure) {
    tenant.value.external_configure = JSON.parse(tenant.value.external_configure);
  } else {
    delete tenant.value.external_configure;
  }
  const data = createFormData(tenant.value);
  data.delete("logo");
  if (newFile.value) {
    data.append("logo", newFile.value);
  }
  await tenantRepository.updateTenant(id, data);
  addToast({
    message: "Tenant updated successfully",
    type: "success",
  });
}

// const attachZones = async (data) => {
//   try {
//     const response = await $api(`/tenants/${data.data.id}/delivery-zones`, {
//       method: "POST",
//       body: { delivery_zones: delivery_zone.value },
//     });
//     console.log(response);
//   } catch (e) {
//     console.error(e);
//   }
// };
</script>
