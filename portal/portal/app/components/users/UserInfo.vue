<template>
  <UICard class="grid grid-cols-1 p-4 py-4 text-xs lg:grid-cols-3">
    <EditRolesModal
      v-if="updatingRoles"
      :user="user"
      @roles-updated="$emit('user-updated')"
      @close-modal="updatingRoles = false"
    />

    <UserAccessList
      v-if="permissions.includes('acl-roles-list')"
      :title="$t('Role(s)')"
      :items-icon="['fal', 'tag']"
      :items="user ? user.roles : null"
      :is-loading="isLoading"
      @edit-click="updatingRoles = true"
    />

    <EditTeamsModal
      v-if="updatingTeams"
      :user="user"
      @teams-updated="$emit('user-updated')"
      @close-modal="updatingTeams = false"
    />

    <UserAccessList
      v-if="permissions.includes('teams-list')"
      :title="$t('Teams')"
      :items-icon="['fal', 'users']"
      :items="user ? user.teams : null"
      class="py-4 lg:px-4 lg:py-0"
      :is-loading="isLoading"
      @edit-click="updatingTeams = true"
    />

    <EditContextsModal
      v-if="updatingContexts"
      :user="user"
      @contexts-updated="$emit('user-updated')"
      @close-modal="updatingContexts = false"
    />

    <UserAccessList
      v-if="permissions.includes('contexts-list')"
      :title="$t('Contexts')"
      :items-icon="['fal', 'users']"
      :items="user ? user.ctx : null"
      :is-loading="isLoading"
      @edit-click="updatingContexts = true"
    />
  </UICard>
</template>

<script>
export default {
  name: "UserInfo",
  props: ["user", "isLoading"],
  emits: ["user-updated"],
  setup() {
    const authStore = useAuthStore();
    return {
      permissions: authStore.permissions,
    };
  },
  data() {
    return {
      updatingRoles: false,
      updatingTeams: false,
      updatingContexts: false,
    };
  },
};
</script>
