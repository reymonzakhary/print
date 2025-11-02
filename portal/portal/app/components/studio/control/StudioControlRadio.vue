<template>
  <StudioControlUIContainer>
    <StudioControlUILabel :for="field.settingKey" :label="field.label" />
    <fieldset
      class="grid gap-2"
      :style="{ gridTemplateColumns: `repeat(${field.options.length}, 1fr)` }"
    >
      <legend class="sr-only">{{ field.label }}</legend>
      <div v-for="option in field.options" :key="option.value">
        <label
          :for="`${field.settingKey}-${option.value}`"
          class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-sm font-medium shadow-sm has-[:checked]:border-gray-200 has-[:checked]:ring-1 has-[:checked]:ring-theme-500 dark:border-gray-600 dark:bg-gray-700 dark:hover:border-gray-800"
        >
          <font-awesome-icon v-if="option.icon" :icon="option.icon" />

          <input
            :id="`${field.settingKey}-${option.value}`"
            :checked="field.value === option.value"
            :value="option.value"
            type="radio"
            :name="field.settingKey"
            class="hidden size-4 border-gray-300 text-theme-500"
            @change="updateField(field.settingKey, option.value)"
          />
        </label>
      </div>
    </fieldset>
  </StudioControlUIContainer>
</template>

<script setup>
const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
});
const emit = defineEmits(["update-field"]);

const updateField = (settingKey, value) => {
  emit("update-field", { settingKey, value });
  // Handle supplementary field relationship
  if (props.field.supplementaryFor) {
    // Also emit an update for the target field
    emit("update-field", { settingKey: props.field.supplementaryFor, value });
  }
};
</script>
