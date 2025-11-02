<template>
  <section class="h-full p-4">
    <div class="sticky top-0 z-10 w-full">
      <UICardHeader class="max-h-[42px] backdrop-blur">
        <template #left>
          <UICardHeaderTitle :icon="['fal', 'users']" :title="$t('Customer management')" />
        </template>

        <template #center>
          <div class="flex">
            <UICardHeaderTab
              v-tooltip="$t('Coming soon')"
              disabled
              :label="$t('Businesses')"
              :active="activeType === 'business'"
              @click="activeType = 'business'"
            />
            <UICardHeaderTab
              :label="$t('Privates')"
              :active="activeType === 'private'"
              @click="activeType = 'private'"
            />
            <UICardHeaderTab
              :label="$t('trashed')"
              :active="activeType === 'trashed'"
              @click="activeType = 'trashed'"
            />
          </div>
        </template>

        <template #right>
          <UIButton
            v-if="activeType === 'private'"
            :disabled="!permissions.includes('members-create')"
            :icon="['fad', 'user-plus']"
            @click="openCreateCustomerModal"
          >
            {{ $t("Create new Customer") }}
          </UIButton>
          <UIButton
            v-else-if="activeType === 'business'"
            :icon="['fad', 'user-plus']"
            @click="() => {}"
          >
            {{ $t("Create new Company") }}
          </UIButton>
        </template>
      </UICardHeader>
      <BusinessListHeader v-if="activeType === 'business'" />
      <PrivateListHeader v-else-if="activeType === 'private'" />
      <TrashedListHeader v-else-if="activeType === 'trashed'" />
    </div>

    <TheUserListSkeleton v-if="isFetchingCustomers" />
    <div v-else>
      <div v-if="activeType === 'business'">
        <BusinessListItem
          v-for="customer in customers"
          :key="customer.id"
          :customer="customer"
          @click="handleCustomerClick(customer)"
        />
      </div>
      <div v-if="activeType === 'trashed'">
        <TrashedListItem
          v-for="customer in customers"
          :key="customer.id"
          :customer="customer"
          :get-trashed="fetchCustomers"
        />
      </div>
      <div v-else-if="permissions.includes('members-list')">
        <UserFormModal
          v-if="showCustomerFormModal"
          :user="selectedUser"
          :is-creating-customer="true"
          @close-modal="closeCustomerFormModal"
          @create-user="handleCreateUser"
          @update-user="handleUpdateUser"
        />
        <PrivateListItem
          v-for="customer in customers"
          :key="customer.id"
          :customer="customer"
          @click="handleCustomerClick(customer)"
        />
      </div>
      <div v-else-if="!permissions.includes('members-list')" class="p-4 text-center">
        {{ $t("You do not have permission to view customers") }}
      </div>
    </div>
  </section>
</template>

<script setup>
// imports
const { permissions } = storeToRefs(useAuthStore());
const route = useRoute();
const router = useRouter();
const businesses = useCompanyRepository();
const privates = usePrivateRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

// data
const activeType = ref(route.query.type || "private");
const isFetchingCustomers = ref(true);
const customers = ref([]);
const showCustomerFormModal = ref(false);
const selectedUser = ref(null);

// watch
watch(
  activeType,
  async (newValue) => {
    customers.value = null;
    await fetchCustomers(newValue);
    await router.push({ query: { ...route.query, type: newValue } });
  },
  { immediate: true },
);

// lifecycles
onBeforeRouteUpdate((to, from, next) => {
  if (to.query.tab !== from.query.tab) {
    activeTab.value = to.query.tab || "employees";
  }
  next();
});

// methods
async function fetchCustomers(type) {
  isFetchingCustomers.value = true;
  try {
    let data;
    if (type === "business") {
      data = await businesses.get();
      customers.value = data;
    } else if (type === "trashed") {
      data = await privates.getTrashedMembers();
    } else {
      data = await privates.get();
    }
    isFetchingCustomers.value = false;
    customers.value = data;
  } catch (error) {
    handleError(error);
  }
}

function handleCustomerClick(customer) {
  if (activeType.value === "business") {
    navigateTo(`/customers/business/${customer.id}`);
  } else {
    navigateTo(`/customers/private/${customer.id}`);
  }
}

function openCreateCustomerModal() {
  userID.value = null;
  showCustomerFormModal.value = true;
}

const userID = ref(null);

async function handleCreateUser({ user, address }) {
  const userData = {
    // sendpassword: true,
    email: user.email,
    first_name: user.profile.first_name,
    last_name: user.profile.last_name,
    gender: user.profile.gender,
    roles: user.roles,
    ctx_id: 2,
    teams: user.teams,
  };
  const newAddress = { ...address };
  if (address.full_name?.length === 0) {
    newAddress.full_name = user.profile.first_name + " " + user.profile.last_name;
  }
  try {
    if (!userID.value) {
      const thePrivate = await privates.create(userData);
      userID.value = thePrivate.id;
    }
    await privates.createAddress(userID.value, newAddress);
    addToast({
      type: "success",
      message: "Customer created successfully",
    });
    navigateTo(`/customers/private/${userID.value}`);
  } catch (error) {
    handleError(error);
  }
}

function handleUpdateUser(user) {
  console.log(user);
}

function closeCustomerFormModal() {
  selectedUser.value = null;
  showCustomerFormModal.value = false;
}
</script>
