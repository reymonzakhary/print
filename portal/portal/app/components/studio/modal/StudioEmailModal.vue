<template>
  <!-- Studio Email Modal -->
  <Teleport to="body">
    <div tabindex="0" @keyup.esc.stop="closeModal">
      <!-- Modal wrapper -->
      <div
        style="z-index: 50"
        class="fixed left-0 top-0 flex h-screen w-screen cursor-pointer items-center justify-center"
        @click.self="closeModal"
      >
        <!-- Modal component -->
        <transition appear name="fade">
          <div
            class="shadow-xl0 flex max-h-screen cursor-default flex-col rounded-lg bg-gray-100 dark:bg-gray-800"
            :class="['h-[80vh] w-11/12 max-w-7xl', 'overflow-y-hidden']"
            role="dialog"
            aria-labelledby="modalTitle"
          >
            <!-- Header -->
            <header
              id="modalTitle"
              class="sticky top-0 rounded-t-lg border-b bg-white p-2 pr-8 dark:border-gray-900 dark:bg-gray-800"
            >
              <div class="flex items-center space-x-2">
                <font-awesome-icon :icon="['fal', 'envelope']" class="text-theme-500" />
                <span class="font-medium dark:text-gray-100">{{ $t("Email Editor") }}</span>
                <span class="text-sm text-gray-500">• {{ $t("Studio") }}</span>
              </div>
              <button
                class="absolute right-0 top-0 mr-3 mt-3 flex items-center justify-center transition-colors hover:text-gray-600"
                title="Close modal"
                aria-label="close"
                @click="closeModal"
              >
                <font-awesome-icon :icon="['fad', 'circle-xmark']" aria-hidden="true" />
              </button>
            </header>

            <!-- Body -->
            <StudioContent
              class="h-full flex-1 overflow-hidden bg-gray-100 p-4 pl-0 dark:!bg-gray-800"
            >
              <template #sidebar>
                <div
                  class="flex h-full flex-col bg-gray-100 px-4 dark:bg-gray-800 dark:text-gray-100"
                >
                  <!-- Language Selection -->
                  <StudioTreeSection :title="$t('Language')" icon="globe" variant="section">
                    <UIVSelect v-model="language" :options="languageOptions" class="" />
                  </StudioTreeSection>

                  <!-- Dynamic Content Tags -->
                  <StudioTreeSection
                    :title="$t('Dynamic Tags')"
                    icon="tags"
                    variant="section"
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
                <div class="flex h-full flex-col dark:text-gray-100">
                  <!-- Tab Navigation -->
                  <div class="">
                    <nav class="flex space-x-1 px-4 pt-4">
                      <button
                        :class="[
                          'border-b-2 px-4 py-3 text-sm transition-all duration-200',
                          activeTab === 'editor'
                            ? 'border-theme-500 bg-theme-50 font-bold text-theme-500 dark:bg-theme-900'
                            : 'text-gray-600 hover:text-theme-500 dark:text-gray-400',
                        ]"
                        @click="activeTab = 'editor'"
                      >
                        <font-awesome-icon :icon="['fal', 'edit']" class="mr-2" />
                        {{ $t("Editor") }}
                      </button>
                      <button
                        :class="[
                          'border-b-2 px-4 py-3 text-sm transition-all duration-200',
                          activeTab === 'preview'
                            ? 'border-theme-500 bg-theme-50 font-bold text-theme-500 dark:bg-theme-900'
                            : 'text-gray-600 hover:text-theme-500 dark:text-gray-400',
                        ]"
                        @click="activeTab = 'preview'"
                      >
                        <font-awesome-icon :icon="['fal', 'eye']" class="mr-2" />
                        {{ $t("Preview") }}
                      </button>
                    </nav>
                  </div>

                  <!-- Tab Content -->
                  <div class="flex-1 overflow-hidden">
                    <!-- Editor Tab -->
                    <div v-show="activeTab === 'editor'" class="h-full rounded p-4 pb-3">
                      <div class="bg-white py-3 dark:bg-gray-700">
                        <h3 class="font-medium">{{ $t("Content Editor") }}</h3>
                        <p class="text-sm text-gray-500">
                          {{ $t("Edit your email content using the rich text editor") }}
                        </p>
                      </div>
                      <div
                        class="h-full overflow-hidden rounded border p-4 shadow-inner dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/30"
                        style="height: calc(100% - 72px)"
                      >
                        <StudioEditorJS
                          v-if="emailContent && isInitialized"
                          v-model="emailContent"
                          :tags="availableTags"
                          :disabled="isLoading"
                          :dark="isDarkMode"
                          class="h-full overflow-y-auto"
                        />

                        <div v-else class="flex h-full items-center justify-center text-gray-500">
                          <font-awesome-icon :icon="['fal', 'spinner']" spin class="mr-2" />
                          {{ $t("Loading editor...") }}
                        </div>
                      </div>
                    </div>

                    <!-- Preview Tab -->
                    <div v-show="activeTab === 'preview'" class="h-full rounded p-4 pb-3">
                      <div class="bg-white py-3 dark:bg-gray-700">
                        <h3 class="font-medium">{{ $t("Live Preview") }}</h3>
                        <p class="text-sm text-gray-500">
                          {{ $t("See how your email will look to recipients") }}
                        </p>
                      </div>
                      <StudioConfigPreview
                        style="height: calc(100% - 72px)"
                        class="rounded dark:!bg-gray-900"
                      >
                        <StudioEntityEmail
                          v-model:subject="emailDetails.subject"
                          :tags="availableTags"
                          fetch-config
                          class="mx-auto max-w-3xl"
                        >
                          <div v-if="previewHTML" v-html="previewHTML" />
                          <div v-else class="py-8 text-center text-gray-500">
                            <font-awesome-icon icon="edit" class="mb-2 text-2xl" />
                            <p>{{ $t("Start typing in the editor to see content here") }}</p>
                          </div>
                        </StudioEntityEmail>
                      </StudioConfigPreview>
                    </div>
                  </div>
                </div>
              </template>
            </StudioContent>

            <!-- Footer -->
            <footer
              class="bg-gray-200s sticky bottom-0 flex items-center justify-end rounded-b-lg border-t p-2 backdrop-blur-md dark:border-gray-900"
            >
              <div class="flex items-center space-x-2">
                <UIButton
                  variant="success"
                  class="!px-4 !py-2"
                  :disabled="isLoading || !canSend"
                  @click="handleSend"
                >
                  {{ $t("Send Email") }}
                  <font-awesome-icon
                    v-if="isLoading"
                    :icon="['fal', 'spinner']"
                    class="ml-2"
                    spin
                  />
                  <font-awesome-icon v-else :icon="['fal', 'paper-plane']" class="ml-2" />
                </UIButton>
              </div>
            </footer>
          </div>
        </transition>
      </div>

      <!-- Background overlay -->
      <transition appear name="fade">
        <div
          style="z-index: 49"
          class="fixed left-0 top-0 h-screen w-screen bg-black bg-opacity-50 backdrop-blur-sm"
        />
      </transition>
    </div>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ["quotation", "invoice"].includes(value),
  },
  entityId: {
    type: [Number, String],
    required: true,
  },
  // Optional initial content
  initialSubject: {
    type: String,
    default: null,
  },
  initialContent: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["onClose", "onSend", "onSave"]);

// Composables
const { t: $t, locale, availableLocales } = useI18n();
const { addToast } = useToastStore();
const { quotationTags: configTags } = useStudioEmailConfig();
const { editorJSToHTML, htmlToEditorJS } = useEditorJSParser();
const quotationRepository = useQuotationRepository();
const invoiceRepository = useInvoiceRepository();
const themeStore = useThemeStore();

// Reactive state
const activeTab = ref("editor");
const isLoading = ref(false);
const isInitialized = ref(false);
const emailDetails = ref({
  subject: props.initialSubject || "",
  body: null,
});

const emailContent = ref(null);

const availableTags = ref(configTags);

// Language options (same as lexicon file)
const language = ref(locale.value);
const languageOptions = computed(() =>
  availableLocales.map((loc) => ({
    value: loc,
    label: new Intl.DisplayNames([loc], { type: "language" }).of(loc),
  })),
);

const isDarkMode = computed({
  get: () => themeStore.activeTheme === "dark",
  set: (val) => themeStore.setActiveTheme(val ? "dark" : "light"),
});

const canSend = computed(() => {
  return (
    emailDetails.value.subject &&
    emailContent.value &&
    emailContent.value.blocks &&
    emailContent.value.blocks.length > 0
  );
});

// Convert EditorJS content to HTML for preview
const previewHTML = computed(() => {
  if (!emailContent.value || !emailContent.value.blocks) {
    return "";
  }
  return editorJSToHTML(emailContent.value);
});

// Methods
const loadEmailTemplate = async () => {
  try {
    isLoading.value = true;

    let templateData = null;

    if (props.type === "quotation") {
      templateData = await quotationRepository.getEmailTemplate(props.entityId, language.value);
    } else if (props.type === "invoice") {
      templateData = await invoiceRepository.getEmailTemplate(props.entityId, language.value);
    }

    // Check if we received any template data
    if (!templateData || (!templateData.subject && !templateData.body)) {
      // Inform user that no template was found and system default will be used
      addToast({
        type: "info",
        // eslint-disable-next-line prettier/prettier
        message: $t(
          "No email template found in Prindustry Manager. Using system default template.",
        ),
        duration: 4000,
      });
      return; // Exit early, will use default content
    }

    // Use template subject if available and no initial subject provided
    if (templateData?.subject && !props.initialSubject) {
      emailDetails.value.subject = templateData.subject;
    }

    // Use template body if available and no initial content provided
    if (templateData?.body && !props.initialContent) {
      // Try to parse the body as EditorJS content
      try {
        const parsedBody = htmlToEditorJS(templateData.body);
        if (parsedBody.blocks) {
          emailContent.value = parsedBody;
          isInitialized.value = true;
          return;
        }
      } catch {
        // If parsing fails, inform user and fall through to set default content
        addToast({
          type: "warning",
          message: $t("Email template format is invalid. Using system default template."),
          duration: 4000,
        });
      }
    }
  } catch (err) {
    console.error("Error loading email template:", err);
    addToast({
      type: "warning",
      message: $t(
        "Unable to load email template from Prindustry Manager. Using system default template.",
      ),
      duration: 4000,
    });
  } finally {
    isLoading.value = false;
  }
};

const setDefaultContent = () => {
  if (props.type === "quotation") {
    // Only set default subject if none is currently set
    if (!emailDetails.value.subject) {
      emailDetails.value.subject = $t("Quotation for mr/mrs [[%customer.full_name]]");
    }

    emailContent.value = {
      time: Date.now(),
      blocks: [
        {
          type: "header",
          data: {
            text: $t("Your Quotation"),
            level: 2,
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t("Dear [[%customer.full_name]],"),
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t(
              "Thank you for allowing us to send you this quotation with ID [[%quotation.id]]. Please let us know with the buttons below if you accept or reject this quotation.",
            ),
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t("Thank you,"),
          },
        },
      ],
      version: "2.26.5",
    };
  } else if (props.type === "invoice") {
    // Only set default subject if none is currently set
    if (!emailDetails.value.subject) {
      emailDetails.value.subject = $t("Invoice for mr/mrs [[%customer.full_name]]");
    }

    emailContent.value = {
      time: Date.now(),
      blocks: [
        {
          type: "header",
          data: {
            text: $t("Your Invoice"),
            level: 2,
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t("Dear [[%customer.full_name]],"),
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t(
              "Your invoice with number [[%invoice.id]] is available and easily accessible via the attached PDF.",
            ),
          },
        },
        {
          type: "paragraph",
          data: {
            text: $t("Thank you,"),
          },
        },
      ],
      version: "2.26.5",
    };
  }
};

const initializeContent = () => {
  // Handle initial content from props
  if (props.initialContent) {
    emailContent.value = props.initialContent;
  }

  // Handle initial subject from props
  if (props.initialSubject) {
    emailDetails.value.subject = props.initialSubject;
  }

  // If no content is set at this point, set default content
  if (!emailContent.value) {
    setDefaultContent();
  }

  isInitialized.value = true;
};

const handleSend = () => {
  const emailData = {
    language: language.value,
    subject: emailDetails.value.subject,
    body: editorJSToHTML(emailContent.value),
  };

  emit("onSend", emailData);
};

const closeModal = () => {
  emit("onClose");
};

// Watchers
watch(language, async (_newLang) => {
  if (isInitialized.value && !props.initialSubject && !props.initialContent) {
    // Reload email template for the new language
    await loadEmailTemplate();

    // If no template was found, set default content for the new language
    if (!emailContent.value) {
      setDefaultContent();
    }
  }
});

// Lifecycle
onMounted(async () => {
  // Load email template first (this may set subject/content if available)
  await loadEmailTemplate();
  // Then initialize content (this will handle props and fallback to defaults)
  initializeContent();
});
</script>

<!-- <style scoped>
/* Custom styling for the studio modal */
:deep(.studio-editor) {
  --editor-bg: #111827;
}

:deep(.confirmation-modal .modal-body) {
  @apply h-full p-0;
}

/* Dark theme adjustments for the modal */
:deep(.studio-content) {
  @apply bg-gray-900;
}

/* Tab styling improvements */
:deep(.tab-navigation button:hover) {
  @apply bg-gray-700;
}

/* Tag styling in preview */
:deep([data-name]) {
  @apply inline-block rounded bg-theme-500 px-2 py-1 text-xs font-semibold text-white;
}

/* Sidebar styling improvements */
:deep(.studio-content .sidebar) {
  @apply bg-gray-800;
}

/* Fix any remaining white backgrounds in sidebar */
:deep(.studio-content .sidebar *) {
  @apply bg-transparent;
}

:deep(.studio-content .sidebar .section) {
  @apply border-gray-700 bg-gray-800;
}

/* Preview content styling */
:deep(.studio-entity-email) {
  @apply text-gray-900;
}

:deep(.studio-entity-email h1) {
  @apply mb-4 text-3xl font-bold;
}

:deep(.studio-entity-email h2) {
  @apply mb-3 text-2xl font-bold;
}

:deep(.studio-entity-email h3) {
  @apply mb-3 text-xl font-semibold;
}

:deep(.studio-entity-email h4) {
  @apply mb-2 text-lg font-medium;
}

:deep(.studio-entity-email h5) {
  @apply mb-2 text-base font-medium;
}

:deep(.studio-entity-email h6) {
  @apply mb-2 text-sm font-medium;
}

:deep(.studio-entity-email p) {
  @apply mb-4 leading-relaxed;
}

:deep(.studio-entity-email ul) {
  @apply mb-4 list-disc pl-6;
}

:deep(.studio-entity-email ol) {
  @apply mb-4 list-decimal pl-6;
}

:deep(.studio-entity-email li) {
  @apply mb-1;
}

:deep(.studio-entity-email hr) {
  @apply my-6 border-gray-300;
}

/* Modal transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}
</style> -->
