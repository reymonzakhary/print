<template>
  <!-- Outlook-style Email Editor -->
  <UICard
    class="relative flex h-fit w-full flex-col overflow-hidden bg-white !shadow-xl"
    rounded-full
  >
    <!-- Outlook-style Header -->
    <div class="flex items-center justify-between bg-theme-400 p-3">
      <div class="flex items-center space-x-2">
        <font-awesome-icon icon="envelope" class="text-white" />
        <span class="font-medium text-white">{{ $t("New Message") }}</span>
      </div>
      <div class="flex items-center space-x-1">
        <button class="rounded p-1 text-white">
          <font-awesome-icon icon="times" />
        </button>
      </div>
    </div>

    <!-- Email Header Fields -->
    <div class="border-b border-gray-200 bg-white text-gray-600">
      <div class="grid grid-cols-[6rem_auto] items-start border-b border-gray-200 p-3">
        <!-- Label -->
        <label for="to-email">{{ $t("To:") }}</label>
        <!-- Input -->
        <div>
          <div id="to-email" class="tag-inserted" data-name="[[%customer.email]]">
            {{ $t("Customer Email") }}
          </div>
        </div>
      </div>
      <div
        v-if="subject"
        class="grid grid-cols-[6rem_auto] items-start border-b border-gray-200 p-3"
      >
        <!-- Label -->
        <label for="subject">{{ $t("Subject:") }}</label>
        <!-- Preview -->
        <div v-if="readOnly" class="text-gray-500">{{ subject }}</div>
        <!-- Input -->
        <UIInputText v-else id="subject" v-model="subject" name="subject" />
      </div>
    </div>

    <!-- Email Content -->
    <div class="flex-grow overflow-auto text-gray-800">
      <div class="email-container">
        <table
          class="email-wrapper"
          :style="{
            backgroundColor: effectiveConfig.theme.backgroundColor,
          }"
          width="100%"
          cellpadding="0"
          cellspacing="0"
          role="presentation"
        >
          <tbody>
            <tr>
              <td class="email-base-style" style="padding: 0 0 12px 0">
                <table
                  class="email-content-wrapper"
                  width="100%"
                  cellpadding="0"
                  cellspacing="0"
                  role="presentation"
                  :style="{
                    width: effectiveConfig.content.width,
                    margin: '0 auto',
                  }"
                >
                  <tbody>
                    <tr v-if="effectiveConfig.logo.position === 'header' && logoImage">
                      <td
                        class="email-header"
                        :style="{
                          backgroundColor: effectiveConfig.header.backgroundColor,
                          padding: `${effectiveConfig.header.padding.pt}px ${effectiveConfig.header.padding.pr}px ${effectiveConfig.header.padding.pb}px ${effectiveConfig.header.padding.pl}px`,
                          textAlign: effectiveConfig.header.alignment,
                        }"
                      >
                        <img
                          :src="logoImage"
                          alt="Logo"
                          class="inline-block"
                          :style="{
                            width: effectiveConfig.logo.width,
                          }"
                        />
                      </td>
                    </tr>

                    <tr>
                      <td class="email-body">
                        <table
                          class="email-inner-body w-full"
                          align="center"
                          cellpadding="0"
                          cellspacing="0"
                          role="presentation"
                          :style="{
                            backgroundColor: effectiveConfig.content.backgroundColor,
                            borderWidth: `${effectiveConfig.content.borderWidth}px`,
                            borderColor: effectiveConfig.content.borderColor,
                            borderRadius: `${effectiveConfig.content.borderRadius.tl}px ${effectiveConfig.content.borderRadius.tr}px ${effectiveConfig.content.borderRadius.br}px ${effectiveConfig.content.borderRadius.bl}px`,
                            borderStyle: effectiveConfig.content.borderWidth > 0 ? 'solid' : 'none',
                            fontFamily: effectiveConfig.text.fontFamily,
                            fontSize: `${effectiveConfig.text.fontSize}`,
                            color: effectiveConfig.text.fontColor,
                          }"
                        >
                          <tbody>
                            <tr>
                              <td
                                class="content-cell"
                                :style="{
                                  padding: `${effectiveConfig.content.padding.pt}px ${effectiveConfig.content.padding.pr}px ${effectiveConfig.content.padding.pb}px ${effectiveConfig.content.padding.pl}px`,
                                  textAlign: effectiveConfig.content.alignment,
                                }"
                              >
                                <div
                                  v-if="logoImage && effectiveConfig.logo.position === 'content'"
                                  :style="{
                                    textAlign: effectiveConfig.logo.alignment,
                                    marginBottom: '20px',
                                  }"
                                >
                                  <img
                                    :src="logoImage"
                                    class="inline-block"
                                    alt="Logo"
                                    :style="{
                                      width: effectiveConfig.logo.width,
                                    }"
                                  />
                                </div>
                                <div ref="slotContainer">
                                  <slot />
                                </div>

                                <p
                                  v-if="showPrimaryButton || showSecondaryButton"
                                  :style="{
                                    display: 'flex',
                                    justifyContent: buttonAlignmentStyle,
                                  }"
                                >
                                  <button
                                    v-if="showPrimaryButton"
                                    :style="{
                                      padding: `${effectiveConfig.buttons.padding.pt}px ${effectiveConfig.buttons.padding.pr}px ${effectiveConfig.buttons.padding.pb}px ${effectiveConfig.buttons.padding.pl}px`,
                                      textDecoration: 'none',
                                      borderRadius: `${effectiveConfig.buttons.borderRadius.tl}px ${effectiveConfig.buttons.borderRadius.tr}px ${effectiveConfig.buttons.borderRadius.br}px ${effectiveConfig.buttons.borderRadius.bl}px`,
                                      color: effectiveConfig.theme.primaryTextColor,
                                      backgroundColor: effectiveConfig.theme.primaryColor,
                                    }"
                                  >
                                    {{ primaryButtonText }}
                                  </button>
                                  <button
                                    v-if="showSecondaryButton"
                                    :style="{
                                      marginLeft: showPrimaryButton ? '10px' : '0',
                                      padding: `${effectiveConfig.buttons.padding.pt}px ${effectiveConfig.buttons.padding.pr}px ${effectiveConfig.buttons.padding.pb}px ${effectiveConfig.buttons.padding.pl}px`,
                                      textDecoration: 'none',
                                      borderRadius: `${effectiveConfig.buttons.borderRadius.tr}px ${effectiveConfig.buttons.borderRadius.tl}px ${effectiveConfig.buttons.borderRadius.br}px ${effectiveConfig.buttons.borderRadius.bl}px`,
                                      color: effectiveConfig.theme.secondaryTextColor,
                                      backgroundColor: effectiveConfig.theme.secondaryColor,
                                    }"
                                  >
                                    {{ secondaryButtonText }}
                                  </button>
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>

                    <tr>
                      <td class="email-base-style">
                        <table
                          class="email-footer"
                          align="center"
                          width="570"
                          cellpadding="0"
                          cellspacing="0"
                          role="presentation"
                          :style="{
                            backgroundColor: effectiveConfig.footer.backgroundColor,
                            fontFamily: effectiveConfig.footer.fontFamily,
                            width: effectiveConfig.content.width,
                          }"
                        >
                          <tbody>
                            <tr>
                              <td
                                class="content-cell"
                                :style="{
                                  textAlign: effectiveConfig.footer.alignment,
                                  color: effectiveConfig.footer.fontColor,
                                  fontSize: `${effectiveConfig.footer.fontSize}`,
                                  padding: `${effectiveConfig.footer.padding.pt}px ${effectiveConfig.footer.padding.pr}px ${effectiveConfig.footer.padding.pb}px ${effectiveConfig.footer.padding.pl}px`,
                                }"
                              >
                                <div
                                  v-if="logoImage && effectiveConfig.logo.position === 'footer'"
                                  :style="{
                                    textAlign: effectiveConfig.logo.alignment,
                                    marginBottom: '20px',
                                  }"
                                >
                                  <img
                                    :src="logoImage"
                                    class="inline-block"
                                    alt="Logo"
                                    :style="{
                                      width: effectiveConfig.logo.width,
                                    }"
                                  />
                                </div>
                                <p class="email-footer-text">
                                  © 2025 Prindustry. All rights reserved.
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Editor Controls -->
    <div class="flex h-12 items-center justify-between border-t border-gray-200 bg-gray-50" />
  </UICard>
</template>

<script setup>
const { t: $t } = useI18n();

const props = defineProps({
  config: {
    type: Object,
    required: false,
    default: () => ({}),
  },
  fetchConfig: {
    type: Boolean,
    default: false,
  },
  primaryButton: {
    type: [String, Boolean],
    required: false,
    default: null,
  },
  secondaryButton: {
    type: [String, Boolean],
    required: false,
    default: null,
  },
  tags: {
    type: Array,
    default: () => [],
  },
  readOnly: {
    type: Boolean,
    default: true,
  },
});

const subject = defineModel("subject", { type: String, required: false, default: null });

const { studioConfig: themeConfig, getEmailConfig } = useStudioEmailConfig();
const themeStudio = useStudioSettings({
  namespace: "themes",
  area: "mail",
  config: themeConfig,
});

const emailConfig = getEmailConfig(themeStudio.getValue);
const slotContainer = ref();
const { processElementContent } = useMagicTags();
let slotObserver = null;

const processSlotTags = () => {
  if (slotContainer.value && props.tags.length > 0) {
    processElementContent(slotContainer.value, props.tags, props.readOnly);
  }
};

onMounted(() => {
  if (props.fetchConfig) {
    themeStudio.load();
  }

  // Process slot content for magic tags initially
  nextTick(() => {
    processSlotTags();

    // Set up observer to watch for slot content changes
    if (slotContainer.value && props.tags.length > 0) {
      slotObserver = new MutationObserver(() => {
        processSlotTags();
      });

      slotObserver.observe(slotContainer.value, {
        childList: true,
        subtree: true,
        characterData: true,
      });
    }
  });
});

onBeforeUnmount(() => {
  if (slotObserver) {
    slotObserver.disconnect();
    slotObserver = null;
  }
});

// Watch for changes in tags and reprocess slot content
watch(
  () => props.tags,
  (newTags) => {
    if (slotContainer.value && newTags.length > 0) {
      processSlotTags();
    }
  },
  { deep: true },
);

const defaultConfig = {
  theme: {
    backgroundColor: "#FFFFFF",
    primaryColor: "#2563EB",
    primaryTextColor: "#FFFFFF",
    secondaryColor: "#4B5563",
    secondaryTextColor: "#FFFFFF",
  },
  logo: {
    image: null,
    width: "150px",
    position: "header",
    alignment: "center",
  },
  header: {
    backgroundColor: "#FFFFFF",
    padding: { pt: 20, pr: 20, pb: 20, pl: 20 },
    alignment: "center",
  },
  content: {
    width: "600px",
    backgroundColor: "#FFFFFF",
    alignment: "left",
    padding: { pt: 20, pr: 20, pb: 20, pl: 20 },
    borderWidth: 0,
    borderColor: "#DDDDDD",
    borderRadius: { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 },
  },
  text: {
    fontFamily:
      '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
    fontSize: 16,
    fontColor: "#333333",
  },
  buttons: {
    alignment: "left",
    padding: { pt: 10, pr: 20, pb: 10, pl: 20 },
    borderRadius: { tr: 5, tl: 5, br: 5, bl: 5 },
  },
  footer: {
    backgroundColor: "#F3F4F6",
    alignment: "center",
    fontFamily:
      '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
    fontSize: 12,
    fontColor: "#6B7280",
    padding: { pt: 20, pr: 20, pb: 20, pl: 20 },
  },
};

const effectiveConfig = computed(() => {
  if (props.fetchConfig) return emailConfig.value;
  const propsConfig = props.config || {};
  return {
    theme: { ...defaultConfig.theme, ...(propsConfig.theme || {}) },
    logo: { ...defaultConfig.logo, ...(propsConfig.logo || {}) },
    header: {
      ...defaultConfig.header,
      ...(propsConfig.header || {}),
      padding: { ...defaultConfig.header.padding, ...(propsConfig.header?.padding || {}) },
    },
    content: {
      ...defaultConfig.content,
      ...(propsConfig.content || {}),
      padding: { ...defaultConfig.content.padding, ...(propsConfig.content?.padding || {}) },
      borderRadius: {
        ...defaultConfig.content.borderRadius,
        ...(propsConfig.content?.borderRadius || {}),
      },
    },
    text: { ...defaultConfig.text, ...(propsConfig.text || {}) },
    buttons: {
      ...defaultConfig.buttons,
      ...(propsConfig.buttons || {}),
      padding: { ...defaultConfig.buttons.padding, ...(propsConfig.buttons?.padding || {}) },
      borderRadius: {
        ...defaultConfig.buttons.borderRadius,
        ...(propsConfig.buttons?.borderRadius || {}),
      },
    },
    footer: {
      ...defaultConfig.footer,
      ...(propsConfig.footer || {}),
      padding: { ...defaultConfig.footer.padding, ...(propsConfig.footer?.padding || {}) },
    },
  };
});

const logoImage = computed(() => unref(effectiveConfig.value?.logo?.image));

// Map button alignment to CSS justify-content values
const buttonAlignmentStyle = computed(() => {
  const alignment = effectiveConfig.value?.buttons?.alignment;
  if (alignment === "left") return "flex-start";
  if (alignment === "center") return "center";
  if (alignment === "right") return "flex-end";
  return "flex-start"; // Default
});

// Primary button text and visibility
const primaryButtonText = computed(() => {
  if (props.primaryButton === false) return null;
  if (typeof props.primaryButton === "string") return props.primaryButton;
  return $t("Primary Button");
});

const showPrimaryButton = computed(() => {
  return props.primaryButton !== false;
});

// Secondary button text and visibility
const secondaryButtonText = computed(() => {
  if (props.secondaryButton === false) return null;
  if (typeof props.secondaryButton === "string") return props.secondaryButton;
  return $t("Secondary Button");
});

const showSecondaryButton = computed(() => {
  return props.secondaryButton !== false;
});
</script>

<style scoped>
/* Email Template Styles */
.email-base-style {
  box-sizing: border-box;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif,
    "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  position: relative;
}

.email-content-wrapper {
  box-sizing: border-box;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif,
    "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  -premailer-width: 100%;
  padding: 0;
  width: 100%;
}

.email-wrapper {
  box-sizing: border-box;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif,
    "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  -premailer-width: 100%;
  margin: 0;
  padding: 0;
  width: 100%;
}

.email-header {
  box-sizing: border-box;
  position: relative;
  padding: 12px 0;
}

.email-header-link {
  position: relative;
  color: #3d4852;
  font-size: 19px;
  font-weight: bold;
  text-decoration: none;
  display: inline-block;
}

.email-header-link img {
  display: block;
  max-width: 100%;
}

.email-body {
  box-sizing: border-box;
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  -premailer-width: 100%;
  border-bottom: 1px solid #edf2f7;
  border-top: 1px solid #edf2f7;
  margin: 0;
  padding: 0;
  width: 100%;
  border: hidden !important;
}

.email-inner-body {
  box-sizing: border-box;
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  padding: 0;
}

.content-cell {
  box-sizing: border-box;
  position: relative;
  max-width: 100vw;
}

.email-heading {
  position: relative;
  font-size: 18px;
  font-weight: bold;
  margin-top: 0;
}

.email-paragraph {
  position: relative;
  line-height: 1.5em;
  margin-top: 0;
}

.email-action {
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  margin: 30px auto;
  padding: 0;
  text-align: center;
  width: 100%;
}

.button-primary {
  position: relative;
  -webkit-text-size-adjust: none;
  border-radius: 4px;
  color: #fff;
  display: inline-block;
  overflow: hidden;
  text-decoration: none;
  background-color: #2d3748;
  border-bottom: 8px solid #2d3748;
  border-left: 18px solid #2d3748;
  border-right: 18px solid #2d3748;
  border-top: 8px solid #2d3748;
}

.email-footer {
  position: relative;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  margin: 0 auto;
  padding: 0;
}

.email-footer-text {
  position: relative;
  line-height: 1.5em;
  margin-top: 0;
}

.email-container {
  box-sizing: border-box;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif,
    "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  position: relative;
  -webkit-text-size-adjust: none;
  background-color: #ffffff;
  color: #718096;
  height: 100%;
  line-height: 1.4;
  margin: 0;
  padding: 0;
  width: 100% !important;
}

.content-cell p {
  display: block;
  margin-block-start: 1em;
  margin-block-end: 1em;
  margin-inline-start: 0px;
  margin-inline-end: 0px;
  unicode-bidi: isolate;
}

/* Magic tag styles */
:deep(.magic-tag) {
  display: inline-block;
  background-color: #3b82f6;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  transition: all 0.2s ease;
}

:deep(.magic-tag:hover) {
  background-color: #2563eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Readonly magic tag styles */
:deep(.magic-tag--readonly) {
  background-color: #6b7280;
  cursor: default;
  pointer-events: none;
}

:deep(.magic-tag--readonly:hover) {
  background-color: #6b7280;
  box-shadow: none;
}

/* EditorJS Content Styles */
:deep(h1) {
  @apply mb-4 text-3xl font-bold;
}

:deep(h2) {
  @apply mb-3 text-2xl font-bold;
}

:deep(h3) {
  @apply mb-3 text-xl font-semibold;
}

:deep(h4) {
  @apply mb-2 text-lg font-medium;
}

:deep(h5) {
  @apply mb-2 text-base font-medium;
}

:deep(h6) {
  @apply mb-2 text-sm font-medium;
}

:deep(p) {
  @apply mb-4 leading-relaxed;
}

:deep(ul) {
  @apply mb-4 list-disc pl-6;
}

:deep(ol) {
  @apply mb-4 list-decimal pl-6;
}

:deep(li) {
  @apply mb-1;
}

:deep(hr) {
  @apply my-6 border-gray-300;
}
</style>
