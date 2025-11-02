<template>
  <div>
    <UILoader v-if="loading" />
    <UISwitchList v-else>
      <UISwitchListItem
        v-for="(role, index) in roles"
        :key="'user_role_' + index"
        :name="$display_name(role.display_name)"
        :label="$display_name(role.display_name)"
        :model-value="selectedRoles.some((sR) => sR.id === role.id)"
        :disabled="user && isSameUser && role.id === 1"
        @update:model-value="updateUserRoles(role)"
      />
    </UISwitchList>
  </div>
</template>

<script setup>
const props = defineProps({
  user: {
    type: [Object, undefined],
    default: undefined,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emits = defineEmits(["roles-changed"]);

const authStore = useAuthStore();

const { data: roles } = await useLazyAPI("/acl/roles", {
  transform: ({ data }) => data,
  default: () => [],
});
const loading = computed(() => roles.value.length === 0);

const selectedRoles = ref([]);
watch(
  roles,
  (newRoles) => {
    if (newRoles.length > 0 && props.user) {
      if (props.user?.roles[0]?.id) {
        const userRoleIds = props.user.roles.map((userRole) => userRole.id);
        selectedRoles.value = newRoles.filter((role) => userRoleIds.includes(role.id));
      } else {
        selectedRoles.value = newRoles.filter((role) => props.user.roles.includes(role.name));
      }
    }
  },
  { immediate: true },
);
const isSameUser = computed(() => props.user && props.user.id === authStore.theUser.id);
watch(selectedRoles, (newVal) => emits("roles-changed", newVal), { deep: true });

function updateUserRoles(role) {
  if (selectedRoles.value.some((sR) => sR.id === role.id)) {
    selectedRoles.value = selectedRoles.value.filter((selectedRole) => selectedRole.id !== role.id);
  } else {
    selectedRoles.value.push(role);
  }
}
</script>
