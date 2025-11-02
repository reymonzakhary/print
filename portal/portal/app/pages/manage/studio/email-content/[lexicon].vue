<!-- pages/studio/email-templates/[lexicon].vue -->
<template>
  <StudioContent :loading="studio.loading.value">
    <template #sidebar>
      <div class="flex h-full flex-col px-4">
        <StudioTreeSection :title="$t('Language')" icon="globe">
          <UIVSelect v-model="language" :options="languageOptions" />
        </StudioTreeSection>

        <StudioTreeSection
          :title="$t('Available Tags')"
          icon="tags"
          class="flex h-full flex-1 flex-col overflow-hidden"
        >
          <StudioMagicTagList
            :tags="availableTags"
            :searchable="true"
            :grouped="true"
            :show-copy="true"
            class="h-full flex-1"
          />
        </StudioTreeSection>
      </div>
    </template>

    <template #content>
      <StudioConfigPreview>
        <StudioEntityEmail
          v-model:subject="subject"
          :primary-button="primaryButton"
          :secondary-button="secondaryButton"
          :tags="availableTags"
          :read-only="false"
          fetch-config
        >
          <StudioEditorJS
            v-show="contentExists"
            ref="editorRef"
            v-model="content"
            :tags="availableTags"
            :placeholder="$t('Start typing your email content...')"
          />
          <div
            v-show="!contentExists"
            class="flex h-full w-full flex-col items-center justify-center p-8 text-center"
          >
            <div class="mb-4 rounded-full bg-gray-100 p-4">
              <font-awesome-icon icon="envelope" class="text-4xl text-gray-400" />
            </div>
            <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ $t("No Email Template") }}</h3>
            <p class="max-w-md text-gray-800">
              {{ $t("Please contact Prindustry Support to create the email template.") }}
            </p>
          </div>
        </StudioEntityEmail>
      </StudioConfigPreview>
    </template>
  </StudioContent>
</template>

<script setup>
const { t: $t, locale, availableLocales } = useI18n();
const route = useRoute();
const router = useRouter();
const { addToast } = useToastStore();

// Props from parent
const hasChanges = defineModel("hasChanges", { type: Boolean, default: false });
const emit = defineEmits(["saving"]);

// Tags
const { quotationTags: availableTags } = useStudioEmailConfig();

// Language options
const language = ref(route.query.lang || locale.value);
const languageOptions = computed(() =>
  availableLocales.map((loc) => ({
    value: loc,
    label: new Intl.DisplayNames([loc], { type: "language" }).of(loc),
  })),
);

// Lexicon data management
const lexiconPrefix = computed(() => route.params.lexicon);

const studioConfig = computed(() => [
  {
    name: $t("Email Content"),
    fields: [
      {
        label: $t("Subject"),
        settingKey: `${lexiconPrefix.value}.subject`,
        value: "",
        dataType: "text",
      },
      {
        label: $t("Email Body"),
        settingKey: `${lexiconPrefix.value}.body`,
        value: null,
        dataType: "editorjs",
      },
    ],
  },
]);

const studio = useStudioLexicons({
  config: studioConfig,
  language: language.value,
  onSuccess: () => {
    addToast({ type: "success", message: $t("Email template saved successfully") });
    hasChanges.value = false;
  },
  onError: (error) => {
    console.error("Error saving template:", error);
    addToast({ type: "error", message: $t("Error saving template") });
  },
});

const primaryButton = computed(() => {
  switch (lexiconPrefix.value) {
    case "quotation":
      return $t("Accept");
    case "invoice":
      return false;
    default:
      return false;
  }
});

const secondaryButton = computed(() => {
  switch (lexiconPrefix.value) {
    case "quotation":
      return $t("Reject");
    case "invoice":
      return false;
    default:
      return false;
  }
});

// Field bindings
const subject = computed({
  get: () => studio.getValue(`${lexiconPrefix.value}.subject`, "subject").value || "",
  set: (value) => studio.update(`${lexiconPrefix.value}.subject`, value),
});

const content = computed({
  get: () => studio.getValue(`${lexiconPrefix.value}.body`, "body").value || { blocks: [] },
  set: (value) => studio.update(`${lexiconPrefix.value}.body`, value),
});
const contentExists = computed(
  () => !!studio.getValue(`${lexiconPrefix.value}.body`, "body").value,
);

// Watch for changes
watchEffect(() => (hasChanges.value = studio.isDirty.value));

// Handle language change
watch(language, async (newLang) => {
  await router.push({ query: { ...route.query, lang: newLang.value } });
  studio.changeLanguage(newLang.value);
});

// Sync saving state with parent
watchEffect(() => emit("saving", studio.saving.value));

// Editor ref
const editorRef = ref(null);

// Custom reset method that resets the studio state only (editor updates via watch)
const resetAll = () => studio.reset();

// Initialize on mount
onMounted(() => studio.load());

// Expose methods for parent
defineExpose({
  save: studio.save,
  reset: resetAll,
});
</script>
