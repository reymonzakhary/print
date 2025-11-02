<template>
  <label
    class="group flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-white dark:hover:bg-gray-700"
    :for="name"
  >
    <div class="flex-1 pr-4">
      {{ title }}
      <p v-if="description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ description }}
      </p>
    </div>
    <div class="flex items-center">
      <UISwitch
        :name="name"
        :value="value"
        @input="emit('toggle', { key: settingKey, value: $event })"
      />
    </div>
  </label>
</template>

<script setup>
defineProps({
  settingKey: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: null,
  },
  value: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["toggle"]);
</script>
