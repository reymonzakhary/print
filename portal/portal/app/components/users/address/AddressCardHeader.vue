<template>
  <div class="flex">
    <div
      class="flex w-1/3 items-center rounded-t bg-theme-400 p-2 text-sm font-bold uppercase tracking-wide text-themecontrast-400 dark:bg-gray-900"
    >
      {{ type ?? $t("address") }}
    </div>

    <div class="ml-auto flex gap-2 px-2 py-1">
      <UIButton
        v-if="mayUpdate || permissions.includes('users-addresses-update')"
        :disabled="disabled"
        :icon="['fal', 'pencil']"
        variant="neutral"
        @click="$emit('update-address')"
      />
      <UIButton
        v-if="mayDelete || permissions.includes('users-addresses-delete')"
        :disabled="disabled"
        :icon="['fal', 'trash-can']"
        variant="danger"
        @click="$emit('delete-address')"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "AddressCardHeader",
  props: {
    type: {
      type: [String, null],
      required: false,
      default: null,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    mayUpdate: {
      type: Boolean,
      required: false,
      default: false,
    },
    mayDelete: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  emits: ["delete-address", "edit-address", "update-address"],
  setup() {
    const authStore = useAuthStore();
    return {
      permissions: authStore.permissions,
    };
  },
};
</script>
