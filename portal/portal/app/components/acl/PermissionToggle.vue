<template>
  <div
    class="flex w-1/6 flex-col items-center justify-items-stretch py-1 text-sm transition duration-300"
    :class="{
      'even:bg-gray-100 dark:even:bg-gray-900': toggleActive,
    }"
  >
    <div v-if="hasPermissions">
      {{ $t(item) }}
    </div>

    <div
      v-if="hasPermissions && canReadPermissions"
      v-tooltip="canUpdatePermissions ? '' : $t('you have no permissions to update')"
      class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
      :class="[allChecked ? 'bg-theme-400' : someChecked ? 'bg-yellow-500' : 'bg-gray-400']"
      @click="handleToggleClick"
    >
      <label
        class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
        :class="[
          allChecked
            ? 'translate-x-6 border-theme-500'
            : someChecked
              ? 'translate-x-3 border-yellow-500'
              : 'translate-x-0 border-gray-400',
        ]"
      />
      <input
        :id="namespace.name + '-' + item"
        :name="namespace.name + '-' + item"
        type="checkbox"
        class="h-full w-full appearance-none focus:outline-none active:outline-none"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  namespace: {
    type: Object,
    required: true,
  },
  item: {
    type: String,
    required: true,
  },
  countedNamespaces: {
    type: Object,
    required: true,
  },
  toggleActive: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-all"]);

// Get permissions from composable
const { permissions } = storeToRefs(useAuthStore());

// Permission checks
const canReadPermissions = computed(() => permissions.value.includes("acl-permissions-read"));
const canUpdatePermissions = computed(() => permissions.value.includes("acl-permissions-update"));

// Computed properties for toggle state
const hasPermissions = computed(
  () =>
    props.countedNamespaces[props.namespace.name] &&
    props.countedNamespaces[props.namespace.name][props.item].countTotal > 0,
);

const allChecked = computed(() => {
  if (!props.countedNamespaces[props.namespace.name]) return false;

  const counts = props.countedNamespaces[props.namespace.name][props.item];
  return counts.countChecked === counts.countTotal;
});

const someChecked = computed(() => {
  if (!props.countedNamespaces[props.namespace.name]) return false;

  const counts = props.countedNamespaces[props.namespace.name][props.item];
  return counts.countChecked > 0;
});

// Handle toggle click
const handleToggleClick = () => {
  if (!canUpdatePermissions.value) return;

  emit("toggle-all", someChecked.value ? "uncheck" : "check");
};
</script>
