<template>
  <li
    :key="role.id"
    class="group flex cursor-pointer items-center justify-between rounded px-4 py-1 capitalize transition-colors duration-100 hover:bg-theme-50 hover:text-theme-500 focus:outline-none"
    :class="{ 'bg-theme-100 text-theme-800 dark:bg-theme-900 dark:text-theme-100': selected }"
    @click="emit('select-role', role)"
  >
    <p>{{ $display_name(role.display_name) }}</p>
    <span class="flex">
      <button
        v-if="permissions.includes('roles-delete')"
        class="invisible mr-1 flex rounded-full p-2 text-red-500 hover:bg-red-100 group-hover:visible"
        @click="emit('remove-role', role)"
      >
        <font-awesome-icon :icon="['fal', 'trash-can']" />
      </button>
      <button
        v-if="permissions.includes('roles-read') && permissions.includes('roles-update')"
        class="invisible mr-1 flex rounded-full p-2 text-theme-500 hover:bg-theme-100 group-hover:visible"
        @click="emit('edit-role', role)"
      >
        <font-awesome-icon :icon="['fal', 'pencil']" />
      </button>
      <button
        v-if="permissions.includes('acl-roles-create')"
        class="invisible mr-1 flex rounded-full p-2 text-theme-500 hover:bg-theme-100 group-hover:visible"
        @click.stop="emit('copy-role', role)"
      >
        <font-awesome-icon :icon="['fal', 'copy']" />
      </button>
    </span>
  </li>
</template>

<script setup>
defineProps({
  role: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["select-role", "remove-role", "edit-role", "copy-role"]);

const { permissions } = storeToRefs(useAuthStore());
</script>
