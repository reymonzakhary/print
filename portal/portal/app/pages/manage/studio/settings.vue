<template>
  <StudioContent>
    <template #sidebar>
      <div class="px-4">
        <StudioTreeSection
          v-for="section in settingSections"
          :key="section.key"
          :title="section.displayName"
          :icon="section.icon"
          not-expandable
        >
          <StudioTreeItem
            v-for="item in section.children"
            :key="item.key"
            :icon="item.icon"
            :display-name="item.displayName"
            :is-active="activeSection === item.key"
            @click="activeSection = item.key"
          />
        </StudioTreeSection>
      </div>
    </template>

    <template #content>
      <transition as="div" class="max-w-5xl px-8 py-6" name="subpage">
        <!-- Theme Section -->
        <div v-if="activeSection === 'theme'" class="space-y-10">
          <StudioUIHeader
            icon="palette"
            :title="currentSection.displayName"
            :description="currentSection.description"
            variant="primary"
          />

          <!-- Color Presets -->
          <section>
            <StudioUIHeader icon="swatchbook" :title="$t('Color Presets')" variant="secondary" />
            <div class="mb-4 grid grid-cols-4 gap-4 sm:grid-cols-5 md:grid-cols-7 lg:grid-cols-9">
              <StudioThemeSwatch
                v-for="(color, index) in presetColors"
                :key="index"
                :color="color.hex"
                :label="$t(color.name)"
                :active="color.hex === hex"
                @click="previewTheme(color.hex)"
              />
            </div>
          </section>

          <!-- Custom Color Picker -->
          <section
            class="grid gap-x-8 rounded-xl bg-gray-100 p-6 dark:bg-gray-800 md:grid-cols-[1fr_2fr]"
          >
            <StudioUIHeader
              icon="paint-brush"
              :title="$t('Custom Color')"
              variant="secondary"
              class="col-span-full"
            />
            <StudioThemePicker v-model="hex" />

            <!-- Color Shades -->
            <div>
              <StudioUISubtitle :title="$t('Generated Shades')" />
              <div class="grid grid-cols-5 gap-2">
                <StudioThemeShade
                  v-for="(shade, i) in colorShades.shades"
                  :key="`shade_${i}`"
                  :color="shade.hex"
                  :text-color="shade.textColor"
                  :label="`${i === 0 ? 50 : i * 100}`"
                />
              </div>
            </div>
          </section>

          <!-- Preview -->
          <section
            class="grid gap-x-6 rounded-xl bg-gray-100 p-6 pb-6 dark:bg-gray-800 md:grid-cols-2"
          >
            <StudioUIHeader
              icon="desktop"
              :title="$t('Preview')"
              variant="secondary"
              class="col-span-full"
            />
            <!-- Buttons -->
            <UICard class="flex flex-col" rounded-full>
              <UICardHeader>
                <template #left>
                  <UICardHeaderTitle :icon="['fal', 'desktop']" :title="$t('Buttons')" />
                </template>
              </UICardHeader>
              <div class="flex flex-1 items-center justify-center gap-3 p-4">
                <UIButton variant="default">
                  {{ $t("Default") }}
                </UIButton>
                <UIButton variant="theme-light">
                  {{ $t("Theme Light") }}
                </UIButton>
                <UIButton variant="theme">
                  {{ $t("Theme") }}
                </UIButton>
              </div>
            </UICard>

            <!-- Text & Links -->
            <UICard rounded-full>
              <UICardHeader>
                <template #left>
                  <UICardHeaderTitle :icon="['fal', 'desktop']" :title="$t('Text & Links')" />
                </template>
              </UICardHeader>
              <div class="p-4">
                <p class="mb-2 text-gray-900 dark:text-white">
                  {{ $t("This is standard text.") }}
                </p>
                <p class="mb-2 text-gray-600 dark:text-gray-400">
                  {{ $t("This is secondary text.") }}
                </p>
                <a href="#" class="text-theme-500 hover:text-theme-600 hover:underline">
                  {{ $t("This is a link.") }}
                </a>
              </div>
            </UICard>
          </section>
        </div>

        <!-- Other Settings Sections -->
        <div v-else>
          <!-- Header -->
          <StudioUIHeader
            :icon="currentSection.icon"
            :title="currentSection.displayName"
            :description="currentSection.description"
            variant="primary"
          />

          <!-- Settings Groups -->
          <div class="space-y-8">
            <StudioThemeSettingsList
              v-for="(group, groupIndex) in currentSection.groups || []"
              :key="groupIndex"
              :title="group.displayName"
              :icon="group.icon"
            >
              <StudioThemeSettingsListItem
                v-for="(setting, settingIndex) in group.settings"
                :key="settingIndex"
                :setting-key="setting.key"
                :name="`setting-${group.key}-${setting.key}`"
                :title="setting.displayName"
                :description="setting.description"
                :value="!!settingsValues[setting.key]"
                @toggle="updateSetting($event.key, $event.value)"
              />
            </StudioThemeSettingsList>
          </div>
        </div>
      </transition>
    </template>
  </StudioContent>
</template>

<script setup>
import { useStore } from "vuex";

const hasChanges = defineModel("hasChanges", { type: Boolean, default: false });

defineExpose({
  reset: resetSettings,
  save: saveSettings,
});

const { t: $t } = useI18n();
const store = useStore();
const themeStore = useThemeStore();

/**
 * Navigation Management
 */
const route = useRoute();
const activeSection = computed({
  get: () => route.query.section || "theme",
  set: (val) => navigateTo({ query: { section: val } }),
});

// Navigation Items
const settingSections = computed(() => [
  {
    key: "settings",
    displayName: $t("Settings"),
    icon: "cog",
    expanded: true,
    children: [
      {
        key: "theme",
        icon: "palette",
        displayName: $t("Theme"),

        description: $t(
          "Customize the appearance of your portal by choosing colors and visual styles.",
        ),
      },
      {
        key: "behavior",
        icon: "cog",
        displayName: $t("Behavior"),
        description: $t("Configure how the application behaves and operates"),
        groups: [
          {
            key: "theme",
            displayName: $t("Display Mode"),
            icon: "moon-stars",
            settings: [
              {
                key: "darkMode",
                displayName: $t("Dark Mode"),
                description: $t("Switch between light and dark mode"),
              },
              {
                key: "autoTheme",
                displayName: $t("Auto Theme"),
                description: $t("Follow system color scheme preference"),
              },
            ],
          },
          {
            key: "general",
            displayName: $t("General Settings"),
            icon: "sliders-h",
            settings: [
              {
                key: "showResourceIDs",
                displayName: $t("Show Resource IDs"),
                description: $t("Display resource IDs in listings and details"),
              },
              {
                key: "disableForever",
                displayName: $t("Never Show No Calculation Message"),
                description: $t("Permanently hide calculation warning messages"),
              },
            ],
          },
        ],
      },
    ],
  },
]);

/**
 * Settings State Management
 */
// Settings state
const settingsValues = ref({
  showResourceIDs: store.getters["usersettings/showResourceIDs"] || false,
  disableForever: localStorage.getItem("disableForever") === "true",
  darkMode: themeStore.activeTheme === "dark",
  autoTheme: themeStore.autoTheme,
  themeColor: themeStore.hex,
});

// Store original settings to track changes and for resetting them
const originalSettings = ref({ ...settingsValues.value });

// Current Active Section
const currentSection = computed(() => {
  for (const section of settingSections.value) {
    if (section.children) {
      const foundChild = section.children.find((child) => child.key === activeSection.value);
      if (foundChild) {
        return foundChild;
      }
    }
  }
  return settingSections.value[0].children[0];
});

/**
 * Theme Settings Management
 */
const presetColors = [
  { displayName: $t("Prindustry Blue"), name: "Prindustry Blue", hex: "#46afcf" },
  { displayName: $t("Sharp Blue"), name: "Sharp Blue", hex: "#1e4cf1" },
  { displayName: $t("Red"), name: "Red", hex: "#f87171" },
  { displayName: $t("Orange"), name: "Orange", hex: "#fb923c" },
  { displayName: $t("Amber"), name: "Amber", hex: "#fbbf24" },
  { displayName: $t("Yellow"), name: "Yellow", hex: "#facc15" },
  { displayName: $t("Lime"), name: "Lime", hex: "#a3e635" },
  { displayName: $t("Green"), name: "Green", hex: "#4ade80" },
  { displayName: $t("Emerald"), name: "Emerald", hex: "#34d399" },
  { displayName: $t("Teal"), name: "Teal", hex: "#2dd4bf" },
  { displayName: $t("Cyan"), name: "Cyan", hex: "#22d3ee" },
  { displayName: $t("Sky"), name: "Sky", hex: "#38bdf8" },
  { displayName: $t("Blue"), name: "Blue", hex: "#2e89f8" },
  { displayName: $t("Indigo"), name: "Indigo", hex: "#818cf8" },
  { displayName: $t("Violet"), name: "Violet", hex: "#a78bfa" },
  { displayName: $t("Purple"), name: "Purple", hex: "#c084fc" },
  { displayName: $t("Fuchsia"), name: "Fuchsia", hex: "#e879f9" },
  { displayName: $t("Pink"), name: "Pink", hex: "#f472b6" },
];

// Local preview state
const hex = computed({
  get: () => settingsValues.value.themeColor,
  set: (val) => updateSetting("themeColor", val),
});

// Color shades are now in the theme store
const colorShades = computed(() => themeStore.colorShades);

// Preview theme without saving permanently
const previewTheme = (color) => updateSetting("themeColor", color);

// Watch for external dark mode changes
watch(
  () => themeStore.activeTheme,
  (newValue) => {
    if (settingsValues.value.darkMode !== (newValue === "dark")) {
      settingsValues.value.darkMode = newValue === "dark";
    }
  },
);

// Watch for external auto theme changes
watch(
  () => themeStore.autoTheme,
  (newValue) => {
    if (settingsValues.value.autoTheme !== newValue) {
      settingsValues.value.autoTheme = newValue;
    }
  },
);

/**
 * Regular Settings Management
 */
// Reset before unmounting if changes haven't been applied
onBeforeUnmount(() => hasChanges.value && resetSettings());

// Update a singular setting
function updateSetting(id, value) {
  settingsValues.value[id] = value;

  // Apply theme settings immediately for preview
  switch (id) {
    case "darkMode":
      themeStore.setActiveTheme(value ? "dark" : "light");
      break;
    case "autoTheme":
      themeStore.setAutoTheme(value);
      break;
    case "themeColor":
      themeStore.previewHexColor(value);
      break;
  }

  // Check if values have changed from original
  const hasChanged = Object.keys(settingsValues.value).some(
    (key) => settingsValues.value[key] !== originalSettings.value[key],
  );

  // Update hasChanges model value
  hasChanges.value = hasChanged;
}

// Reset settings to original state
function resetSettings() {
  settingsValues.value = { ...originalSettings.value };

  // Apply theme settings immediately after reset
  themeStore.setActiveTheme(settingsValues.value.darkMode ? "dark" : "light");
  themeStore.setAutoTheme(settingsValues.value.autoTheme);

  // Reset theme color
  themeStore.setHexColor(settingsValues.value.themeColor);
  themeStore.applyTheme();

  hasChanges.value = false;
}

// Save all settings
function saveSettings() {
  // Save theme settings to theme store
  if (settingsValues.value.darkMode !== originalSettings.value.darkMode) {
    themeStore.setActiveTheme(settingsValues.value.darkMode ? "dark" : "light");
  }

  if (settingsValues.value.autoTheme !== originalSettings.value.autoTheme) {
    themeStore.setAutoTheme(settingsValues.value.autoTheme);
    if (settingsValues.value.autoTheme) {
      themeStore.detectColorScheme();
    }
  }

  // Save theme color if changed
  if (settingsValues.value.themeColor !== originalSettings.value.themeColor) {
    themeStore.setHexColor(settingsValues.value.themeColor);
  }

  // Save showResourceIDs to Vuex store
  if (settingsValues.value.showResourceIDs !== originalSettings.value.showResourceIDs) {
    store.dispatch("usersettings/set_showResourceIDs", settingsValues.value.showResourceIDs);
  }

  // Save other settings to localStorage
  Object.keys(settingsValues.value).forEach((key) => {
    if (
      key !== "showResourceIDs" &&
      key !== "darkMode" &&
      key !== "autoTheme" &&
      key !== "themeColor"
    ) {
      localStorage.setItem(key, settingsValues.value[key]);
    }
  });

  // Update original settings to match current values
  originalSettings.value = { ...settingsValues.value };

  // Reset hasChanges flag
  hasChanges.value = false;
}
</script>
