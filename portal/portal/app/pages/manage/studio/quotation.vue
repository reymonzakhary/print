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
      <StudioConfigPreview ref="container">
        <StudioEntityPDFQuotation
          :loading="imagesLoading"
          class="z-10 mx-auto aspect-[1/1.414] rounded shadow-xl !shadow-black/10"
          :font-size="Number(fontSize)"
          :font-family="fontFamily"
          :background-image="backgroundImageBlob"
          :show-guides="showGuides"
          :logo="logoBlob"
          :logo-width="Number(logoWidth)"
          :logo-position="logoPosition"
          :address-direction="addressDirection"
          :address-offset="Number(addressOffset)"
          :style="scaleStyle"
        />
        <StudioGuidesToggle v-model="showGuides" />
      </StudioConfigPreview>
    </template>
  </StudioContent>
</template>

<script setup>
const { addToast } = useToastStore();
const { t: $t } = useI18n();

const emit = defineEmits(["saving"]);
const hasChanges = defineModel("hasChanges", { type: Boolean, default: false });

// Content Guidelines
const showGuides = ref(true);

// Studio Configuration
const studioConfig = ref([
  {
    id: "logo",
    icon: "image",
    displayName: $t("Logo & Background"),
    fields: [
      {
        settingKey: null,
        supplementaryFor: "quotation_logo_width",
        label: $t("Logo Visibility"),
        type: "radio",
        value: 300,
        options: [
          { value: 0, icon: ["fas", "eye-slash"] },
          { value: 300, icon: ["fas", "eye"] },
        ],
      },
      {
        settingKey: "quotation_logo_width",
        label: $t("Logo width:"),
        type: "input",
        placeholder: "300",
        value: 300,
        inputType: "number",
      },
      {
        settingKey: "quotation_logo_position",
        label: $t("Logo position:"),
        type: "radio",
        value: "center",
        options: [
          { value: "left", icon: ["fas", "align-left"] },
          { value: "center", icon: ["fas", "align-center"] },
          { value: "right", icon: ["fas", "align-right"] },
        ],
      },
      {
        settingKey: "quotation_logo",
        label: $t("Logo Image:"),
        type: "image",
        value: null,
        withFetch: true,
      },
      {
        settingKey: "quotation_background",
        label: $t("Background image:"),
        type: "image",
        value: null,
        withFetch: true,
      },
    ],
  },
  {
    id: "address",
    icon: "address-card",
    displayName: $t("Address Styling"),
    fields: [
      {
        settingKey: "quotation_customer_address_position",
        label: $t("Address Offset"),
        type: "input",
        placeholder: "0",
        value: 0,
        inputType: "number",
      },
      {
        settingKey: "quotation_customer_address_position_direction",
        label: $t("Address Position"),
        type: "radio",
        value: "ltr",
        options: [
          { value: "ltr", icon: ["fas", "align-left"] },
          { value: "rtl", icon: ["fas", "align-right"] },
        ],
      },
    ],
  },
  {
    id: "font",
    icon: "font",
    displayName: $t("Font Styling"),
    fields: [
      {
        settingKey: "quotation_font",
        label: $t("Font family:"),
        type: "select",
        value: "Lucida Grande",
        options: ["Lucida Grande", "Helvetica", "Arial", "Verdana"],
      },
      {
        settingKey: "quotation_font_size",
        label: $t("Font size:"),
        type: "input",
        placeholder: "12",
        value: 12,
        inputType: "number",
      },
    ],
  },
]);

// Use the simplified settings composable
const studio = useStudioSettings({
  namespace: "orders",
  area: "quotation",
  config: studioConfig,
  onSuccess: () => {
    addToast({
      type: "success",
      message: $t("Quotation saved successfully"),
    });
  },
});

// Handle field updates from the config component
const handleFieldUpdate = ({ settingKey, value }) => studio.update({ settingKey, value });

// Sync hasChanges with studio state
watchEffect(() => (hasChanges.value = studio.isDirty.value));

// Sync saving state with parent
watchEffect(() => emit("saving", studio.saving.value));

// Get the values of the fields for the Quotation component
const fontFamily = studio.getValue("quotation_font", "Lucida Grande");
const fontSize = studio.getValue("quotation_font_size", 12);
const logoWidth = studio.getValue("quotation_logo_width", 300);
const logoPosition = studio.getValue("quotation_logo_position", "center");
const addressDirection = studio.getValue("quotation_customer_address_position_direction", "ltr");
const addressOffset = studio.getValue("quotation_customer_address_position", 0);

/**
 * Image Management
 */
const { getPreview } = useLoadImage();
// Get the fields for the Quotation component
const logoField = computed(() => studio.values.value["quotation_logo"]);
const backgroundField = computed(() => studio.values.value["quotation_background"]);
// Get the image blobs and loading states
const { blob: logoBlob, loading: logoLoading } = getPreview(logoField);
const { blob: backgroundImageBlob, loading: bgLoading } = getPreview(backgroundField);
// Track the overall loading state
const imagesLoading = computed(() => logoLoading.value || bgLoading.value);

/**
 * Scaling
 */
const container = ref(null);
const header = inject("studioHeader", ref(null));
const { scaleStyle, initializeScaling } = useElementScaling({
  containerRef: container,
  headerRef: header,
  // A target height of 1122.5 is the A4 height in pixels
  targetHeight: 1122.5,
  // Run the scaling function twice because of some weird reason
  runTwice: true,
});

// Initialize on mount
onMounted(async () => {
  await studio.load();
  await nextTick();
  await initializeScaling();
});

// Expose methods for parent component
defineExpose({
  save: studio.save,
  reset: studio.reset,
});
</script>
