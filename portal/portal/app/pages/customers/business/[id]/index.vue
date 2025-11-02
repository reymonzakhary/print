<template>
  <div class="p-4 h-full grid gap-4 customer-layout">
    <aside class="flex flex-col gap-4">
      <section>
        <BusinessInformation v-if="!isLoading" :company="company" />
      </section>
      <section>
        <BusinessContact v-if="!isLoading" :contact="contact" />
      </section>
    </aside>
    <main>
      <UICardHeader>
        <template #left>
          <div class="flex">
            <UICardHeaderTab
              v-tooltip="{ content: $t('Coming soon') }"
              :label="$t('Quotations')"
              :active="activeTab === 'quotations'"
              disabled
              @click="
                () => {
                  activeTab = 'quotations';
                }
              "
            />
            <UICardHeaderTab
              v-tooltip="{ content: $t('Coming soon') }"
              :label="$t('Orders')"
              :active="activeTab === 'orders'"
              disabled
              @click="
                () => {
                  activeTab = 'orders';
                }
              "
            />
            <UICardHeaderTab
              :label="$t('Employees')"
              :active="activeTab === 'employees'"
              @click="
                () => {
                  activeTab = 'employees';
                }
              "
            />
            <UICardHeaderTab
              v-tooltip="{ content: $t('Coming soon') }"
              :label="$t('Deals')"
              :active="activeTab === 'deals'"
              disabled
              @click="
                () => {
                  activeTab = 'deals';
                }
              "
            />
            <UICardHeaderTab
              :label="$t('Teams')"
              :active="activeTab === 'teams'"
              @click="
                () => {
                  activeTab = 'teams';
                }
              "
            />
            <UICardHeaderTab
              :label="$t('Addresses')"
              :active="activeTab === 'addresses'"
              @click="
                () => {
                  activeTab = 'addresses';
                }
              "
            />
          </div>
        </template>
        <template #right>
          <UIButton v-if="activeTab === 'employees'" :icon="['fal', 'plus']">{{
            $t("Create Employee")
          }}</UIButton>
          <UIButton v-if="activeTab === 'teams'" :icon="['fal', 'plus']">{{
            $t("Create Team")
          }}</UIButton>
          <UIButton v-if="activeTab === 'addresses'" :icon="['fal', 'plus']">{{
            $t("Create Address")
          }}</UIButton>
        </template>
      </UICardHeader>
      <section>
        <BusinessEmployeeList
          v-if="activeTab === 'employees'"
          :business-id="company.id"
          :employees="employees"
        />
        <article v-if="activeTab === 'deals'">{{ $t("Deals") }}</article>
        <BusinessTeamList
          v-if="activeTab === 'teams'"
          :business-id="company.id"
          :teams="teams"
        />
        <BusinessAddressList
          v-if="activeTab === 'addresses'"
          :addresses="addresses"
          class="mt-2"
        />
        <article v-if="activeTab === 'quotations'">
          {{ $t("Quotations") }}
        </article>
        <article v-if="activeTab === 'orders'">{{ $t("Orders") }}</article>
      </section>
    </main>
  </div>
</template>

<script setup>
const route = useRoute();
const router = useRouter();
const companies = useCompanyRepository();
const { handleError } = useMessageHandler();

const isLoading = ref(true);
const company = ref({});
const contact = ref();
const activeTab = ref(route.query.tab || "employees");
const employees = ref([]);
const teams = ref([]);
const addresses = ref([]);

try {
  const { contact: companyContact, ...companyData } = await companies.getById(
    route.params.id,
  );
  company.value = companyData;
  contact.value = companyContact;
} catch (err) {
  handleError(err);
} finally {
  isLoading.value = false;
}

watch(
  activeTab,
  async (newValue) => {
    if (newValue === "employees") {
      employees.value = await companies.getEmployees(route.params.id);
    } else if (newValue === "deals") {
      // Code to execute when activeTab is 'deals'
    } else if (newValue === "teams") {
      teams.value = await companies.getTeams(route.params.id);
    } else if (newValue === "addresses") {
      addresses.value = await companies.getAddresses(route.params.id);
    } else if (newValue === "quotations") {
      // Code to execute when activeTab is 'quotations'
    } else if (newValue === "orders") {
      // Code to execute when activeTab is 'orders'
    }
    await router.push({ query: { ...route.query, tab: newValue } });
  },
  { immediate: true },
);

onBeforeRouteUpdate((to, from, next) => {
  if (to.query.tab !== from.query.tab) {
    activeTab.value = to.query.tab || "employees";
  }
  next();
});
</script>

<style scoped>
.customer-layout {
  grid-template-columns: 1fr 3fr;
  grid-template-rows: 1fr;
  grid-template-areas: "sidebar main";
}
</style>
