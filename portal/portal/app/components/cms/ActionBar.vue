<template>
  <aside
    class="flex flex-col h-full p-4 rounded bg-theme-400 dark:bg-theme-800"
  >
    <ActionBarSection>
      <ActionBarSectionTitle>
        {{ $t("choose resource type") }}
      </ActionBarSectionTitle>
      <ResourceTypeSelector
        :value="resourceType"
        :disabled="locked"
        @input="$emit('onResourceTypeChange', $event)"
      />
    </ActionBarSection>

    <ActionBarSection>
      <ActionBarSectionTitle>
        {{ $t("choose template") }}
      </ActionBarSectionTitle>
      <TemplateSelector
        :value="template"
        :disabled="locked"
        @input="$emit('onTemplateChange', $event)"
      />
    </ActionBarSection>

    <ActionBarSection>
      <ActionBarSectionTitle>
        {{ $t("choose resource groups") }}
      </ActionBarSectionTitle>
      <UIButton
        :icon="['fal', 'window-restore']"
        variant="outline"
        class="underline text-themecontrast-100"
        :disabled="locked"
        @click="handleGroupButtonClick"
        >Resource Groups
      </UIButton>
    </ActionBarSection>

    <ActionBarSection>
      <ActionBarSectionTitle :icon="['fal', 'eye']">
        {{ $t("visibility") }}
      </ActionBarSectionTitle>
      <div class="grid grid-cols-2">
        <span class="text-xs text-themecontrast-400">
          {{ $t("Will the page be visible in the webshop menu?") }}
        </span>
        <div class="flex items-center">
          <UISwitch
            class="ml-auto"
            variant="success"
            :value="visibility"
            :disabled="locked"
            @input="$emit('onVisibilityChange', $event)"
          />
        </div>
      </div>
    </ActionBarSection>

    <ActionBarSection>
      <ActionBarSectionTitle :icon="['fal', 'book-open']">
        {{ $t("publication") }}
      </ActionBarSectionTitle>
      <div class="grid grid-cols-2">
        <span class="text-xs text-themecontrast-400">
          {{ $t("Will the page be visitable?") }}
        </span>
        <div class="flex items-center">
          <UISwitch
            class="ml-auto"
            variant="success"
            :value="published"
            :disabled="locked"
            @input="$emit('onPublicationChange', $event)"
          />
        </div>
      </div>
    </ActionBarSection>

    <ActionBarSection class="flex flex-wrap mt-auto !mb-0">
      <UIButton
        :icon="['fal', 'floppy-disk']"
        variant="success"
        class="mt-2 mr-2"
        :disabled="locked"
        @click="$emit('onSaveButtonClick')"
        >Save
      </UIButton>
      <UIButton
        :icon="['fal', 'trash']"
        variant="danger"
        class="mt-2 mr-2"
        :disabled="locked"
        @click="$emit('onDeleteButtonClick')"
      >
        Delete
      </UIButton>
      <UIButton
        :icon="['fal', 'trash']"
        variant="default"
        class="mt-2"
        @click="$emit('onPreviewButtonClick')"
        >Preview
      </UIButton>
    </ActionBarSection>
  </aside>
</template>

<script>
export default {
  name: "ActionBar",
  props: {
    resourceType: {
      type: [Number, String],
      default: "0",
    },
    template: {
      type: [Number, String],
      default: "0",
    },
    visibility: {
      type: Boolean,
      default: false,
    },
    published: {
      type: Boolean,
      default: false,
    },
    locked: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    "onTemplateChange",
    "onResourceTypeChange",
    "onVisibilityChange",
    "onPublicationChange",
    "onSaveButtonClick",
    "onDeleteButtonClick",
    "onPreviewButtonClick",
    "onResourceGroupButtonClick",
  ],
  methods: {
    handleGroupButtonClick() {
      this.$emit("onResourceGroupButtonClick");
    },
    handlePreviewButtonClick() {
      this.$emit("onPreviewButtonClick");
    },
  },
};
</script>
