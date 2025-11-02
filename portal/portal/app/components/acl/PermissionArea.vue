<template>
  <div class="flex items-stretch">
    <div class="w-2/6 py-2">
      <h3 class="capitalize">
        {{ area }}
      </h3>
    </div>
    <div class="flex w-3/6 items-stretch justify-between">
      <div
        v-for="perm in organizedPerms"
        :key="perm.id"
        class="flex w-1/6 items-center justify-center transition duration-100 even:bg-gray-100 dark:even:bg-gray-900"
      >
        <input
          v-if="perm.display_name"
          v-tooltip="perm.description"
          :name="perm.id"
          type="checkbox"
          class="scale-150 transform"
          :checked="filteredPerms.includes(perm.id)"
          :disabled="!canUpdate"
          @change="$emit('toggle-perm', perm)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  area: {
    type: String,
    required: true,
  },
  perms: {
    type: Array,
    required: true,
  },
  filteredPerms: {
    type: Array,
    default: () => [],
  },
  canUpdate: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["toggle-perm"]);

// Create consistently organized permissions array with 6 slots
const organizedPerms = computed(() => {
  const crudOrder = ["access", "list", "read", "create", "update", "delete"];
  const result = Array(6).fill({});

  // Organize permissions into the right slots
  props.perms.forEach((perm) => {
    const index = crudOrder.indexOf(perm.display_name);
    if (index !== -1) {
      result[index] = perm;
    }
  });

  return result;
});
</script>
