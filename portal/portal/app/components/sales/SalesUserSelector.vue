<template>
  <div>
    <UserFormModal
      v-if="showUserFormModal"
      is-creating-customer
      @create-user="handleCreateUser"
      @close-modal="showUserFormModal = false"
    />
    <div class="mb-1 flex items-center justify-between">
      <p class="text-xs font-bold uppercase tracking-wide">
        {{ $t("for") }}
      </p>
      <UIButton
        v-if="editable && !isHaveExternalItem && hasPermissionGroup(permissions.members.groups.memberCreate)"
        :icon="['fal', 'plus']"
        variant="link"
        @click="showUserFormModal = true"
      >
        {{ $t("new customer") }}
      </UIButton>
    </div>
    <UIVSelect
      v-if="editable && !isHaveExternalItem && hasPermissionGroup(permissions.quotations.groups.userUpdate)"
      v-model="modelValue"
      :options="users"
      :loading="loading"
      :icon="['fal', 'user']"
      :placeholder="$t('select a customer')"
      label="name"
    >
      <template #option="{ name, email }">
        <span class="mb-2 block">
          <b class="block">{{ name }}</b>
          <small>{{ email }}</small>
        </span>
      </template>
    </UIVSelect>
    <div
      v-if="!modelValue && !hasPermissionGroup(permissions.quotations.groups.userUpdate)"
      class="-mt-1 rounded-b bg-gray-200 p-2 pt-3 text-xs dark:bg-gray-900"
    >
      <NoPermissions :message="$t('You have no permission to choose a customer.')" size="sm" />
    </div>
    <div
      v-else-if="modelValue && Object.keys(modelValue).length"
      class="-mt-1 rounded-b bg-gray-200 p-2 pt-3 text-xs dark:bg-gray-900"
    >
      <ul>
        <li>
          <b>#{{ modelValue.id }}</b> {{ modelValue.name }}
        </li>
        <li>
          <font-awesome-icon :icon="['fal', 'envelope']" />
          {{ modelValue.email }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  users: {
    type: Array,
    required: true,
  },
  onlyShow: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["user-created"]);

const modelValue = defineModel({ type: [Object, Number, null] });

const { permissions, hasPermissionGroup } = usePermissions();
const privateRepositoy = usePrivateRepository();

const { isEditable, isHaveExternalItem } = storeToRefs(useSalesStore());
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

const showUserFormModal = ref(false);

const loading = computed(() => !props.users.length);
const editable = computed(() => isEditable.value && !props.onlyShow);

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
  try {
    const thePrivate = await privateRepositoy.create(userData);
    if (address.full_name?.length === 0) {
      address.full_name = userData.first_name + " " + userData.last_name;
    }
    await privateRepositoy.createAddress(thePrivate.id, address);
    emit("user-created", thePrivate);
    addToast({
      type: "success",
      message: "customer created successfully",
    });
    showUserFormModal.value = false;
  } catch (error) {
    handleError(error);
  }
}
</script>
