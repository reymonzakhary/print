<template>
  <StudioContent :loading="studio.loading.value">
    <template #sidebar>
      <div class="px-4">
        <StudioConfig
          :config="studioConfig"
          :values="studio.values.value"
          @field-update="handleFieldUpdate"
        />
      </div>
    </template>

    <template #content>
      <div class="relative h-full">
        <StudioConfigPreview class="h-full">
          <StudioEntityEmail :config="emailConfig">
            <div>
              <p>{{ $t("Hello there,") }}</p>
              <br />
              <!-- eslint-disable-next-line prettier/prettier -->
              <p>
                {{
                  $t(
                    "This is your Prindustry Manager 3.0 email template. Through this email template, your customers, prospects and team will receive all your email brand communication.",
                  )
                }}
              </p>
              <br />
              <!-- eslint-disable-next-line prettier/prettier -->
              <p>
                {{
                  $t(
                    "You can edit this template to your liking, and add your own content under the 'Email content' section in the Studio navigation on your left.",
                  )
                }}
              </p>
            </div>
          </StudioEntityEmail>
        </StudioConfigPreview>
      </div>
    </template>
  </StudioContent>
</template>

<script setup>
const { addToast } = useToastStore();
const { t: $t } = useI18n();

const hasChanges = defineModel("hasChanges", { type: Boolean, default: false });
const emit = defineEmits(["saving"]);

// Get email config structure
const { studioConfig, getEmailConfig } = useStudioEmailConfig();

// Use the simplified settings composable
const studio = useStudioSettings({
  namespace: "themes",
  area: "mail",
  config: studioConfig,
  onSuccess: () => {
    addToast({
      type: "success",
      message: $t("Email template saved successfully"),
    });
  },
});

// Compute email config from studio values
const emailConfig = getEmailConfig(studio.getValue);

// Handle field updates from the config component
const handleFieldUpdate = ({ settingKey, value }) => studio.update({ settingKey, value });

// Sync hasChanges with studio state
watchEffect(() => (hasChanges.value = studio.isDirty.value));

// Sync saving state with parent
watchEffect(() => emit("saving", studio.saving.value));

// Initialize on mount
onMounted(() => studio.load());

// Expose methods for parent component
defineExpose({
  save: studio.save,
  reset: studio.reset,
});
</script>
