<template>
  <div class="flex h-full flex-col p-4 pb-2 pr-2">
    <StudioHeader
      ref="studioHeader"
      v-model:show-not-saved="showNotSaved"
      :subtitle="activeEntity?.displayName"
      :settings-changed="hasChanges"
      :saving="saving"
      no-back-button
      @discard-changes="handleDiscardChanges"
      @save-changes="handleSaveChanges"
    />
    <div class="grid h-full grid-cols-[250px,auto] overflow-hidden rounded-md dark:bg-gray-750">
      <div class="h-full overflow-y-auto shadow-md">
        <StudioTree class="bg-gray-100 pr-4 dark:bg-gray-800" :tree-config="treeConfig" />
      </div>
      <div
        class="h-full overflow-y-auto rounded-b-md border-b bg-gray-50 shadow-md dark:border-gray-900 dark:bg-gray-750"
      >
        <NuxtPage
          v-if="!error"
          ref="studioPage"
          class="bg-gray-50 pb-2 dark:bg-gray-750"
          v-model:has-changes="hasChanges"
          @saving="saving = $event"
        />
        <StudioError v-else :error="error" @try-again="error = null" />
      </div>
    </div>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();
const route = useRoute();

const hasChanges = ref(false);
const saving = ref(false);

// Provide the Studio Header to the Quotation and Invoice pages for scaling purposes
const studioHeader = ref(null);
provide("studioHeader", studioHeader);

// Call the the Discard and Save methods from the current Studio Page
const studioPage = ref(null);
const handleDiscardChanges = () => studioPage.value.pageRef.reset();
const handleSaveChanges = () => studioPage.value.pageRef.save();

// Error Handling
const error = ref(null);
onErrorCaptured((err, instance, info) => {
  error.value = {
    statusCode: 500,
    message: err.message,
  };
  console.log("error: ", err, "instance: ", instance, "info: ", info);
  return false; // Prevent the error from propagating further
});

// Navigation Configuration
const treeConfig = ref([
  {
    key: "personal",
    title: $t("Personal"),
    icon: "user",
    expanded: true,
    children: [
      {
        key: "theme",
        route: "/manage/studio/settings",
        displayName: $t("Local theme"),
        icon: "palette",
      },
    ],
  },
  {
    key: "templates",
    title: $t("Templates"),
    icon: "hammer-brush",
    expanded: true,
    children: [
      {
        key: "emails",
        route: "/manage/studio/emails",
        displayName: $t("Email"),
        icon: "envelope-open-text",
      },
      {
        key: "pdf",
        type: "sub-section",
        title: $t("PDFs"),
        icon: "file-pdf",
        expanded: true,
        children: [
          {
            key: "invoice",
            route: "/manage/studio/invoice",
            displayName: $t("Invoice"),
            icon: "file-invoice-dollar",
          },
          {
            key: "quotation",
            route: "/manage/studio/quotation",
            displayName: $t("Quotation"),
            icon: "file-invoice",
          },
        ],
      },
    ],
  },
  {
    key: "email-content",
    title: $t("Email Content"),
    icon: "envelope",
    expanded: true,
    children: [
      {
        key: "quotation-email-content",
        route: "/manage/studio/email-content/quotation",
        displayName: $t("Quotation"),
        icon: "file-invoice",
      },
      {
        key: "order-email-content",
        route: "/manage/studio/email-content/order",
        displayName: $t("Order"),
        icon: "file-invoice",
      },
      {
        key: "invoice-email-content",
        route: "/manage/studio/email-content/invoice",
        displayName: $t("Invoice"),
        icon: "file-invoice",
      },
    ],
  },
]);

// Default redirect
if (route.name === "manage-studio") navigateTo(treeConfig.value[0].children[0].route);

// Get the active entity based on current route
const getEntity = (p) => {
  for (const section of treeConfig.value) {
    const child = section.children.find((ent) => ent.route === p);
    if (child) return child;
  }
  return null;
};

// Get the active entity based on current route
const activeEntity = computed(() => getEntity(route.path));

/**
 * Show a warning when the user tries to leave the page without saving the changes
 */
const showNotSaved = ref(false);
onBeforeRouteLeave((_, __, next) => {
  if (hasChanges.value) {
    showNotSaved.value = true;
    return next(false);
  }
  next();
});
onBeforeRouteUpdate((_, __, next) => {
  if (hasChanges.value) {
    showNotSaved.value = true;
    return next(false);
  }
  next();
});
</script>
