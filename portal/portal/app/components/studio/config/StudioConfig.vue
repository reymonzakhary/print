<template>
  <StudioTreeSection
    v-for="panel in config"
    :key="panel.id"
    :class="panel.type === 'hidden' ? 'hidden' : ''"
    :title="panel.displayName"
    :icon="panel.icon"
    managed
    :expanded="expandedPanel === panel.id"
    @toggle="expandedPanel = expandedPanel === panel.id ? null : panel.id"
  >
    <div
      v-for="(field, fieldIndex) in panel.fields"
      :key="`${panel.id}-${fieldIndex}`"
      class="mb-5"
    >
      <StudioControlInput
        v-if="field.type === 'input'"
        :field="getFieldWithValue(field)"
        @update-field="handleFieldUpdate"
      />
      <StudioControlSelect
        v-else-if="field.type === 'select'"
        :field="getFieldWithValue(field)"
        @update-field="handleFieldUpdate"
      />
      <StudioControlRadio
        v-else-if="field.type === 'radio'"
        :field="getFieldWithValue(field)"
        @update-field="handleFieldUpdate"
      />
      <StudioControlImage
        v-else-if="field.type === 'image'"
        :field="getFieldWithValue(field)"
        @update-field="handleFieldUpdate"
      />
      <StudioControlUIContainer v-else-if="field.type === 'color'">
        <StudioControlUILabel :label="field.label" :for="field.settingKey" />
        <UIColorPicker
          :model-value="getFieldWithValue(field).value"
          variant="sketch"
          color-scheme="studio"
          class="w-full"
          @update:model-value="
            (val) => handleFieldUpdate({ settingKey: field.settingKey, value: val })
          "
        />
      </StudioControlUIContainer>
      <div v-else-if="field.type === 'container'">
        <StudioControlUILabel :for="field.label" :label="field.label" />
        <div :class="field.class">
          <template
            v-for="(containerChild, containerChildIndex) in field.children"
            :key="`${panel.id}-${fieldIndex}-${containerChildIndex}`"
          >
            <StudioControlInput
              v-if="containerChild.type === 'input'"
              :field="getFieldWithValue(containerChild)"
              @update-field="handleFieldUpdate"
            />
            <StudioControlSelect
              v-else-if="containerChild.type === 'select'"
              :field="getFieldWithValue(containerChild)"
              @update-field="handleFieldUpdate"
            />
            <StudioControlRadio
              v-else-if="containerChild.type === 'radio'"
              :field="getFieldWithValue(containerChild)"
              @update-field="handleFieldUpdate"
            />
            <StudioControlImage
              v-else-if="containerChild.type === 'image'"
              :field="getFieldWithValue(containerChild)"
              @update-field="handleFieldUpdate"
            />
            <StudioControlUIContainer v-else-if="containerChild.type === 'color'">
              <StudioControlUILabel
                :label="containerChild.label"
                :for="containerChild.settingKey"
              />
              <UIColorPicker
                :model-value="getFieldWithValue(containerChild).value"
                variant="sketch"
                color-scheme="studio"
                class="w-full"
                @update:model-value="
                  (v) => handleFieldUpdate({ settingKey: containerChild.settingKey, value: v })
                "
              />
            </StudioControlUIContainer>
          </template>
        </div>
      </div>
    </div>
  </StudioTreeSection>
</template>

<script setup>
const props = defineProps({
  config: {
    type: Object,
    required: true,
  },
  values: {
    type: Object,
    required: true,
  },
});
const emit = defineEmits(["field-update"]);

// Helper to get the value of a field from the values object
const getFieldWithValue = (field) => {
  if (!field.settingKey) return field;

  const value = props.values[field.settingKey] ?? field.value;

  return { ...field, value };
};

const expandedPanel = ref(null);

/**
 * Field update management
 */
const handleFieldUpdate = ({ settingKey, value }) => {
  emit("field-update", { settingKey, value });

  // If this is a supplementary field (no settingKey), look for related field
  if (!settingKey) {
    // Find the field by checking all panels
    for (const panel of props.config) {
      for (const field of panel.fields) {
        if (field.supplementaryFor && !field.settingKey) {
          // Found the supplementary field, update its value as well
          field.value = value;
          // Also update the target field
          emit("field-update", { settingKey: field.supplementaryFor, value });
        }
      }
    }
  }
};
</script>
