<template>
  <div class="h-full p-4">
    <UICardHeader class="sticky top-0 z-10 flex md:h-[42px]" :use-tabs="true" rounded-full>
      <template #left>
        <div class="flex items-center">
          <UICardHeaderTitle :icon="['fal', 'crown']" :title="$t('tenant settings')" />
        </div>
      </template>
      <template #center>
        <div class="mx-auto flex flex-wrap justify-evenly">
          <UICardHeaderTab
            :icon="['fal', 'user-crown']"
            :label="$t('tenant information')"
            :active="activeTab === 'tenantInformation'"
            @click="
              ((activeTab = 'tenantInformation'),
              $router.push({ path: '/manage/tenant-settings/tenant-information' }))
            "
          />
          <UICardHeaderTab
            :icon="['fal', 'parachute-box']"
            :label="$t('producer settings')"
            :active="activeTab === 'producerSettings'"
            @click="
              ((activeTab = 'producerSettings'),
              $router.push({ path: '/manage/tenant-settings/producer-information' }))
            "
          />
          <UICardHeaderTab
            v-if="tenant.supplier"
            :icon="['fal', 'handshake']"
            :label="$t('handshakes')"
            :active="activeTab === 'producerContracts'"
            @click="
              ((activeTab = 'producerContracts'),
              $router.push({ path: '/manage/tenant-settings/producer-contracts' }))
            "
          />
          <UICardHeaderTab
            :icon="['fal', 'address-card']"
            :label="$t('tenant addresses')"
            :active="activeTab === 'addresses'"
            @click="
              ((activeTab = 'addresses'),
              $router.push({ path: '/manage/tenant-settings/tenant-addresses' }))
            "
          />
        </div>
      </template>
    </UICardHeader>

    <UIListSkeleton
      v-if="loading"
      :key="'skeleton1'"
      :skeleton-line-height="24"
      :skeleton-line-amount="1"
      class="h-24 w-full"
    />

    <!-- render child pages -->
    <NuxtPage
      v-else
      :page-key="(route) => route.fullPath"
      :me="me"
      :tenant="tenant"
      :saving="saving"
      class="relative z-0"
      @update:tenant="updateTenant"
    />
  </div>
</template>

<script setup>
const { t: $t } = useI18n();
const { updateTenantSettings, fetchTenantSettings, fetchExternalProducerConfig } =
  useTenantRepository();
const { handleSuccess, handleError } = useMessageHandler();

const activeTab = ref("tenantInformation");
const tenant = ref({});
const loading = ref(false);
const saving = ref(false);

// vuex state
const { theUser: me } = storeToRefs(useAuthStore());

async function setTenantSettings() {
  loading.value = true;
  const response = await fetchTenantSettings();
  tenant.value = response.data;
  if (tenant.value.external) {
    const externalConfig = await fetchExternalProducerConfig();
    tenant.value = { ...tenant.value, producerConfig: externalConfig.data };
  }
  loading.value = false;
}

onMounted(async () => {
  const path = window.location.pathname;
  if (path.includes("tenant-information")) {
    activeTab.value = "tenantInformation";
  }
  if (path.includes("producer-information")) {
    activeTab.value = "producerSettings";
  }
  if (path.includes("tenant-addresses")) {
    activeTab.value = "addresses";
  }
  if (path.includes("producer-contracts")) {
    activeTab.value = "producerContracts";
  }

  loading.value = true;

  fetchTenantSettings()
    .then((response) => {
      tenant.value = response.data;
      if (tenant.value.external) {
        fetchExternalProducerConfig()
          .then((response) => {
            tenant.value = { ...tenant.value, producerConfig: response.data };
            console.log(response.data);
          })
          .catch((error) => {
            handleError(error);
          });
      }
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      loading.value = false;
    });
  await setTenantSettings();

  if (Object.keys(me).length > 0) {
    loading.value = false;
  }
});

async function updateTenant(dynamicTenant) {
  try {
    saving.value = true;
    const updatedTenant = {
      ...dynamicTenant,
      operation_countries: dynamicTenant.operation_countries.map((country) => country.id)  || [],
    };
    if (updatedTenant.coc.length === 0) delete updatedTenant.coc;
    const response = await updateTenantSettings(updatedTenant);
    handleSuccess(response);
    setTenantSettings();
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}
</script>
