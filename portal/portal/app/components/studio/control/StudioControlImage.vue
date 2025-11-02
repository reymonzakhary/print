<template>
  <StudioControlUIContainer>
    <StudioControlUILabel :for="field.settingKey" :label="field.label" />
    <UIImageSelector
      :id="field.settingKey"
      :selected-image="field.value"
      :with-fetch="field.withFetch"
      @on-image-select="updateField(field.settingKey, $event)"
      @on-image-remove="updateField(field.settingKey, null)"
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
  emit("update-field", { settingKey, value });
  // Handle supplementary field relationship if defined
  if (props.field.supplementaryFor) {
    // Also emit an update for the target field
    emit("update-field", { settingKey: props.field.supplementaryFor, value });
  }
};
</script>
