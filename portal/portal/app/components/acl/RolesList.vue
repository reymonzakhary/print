<template>
  <UICardHeader :background-color="false" class="mb-3">
    <template #left>
      <UICardHeaderTitle :title="$t('roles')" :icon="['fal', 'folder-tree']" />
    </template>
    <template #right>
      <div class="flex items-center">
        <button
          v-if="permissions.includes('acl-roles-create')"
          class="rounded px-3 text-sm uppercase text-theme-500 hover:bg-theme-50"
          @click="emit('add-role')"
        >
          <font-awesome-icon :icon="['fal', 'plus']" />
          {{ $t("new") }}
        </button>
        <button
          v-if="permissions.includes('acl-roles-update')"
          v-tooltip="$t('Coming soon')"
          class="rounded px-3 text-sm uppercase text-gray-500"
          disabled
          @click="false && (sortMode = !sortMode)"
        >
          <font-awesome-icon :icon="['fal', sortMode ? 'check' : 'arrows-up-down']" />
          {{ sortMode ? $t("done") : $t("reorder") }}
        </button>
      </div>
    </template>
  </UICardHeader>

  <section v-if="permissions.includes('roles-list')">
    <template v-if="loading">
      <SkeletonLine v-for="i in 10" :key="i" class="mb-2 !h-12" />
    </template>

    <draggable
      v-else
      v-model="localRoles"
      :disabled="!sortMode"
      item-key="id"
      class="space-y-2"
      easing="linear"
      tag="ul"
      :animation="200"
      :remove-clone-on-hide="true"
      ghost-class="ghost"
      drag-class="drag"
      chosen-class="chosen"
      @end="emit('save-roles-order')"
    >
      <template #item="{ element }">
        <RolesListItem
          :role="element"
          :selected="selectedRole?.id === element.id"
          @select-role="emit('select-role', $event)"
          @remove-role="emit('remove-role', $event)"
          @edit-role="emit('edit-role', $event)"
          @copy-role="emit('copy-role', $event)"
        />
      </template>
      <template #footer>
        <span v-if="!roles?.length" class="text-sm text-gray-500 dark:text-gray-400">
          {{ $t("No roles available") }}
        </span>
      </template>
    </draggable>
  </section>
</template>

<script setup>
const props = defineProps({
  roles: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  selectedRole: {
    type: [Object, null],
    required: false,
    default: null,
  },
});

const emit = defineEmits([
  "select-role",
  "add-role",
  "remove-role",
  "edit-role",
  "copy-role",
  "save-roles-order",
]);

const { permissions } = storeToRefs(useAuthStore());

const sortMode = ref(false);

const localRoles = computed({
  get() {
    return [...props.roles];
  },
  set(value) {
    emit("update-role-order", value);
  },
});
</script>
