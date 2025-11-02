<template>
  <StudioControlUIContainer>
    <StudioControlUILabel :for="field.settingKey" :label="field.label" />
    <UIVSelect
      :id="field.settingKey"
      :model-value="field.value"
      :options="field.options"
      @update:model-value="updateField(field.settingKey, $event)"
    />
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
  value = value.value ?? value;

  emit("update-field", { settingKey, value });
  // Handle supplementary field relationship
  if (props.field.supplementaryFor) {
    // Also emit an update for the target field
    emit("update-field", { settingKey: props.field.supplementaryFor, value });
  }
};
</script>
