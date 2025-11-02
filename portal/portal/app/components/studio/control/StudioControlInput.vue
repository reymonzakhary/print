<template>
  <div>
    <StudioControlUILabel :for="field.settingKey" :label="field.label" />
    <UIInputText
      :id="field.settingKey"
      :model-value="field.value"
      :name="field.settingKey"
      :type="field.inputType || 'text'"
      :placeholder="field.placeholder"
      @update:model-value="updateField(field.settingKey, $event)"
    />
  </div>
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
  // Handle supplementary field relationship if defined
  if (props.field.supplementaryFor) {
    // Also emit an update for the target field
    emit("update-field", { settingKey: props.field.supplementaryFor, value });
  }
};
</script>
