<template>
  <div v-if="permissions.includes('members-read')" class="p-4">
    <header class="col-span-full flex flex-wrap items-center justify-between pb-4">
      <button class="text-theme-500" @click="navigateTo(`/customers?type=private`)">
        <font-awesome-icon :icon="['fal', 'chevron-left']" />
        <span class="has-text-weight-normal capitalize">
          {{ $t("back") }}
        </span>
      </button>

      <p class="text-lg">
        <font-awesome-icon :icon="['fal', 'user-gear']" />
        {{ $t("Customer details") }} -
        <b>
          {{ memberFullName }}
        </b>
      </p>

      <button
        class="card-header-icon ml-auto hidden sm:ml-0 md:block"
        @click="navigateTo(`/customers?type=private/`)"
      >
        <font-awesome-icon :icon="['fad', 'circle-xmark']" />
      </button>
    </header>

    <section class="grid h-full grid-cols-1 gap-4 md:grid-cols-4">
      <aside class="sticky top-4 col-span-2 flex flex-col gap-4 xl:col-span-1">
        <section>
          <UserProfile
            v-if="isFetchingMemberProfile || selectedMember"
            :has-view-permission="true"
            :user-id="selectedMember?.id"
            :profile="selectedMember?.profile"
            :show-edit="true"
            :is-loading="isFetchingMemberProfile"
            @profile-updated="() => fetchMember(selectedMember.id)"
          />
          <span v-else>
            {{ $t("You do not have permission to view profiles") }}
          </span>
        </section>
        <section>
          <UICard
            v-if="permissions.includes('members-update') || permissions.includes('members-delete')"
            rounded-full
          >
            <div class="flex flex-wrap gap-2 p-2">
              <UserFormModal
                v-if="showCustomerFormModal"
                :user="selectedMember"
                @close-modal="showCustomerFormModal = false"
                @update-user="handleUpdateUser"
              />

              <UIButton
                v-if="permissions.includes('members-update')"
                class="flex-grow outline outline-1 outline-theme-300"
                variant="default"
                :icon="['fal', 'pencil']"
                @click="showCustomerFormModal = true"
              >
                {{ $t("Edit private") }}
              </UIButton>

              <UIButton
                v-if="
                  selectedMember &&
                  !selectedMember.email_verified_at &&
                  permissions.includes('members-update')
                "
                class="flex-grow outline outline-1 outline-gray-300"
                variant="inverted-neutral"
                :icon="['fal', 'paper-plane']"
                @click="handleResendVerification"
              >
                {{ $t("Resend verification") }}
              </UIButton>
              <UIButton
                v-if="permissions.includes('members-delete')"
                class="flex-grow outline outline-1 outline-red-300"
                variant="inverted-danger"
                :icon="['fal', 'trash']"
                @click="showUserRemoveModal = true"
              >
                {{ $t("Delete private") }}
              </UIButton>
              <UIButton
                v-if="permissions.includes('members-update')"
                v-tooltip="$t('Coming soon')"
                class="flex-grow outline outline-1 outline-green-300"
                variant="inverted-success"
                :icon="['fal', 'user-plus']"
                disabled
                @click="handleSendPassword"
              >
                {{ $t("Send password") }}
              </UIButton>
              <RemoveUserModal
                v-if="showUserRemoveModal"
                @confirm-delete="handleUserDelete"
                @close-modal="showUserRemoveModal = false"
              />
            </div>
          </UICard>
        </section>
        <section>
          <UserInfo
            v-if="
              (isFetchingMemberProfile || selectedMember) && permissions.includes('members-update')
            "
            :is-loading="isFetchingMemberProfile"
            :user="selectedMember"
            @user-updated="fetchMember(selectedMember.id)"
          />
        </section>
      </aside>

      <main class="sticky top-4 col-span-2 xl:col-span-3">
        <UICardHeader class="max-h-[42px]">
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
                v-if="true"
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
            <UIButton
              v-if="
                activeTab === 'addresses' &&
                hasPermissionGroup(newPermissions.members.groups.addressCreate)
              "
              :icon="['fal', 'plus']"
              @click="showAddressFormModal = true"
            >
              {{ $t("Create Address") }}
            </UIButton>
          </template>
        </UICardHeader>
        <BusinessAddressList
          v-if="activeTab === 'addresses'"
          class="mt-2 w-full lg:w-1/2 2xl:w-1/3"
          :selected-member="selectedMember"
          :addresses="addresses.filter((address) => !address.team_address)"
          :show-address-form-modal="showAddressFormModal"
          @create-address="handleAddressCreate"
          @update-address="handleAddressUpdate"
          @delete-address="handleAddressDelete"
          @close-address-form-modal="showAddressFormModal = false"
        />
      </main>
    </section>
  </div>
</template>

<script setup>
const { permissions } = storeToRefs(useAuthStore());
const route = useRoute();
const router = useRouter();
const privates = usePrivateRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { t: $t } = useI18n();
const { confirm } = useConfirmation();
const { permissions: newPermissions, hasPermissionGroup } = usePermissions();

provide("endpoint", "members");

const activeTab = ref(route.query.tab || "addresses");
const isFetchingMemberProfile = ref(false);
const selectedMember = ref(null);
const addresses = ref([]);
const showAddressFormModal = ref(false);
const showUserRemoveModal = ref(false);
const showCustomerFormModal = ref(false);

const privateId = computed(() => route.params.id);

const memberFullName = computed(() => {
  return selectedMember.value
    ? `${selectedMember.value.profile.first_name} ${selectedMember.value.profile.last_name}`
    : "";
});

onMounted(() => fetchMember(route.params.id));

async function fetchMember(id) {
  try {
    isFetchingMemberProfile.value = true;
    selectedMember.value = null;

    const thePrivate = await privates.getById(id);
    selectedMember.value = thePrivate;
    isFetchingMemberProfile.value = false;
  } catch (err) {
    if (err.cancelled) return;
    handleError(err);
  }
}

watch(
  activeTab,
  async (newValue) => {
    if (newValue === "addresses") {
      const memberAddresses = await privates.getAddresses(privateId.value);
      addresses.value = memberAddresses.filter((address) => address.team_address === false);
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

async function handleUpdateUser({ user, profile }) {
  try {
    const [updatedUser, updatedProfile] = await Promise.all([
      privates.update(route.params.id, user),
      privates.updateProfile(route.params.id, profile),
    ]);
    addToast({
      type: "success",
      message: $t("Private updated successfully"),
    });
    showCustomerFormModal.value = false;
    selectedMember.value = {
      ...updatedUser,
      profile: updatedProfile,
    };
  } catch (error) {
    handleError(error);
  }
}

async function handleUserDelete() {
  try {
    await privates.remove(route.params.id);
    addToast({
      type: "success",
      message: $t("Private deleted successfully"),
    });
    navigateTo("/customers?tab=privates");
  } catch (error) {
    handleError(error);
  }
}

async function handleResendVerification() {
  try {
    await privates.resendVerification(route.params.id);
    addToast({
      type: "success",
      message: $t("Verification mail sent!"),
    });
  } catch (error) {
    handleError(error);
  }
}

async function handleSendPassword() {
  try {
    await confirm({
      title: $t("Send password"),
      message: $t("Are you sure you want to send a password to the private?"),
    });
    await privates.sendPassword(route.params.id);
    addToast({
      type: "success",
      message: $t("Password sent successfully"),
    });
  } catch (error) {
    handleError(error);
  }
}

const handleAddressUpdate = ({ address, newAddress }) =>
  (addresses.value = addresses.value.map((addr) => (addr.id === address.id ? newAddress : addr)));

const handleAddressDelete = (deletedAddress) =>
  (addresses.value = addresses.value.filter((address) => address.id !== deletedAddress.id));

const handleAddressCreate = (newAddress) => (addresses.value = [...addresses.value, newAddress]);
</script>
