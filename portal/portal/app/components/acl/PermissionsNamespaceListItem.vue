<template>
  <div
    class="w-full rounded bg-white px-4 py-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
  >
    <!-- NAMESPACE HEADER -->
    <div class="flex w-full flex-wrap items-center justify-between">
      <h2
        v-if="canListPermissions"
        class="w-full text-sm font-bold uppercase tracking-wide sm:w-2/6"
      >
        <font-awesome-icon
          v-if="namespace.icon"
          :icon="['fal', namespace.icon]"
          class="fa-fw text-theme-500"
        />
        {{ namespace.name }}
      </h2>

      <!-- CRUD TOGGLES -->
      <div class="flex w-5/6 items-center justify-between sm:w-3/6">
        <PermissionToggle
          v-for="item in crud"
          :key="item"
          :namespace="namespace"
          :item="item"
          :counted-namespaces="countedNamespaces"
          @toggle-all="$emit('toggle-all', namespace, item, $event)"
        />
      </div>

      <!-- EXPAND/COLLAPSE BUTTON -->
      <div
        v-if="canReadPermissions"
        class="flex w-1/6 justify-end"
        @click="$emit('toggle-namespace', namespace.id)"
      >
        <button
          class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-black"
        >
          <font-awesome-icon :icon="['fal', 'caret-down']" />
        </button>
      </div>
    </div>

    <!-- DETAILED PERMISSIONS -->
    <transition name="slide">
      <div
        v-show="toggleId === namespace.id"
        v-if="canReadNamespaces"
        class="divide-y dark:divide-black"
      >
        <PermissionArea
          v-for="(perms, area, i) in namespace.area"
          :key="area + i"
          :area="area"
          :perms="perms"
          :filtered-perms="filteredPerms"
          :can-update="canUpdatePermissions"
          @toggle-perm="$emit('toggle-perm', namespace, $event)"
        />
      </div>
    </transition>
  </div>
</template>

<script setup>
defineProps({
  namespace: {
    type: Object,
    required: true,
  },
  filteredPerms: {
    type: Array,
    default: () => [],
  },
  toggleId: {
    type: [Boolean, String, Number],
    default: false,
  },
  countedNamespaces: {
    type: Object,
    required: true,
  },
  crud: {
    type: Array,
    default: () => ["access", "list", "read", "create", "update", "delete"],
  },
});

defineEmits(["toggle-namespace", "toggle-perm", "toggle-all"]);

// Get permissions from composable
const { permissions } = storeToRefs(useAuthStore());

// Permission checks
const canListPermissions = computed(() => permissions.value.includes("acl-permissions-list"));
const canReadPermissions = computed(() => permissions.value.includes("acl-permissions-read"));
const canUpdatePermissions = computed(() => permissions.value.includes("acl-permissions-update"));
const canReadNamespaces = computed(() => permissions.value.includes("namespaces-read"));
</script>
