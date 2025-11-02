<template>
  <div
    v-if="canAccessPermissions"
    class="absolute left-0 top-0 h-full w-full space-y-3 overflow-y-auto bg-gray-100 px-4 lg:relative"
  >
    <PermissionsHeader
      v-if="canReadRoles"
      class="sticky top-0 z-10"
      :selected-role="selectedRole"
      :has-changes="hasChanges"
      :can-update="canUpdateAcl"
      @save="saveRolePermissions"
      @close="emit('close')"
    />

    <!-- Search permissions by namespace name -->
    <div class="sticky top-10 z-10 bg-gray-100">
      <ProductFinderSearchInput v-model="searchQuery" :placeholder="$t('Search namespace')" />
    </div>

    <div v-if="status === 'pending'" class="space-y-2">
      <SkeletonLine v-for="i in 10" :key="i" class="!h-12 w-full" />
    </div>
    <PermissionsNamespaceList v-else-if="canListAcl && visiblePermissions.length > 0">
      <PermissionsNamespaceListItem
        v-for="namespace in visiblePermissions"
        :key="`namespace_${namespace.id}`"
        :namespace="namespace"
        :filtered-perms="filteredPerms"
        :toggle-id="toggleId"
        :counted-namespaces="countedNamespaces"
        :crud="crud"
        @toggle-namespace="toggleNamespace"
        @toggle-perm="togglePerm"
        @toggle-all="toggleAll"
      />
    </PermissionsNamespaceList>
    <NoPermissions
      v-else-if="!canListAcl"
      :message="$t('You do not have access to the ACL namespaces.')"
    />
    <ZeroState v-else-if="allPermissions.length === 0" :message="$t('No permissions found.')" />
  </div>
  <NoPermissions v-else :message="$t('You do not have access to any permissions.')" />
</template>

<script setup>
const props = defineProps({
  selectedRole: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "update"]);

const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();

// Permissions
const { permissions } = storeToRefs(useAuthStore());

// Access control
const canAccessPermissions = computed(
  () => props.selectedRole.permissions && permissions.value.includes("acl-permissions-access"),
);
const canReadRoles = computed(() => permissions.value.includes("acl-roles-read"));
const canListAcl = computed(() => permissions.value.includes("acl-list"));
const canUpdateAcl = computed(() => permissions.value.includes("acl-permissions-update"));

// Permission state
const filteredPerms = ref([]);
const hasChanges = ref(false);
const toggleId = ref(false);
const countedNamespaces = ref({});
const crud = ref(["access", "list", "read", "create", "update", "delete"]);

const { data: allPermissions, status } = useAPI("acl", { transform: (data) => data.data });

// Use the fuzzy search composable
const { searchQuery, filteredResults: visiblePermissions } = useFuzzySearch(allPermissions, {
  keys: ["name"],
});

watch(allPermissions, (newValue) => {
  if (newValue) {
    newValue.forEach(countNamespacePermissions);
    setCheckedPermissions();
  }
});

// Count permissions in a namespace
const countNamespacePermissions = (namespace) => {
  // Initialize counter object for this namespace
  const counter = {
    access: { checked: [], countTotal: 0, countChecked: 0 },
    list: { checked: [], countTotal: 0, countChecked: 0 },
    read: { checked: [], countTotal: 0, countChecked: 0 },
    create: { checked: [], countTotal: 0, countChecked: 0 },
    update: { checked: [], countTotal: 0, countChecked: 0 },
    delete: { checked: [], countTotal: 0, countChecked: 0 },
  };

  // Process permissions recursively
  const processObject = (obj) => {
    for (const key in obj) {
      const value = obj[key];

      if (value !== undefined) {
        if (value && typeof value === "object") {
          processObject(value);
        } else {
          // Update counters based on permission type
          switch (value) {
            case "access":
            case "list":
            case "read":
            case "create":
            case "update":
            case "delete":
              counter[value].countTotal++;
              if (filteredPerms.value.includes(obj.id)) {
                counter[value].checked.push(obj.id);
                counter[value].countChecked++;
              }
              break;
          }
        }
      }
    }
  };

  processObject(namespace);
  countedNamespaces.value[namespace.name] = counter;
};

// Set initially checked permissions based on role
const setCheckedPermissions = () => {
  if (props.selectedRole.permissions) {
    filteredPerms.value = props.selectedRole.permissions.map((permission) => permission.id);
  } else {
    filteredPerms.value = [];
  }
};

// Watch for role selection changes
watch(
  () => props.selectedRole,
  () => {
    hasChanges.value = false;
    setCheckedPermissions();

    if (allPermissions.value) {
      allPermissions.value.forEach((namespace) => {
        countNamespacePermissions(namespace);
      });
    }
  },
  { deep: true, immediate: true },
);

// Toggle a single permission
const togglePerm = (namespace, permission) => {
  hasChanges.value = true;

  if (!filteredPerms.value.includes(permission.id)) {
    filteredPerms.value.push(permission.id);
  } else {
    const index = filteredPerms.value.findIndex((x) => x === permission.id);
    filteredPerms.value.splice(index, 1);
  }

  countNamespacePermissions(namespace);
};

// Toggle all permissions of a specific type in a namespace
const toggleAll = (namespace, type, state) => {
  allPermissions.value.forEach((nspace) => {
    if (nspace.name === namespace.name) {
      for (const key in nspace.area) {
        if (Object.hasOwnProperty.call(nspace.area, key)) {
          const area = nspace.area[key];

          for (const key in area) {
            if (Object.hasOwnProperty.call(area, key)) {
              const permission = area[key];

              if (permission.display_name === type) {
                hasChanges.value = true;

                if (state === "check") {
                  if (!filteredPerms.value.includes(permission.id)) {
                    filteredPerms.value.push(permission.id);
                  }
                } else {
                  const index = filteredPerms.value.findIndex((x) => x === permission.id);
                  if (index !== -1) {
                    filteredPerms.value.splice(index, 1);
                  }
                }
              }
            }
          }
        }
      }
    }
  });

  countNamespacePermissions(namespace);
};

// Toggle namespace expansion
const toggleNamespace = (namespaceId) => {
  toggleId.value = toggleId.value === namespaceId ? false : namespaceId;
};

// Save role permissions
const saveRolePermissions = async () => {
  try {
    const response = await api.post(`acl/roles/${props.selectedRole.id}`, {
      permissions: filteredPerms.value,
    });

    handleSuccess(response);
    hasChanges.value = false;
    emit("update");
  } catch (error) {
    handleError(error);
  }
};
</script>
