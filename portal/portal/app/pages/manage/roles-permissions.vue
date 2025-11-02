<template>
  <div class="container relative flex h-full p-4">
    <div class="h-full w-[28rem] overflow-y-auto">
      <RolesList
        :loading="status === 'pending'"
        :roles="roles"
        :selected-role="selectedRole"
        @select-role="selectedRole = $event"
        @add-role="addRole"
        @remove-role="showRemoveRole = $event"
        @edit-role="showEditRole = $event"
        @copy-role="copyRole($event)"
        @save-roles-order="saveRolesOrder"
      />
    </div>

    <div class="h-full w-full">
      <transition name="fade">
        <Permissions
          v-if="selectedRole"
          :selected-role="selectedRole"
          @close="selectedRole = null"
          @update="refresh"
        />
      </transition>
    </div>

    <RemoveRole
      v-if="showRemoveRole && permissions.includes('acl-roles-delete')"
      :role="selectedRole"
      @close="showRemoveRole = false"
      @remove-role="removeRole"
    />
    <EditRole
      v-if="showEditRole && permissions.includes('acl-roles-update')"
      :role="selectedRole"
      @update-role="updateRole"
      @close="showEditRole = false"
    />
  </div>
</template>

<script setup>
const { $display_name } = useNuxtApp();
const { t: $t } = useI18n();
const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();
const { confirm } = useConfirmation();
const { permissions } = storeToRefs(useAuthStore());

useHead({ title: `${$t("roles permissions")} | Prindustry Manager` });

const selectedRole = ref(null);
const showRemoveRole = ref(false);
const showEditRole = ref(false);

const {
  data: roles,
  status,
  refresh,
} = useLazyAPI("acl/roles", {
  transform: (data) => data.data,
  default: () => [],
});

// Add a new role
async function addRole() {
  if (!roles.value) return;

  const payload = {
    name: roles.value.find((role) => role.name === "new-role")
      ? `new-role-${Math.floor(Math.random() * 9999)}`
      : "new-role",
    display_name: `New role ${Math.floor(Math.random() * 9999)}`,
    description: "New roles description",
  };

  try {
    const response = await api.post("acl/roles", payload);
    await refresh();
    selectedRole.value = response.data;
  } catch (error) {
    handleError(error);
  }
}

async function copyRole(roleId) {
  const role = localRoles.value.find((role) => role.id === roleId);
  if (!role) return;

  try {
    // Show confirmation dialog
    await confirm({
      title: $t("Copy Role"),
      message: $t("Are you sure you want to copy the role {role}?", {
        role: $display_name(role.display_name),
      }),
      confirmOptions: {
        confirmText: $t("Copy"),
        cancelText: $t("Cancel"),
      },
    });

    // Create a new role with similar name
    const newRoleName = `${role.name}-copy-${Math.floor(Math.random() * 9999)}`;
    const newDisplayName = `${$display_name(role.display_name)} (Copy)`;

    // Determine the sort order for the new role (right after the original role)
    const originalSortOrder = getSortOrderFromDescription(
      role.description,
      localRoles.value.indexOf(role),
    );
    const newSortOrder = originalSortOrder + SORT_INTERVAL / 2; // Place between roles

    const payload = {
      name: newRoleName,
      display_name: newDisplayName,
      description: updateDescriptionWithSortOrder("Copied role", newSortOrder),
    };

    // Create the new role
    const response = await api.post("acl/roles", payload);
    const newRole = response.data;

    // If the original role has permissions, copy them to the new role
    if (role.permissions && role.permissions.length > 0) {
      const permissionIds = role.permissions.map((permission) => permission.id);

      await api.post(`acl/roles/${newRole.id}`, {
        permissions: permissionIds,
      });
    }

    // Refresh roles list and select the new role
    await refresh();
    selectedRole.value = newRole;

    handleSuccess({ message: $t("Role copied successfully") });
  } catch (error) {
    if (error.cancelled) {
      // User cancelled the operation
      return;
    }
    handleError(error);
  }
}

async function removeRole(roleId) {
  try {
    await api.delete(`acl/roles/${roleId}`);
    await refresh();
    handleSuccess({ message: $t("Role removed successfully") });
  } catch (error) {
    handleError(error);
  }
}

async function updateRole(role) {
  try {
    await api.put(`acl/roles/${role.id}`, role);
    await refresh();
    handleSuccess({ message: $t("Role updated successfully") });
  } catch (error) {
    handleError(error);
  }
}

function saveRolesOrder() {
  /**
   * Implement the logic to save the roles order to the backend.
   */
}
</script>

<style>
.animate-left {
  position: fixed;
  animation: send-left ease-in 0.5s;
}

@keyframes send-left {
  0% {
    right: 25vw;
    opacity: 1;
  }
  100% {
    right: 50vw;
    opacity: 0;
  }
}
.animate-right {
  position: fixed;
  animation: send-right ease-in 0.5s;
}

@keyframes send-right {
  0% {
    left: 50vw;
    opacity: 1;
  }
  100% {
    left: 75vw;
    opacity: 0;
  }
}
</style>
