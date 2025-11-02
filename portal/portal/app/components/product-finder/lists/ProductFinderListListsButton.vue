<template>
  <button
    v-if="variant === 'edit'"
    class="flex items-center rounded-full bg-gradient-to-br from-theme-50 to-theme-100 px-4 py-1.5 text-sm font-medium text-theme-700 shadow-sm outline outline-1 outline-theme-100 hover:from-theme-100 hover:to-theme-200 dark:bg-theme-900 dark:text-theme-200 dark:hover:bg-theme-800"
    @click="emit('click')"
  >
    <font-awesome-icon
      :icon="editMode ? ['fal', 'check'] : ['fal', 'pencil']"
      class="mr-2 h-3.5 w-3.5"
    />
    <span>{{ editMode ? $t("Done") : $t("Edit list") }}</span>
  </button>
  <button
    v-if="variant === 'create'"
    class="flex items-center rounded-full border border-dashed border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-500 transition-all hover:border-theme-300 hover:text-theme-600 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-theme-700"
    @click="emit('click')"
  >
    <font-awesome-icon :icon="['fal', 'plus']" class="mr-1.5 h-3.5 w-3.5" />
    <span>{{ $t("New list") }}</span>
  </button>
</template>

<script setup>
defineProps({
  editMode: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: "edit",
    validator(value) {
      return ["edit", "create"].includes(value);
    },
  },
});

const emit = defineEmits(["click"]);
</script>
