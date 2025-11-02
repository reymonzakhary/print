# Prindustry Studio 2.0 Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Quick Start Guide](#quick-start-guide)
3. [Architecture Overview](#architecture-overview)
4. [Development Guidelines](#development-guidelines)
5. [Core Components](#core-components)
6. [State Management](#state-management)
7. [Complete Configuration Reference](#complete-configuration-reference)
8. [EditorJS Deep Dive](#editorjs-deep-dive)
9. [Magic Tags System](#magic-tags-system)
10. [Creating New Studio Pages](#creating-new-studio-pages)
11. [Email Templates](#email-templates)
12. [PDF Templates](#pdf-templates)
13. [Best Practices](#best-practices)
14. [Testing](#testing)
15. [Troubleshooting](#troubleshooting)

## Introduction

Prindustry Studio 2.0 is a powerful customization platform integrated into Prindustry Manager 3.0. It provides a unified interface for customizing various aspects of the application, including:

- **Email Templates**: Customize the look and feel of system emails
- **PDF Templates**: Design invoice and quotation PDF layouts
- **Theme Settings**: Personalize colors and visual styles
- **Email Content**: Manage multilingual email content with dynamic tags

### Key Features
- 🎨 Real-time preview of changes
- 🌐 Multi-language support
- 🏷️ Dynamic content with Magic Tags
- 💾 Automatic saving and state management
- 🔄 Change tracking with discard/save functionality
- ✏️ Rich text editing with EditorJS
- 🖼️ Image upload and management
- 📱 Responsive design tools

## Quick Start Guide

### Creating Your First Studio Page (5 minutes)

#### Step 1: Create the Page File
Create `pages/studio/my-feature.vue`:

```vue
<template>
  <StudioWrapper>
    <StudioHeader 
      :subtitle="'My Feature Settings'"
      :settings-changed="hasChanges"
      :saving="isSaving"
      @save-changes="save"
      @discard-changes="reset"
    />
    <StudioContainer>
      <template #navigation>
        <StudioTree :tree-config="treeConfig" />
      </template>
      <template #content>
        <StudioContent :loading="studio.loading.value">
          <template #sidebar>
            <StudioConfig
              :config="studioConfig"
              :values="studio.values.value"
              @field-update="studio.update"
            />
          </template>
          
          <template #content>
            <StudioConfigPreview>
              <div class="p-4 bg-white rounded-lg">
                <h2 :style="{ color: primaryColor }">
                  Preview: {{ featureName }}
                </h2>
                <p :style="{ fontSize: fontSize + 'px' }">
                  This is your feature preview
                </p>
              </div>
            </StudioConfigPreview>
          </template>
        </StudioContent>
      </template>
    </StudioContainer>
  </StudioWrapper>
</template>

<script setup>
// Page state
const hasChanges = ref(false)
const isSaving = ref(false)

// Studio configuration
const studioConfig = ref([
  {
    id: "general",
    icon: "cog",
    displayName: "General Settings",
    fields: [
      {
        settingKey: "feature_name",
        label: "Feature Name",
        type: "input",
        value: "My Awesome Feature"
      },
      {
        settingKey: "primary_color",
        label: "Primary Color",
        type: "color",
        value: "#2563EB"
      },
      {
        settingKey: "font_size",
        label: "Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24
      }
    ]
  }
])

// Navigation tree (minimal for this page)
const treeConfig = ref([
  {
    key: "settings",
    title: "Settings",
    icon: "cog",
    children: [
      {
        key: "general",
        displayName: "General",
        icon: "settings"
      }
    ]
  }
])

// Initialize studio
const studio = useStudioSettings({
  namespace: "my_feature",
  area: "settings",
  config: studioConfig,
  onSuccess: () => {
    addToast({ type: "success", message: "Settings saved!" })
  },
  onError: (error) => {
    addToast({ type: "error", message: "Failed to save settings" })
  }
})

// Reactive values for preview
const featureName = computed(() => studio.getValue("feature_name").value)
const primaryColor = computed(() => studio.getValue("primary_color").value)
const fontSize = computed(() => studio.getValue("font_size").value)

// Sync state
watchEffect(() => {
  hasChanges.value = studio.isDirty.value
  isSaving.value = studio.saving.value
})

// Methods
const save = () => studio.save()
const reset = () => studio.reset()

// Load on mount
onMounted(() => studio.load())
</script>
```

#### Step 2: Add to Navigation
Update `pages/studio.vue` tree configuration:

```javascript
const treeConfig = ref([
  // ... existing config
  {
    key: "my-section",
    title: "My Section",
    icon: "star",
    expanded: true,
    children: [
      {
        key: "my-feature",
        route: "/manage/studio/my-feature",
        displayName: "My Feature",
        icon: "sparkles"
      }
    ]
  }
])
```

#### Step 3: Test Your Page
1. Start the development server
2. Navigate to `/manage/studio/my-feature`
3. Try changing settings and see the preview update
4. Test save/discard functionality

**🎉 Congratulations!** You've created your first Studio page. Now let's dive deeper into the system.

## Architecture Overview

The Studio follows a modular architecture built with Vue 3's Composition API:

```
studio/
├── components/              # UI components
│   ├── StudioWrapper.vue   # Root wrapper with styling
│   ├── StudioHeader.vue    # Header with save/discard
│   ├── StudioTree.vue      # Navigation sidebar
│   ├── StudioContent.vue   # Main content area
│   ├── StudioConfig.vue    # Configuration panel
│   └── preview/            # Preview components
│       ├── StudioConfigPreview.vue
│       ├── StudioEntityPDFInvoice.vue
│       └── StudioEmailModal.vue
├── composables/            # Business logic
│   ├── useStudioSettings.js    # Settings management
│   ├── useStudioLexicons.js    # Multi-language content
│   ├── useStudioState.js       # Global state
│   ├── useEditorJS.js          # EditorJS integration
│   ├── useLoadImage.js         # Image handling
│   └── useElementScaling.js    # PDF scaling
├── pages/                  # Studio pages
│   ├── studio.vue         # Main studio layout
│   ├── emails.vue         # Email template editor
│   ├── invoice.vue        # Invoice PDF editor
│   └── quotation.vue      # Quotation PDF editor
└── plugins/               # EditorJS plugins
    └── editorjs/
        ├── MagicTagInlineTool.js
        ├── MagicTagProcessor.js
        └── config.js
```

### Core Concepts

1. **Settings**: Configuration values stored in the backend, managed by namespace/area
2. **Lexicons**: Multilingual content entries with EditorJS support
3. **State Management**: Reactive state tracking with automatic change detection
4. **Preview System**: Real-time visualization of changes with scaling support
5. **Magic Tags**: Dynamic placeholders that get replaced with actual data
6. **Configuration Schema**: Declarative field definitions for forms and validation

### Project Structure Guidelines

#### File Naming Conventions
```
pages/studio/
├── feature-name.vue        # Kebab case for routes
├── complex-feature/
│   ├── index.vue          # Main page
│   ├── settings.vue       # Sub-page
│   └── preview.vue        # Preview component
```

#### Component Organization
```
components/studio/
├── config/                # Configuration components
│   ├── StudioColorPicker.vue
│   ├── StudioImageUpload.vue
│   └── StudioFieldGroup.vue
├── preview/               # Preview components
│   ├── email/
│   └── pdf/
└── editor/                # EditorJS related
    ├── StudioEditorJS.vue
    └── tools/
```

### Code Style Guidelines

#### Composable Usage
Always use Studio composables for state management:

```javascript
// ✅ Good: Using Studio composables
const studio = useStudioSettings({
  namespace: "emails",
  area: "quotation",
  config: configSchema
})

// ❌ Bad: Manual state management
const settings = ref({})
const hasChanges = ref(false)
```

#### Reactive Values
Use computed properties for derived state:

```javascript
// ✅ Good: Reactive computed
const emailStyle = computed(() => ({
  backgroundColor: studio.getValue("background_color").value,
  color: studio.getValue("text_color").value,
  fontSize: studio.getValue("font_size").value + 'px'
}))

// ❌ Bad: Manual reactive management
const emailStyle = ref({})
watch([bgColor, textColor, fontSize], ([bg, text, size]) => {
  emailStyle.value = { backgroundColor: bg, color: text, fontSize: size + 'px' }
})
```

## Core Components

### StudioWrapper
The root component that provides the Studio context and styling.

```vue
<StudioWrapper>
  <!-- All Studio content must be wrapped in this component -->
  <!-- It provides: -->
  <!-- - Studio-specific CSS scoping -->
  <!-- - Global event bus -->
  <!-- - Theme context -->
  <!-- - Responsive breakpoints -->
</StudioWrapper>
```

**Features:**
- Provides CSS custom properties for theming
- Sets up keyboard shortcuts (Ctrl+S for save)
- Manages responsive breakpoints
- Provides Studio-wide event bus

### StudioHeader
Manages the top navigation bar with save/discard functionality.

```vue
<StudioHeader
  :subtitle="pageTitle"
  :settings-changed="hasChanges"
  :saving="isSaving"
  :no-back-button="false"
  @save-changes="save"
  @discard-changes="reset"
/>
```

**Props:**
- `subtitle` (String): Page title displayed in the header
- `settings-changed` (Boolean): Shows save/discard buttons when true
- `saving` (Boolean): Shows loading state during save operations
- `no-back-button` (Boolean): Hides the back navigation button
- `disabled` (Boolean): Disables all interactions

**Events:**
- `save-changes`: Emitted when save button is clicked
- `discard-changes`: Emitted when discard button is clicked
- `back`: Emitted when back button is clicked (if not disabled)

### StudioTree
Navigation tree component for the sidebar.

```vue
<StudioTree 
  :tree-config="navigationConfig"
  :active-key="currentKey"
  @node-click="handleNodeClick"
/>
```

**Tree Configuration Schema:**
```javascript
const treeConfig = [
  {
    key: "unique_identifier",        // Required: Unique node identifier
    title: "Display Title",          // Required: Shown in the tree
    icon: "fontawesome-icon-name",   // Optional: Icon next to title
    route: "/path/to/page",          // Optional: Navigation route
    displayName: "Alternative Name", // Optional: Different name for breadcrumbs
    expanded: true,                  // Optional: Initially expanded
    disabled: false,                 // Optional: Disable interaction
    badge: "New",                    // Optional: Badge text
    children: [                      // Optional: Nested items
      {
        key: "child_key",
        title: "Child Item",
        route: "/child/route"
      }
    ]
  }
]
```

**Advanced Tree Features:**
```javascript
// Conditional nodes
const treeConfig = computed(() => [
  {
    key: "premium_features",
    title: "Premium Features",
    children: userHasPremium.value ? premiumNodes : []
  }
])

// Dynamic badges
{
  key: "notifications",
  title: "Notifications",
  badge: unreadCount.value > 0 ? unreadCount.value.toString() : null
}

// Custom styling
{
  key: "important",
  title: "Important Section",
  class: "text-red-600 font-semibold"
}
```

### StudioContainer
Layout container with navigation and content areas.

```vue
<StudioContainer>
  <template #navigation>
    <StudioTree :tree-config="treeConfig" />
    <!-- Additional navigation content -->
  </template>
  
  <template #content>
    <StudioContent>
      <!-- Main content -->
    </StudioContent>
  </template>
</StudioContainer>
```

### StudioContent
Main content area with optional sidebar.

```vue
<StudioContent 
  :loading="isLoading"
  :error="errorMessage"
  sidebar-width="300px"
>
  <template #sidebar>
    <StudioConfig
      :config="configSchema"
      :values="currentValues"
      @field-update="handleUpdate"
    />
  </template>
  
  <template #content>
    <StudioConfigPreview>
      <!-- Preview content -->
    </StudioConfigPreview>
  </template>
  
  <template #footer>
    <!-- Optional footer content -->
  </template>
</StudioContent>
```

**Props:**
- `loading` (Boolean): Shows loading spinner overlay
- `error` (String): Shows error message if provided
- `sidebar-width` (String): CSS width for sidebar (default: "280px")
- `full-height` (Boolean): Use full viewport height

### StudioConfig
Configuration panel component that renders form fields based on schema.

```vue
<StudioConfig
  :config="configSchema"
  :values="currentValues"
  :errors="validationErrors"
  :disabled="isDisabled"
  @field-update="handleFieldUpdate"
  @field-focus="handleFieldFocus"
  @field-blur="handleFieldBlur"
/>
```

**Events:**
- `field-update`: `(key: string, value: any) => void`
- `field-focus`: `(key: string) => void`
- `field-blur`: `(key: string) => void`
- `section-expand`: `(sectionId: string, expanded: boolean) => void`

## Complete Configuration Reference

### Field Types Reference

#### Input Fields

```javascript
// Text Input
{
  settingKey: "text_field",
  label: "Text Field",
  type: "input",
  inputType: "text",              // text, email, url, tel, password
  placeholder: "Enter text here",
  value: "",
  required: true,
  disabled: false,
  readonly: false,
  maxlength: 255,
  pattern: "^[a-zA-Z0-9]+$",     // Regex pattern
  autocomplete: "off",            // Browser autocomplete
  spellcheck: false,
  validation: {
    rules: ["required", "min:3", "max:50"],
    messages: {
      required: "This field is required",
      min: "Minimum 3 characters required",
      max: "Maximum 50 characters allowed"
    }
  }
}

// Number Input
{
  settingKey: "number_field",
  label: "Number Field",
  type: "input",
  inputType: "number",
  value: 0,
  min: -100,
  max: 100,
  step: 0.1,
  decimals: 2,                   // Force decimal places
  suffix: "px",                  // Display suffix (px, %, etc)
  prefix: "$",                   // Display prefix
  validation: {
    rules: ["required", "number", "min:-100", "max:100"],
    messages: {
      number: "Must be a valid number",
      min: "Must be at least -100",
      max: "Must be at most 100"
    }
  }
}

// Range Slider
{
  settingKey: "range_field",
  label: "Range Field",
  type: "range",
  value: 50,
  min: 0,
  max: 100,
  step: 5,
  showValue: true,               // Show current value
  marks: {                       // Show marks at specific values
    0: "Min",
    50: "Mid",
    100: "Max"
  }
}
```

#### Selection Fields

```javascript
// Select Dropdown
{
  settingKey: "select_field",
  label: "Select Field",
  type: "select",
  value: "option1",
  placeholder: "Choose an option",
  multiple: false,               // Allow multiple selections
  searchable: true,              // Enable search filtering
  clearable: true,               // Allow clearing selection
  loading: false,                // Show loading state
  options: [
    { value: "option1", label: "Option 1", disabled: false },
    { value: "option2", label: "Option 2", icon: "star" },
    { 
      value: "group1", 
      label: "Group 1", 
      children: [                // Grouped options
        { value: "sub1", label: "Sub Option 1" }
      ]
    }
  ],
  // Dynamic options from API
  optionsUrl: "/api/dynamic-options",
  optionsTransform: (data) => data.map(item => ({
    value: item.id,
    label: item.name
  }))
}

// Radio Buttons
{
  settingKey: "radio_field",
  label: "Radio Field",
  type: "radio",
  value: "left",
  layout: "horizontal",          // horizontal, vertical, grid
  options: [
    { 
      value: "left", 
      label: "Left", 
      icon: "align-left",
      description: "Align content to the left"
    },
    { 
      value: "center", 
      label: "Center", 
      icon: "align-center",
      description: "Center the content"
    },
    { 
      value: "right", 
      label: "Right", 
      icon: "align-right",
      description: "Align content to the right"
    }
  ]
}

// Checkbox
{
  settingKey: "checkbox_field",
  label: "Checkbox Field",
  type: "checkbox",
  value: false,
  description: "Enable this feature",
  indeterminate: false,          // Show indeterminate state
  size: "default"                // small, default, large
}

// Checkbox Group
{
  settingKey: "checkbox_group",
  label: "Checkbox Group",
  type: "checkbox-group",
  value: ["option1", "option3"],
  layout: "vertical",            // horizontal, vertical, grid
  options: [
    { value: "option1", label: "Option 1" },
    { value: "option2", label: "Option 2" },
    { value: "option3", label: "Option 3" }
  ]
}
```

#### Media Fields

```javascript
// Color Picker
{
  settingKey: "color_field",
  label: "Color Field",
  type: "color",
  value: "#2563EB",
  format: "hex",                 // hex, rgb, hsl
  alpha: true,                   // Allow transparency
  presets: [                     // Predefined colors
    "#FF0000", "#00FF00", "#0000FF"
  ],
  swatches: {                    // Color palette groups
    "Brand Colors": ["#2563EB", "#DC2626"],
    "Grays": ["#6B7280", "#9CA3AF"]
  }
}

// Image Upload
{
  settingKey: "image_field",
  label: "Image Field",
  type: "image",
  value: null,                   // File object or URL
  accept: "image/*",             // Accepted file types
  maxSize: 5242880,              // Max file size in bytes (5MB)
  maxWidth: 1920,                // Max image width
  maxHeight: 1080,               // Max image height
  crop: true,                    // Enable image cropping
  aspectRatio: 16/9,             // Crop aspect ratio
  preview: true,                 // Show image preview
  withFetch: true,               // Fetch image blob for preview
  uploadUrl: "/api/upload",      // Custom upload endpoint
  onUpload: (file) => {          // Custom upload handler
    return uploadToCustomService(file)
  }
}

// File Upload
{
  settingKey: "file_field",
  label: "File Field",
  type: "file",
  value: null,
  accept: ".pdf,.doc,.docx",
  maxSize: 10485760,             // 10MB
  multiple: false,               // Allow multiple files
  drag: true,                    // Enable drag & drop
  preview: true                  // Show file preview
}
```

#### Layout Fields

```javascript
// Container for grouped fields
{
  type: "container",
  label: "Padding Settings",
  description: "Configure padding for all sides",
  class: "grid grid-cols-2 gap-4 md:grid-cols-4",
  collapsed: false,              // Start collapsed
  collapsible: true,             // Allow collapse/expand
  children: [
    {
      settingKey: "padding.top",
      label: "Top",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.right",
      label: "Right",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.bottom",
      label: "Bottom",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.left",
      label: "Left",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    }
  ]
}

// Tabs Container
{
  type: "tabs",
  tabs: [
    {
      key: "general",
      label: "General",
      icon: "cog",
      children: [
        // Fields for general tab
      ]
    },
    {
      key: "advanced",
      label: "Advanced",
      icon: "sliders",
      children: [
        // Fields for advanced tab
      ]
    }
  ]
}

// Section Divider
{
  type: "divider",
  label: "Advanced Settings",
  description: "Configure advanced options below"
}

// Info/Help Text
{
  type: "info",
  content: "This setting affects how emails are displayed.",
  variant: "info",               // info, warning, error, success
  icon: "info-circle",
  dismissible: false
}
```

#### Advanced Fields

```javascript
// Repeater Field (Dynamic List)
{
  settingKey: "repeater_field",
  label: "Repeater Field",
  type: "repeater",
  value: [],
  addLabel: "Add Item",
  removeLabel: "Remove",
  minItems: 1,
  maxItems: 10,
  sortable: true,                // Allow drag & drop reordering
  template: [                    // Fields for each item
    {
      settingKey: "name",
      label: "Name",
      type: "input",
      value: ""
    },
    {
      settingKey: "value",
      label: "Value",
      type: "input",
      inputType: "number",
      value: 0
    }
  ]
}

// Code Editor
{
  settingKey: "code_field",
  label: "Custom CSS",
  type: "code",
  value: "",
  language: "css",               // css, javascript, html, json
  theme: "dark",                 // light, dark
  lineNumbers: true,
  wordWrap: true,
  height: "200px",
  placeholder: "/* Enter your CSS here */"
}

// Rich Text Editor (EditorJS)
{
  settingKey: "content_field",
  label: "Rich Content",
  type: "editor",
  value: null,                   // EditorJS data object
  placeholder: "Start typing...",
  tools: ["paragraph", "header", "list", "table"],
  magicTags: true,               // Enable magic tag support
  minHeight: "200px",
  maxHeight: "600px"
}

// Date/Time Picker
{
  settingKey: "date_field",
  label: "Date Field",
  type: "date",
  value: null,
  format: "YYYY-MM-DD",
  placeholder: "Select date",
  clearable: true,
  showTime: false,               // Include time picker
  disabledDates: {               // Disable specific dates
    before: new Date(),          // Disable past dates
    after: new Date("2025-12-31"), // Disable future dates
    dates: ["2024-12-25"]        // Disable specific dates
  }
}
```

### Field Validation System

#### Built-in Validation Rules

```javascript
{
  validation: {
    rules: [
      "required",                          // Field is required
      "email",                            // Valid email format
      "url",                              // Valid URL format
      "number",                           // Valid number
      "integer",                          // Valid integer
      "min:5",                            // Minimum value/length
      "max:100",                          // Maximum value/length
      "between:5,100",                    // Value between range
      "in:red,green,blue",               // Value in allowed list
      "regex:^[a-zA-Z0-9]+$",           // Custom regex pattern
      "confirmed:password_confirmation",  // Field confirmation
      "unique:users,email",              // Unique in database
      "exists:categories,id",            // Exists in database
      "file_size:5242880",               // Max file size (5MB)
      "file_types:jpg,png,gif",          // Allowed file types
      "image_dimensions:800,600",        // Image dimensions
      "alpha",                           // Only letters
      "alpha_num",                       // Letters and numbers
      "alpha_dash",                      // Letters, numbers, dashes
      "date",                            // Valid date
      "date_after:2024-01-01",          // Date after specified
      "date_before:2025-12-31"          // Date before specified
    ],
    messages: {
      required: "This field is required",
      email: "Please enter a valid email address",
      min: "Minimum {min} characters required",
      max: "Maximum {max} characters allowed",
      file_size: "File size must be less than {size}",
      custom_rule: "Custom validation message"
    }
  }
}
```

#### Custom Validation Functions

```javascript
{
  validation: {
    rules: ["required", "custom_validation"],
    customValidators: {
      custom_validation: {
        validate: (value, field, allValues) => {
          // Custom validation logic
          if (field.settingKey === "username" && value.length < 3) {
            return false
          }
          // Cross-field validation
          if (field.settingKey === "end_date" && allValues.start_date) {
            return new Date(value) > new Date(allValues.start_date)
          }
          return true
        },
        message: "Custom validation failed"
      }
    }
  }
}
```

### Conditional Field Display

```javascript
{
  settingKey: "notification_email",
  label: "Notification Email",
  type: "input",
  inputType: "email",
  value: "",
  showIf: (values) => {
    // Show only if notifications are enabled
    return values.enable_notifications === true
  },
  disableIf: (values) => {
    // Disable if in maintenance mode
    return values.maintenance_mode === true
  }
}

// Multiple conditions
{
  settingKey: "advanced_setting",
  label: "Advanced Setting",
  type: "input",
  showIf: (values) => {
    return values.user_role === "admin" && 
           values.enable_advanced === true
  }
}
```

### Field Dependencies and Reactions

```javascript
{
  settingKey: "logo_width",
  label: "Logo Width",
  type: "input",
  inputType: "number",
  value: 150,
  suffix: "px",
  reactions: {
    // React to changes in other fields
    logo_enabled: (value, fieldValue) => {
      // Hide/disable when logo is disabled
      return {
        visible: value === true,
        disabled: value === false
      }
    },
    // Update value based on other field
    container_width: (value, fieldValue) => {
      // Max logo width is 50% of container
      return {
        max: Math.floor(value * 0.5)
      }
    }
  }
}

// Supplementary fields (affect other fields without storing value)
{
  settingKey: null,                      // No storage
  supplementaryFor: "logo_width",        // Affects this field
  label: "Logo Visibility",
  type: "radio",
  value: 150,                           // Default for logo_width
  options: [
    { value: 0, label: "Hidden", icon: "eye-slash" },
    { value: 150, label: "Visible", icon: "eye" }
  ]
}
```

## EditorJS Deep Dive

### Understanding the Editor Lifecycle

#### 1. Initialization Phase

```javascript
// useEditorJS composable handles initialization
const editor = useEditorJS({
  elementId: "editor-container",
  tools: editorTools,
  data: initialData,
  placeholder: "Start typing...",
  onChange: (data) => {
    // Handle content changes
    emit("update:modelValue", data)
  }
})

// Lifecycle hooks
onMounted(async () => {
  await editor.initialize()
  // Editor is ready
})

onBeforeUnmount(() => {
  editor.destroy()
  // Cleanup resources
})
```

#### 2. Tool Registration and Configuration

```javascript
// Complete tool configuration
const editorTools = {
  // Basic tools
  paragraph: {
    class: Paragraph,
    inlineToolbar: true,
    config: {
      preserveBlank: true,        // Keep empty paragraphs
      placeholder: "Type here..."
    }
  },
  
  header: {
    class: Header,
    inlineToolbar: true,
    shortcut: "CMD+SHIFT+H",
    config: {
      levels: [2, 3, 4, 5, 6],
      defaultLevel: 2,
      allowAnchor: true,
      anchorLength: 100
    }
  },
  
  list: {
    class: List,
    inlineToolbar: true,
    shortcut: "CMD+SHIFT+L",
    config: {
      defaultStyle: "unordered"
    }
  },
  
  table: {
    class: Table,
    inlineToolbar: true,
    config: {
      rows: 2,
      cols: 3,
      maxRows: 10,
      maxCols: 8,
      withHeadings: true,
      stretch: false
    }
  },
  
  // Custom magic tag tool
  magicTag: {
    class: MagicTagInlineTool,
    config: {
      tags: availableTags,
      categories: tagCategories,
      validation: {
        required: false,
        allowNested: false
      }
    }
  },
  
  // Custom block tools
  customBlock: {
    class: CustomBlockTool,
    toolbox: {
      title: "Custom Block",
      icon: "<svg>...</svg>"
    },
    config: {
      // Custom configuration
    }
  }
}
```

#### 3. Data Flow Management

```javascript
// EditorJS data structure
const editorData = {
  time: 1672531200000,           // Timestamp
  blocks: [
    {
      id: "block-id-1",
      type: "paragraph",
      data: {
        text: "Hello [[%customer.name]]!"
      }
    },
    {
      id: "block-id-2", 
      type: "header",
      data: {
        text: "Invoice Details",
        level: 2
      }
    }
  ],
  version: "2.28.0"
}

// Reactive data management
const content = ref(null)
const editor = useEditorJS({
  data: content.value,
  onChange: (outputData) => {
    content.value = outputData
    // Auto-save or validation logic
    validateContent(outputData)
  }
})

// Watch for external changes
watch(() => props.modelValue, (newData) => {
  if (newData && editor.isReady.value) {
    editor.render(newData)
  }
}, { deep: true })
```

#### 4. Memory Management and Cleanup

```javascript
// Proper cleanup to prevent memory leaks
const editor = useEditorJS({
  // ... config
})

onBeforeUnmount(async () => {
  if (editor.instance.value) {
    try {
      // Save data before destroying
      const data = await editor.save()
      emit("beforeDestroy", data)
      
      // Destroy editor instance
      await editor.destroy()
      
      // Clear references
      editor.instance.value = null
    } catch (error) {
      console.error("Editor cleanup error:", error)
    }
  }
})

// Handle route changes
onBeforeRouteLeave(async (to, from, next) => {
  if (editor.hasUnsavedChanges.value) {
    const result = await confirmDialog("You have unsaved changes. Continue?")
    if (result) {
      await editor.destroy()
      next()
    } else {
      next(false)
    }
  } else {
    next()
  }
})
```

### Magic Tag Integration Deep Dive

#### MagicTagInlineTool Implementation

```javascript
// plugins/editorjs/MagicTagInlineTool.js
class MagicTagInlineTool {
  constructor({ api, config }) {
    this.api = api
    this.config = config
    this.tags = config.tags || []
    this.button = null
    this.state = false
  }
  
  static get isInline() {
    return true
  }
  
  static get title() {
    return "Magic Tag"
  }
  
  render() {
    this.button = document.createElement("button")
    this.button.type = "button"
    this.button.innerHTML = "🏷️"
    this.button.classList.add("ce-inline-tool")
    
    return this.button
  }
  
  surround(range) {
    if (this.state) {
      this.unwrap(range)
      return
    }
    
    this.wrap(range)
  }
  
  wrap(range) {
    const selectedText = range.extractContents()
    const span = document.createElement("span")
    
    span.classList.add("magic-tag")
    span.setAttribute("contenteditable", "false")
    span.appendChild(selectedText)
    
    range.insertNode(span)
    
    // Show tag selector
    this.showTagSelector(span)
  }
  
  unwrap(range) {
    const span = this.api.selection.findParentTag("SPAN", "magic-tag")
    if (span) {
      this.api.selection.expandToTag(span)
      this.api.caret.setToBlock(
        this.api.blocks.getCurrentBlockIndex(),
        span.textContent.length
      )
      span.outerHTML = span.textContent
    }
  }
  
  showTagSelector(element) {
    // Implementation for tag selection dropdown
    const dropdown = new TagDropdown({
      tags: this.tags,
      onSelect: (tag) => {
        element.textContent = tag.name
        element.setAttribute("data-tag", tag.name)
        element.title = tag.description
      }
    })
    
    dropdown.show(element)
  }
  
  checkState() {
    const span = this.api.selection.findParentTag("SPAN", "magic-tag")
    this.state = !!span
    
    if (this.state) {
      this.button.classList.add("ce-inline-tool--active")
    } else {
      this.button.classList.remove("ce-inline-tool--active")
    }
    
    return this.state
  }
}
```

#### MagicTagProcessor Implementation

```javascript
// plugins/editorjs/MagicTagProcessor.js
class MagicTagProcessor {
  constructor(config = {}) {
    this.tagPattern = /\[\[%([^%\]]+)%?\]\]/g
    this.config = {
      allowNested: false,
      caseSensitive: true,
      maxTagLength: 100,
      ...config
    }
  }
  
  // Process content when loading into editor
  processInbound(data) {
    if (!data || !data.blocks) return data
    
    return {
      ...data,
      blocks: data.blocks.map(block => this.processBlock(block))
    }
  }
  
  // Process content when saving from editor
  processOutbound(data) {
    if (!data || !data.blocks) return data
    
    return {
      ...data,
      blocks: data.blocks.map(block => this.extractTags(block))
    }
  }
  
  processBlock(block) {
    if (block.type === "paragraph" || block.type === "header") {
      return {
        ...block,
        data: {
          ...block.data,
          text: this.convertTagsToSpans(block.data.text)
        }
      }
    }
    
    return block
  }
  
  convertTagsToSpans(text) {
    if (!text) return text
    
    return text.replace(this.tagPattern, (match, tagContent) => {
      const tag = this.findTag(tagContent)
      const spanClass = tag ? "magic-tag" : "magic-tag invalid"
      const title = tag ? tag.description : "Invalid tag"
      
      return `<span class="${spanClass}" contenteditable="false" data-tag="${match}" title="${title}">${match}</span>`
    })
  }
  
  extractTags(block) {
    if (block.type === "paragraph" || block.type === "header") {
      return {
        ...block,
        data: {
          ...block.data,
          text: this.convertSpansToTags(block.data.text)
        }
      }
    }
    
    return block
  }
  
  convertSpansToTags(text) {
    if (!text) return text
    
    // Convert magic tag spans back to text format
    const tempDiv = document.createElement("div")
    tempDiv.innerHTML = text
    
    const spans = tempDiv.querySelectorAll(".magic-tag")
    spans.forEach(span => {
      const tagText = span.getAttribute("data-tag") || span.textContent
      span.outerHTML = tagText
    })
    
    return tempDiv.innerHTML
  }
  
  findTag(tagContent) {
    const fullTag = `[[%${tagContent}]]`
    return this.config.tags?.find(tag => 
      this.config.caseSensitive 
        ? tag.name === fullTag
        : tag.name.toLowerCase() === fullTag.toLowerCase()
    )
  }
  
  validateTags(data) {
    const errors = []
    
    if (!data || !data.blocks) return { valid: true, errors }
    
    data.blocks.forEach((block, blockIndex) => {
      if (block.type === "paragraph" || block.type === "header") {
        const text = block.data.text || ""
        const matches = text.match(this.tagPattern)
        
        if (matches) {
          matches.forEach(match => {
            const tagContent = match.replace(/\[\[%?|%?\]\]/g, "")
            const tag = this.findTag(tagContent)
            
            if (!tag) {
              errors.push({
                block: blockIndex,
                tag: match,
                error: "Invalid tag"
              })
            }
            
            if (match.length > this.config.maxTagLength) {
              errors.push({
                block: blockIndex,
                tag: match,
                error: "Tag too long"
              })
            }
          })
        }
      }
    })
    
    return {
      valid: errors.length === 0,
      errors
    }
  }
}
```

#### Advanced EditorJS Configuration

```javascript
// Complete EditorJS setup with error handling
const useAdvancedEditorJS = ({
  elementId,
  initialData,
  availableTags,
  onSave,
  onError
}) => {
  const editor = ref(null)
  const isReady = ref(false)
  const hasUnsavedChanges = ref(false)
  
  // Initialize tag processor
  const tagProcessor = new MagicTagProcessor({
    tags: availableTags,
    allowNested: false,
    caseSensitive: true
  })
  
  // Editor configuration
  const editorConfig = {
    holder: elementId,
    placeholder: "Start typing or insert a magic tag...",
    autofocus: true,
    hideToolbar: false,
    
    tools: {
      paragraph: {
        class: Paragraph,
        inlineToolbar: ["magicTag", "bold", "italic", "link"]
      },
      
      header: {
        class: Header,
        inlineToolbar: ["magicTag", "bold", "italic"],
        config: { levels: [2, 3, 4], defaultLevel: 2 }
      },
      
      list: {
        class: List,
        inlineToolbar: ["magicTag", "bold", "italic"]
      },
      
      table: {
        class: Table,
        inlineToolbar: ["magicTag", "bold", "italic"]
      },
      
      magicTag: {
        class: MagicTagInlineTool,
        config: {
          tags: availableTags,
          onInsert: (tag) => {
            console.log("Tag inserted:", tag)
          }
        }
      }
    },
    
    onChange: async (api, event) => {
      hasUnsavedChanges.value = true
      
      try {
        const data = await api.saver.save()
        const processedData = tagProcessor.processOutbound(data)
        
        // Validate tags
        const validation = tagProcessor.validateTags(processedData)
        if (!validation.valid) {
          console.warn("Invalid tags found:", validation.errors)
        }
        
        onSave?.(processedData)
      } catch (error) {
        console.error("Save error:", error)
        onError?.(error)
      }
    },
    
    onReady: () => {
      isReady.value = true
      console.log("EditorJS is ready")
    }
  }
  
  // Initialize editor
  const initialize = async () => {
    try {
      editor.value = new EditorJS(editorConfig)
      await editor.value.isReady
      
      // Load initial data
      if (initialData) {
        const processedData = tagProcessor.processInbound(initialData)
        await editor.value.render(processedData)
      }
      
      hasUnsavedChanges.value = false
    } catch (error) {
      console.error("Editor initialization error:", error)
      onError?.(error)
    }
  }
  
  // Save content
  const save = async () => {
    if (!editor.value) return null
    
    try {
      const data = await editor.value.save()
      const processedData = tagProcessor.processOutbound(data)
      hasUnsavedChanges.value = false
      return processedData
    } catch (error) {
      console.error("Save error:", error)
      onError?.(error)
      return null
    }
  }
  
  // Destroy editor
  const destroy = async () => {
    if (editor.value) {
      try {
        await editor.value.destroy()
        editor.value = null
        isReady.value = false
      } catch (error) {
        console.error("Destroy error:", error)
      }
    }
  }
  
  return {
    editor,
    isReady,
    hasUnsavedChanges,
    initialize,
    save,
    destroy
  }
}
```

### Performance Considerations

#### Large Document Optimization

```javascript
// Lazy loading for large documents
const useLazyEditorJS = ({ threshold = 1000 }) => {
  const shouldLazyLoad = computed(() => {
    return document.blocks?.length > threshold
  })
  
  const loadInChunks = async (data, chunkSize = 100) => {
    if (!shouldLazyLoad.value) {
      return editor.value.render(data)
    }
    
    const blocks = data.blocks || []
    const chunks = []
    
    for (let i = 0; i < blocks.length; i += chunkSize) {
      chunks.push(blocks.slice(i, i + chunkSize))
    }
    
    // Load first chunk immediately
    if (chunks[0]) {
      await editor.value.render({ blocks: chunks[0] })
    }
    
    // Load remaining chunks with delay
    for (let i = 1; i < chunks.length; i++) {
      await new Promise(resolve => setTimeout(resolve, 50))
      await editor.value.blocks.insert(chunks[i])
    }
  }
  
  return { loadInChunks, shouldLazyLoad }
}
```

#### Memory Management

```javascript
// Memory-efficient tag management
const useTagMemoryManagement = () => {
  const tagCache = new Map()
  const maxCacheSize = 1000
  
  const getCachedTag = (tagName) => {
    if (tagCache.has(tagName)) {
      // Move to end (LRU)
      const tag = tagCache.get(tagName)
      tagCache.delete(tagName)
      tagCache.set(tagName, tag)
      return tag
    }
    return null
  }
  
  const cacheTag = (tag) => {
    if (tagCache.size >= maxCacheSize) {
      // Remove oldest entry
      const firstKey = tagCache.keys().next().value
      tagCache.delete(firstKey)
    }
    tagCache.set(tag.name, tag)
  }
  
  const clearCache = () => {
    tagCache.clear()
  }
  
  return { getCachedTag, cacheTag, clearCache }
}
```

## Magic Tags System

### Complete Tag Structure and Validation

#### Tag Schema Definition

```javascript
// Complete tag structure
const tagDefinition = {
  // Required properties
  name: "[[%customer.email]]",           // The tag pattern used in content
  display: "Customer Email",             // Human-readable name
  
  // Optional properties
  description: "Customer's email address", // Tooltip/help text
  category: "customer",                   // Grouping category
  dataType: "string",                     // Data type for validation
  preview: "john@example.com",           // Preview value for testing
  icon: "envelope",                      // Icon for UI
  
  // Validation rules
  validation: {
    required: false,                     // Is this tag required in content?
    format: "email",                     // Expected format (email, url, number, etc.)
    maxLength: 255,                      // Maximum rendered length
    pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/ // Custom regex
  },
  
  // Access control
  permissions: {
    view: ["admin", "manager"],          // Who can see this tag
    use: ["admin", "manager", "user"]    // Who can use this tag
  },
  
  // Context information
  context: {
    entity: "customer",                  // Related entity
    field: "email",                      // Specific field
    relationship: null                   // Relationship path (e.g., "customer.company.name")
  },
  
  // Advanced options
  options: {
    fallback: "No email provided",       // Default value if data missing
    transform: "lowercase",              // Data transformation (lowercase, uppercase, capitalize)
    conditional: true,                   // Can be used in conditional logic
    sensitive: false                     // Contains sensitive data
  }
}
```

#### Tag Categories and Organization

```javascript
// Organizing tags by category
const tagCategories = {
  customer: {
    label: "Customer Information",
    icon: "user",
    description: "Tags related to customer data",
    color: "#3B82F6",
    tags: [
      {
        name: "[[%customer.name]]",
        display: "Customer Name",
        description: "Full name of the customer"
      },
      {
        name: "[[%customer.email]]",
        display: "Customer Email",
        description: "Customer's email address"
      },
      {
        name: "[[%customer.company]]",
        display: "Company Name",
        description: "Customer's company name"
      }
    ]
  },
  
  quotation: {
    label: "Quotation Details",
    icon: "file-invoice",
    description: "Tags related to quotation information",
    color: "#10B981",
    tags: [
      {
        name: "[[%quotation.id]]",
        display: "Quotation ID",
        description: "Unique quotation identifier"
      },
      {
        name: "[[%quotation.date]]",
        display: "Quotation Date",
        description: "Date when quotation was created",
        dataType: "date",
        options: {
          format: "YYYY-MM-DD"
        }
      },
      {
        name: "[[%quotation.total]]",
        display: "Total Amount",
        description: "Total quotation amount",
        dataType: "currency",
        options: {
          currency: "EUR",
          decimals: 2
        }
      }
    ]
  },
  
  company: {
    label: "Company Information",
    icon: "building",
    description: "Tags related to your company",
    color: "#8B5CF6",
    tags: [
      {
        name: "[[%company.name]]",
        display: "Company Name",
        description: "Your company name"
      },
      {
        name: "[[%company.address]]",
        display: "Company Address",
        description: "Company address",
        dataType: "multiline"
      }
    ]
  },
  
  system: {
    label: "System Information",
    icon: "cog",
    description: "System-generated tags",
    color: "#6B7280",
    tags: [
      {
        name: "[[%system.date]]",
        display: "Current Date",
        description: "Current system date",
        dataType: "date"
      },
      {
        name: "[[%system.time]]",
        display: "Current Time",
        description: "Current system time",
        dataType: "time"
      }
    ]
  }
}
```

#### Advanced Tag Features

```javascript
// Conditional tags
const conditionalTags = {
  name: "[[%if:customer.premium]]Premium Customer[[/if]]",
  display: "Premium Customer Badge",
  description: "Shows 'Premium Customer' only for premium customers",
  type: "conditional",
  condition: {
    field: "customer.premium",
    operator: "equals",
    value: true
  },
  content: {
    true: "Premium Customer",
    false: ""
  }
}

// Nested/composite tags
const compositeTags = {
  name: "[[%customer.full_address]]",
  display: "Full Customer Address",
  description: "Complete customer address (street, city, country)",
  type: "composite",
  components: [
    "[[%customer.street]]",
    "[[%customer.city]]", 
    "[[%customer.postal_code]]",
    "[[%customer.country]]"
  ],
  template: "{street}, {city} {postal_code}, {country}",
  separator: ", "
}

// Calculated tags
const calculatedTags = {
  name: "[[%quotation.tax_amount]]",
  display: "Tax Amount",
  description: "Calculated tax amount based on total and tax rate",
  type: "calculated",
  formula: "quotation.subtotal * quotation.tax_rate / 100",
  dependencies: ["quotation.subtotal", "quotation.tax_rate"],
  dataType: "currency"
}
```

### Tag Processing Lifecycle

#### 1. Tag Registration and Validation

```javascript
class TagRegistry {
  constructor() {
    this.tags = new Map()
    this.categories = new Map()
    this.validators = new Map()
  }
  
  register(tagDefinition) {
    // Validate tag definition
    const validation = this.validateTagDefinition(tagDefinition)
    if (!validation.valid) {
      throw new Error(`Invalid tag definition: ${validation.errors.join(", ")}`)
    }
    
    // Register tag
    this.tags.set(tagDefinition.name, tagDefinition)
    
    // Add to category
    if (tagDefinition.category) {
      if (!this.categories.has(tagDefinition.category)) {
        this.categories.set(tagDefinition.category, [])
      }
      this.categories.get(tagDefinition.category).push(tagDefinition)
    }
    
    // Register custom validator if provided
    if (tagDefinition.validation?.custom) {
      this.validators.set(tagDefinition.name, tagDefinition.validation.custom)
    }
  }
  
  validateTagDefinition(definition) {
    const errors = []
    
    // Required fields
    if (!definition.name) errors.push("Tag name is required")
    if (!definition.display) errors.push("Display name is required")
    
    // Name format validation
    if (definition.name && !/^\[\[%[^%\]]+\]\]$/.test(definition.name)) {
      errors.push("Tag name must match pattern [[%tag_name]]")
    }
    
    // Data type validation
    const validDataTypes = ["string", "number", "date", "time", "currency", "email", "url", "multiline"]
    if (definition.dataType && !validDataTypes.includes(definition.dataType)) {
      errors.push(`Invalid data type: ${definition.dataType}`)
    }
    
    return {
      valid: errors.length === 0,
      errors
    }
  }
  
  getTag(name) {
    return this.tags.get(name)
  }
  
  getTagsByCategory(category) {
    return this.categories.get(category) || []
  }
  
  getAllTags() {
    return Array.from(this.tags.values())
  }
  
  searchTags(query) {
    const results = []
    const lowerQuery = query.toLowerCase()
    
    for (const tag of this.tags.values()) {
      if (
        tag.name.toLowerCase().includes(lowerQuery) ||
        tag.display.toLowerCase().includes(lowerQuery) ||
        tag.description?.toLowerCase().includes(lowerQuery)
      ) {
        results.push(tag)
      }
    }
    
    return results
  }
}
```

#### 2. Tag Resolution and Data Binding

```javascript
class TagResolver {
  constructor(dataContext = {}) {
    this.dataContext = dataContext
    this.cache = new Map()
    this.resolvers = new Map()
  }
  
  registerResolver(pattern, resolver) {
    this.resolvers.set(pattern, resolver)
  }
  
  async resolveTag(tagName, options = {}) {
    // Check cache first
    const cacheKey = `${tagName}:${JSON.stringify(options)}`
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }
    
    // Extract tag path
    const tagPath = tagName.replace(/\[\[%?|%?\]\]/g, "")
    
    // Find appropriate resolver
    const resolver = this.findResolver(tagPath)
    if (!resolver) {
      throw new Error(`No resolver found for tag: ${tagName}`)
    }
    
    // Resolve value
    const value = await resolver(tagPath, this.dataContext, options)
    
    // Apply transformations
    const transformedValue = this.applyTransformations(value, options)
    
    // Cache result
    this.cache.set(cacheKey, transformedValue)
    
    return transformedValue
  }
  
  findResolver(tagPath) {
    for (const [pattern, resolver] of this.resolvers) {
      if (this.matchesPattern(tagPath, pattern)) {
        return resolver
      }
    }
    return null
  }
  
  matchesPattern(path, pattern) {
    // Simple pattern matching - can be enhanced
    if (pattern === "*") return true
    if (pattern.includes("*")) {
      const regexPattern = pattern.replace(/\*/g, ".*")
      return new RegExp(`^${regexPattern}$`).test(path)
    }
    return path.startsWith(pattern)
  }
  
  applyTransformations(value, options) {
    let result = value
    
    // Type-specific transformations
    if (options.dataType === "currency") {
      result = this.formatCurrency(result, options)
    } else if (options.dataType === "date") {
      result = this.formatDate(result, options)
    }
    
    // General transformations
    if (options.transform === "lowercase") {
      result = String(result).toLowerCase()
    } else if (options.transform === "uppercase") {
      result = String(result).toUpperCase()
    } else if (options.transform === "capitalize") {
      result = String(result).charAt(0).toUpperCase() + String(result).slice(1)
    }
    
    return result
  }
  
  formatCurrency(value, options) {
    const currency = options.currency || "EUR"
    const decimals = options.decimals || 2
    
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency,
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(value)
  }
  
  formatDate(value, options) {
    const format = options.format || "YYYY-MM-DD"
    const date = new Date(value)
    
    // Simple date formatting - use a proper library like date-fns in real implementation
    if (format === "YYYY-MM-DD") {
      return date.toISOString().split('T')[0]
    }
    
    return date.toLocaleDateString()
  }
}
```

#### 3. Tag Integration with EditorJS

```javascript
// Enhanced StudioEditorJS component
const StudioEditorJS = defineComponent({
  props: {
    modelValue: Object,
    tags: Array,
    placeholder: String,
    minHeight: String,
    maxHeight: String
  },
  
  setup(props, { emit }) {
    const editorRef = ref(null)
    const isReady = ref(false)
    
    // Initialize tag registry
    const tagRegistry = new TagRegistry()
    props.tags?.forEach(tag => tagRegistry.register(tag))
    
    // Initialize tag resolver
    const tagResolver = new TagResolver()
    
    // Register default resolvers
    tagResolver.registerResolver("customer.*", async (path, context) => {
      const [entity, field] = path.split(".")
      return context.customer?.[field] || ""
    })
    
    tagResolver.registerResolver("quotation.*", async (path, context) => {
      const [entity, field] = path.split(".")
      return context.quotation?.[field] || ""
    })
    
    tagResolver.registerResolver("system.*", async (path, context) => {
      const [entity, field] = path.split(".")
      if (field === "date") return new Date().toISOString().split('T')[0]
      if (field === "time") return new Date().toLocaleTimeString()
      return ""
    })
    
    // Enhanced magic tag tool configuration
    const magicTagTool = {
      class: MagicTagInlineTool,
      config: {
        tags: props.tags,
        registry: tagRegistry,
        resolver: tagResolver,
        onInsert: (tag) => {
          emit("tag-inserted", tag)
        },
        onValidation: (errors) => {
          emit("validation-errors", errors)
        }
      }
    }
    
    // Editor configuration
    const editorConfig = {
      holder: editorRef.value,
      placeholder: props.placeholder || "Start typing...",
      minHeight: parseInt(props.minHeight) || 200,
      tools: {
        paragraph: {
          class: Paragraph,
          inlineToolbar: ["magicTag", "bold", "italic", "link"]
        },
        header: {
          class: Header,
          inlineToolbar: ["magicTag", "bold", "italic"],
          config: { levels: [2, 3, 4], defaultLevel: 2 }
        },
        list: {
          class: List,
          inlineToolbar: ["magicTag", "bold", "italic"]
        },
        magicTag: magicTagTool
      },
      onChange: async (api, event) => {
        const data = await api.saver.save()
        emit("update:modelValue", data)
      }
    }
    
    // Initialize editor
    const initializeEditor = async () => {
      const { default: EditorJS } = await import("@editorjs/editorjs")
      
      const editor = new EditorJS(editorConfig)
      await editor.isReady
      
      if (props.modelValue) {
        await editor.render(props.modelValue)
      }
      
      isReady.value = true
    }
    
    onMounted(() => {
      initializeEditor()
    })
    
    return {
      editorRef,
      isReady
    }
  }
})
```

### Performance and Optimization

#### Tag Caching Strategy

```javascript
// Intelligent tag caching
class TagCache {
  constructor(options = {}) {
    this.maxSize = options.maxSize || 1000
    this.ttl = options.ttl || 300000 // 5 minutes
    this.cache = new Map()
    this.timers = new Map()
  }
  
  set(key, value, customTTL) {
    // Remove oldest if at capacity
    if (this.cache.size >= this.maxSize) {
      const firstKey = this.cache.keys().next().value
      this.delete(firstKey)
    }
    
    // Set value
    this.cache.set(key, {
      value,
      timestamp: Date.now()
    })
    
    // Set expiration timer
    const ttl = customTTL || this.ttl
    const timer = setTimeout(() => {
      this.delete(key)
    }, ttl)
    
    this.timers.set(key, timer)
  }
  
  get(key) {
    const entry = this.cache.get(key)
    if (!entry) return null
    
    // Check if expired
    if (Date.now() - entry.timestamp > this.ttl) {
      this.delete(key)
      return null
    }
    
    return entry.value
  }
  
  delete(key) {
    this.cache.delete(key)
    
    // Clear timer
    const timer = this.timers.get(key)
    if (timer) {
      clearTimeout(timer)
      this.timers.delete(key)
    }
  }
  
  clear() {
    // Clear all timers
    for (const timer of this.timers.values()) {
      clearTimeout(timer)
    }
    
    this.cache.clear()
    this.timers.clear()
  }
  
  stats() {
    return {
      size: this.cache.size,
      maxSize: this.maxSize,
      hitRate: this.hits / (this.hits + this.misses) || 0
    }
  }
}
```

## State Management

### Using Studio Settings

The `useStudioSettings` composable is the primary way to manage settings in the Studio:

```javascript
// Basic usage
const studio = useStudioSettings({
  namespace: "themes",           // Settings namespace
  area: "mail",                 // Settings area
  config: studioConfig,         // Configuration schema
  
  // Optional callbacks
  onSuccess: (data) => {
    addToast({ type: "success", message: "Settings saved!" })
  },
  onError: (error) => {
    addToast({ type: "error", message: "Failed to save settings" })
    console.error("Save error:", error)
  },
  onLoad: (data) => {
    console.log("Settings loaded:", data)
  },
  
  // Optional configuration
  autoSave: false,              // Enable auto-save
  autoSaveDelay: 2000,         // Auto-save delay in ms
  debug: true                   // Enable debug logging
})

// Reactive properties
const isLoading = studio.loading          // Boolean: Loading state
const isSaving = studio.saving            // Boolean: Saving state
const isDirty = studio.isDirty            // Boolean: Has unsaved changes
const values = studio.values              // Ref: Current field values
const originalValues = studio.originalValues // Ref: Original loaded values
const changedFields = studio.changedFields   // Computed: Array of changed field keys

// Methods
await studio.load()                       // Load settings from backend
studio.update("setting_key", newValue)   // Update a field value
const value = studio.getValue("setting_key", defaultValue) // Get reactive field value
await studio.save()                      // Save changes to backend
studio.reset()                           // Reset to original values
studio.setConfig(newConfig)              // Update configuration schema
```

### Advanced Settings Usage

#### Nested Field Values

```javascript
// Working with nested objects
const studio = useStudioSettings({
  namespace: "layout",
  area: "email",
  config: configSchema
})

// Update nested values
studio.update("padding.top", 20)
studio.update("padding.right", 15)
studio.update("padding.bottom", 20)
studio.update("padding.left", 15)

// Get nested values
const padding = computed(() => ({
  top: studio.getValue("padding.top", 0).value,
  right: studio.getValue("padding.right", 0).value,
  bottom: studio.getValue("padding.bottom", 0).value,
  left: studio.getValue("padding.left", 0).value
}))

// Bulk update nested values
const updatePadding = (newPadding) => {
  Object.entries(newPadding).forEach(([key, value]) => {
    studio.update(`padding.${key}`, value)
  })
}
```

#### Validation Integration

```javascript
const studio = useStudioSettings({
  namespace: "validation_example",
  area: "settings",
  config: configWithValidation,
  
  // Custom validation
  validate: (values) => {
    const errors = {}
    
    // Custom validation logic
    if (values.start_date && values.end_date) {
      if (new Date(values.start_date) > new Date(values.end_date)) {
        errors.end_date = "End date must be after start date"
      }
    }
    
    if (values.email && !isValidEmail(values.email)) {
      errors.email = "Invalid email format"
    }
    
    return {
      valid: Object.keys(errors).length === 0,
      errors
    }
  },
  
  // Prevent save if validation fails
  beforeSave: (values) => {
    const validation = studio.validate(values)
    if (!validation.valid) {
      throw new Error("Validation failed: " + Object.values(validation.errors).join(", "))
    }
    return values
  }
})

// Reactive validation state
const validationErrors = computed(() => studio.validate(studio.values.value).errors)
const isValid = computed(() => studio.validate(studio.values.value).valid)
```

#### Auto-Save Implementation

```javascript
const studio = useStudioSettings({
  namespace: "autosave_example",
  area: "settings",
  config: configSchema,
  autoSave: true,
  autoSaveDelay: 3000,
  
  onAutoSave: (data) => {
    // Show subtle auto-save indicator
    showToast("Auto-saved", { type: "info", duration: 1000 })
  }
})

// Manual control over auto-save
const pauseAutoSave = () => studio.pauseAutoSave()
const resumeAutoSave = () => studio.resumeAutoSave()

// Watch for specific fields to trigger immediate save
watch(() => studio.getValue("critical_setting").value, (newValue) => {
  if (newValue !== null) {
    studio.save() // Save immediately for critical settings
  }
})
```

### Using Studio Lexicons

For multilingual content with EditorJS support:

```javascript
const studio = useStudioLexicons({
  namespace: "emails",          // Lexicon namespace
  area: "quotation",           // Lexicon area
  language: "en",              // Current language
  config: lexiconConfig,       // Configuration schema
  
  // Language change callback
  onLanguageChange: (language) => {
    console.log("Language changed to:", language)
  },
  
  // Save callback
  onSuccess: () => {
    addToast({ type: "success", message: "Content saved!" })
  }
})

// Language management
const availableLanguages = ["en", "nl", "fr", "de"]
const currentLanguage = studio.language

// Change language
await studio.changeLanguage("nl")

// Get EditorJS content
const emailSubject = computed({
  get: () => studio.getValue("email.subject").value,
  set: (value) => studio.update("email.subject", value)
})

const emailBody = computed({
  get: () => studio.getValue("email.body").value,
  set: (value) => studio.update("email.body", value)
})
```

#### Multi-Language Workflow

```javascript
// Complete multi-language setup
const useMultiLanguageStudio = (config) => {
  const languages = ref(["en", "nl", "fr", "de"])
  const currentLanguage = ref("en")
  const studios = ref({})
  
  // Initialize studio for each language
  const initializeLanguages = async () => {
    for (const lang of languages.value) {
      studios.value[lang] = useStudioLexicons({
        ...config,
        language: lang
      })
      await studios.value[lang].load()
    }
  }
  
  // Get current studio
  const currentStudio = computed(() => studios.value[currentLanguage.value])
  
  // Change language
  const changeLanguage = async (newLanguage) => {
    if (languages.value.includes(newLanguage)) {
      currentLanguage.value = newLanguage
      
      // Load if not already loaded
      if (!studios.value[newLanguage]) {
        studios.value[newLanguage] = useStudioLexicons({
          ...config,
          language: newLanguage
        })
        await studios.value[newLanguage].load()
      }
    }
  }
  
  // Save all languages
  const saveAll = async () => {
    const promises = Object.values(studios.value).map(studio => studio.save())
    await Promise.all(promises)
  }
  
  // Check if any language has changes
  const hasChanges = computed(() => {
    return Object.values(studios.value).some(studio => studio.isDirty.value)
  })
  
  return {
    languages,
    currentLanguage,
    currentStudio,
    changeLanguage,
    saveAll,
    hasChanges,
    initializeLanguages
  }
}
```

### Global Studio State

For sharing state across Studio pages:

```javascript
// useStudioState.js
const globalState = reactive({
  unsavedChanges: false,
  currentPage: null,
  theme: "light",
  sidebarCollapsed: false,
  recentColors: [],
  recentImages: []
})

export const useStudioState = () => {
  // Update global state
  const setUnsavedChanges = (hasChanges) => {
    globalState.unsavedChanges = hasChanges
  }
  
  const setCurrentPage = (page) => {
    globalState.currentPage = page
  }
  
  const addRecentColor = (color) => {
    if (!globalState.recentColors.includes(color)) {
      globalState.recentColors.unshift(color)
      // Keep only last 10 colors
      if (globalState.recentColors.length > 10) {
        globalState.recentColors.pop()
      }
    }
  }
  
  const addRecentImage = (image) => {
    const existing = globalState.recentImages.find(img => img.url === image.url)
    if (!existing) {
      globalState.recentImages.unshift(image)
      if (globalState.recentImages.length > 20) {
        globalState.recentImages.pop()
      }
    }
  }
  
  // Getters
  const hasUnsavedChanges = computed(() => globalState.unsavedChanges)
  const currentPage = computed(() => globalState.currentPage)
  const recentColors = computed(() => globalState.recentColors)
  const recentImages = computed(() => globalState.recentImages)
  
  return {
    // State
    globalState: readonly(globalState),
    
    // Getters
    hasUnsavedChanges,
    currentPage,
    recentColors,
    recentImages,
    
    // Actions
    setUnsavedChanges,
    setCurrentPage,
    addRecentColor,
    addRecentImage
  }
}

// Usage in components
export default {
  setup() {
    const studioState = useStudioState()
    const studio = useStudioSettings({
      namespace: "example",
      area: "settings",
      config: configSchema
    })
    
    // Sync local changes with global state
    watchEffect(() => {
      studioState.setUnsavedChanges(studio.isDirty.value)
    })
    
    // Add color to recent when changed
    watch(() => studio.getValue("primary_color").value, (color) => {
      if (color) {
        studioState.addRecentColor(color)
      }
    })
    
    return {
      studio,
      studioState
    }
  }
}
```

## Creating New Studio Pages

### Complete Page Creation Workflow

#### Step 1: Plan Your Page Structure

Before coding, define:

1. **Purpose**: What will this page customize?
2. **Settings**: What configuration options are needed?
3. **Preview**: How will changes be visualized?
4. **Navigation**: Where does it fit in the Studio structure?

Example planning for a "Newsletter Template" page:

```javascript
// Page planning
const pageDefinition = {
  purpose: "Customize newsletter email templates",
  namespace: "newsletters",
  area: "templates",
  
  settings: [
    "header_color",
    "logo_position", 
    "content_width",
    "footer_text",
    "social_links"
  ],
  
  preview: "Live newsletter preview with sample content",
  
  navigation: {
    section: "Email Templates",
    position: "after emails.vue"
  }
}
```

#### Step 2: Create Configuration Schema

```javascript
// config/newsletter-config.js
export const newsletterConfig = [
  {
    id: "header",
    icon: "header",
    displayName: "Header Settings",
    description: "Configure newsletter header appearance",
    fields: [
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        description: "Background color for the newsletter header"
      },
      {
        settingKey: "header.text_color", 
        label: "Header Text Color",
        type: "color",
        value: "#1F2937"
      },
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "content",
    icon: "file-text",
    displayName: "Content Settings",
    fields: [
      {
        settingKey: "content.width",
        label: "Content Width",
        type: "input",
        inputType: "number",
        value: 600,
        min: 400,
        max: 800,
        suffix: "px"
      },
      {
        type: "container",
        label: "Content Padding",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "content.padding.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          // ... other padding fields
        ]
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.text",
        label: "Footer Text",
        type: "editor",
        value: null,
        placeholder: "Enter footer content...",
        tools: ["paragraph", "bold", "italic", "link"],
        magicTags: true
      },
      {
        settingKey: "social_links.enabled",
        label: "Show Social Links",
        type: "checkbox",
        value: false
      },
      {
        settingKey: "social_links.links",
        label: "Social Links",
        type: "repeater",
        value: [],
        addLabel: "Add Social Link",
        template: [
          {
            settingKey: "platform",
            label: "Platform",
            type: "select",
            options: [
              { value: "facebook", label: "Facebook", icon: "facebook" },
              { value: "twitter", label: "Twitter", icon: "twitter" },
              { value: "linkedin", label: "LinkedIn", icon: "linkedin" }
            ]
          },
          {
            settingKey: "url",
            label: "URL",
            type: "input",
            inputType: "url",
            placeholder: "https://..."
          }
        ],
        showIf: (values) => values["social_links.enabled"] === true
      }
    ]
  }
]
```

#### Step 3: Create Preview Component

```vue
<!-- components/studio/preview/NewsletterPreview.vue -->
<template>
  <div class="newsletter-preview">
    <!-- Newsletter container with dynamic styling -->
    <div 
      class="newsletter-container"
      :style="containerStyle"
    >
      <!-- Header -->
      <header 
        class="newsletter-header"
        :style="headerStyle"
      >
        <div v-if="logoEnabled" class="logo-container" :class="logoAlignmentClass">
          <img v-if="logoBlob" :src="logoBlob" alt="Logo" :style="logoStyle" />
          <div v-else class="logo-placeholder">Logo</div>
        </div>
        
        <h1 class="newsletter-title" :style="titleStyle">
          Weekly Newsletter
        </h1>
      </header>
      
      <!-- Content -->
      <main class="newsletter-content" :style="contentStyle">
        <div class="content-section">
          <h2>Featured Article</h2>
          <p>
            This is a sample newsletter content. Your actual content will appear here
            when you send the newsletter to your subscribers.
          </p>
        </div>
        
        <div class="content-section">
          <h3>Quick Updates</h3>
          <ul>
            <li>Update item 1</li>
            <li>Update item 2</li>
            <li>Update item 3</li>
          </ul>
        </div>
      </main>
      
      <!-- Footer -->
      <footer class="newsletter-footer" :style="footerStyle">
        <div v-if="footerText" class="footer-content">
          <StudioEditorRenderer :content="footerText" />
        </div>
        
        <div v-if="socialLinksEnabled && socialLinks.length" class="social-links">
          <a 
            v-for="link in socialLinks" 
            :key="link.platform"
            :href="link.url"
            class="social-link"
            :class="`social-${link.platform}`"
          >
            <FontAwesome :icon="getSocialIcon(link.platform)" />
          </a>
        </div>
        
        <div class="unsubscribe-link">
          <a href="#" style="color: #6B7280; font-size: 12px;">
            Unsubscribe from this newsletter
          </a>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  headerBackgroundColor: String,
  headerTextColor: String,
  logoEnabled: Boolean,
  logoPosition: String,
  logoBlob: String,
  contentWidth: Number,
  contentPadding: Object,
  footerText: Object,
  socialLinksEnabled: Boolean,
  socialLinks: Array
})

// Computed styles
const containerStyle = computed(() => ({
  width: `${props.contentWidth}px`,
  maxWidth: "100%",
  margin: "0 auto",
  backgroundColor: "#FFFFFF",
  border: "1px solid #E5E7EB",
  borderRadius: "8px",
  overflow: "hidden"
}))

const headerStyle = computed(() => ({
  backgroundColor: props.headerBackgroundColor,
  color: props.headerTextColor,
  padding: "20px",
  textAlign: props.logoPosition
}))

const logoAlignmentClass = computed(() => ({
  "text-left": props.logoPosition === "left",
  "text-center": props.logoPosition === "center", 
  "text-right": props.logoPosition === "right"
}))

const logoStyle = computed(() => ({
  maxHeight: "60px",
  width: "auto"
}))

const titleStyle = computed(() => ({
  color: props.headerTextColor,
  margin: props.logoEnabled ? "10px 0 0 0" : "0",
  fontSize: "24px",
  fontWeight: "bold"
}))

const contentStyle = computed(() => ({
  padding: `${props.contentPadding?.top || 20}px ${props.contentPadding?.right || 20}px ${props.contentPadding?.bottom || 20}px ${props.contentPadding?.left || 20}px`
}))

const footerStyle = computed(() => ({
  backgroundColor: "#F9FAFB",
  padding: "20px",
  borderTop: "1px solid #E5E7EB",
  textAlign: "center"
}))

// Helper methods
const getSocialIcon = (platform) => {
  const icons = {
    facebook: "facebook-f",
    twitter: "twitter", 
    linkedin: "linkedin-in",
    instagram: "instagram"
  }
  return icons[platform] || "link"
}
</script>

<style scoped>
.newsletter-preview {
  padding: 20px;
  background: #F3F4F6;
  min-height: 600px;
}

.content-section {
  margin-bottom: 20px;
}

.content-section h2 {
  color: #1F2937;
  font-size: 18px;
  margin-bottom: 8px;
}

.content-section h3 {
  color: #374151;
  font-size: 16px;
  margin-bottom: 8px;
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 15px;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #4B5563;
  color: white;
  border-radius: 50%;
  text-decoration: none;
  transition: background-color 0.2s;
}

.social-link:hover {
  background: #374151;
}

.footer-content {
  margin-bottom: 15px;
}
</style>
```

#### Step 4: Create the Main Page Component

```vue
<!-- pages/studio/newsletter.vue -->
<template>
  <StudioWrapper>
    <StudioHeader 
      subtitle="Newsletter Templates"
      :settings-changed="hasChanges"
      :saving="isSaving"
      @save-changes="save"
      @discard-changes="reset"
    />
    
    <StudioContainer>
      <template #navigation>
        <StudioTree :tree-config="treeConfig" />
      </template>
      
      <template #content>
        <StudioContent :loading="studio.loading.value">
          <template #sidebar>
            <StudioConfig
              :config="newsletterConfig"
              :values="studio.values.value"
              @field-update="studio.update"
            />
          </template>
          
          <template #content>
            <StudioConfigPreview>
              <NewsletterPreview
                :header-background-color="headerBackgroundColor"
                :header-text-color="headerTextColor"
                :logo-enabled="logoEnabled"
                :logo-position="logoPosition"
                :logo-blob="logoBlob"
                :content-width="contentWidth"
                :content-padding="contentPadding"
                :footer-text="footerText"
                :social-links-enabled="socialLinksEnabled"
                :social-links="socialLinks"
              />
            </StudioConfigPreview>
          </template>
        </StudioContent>
      </template>
    </StudioContainer>
  </StudioWrapper>
</template>

<script setup>
import { newsletterConfig } from "~/config/newsletter-config"

// Page metadata
definePageMeta({
  title: "Newsletter Templates - Studio",
  description: "Customize your newsletter email templates"
})

// Page state
const hasChanges = ref(false)
const isSaving = ref(false)

// Navigation configuration
const treeConfig = ref([
  {
    key: "templates",
    title: "Email Templates",
    icon: "envelope",
    expanded: true,
    children: [
      {
        key: "newsletters",
        displayName: "Newsletter",
        icon: "newspaper",
        route: "/manage/studio/newsletter"
      }
    ]
  }
])

// Initialize studio
const studio = useStudioSettings({
  namespace: "newsletters",
  area: "templates", 
  config: newsletterConfig,
  onSuccess: () => {
    addToast({ 
      type: "success", 
      message: "Newsletter template settings saved!" 
    })
  },
  onError: (error) => {
    addToast({ 
      type: "error", 
      message: "Failed to save newsletter settings" 
    })
    console.error("Save error:", error)
  }
})

// Reactive values for preview
const headerBackgroundColor = computed(() => 
  studio.getValue("header.background_color").value
)
const headerTextColor = computed(() => 
  studio.getValue("header.text_color").value
)
const logoEnabled = computed(() => 
  studio.getValue("logo.enabled").value
)
const logoPosition = computed(() => 
  studio.getValue("logo.position").value
)
const contentWidth = computed(() => 
  studio.getValue("content.width").value
)
const contentPadding = computed(() => ({
  top: studio.getValue("content.padding.top").value,
  right: studio.getValue("content.padding.right").value,
  bottom: studio.getValue("content.padding.bottom").value,
  left: studio.getValue("content.padding.left").value
}))
const footerText = computed(() => 
  studio.getValue("footer.text").value
)
const socialLinksEnabled = computed(() => 
  studio.getValue("social_links.enabled").value
)
const socialLinks = computed(() => 
  studio.getValue("social_links.links").value
)

// Handle logo image
const logoField = computed(() => studio.values.value["logo.image"])
const { blob: logoBlob } = useLoadImage().getPreview(logoField)

// Sync state
watchEffect(() => {
  hasChanges.value = studio.isDirty.value
  isSaving.value = studio.saving.value
})

// Methods
const save = () => studio.save()
const reset = () => studio.reset()

// Load settings on mount
onMounted(() => studio.load())
</script>
```

#### Step 5: Add to Main Studio Navigation

Update `pages/studio.vue`:

```javascript
// Add to tree configuration
const treeConfig = ref([
  // ... existing configuration
  {
    key: "email-templates",
    title: "Email Templates",
    icon: "envelope",
    expanded: true,
    children: [
      {
        key: "newsletters",
        route: "/manage/studio/newsletter",
        displayName: "Newsletter",
        icon: "newspaper"
      },
      {
        key: "notifications",
        route: "/manage/studio/notifications", 
        displayName: "Notifications",
        icon: "bell"
      }
      // ... other email templates
    ]
  }
])
```

# Prindustry Studio 2.0 Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Quick Start Guide](#quick-start-guide)
3. [Architecture Overview](#architecture-overview)
4. [Development Guidelines](#development-guidelines)
5. [Core Components](#core-components)
6. [State Management](#state-management)
7. [Complete Configuration Reference](#complete-configuration-reference)
8. [EditorJS Deep Dive](#editorjs-deep-dive)
9. [Magic Tags System](#magic-tags-system)
10. [Creating New Studio Pages](#creating-new-studio-pages)
11. [Email Templates](#email-templates)
12. [PDF Templates](#pdf-templates)
13. [Best Practices](#best-practices)
14. [Testing](#testing)
15. [Troubleshooting](#troubleshooting)

## Introduction

Prindustry Studio 2.0 is a powerful customization platform integrated into Prindustry Manager 3.0. It provides a unified interface for customizing various aspects of the application, including:

- **Email Templates**: Customize the look and feel of system emails
- **PDF Templates**: Design invoice and quotation PDF layouts
- **Theme Settings**: Personalize colors and visual styles
- **Email Content**: Manage multilingual email content with dynamic tags

### Key Features
- 🎨 Real-time preview of changes
- 🌐 Multi-language support
- 🏷️ Dynamic content with Magic Tags
- 💾 Automatic saving and state management
- 🔄 Change tracking with discard/save functionality
- ✏️ Rich text editing with EditorJS
- 🖼️ Image upload and management
- 📱 Responsive design tools

## Quick Start Guide

### Creating Your First Studio Page (5 minutes)

#### Step 1: Create the Page File
Create `pages/studio/my-feature.vue`:

```vue
<template>
  <StudioWrapper>
    <StudioHeader 
      :subtitle="'My Feature Settings'"
      :settings-changed="hasChanges"
      :saving="isSaving"
      @save-changes="save"
      @discard-changes="reset"
    />
    <StudioContainer>
      <template #navigation>
        <StudioTree :tree-config="treeConfig" />
      </template>
      <template #content>
        <StudioContent :loading="studio.loading.value">
          <template #sidebar>
            <StudioConfig
              :config="studioConfig"
              :values="studio.values.value"
              @field-update="studio.update"
            />
          </template>
          
          <template #content>
            <StudioConfigPreview>
              <div class="p-4 bg-white rounded-lg">
                <h2 :style="{ color: primaryColor }">
                  Preview: {{ featureName }}
                </h2>
                <p :style="{ fontSize: fontSize + 'px' }">
                  This is your feature preview
                </p>
              </div>
            </StudioConfigPreview>
          </template>
        </StudioContent>
      </template>
    </StudioContainer>
  </StudioWrapper>
</template>

<script setup>
// Page state
const hasChanges = ref(false)
const isSaving = ref(false)

// Studio configuration
const studioConfig = ref([
  {
    id: "general",
    icon: "cog",
    displayName: "General Settings",
    fields: [
      {
        settingKey: "feature_name",
        label: "Feature Name",
        type: "input",
        value: "My Awesome Feature"
      },
      {
        settingKey: "primary_color",
        label: "Primary Color",
        type: "color",
        value: "#2563EB"
      },
      {
        settingKey: "font_size",
        label: "Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24
      }
    ]
  }
])

// Navigation tree (minimal for this page)
const treeConfig = ref([
  {
    key: "settings",
    title: "Settings",
    icon: "cog",
    children: [
      {
        key: "general",
        displayName: "General",
        icon: "settings"
      }
    ]
  }
])

// Initialize studio
const studio = useStudioSettings({
  namespace: "my_feature",
  area: "settings",
  config: studioConfig,
  onSuccess: () => {
    addToast({ type: "success", message: "Settings saved!" })
  },
  onError: (error) => {
    addToast({ type: "error", message: "Failed to save settings" })
  }
})

// Reactive values for preview
const featureName = computed(() => studio.getValue("feature_name").value)
const primaryColor = computed(() => studio.getValue("primary_color").value)
const fontSize = computed(() => studio.getValue("font_size").value)

// Sync state
watchEffect(() => {
  hasChanges.value = studio.isDirty.value
  isSaving.value = studio.saving.value
})

// Methods
const save = () => studio.save()
const reset = () => studio.reset()

// Load on mount
onMounted(() => studio.load())
</script>
```

#### Step 2: Add to Navigation
Update `pages/studio.vue` tree configuration:

```javascript
const treeConfig = ref([
  // ... existing config
  {
    key: "my-section",
    title: "My Section",
    icon: "star",
    expanded: true,
    children: [
      {
        key: "my-feature",
        route: "/manage/studio/my-feature",
        displayName: "My Feature",
        icon: "sparkles"
      }
    ]
  }
])
```

#### Step 3: Test Your Page
1. Start the development server
2. Navigate to `/manage/studio/my-feature`
3. Try changing settings and see the preview update
4. Test save/discard functionality

**🎉 Congratulations!** You've created your first Studio page. Now let's dive deeper into the system.

## Architecture Overview

The Studio follows a modular architecture built with Vue 3's Composition API:

```
studio/
├── components/              # UI components
│   ├── StudioWrapper.vue   # Root wrapper with styling
│   ├── StudioHeader.vue    # Header with save/discard
│   ├── StudioTree.vue      # Navigation sidebar
│   ├── StudioContent.vue   # Main content area
│   ├── StudioConfig.vue    # Configuration panel
│   └── preview/            # Preview components
│       ├── StudioConfigPreview.vue
│       ├── StudioEntityPDFInvoice.vue
│       └── StudioEmailModal.vue
├── composables/            # Business logic
│   ├── useStudioSettings.js    # Settings management
│   ├── useStudioLexicons.js    # Multi-language content
│   ├── useStudioState.js       # Global state
│   ├── useEditorJS.js          # EditorJS integration
│   ├── useLoadImage.js         # Image handling
│   └── useElementScaling.js    # PDF scaling
├── pages/                  # Studio pages
│   ├── studio.vue         # Main studio layout
│   ├── emails.vue         # Email template editor
│   ├── invoice.vue        # Invoice PDF editor
│   └── quotation.vue      # Quotation PDF editor
└── plugins/               # EditorJS plugins
    └── editorjs/
        ├── MagicTagInlineTool.js
        ├── MagicTagProcessor.js
        └── config.js
```

### Core Concepts

1. **Settings**: Configuration values stored in the backend, managed by namespace/area
2. **Lexicons**: Multilingual content entries with EditorJS support
3. **State Management**: Reactive state tracking with automatic change detection
4. **Preview System**: Real-time visualization of changes with scaling support
5. **Magic Tags**: Dynamic placeholders that get replaced with actual data
6. **Configuration Schema**: Declarative field definitions for forms and validation

## Development Guidelines

### Setting Up Your Environment

#### Required Dependencies
Ensure these are installed in your project:

```json
{
  "dependencies": {
    "@editorjs/editorjs": "^2.28.0",
    "@editorjs/header": "^2.7.0",
    "@editorjs/paragraph": "^2.9.0",
    "@editorjs/list": "^1.8.0",
    "@editorjs/table": "^2.2.0",
    "vue": "^3.3.0",
    "pinia": "^2.1.0"
  }
}
```

#### Development Server Configuration
Add to your `nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  // Enable Vue devtools
  devtools: { enabled: true },
  
  // CSS configuration
  css: ['~/assets/studio/main.css'],
  
  // Vite configuration for EditorJS
  vite: {
    optimizeDeps: {
      include: ['@editorjs/editorjs', '@editorjs/header', '@editorjs/paragraph']
    }
  }
})
```

#### Debugging Tools Setup

1. **Vue DevTools**: Install browser extension for component inspection
2. **Studio Debug Mode**: Add to your `.env`:
   ```
   STUDIO_DEBUG=true
   EDITORJS_DEBUG=true
   ```
3. **Console Logging**: Enable detailed logging:
   ```javascript
   const studio = useStudioSettings({
     debug: true, // Enables detailed console logs
     namespace: "your_namespace"
   })
   ```

### Project Structure Guidelines

#### File Naming Conventions
```
pages/studio/
├── feature-name.vue        # Kebab case for routes
├── complex-feature/
│   ├── index.vue          # Main page
│   ├── settings.vue       # Sub-page
│   └── preview.vue        # Preview component
```

#### Component Organization
```
components/studio/
├── config/                # Configuration components
│   ├── StudioColorPicker.vue
│   ├── StudioImageUpload.vue
│   └── StudioFieldGroup.vue
├── preview/               # Preview components
│   ├── email/
│   └── pdf/
└── editor/                # EditorJS related
    ├── StudioEditorJS.vue
    └── tools/
```

### Code Style Guidelines

#### Composable Usage
Always use Studio composables for state management:

```javascript
// ✅ Good: Using Studio composables
const studio = useStudioSettings({
  namespace: "emails",
  area: "quotation",
  config: configSchema
})

// ❌ Bad: Manual state management
const settings = ref({})
const hasChanges = ref(false)
```

#### Reactive Values
Use computed properties for derived state:

```javascript
// ✅ Good: Reactive computed
const emailStyle = computed(() => ({
  backgroundColor: studio.getValue("background_color").value,
  color: studio.getValue("text_color").value,
  fontSize: studio.getValue("font_size").value + 'px'
}))

// ❌ Bad: Manual reactive management
const emailStyle = ref({})
watch([bgColor, textColor, fontSize], ([bg, text, size]) => {
  emailStyle.value = { backgroundColor: bg, color: text, fontSize: size + 'px' }
})
```

## Core Components

### StudioWrapper
The root component that provides the Studio context and styling.

```vue
<StudioWrapper>
  <!-- All Studio content must be wrapped in this component -->
  <!-- It provides: -->
  <!-- - Studio-specific CSS scoping -->
  <!-- - Global event bus -->
  <!-- - Theme context -->
  <!-- - Responsive breakpoints -->
</StudioWrapper>
```

**Features:**
- Provides CSS custom properties for theming
- Sets up keyboard shortcuts (Ctrl+S for save)
- Manages responsive breakpoints
- Provides Studio-wide event bus

### StudioHeader
Manages the top navigation bar with save/discard functionality.

```vue
<StudioHeader
  :subtitle="pageTitle"
  :settings-changed="hasChanges"
  :saving="isSaving"
  :no-back-button="false"
  @save-changes="save"
  @discard-changes="reset"
/>
```

**Props:**
- `subtitle` (String): Page title displayed in the header
- `settings-changed` (Boolean): Shows save/discard buttons when true
- `saving` (Boolean): Shows loading state during save operations
- `no-back-button` (Boolean): Hides the back navigation button
- `disabled` (Boolean): Disables all interactions

**Events:**
- `save-changes`: Emitted when save button is clicked
- `discard-changes`: Emitted when discard button is clicked
- `back`: Emitted when back button is clicked (if not disabled)

### StudioTree
Navigation tree component for the sidebar.

```vue
<StudioTree 
  :tree-config="navigationConfig"
  :active-key="currentKey"
  @node-click="handleNodeClick"
/>
```

**Tree Configuration Schema:**
```javascript
const treeConfig = [
  {
    key: "unique_identifier",        // Required: Unique node identifier
    title: "Display Title",          // Required: Shown in the tree
    icon: "fontawesome-icon-name",   // Optional: Icon next to title
    route: "/path/to/page",          // Optional: Navigation route
    displayName: "Alternative Name", // Optional: Different name for breadcrumbs
    expanded: true,                  // Optional: Initially expanded
    disabled: false,                 // Optional: Disable interaction
    badge: "New",                    // Optional: Badge text
    children: [                      // Optional: Nested items
      {
        key: "child_key",
        title: "Child Item",
        route: "/child/route"
      }
    ]
  }
]
```

**Advanced Tree Features:**
```javascript
// Conditional nodes
const treeConfig = computed(() => [
  {
    key: "premium_features",
    title: "Premium Features",
    children: userHasPremium.value ? premiumNodes : []
  }
])

// Dynamic badges
{
  key: "notifications",
  title: "Notifications",
  badge: unreadCount.value > 0 ? unreadCount.value.toString() : null
}

// Custom styling
{
  key: "important",
  title: "Important Section",
  class: "text-red-600 font-semibold"
}
```

### StudioContainer
Layout container with navigation and content areas.

```vue
<StudioContainer>
  <template #navigation>
    <StudioTree :tree-config="treeConfig" />
    <!-- Additional navigation content -->
  </template>
  
  <template #content>
    <StudioContent>
      <!-- Main content -->
    </StudioContent>
  </template>
</StudioContainer>
```

### StudioContent
Main content area with optional sidebar.

```vue
<StudioContent 
  :loading="isLoading"
  :error="errorMessage"
  sidebar-width="300px"
>
  <template #sidebar>
    <StudioConfig
      :config="configSchema"
      :values="currentValues"
      @field-update="handleUpdate"
    />
  </template>
  
  <template #content>
    <StudioConfigPreview>
      <!-- Preview content -->
    </StudioConfigPreview>
  </template>
  
  <template #footer>
    <!-- Optional footer content -->
  </template>
</StudioContent>
```

**Props:**
- `loading` (Boolean): Shows loading spinner overlay
- `error` (String): Shows error message if provided
- `sidebar-width` (String): CSS width for sidebar (default: "280px")
- `full-height` (Boolean): Use full viewport height

### StudioConfig
Configuration panel component that renders form fields based on schema.

```vue
<StudioConfig
  :config="configSchema"
  :values="currentValues"
  :errors="validationErrors"
  :disabled="isDisabled"
  @field-update="handleFieldUpdate"
  @field-focus="handleFieldFocus"
  @field-blur="handleFieldBlur"
/>
```

**Events:**
- `field-update`: `(key: string, value: any) => void`
- `field-focus`: `(key: string) => void`
- `field-blur`: `(key: string) => void`
- `section-expand`: `(sectionId: string, expanded: boolean) => void`

## Complete Configuration Reference

### Field Types Reference

#### Input Fields

```javascript
// Text Input
{
  settingKey: "text_field",
  label: "Text Field",
  type: "input",
  inputType: "text",              // text, email, url, tel, password
  placeholder: "Enter text here",
  value: "",
  required: true,
  disabled: false,
  readonly: false,
  maxlength: 255,
  pattern: "^[a-zA-Z0-9]+$",     // Regex pattern
  autocomplete: "off",            // Browser autocomplete
  spellcheck: false,
  validation: {
    rules: ["required", "min:3", "max:50"],
    messages: {
      required: "This field is required",
      min: "Minimum 3 characters required",
      max: "Maximum 50 characters allowed"
    }
  }
}

// Number Input
{
  settingKey: "number_field",
  label: "Number Field",
  type: "input",
  inputType: "number",
  value: 0,
  min: -100,
  max: 100,
  step: 0.1,
  decimals: 2,                   // Force decimal places
  suffix: "px",                  // Display suffix (px, %, etc)
  prefix: "$",                   // Display prefix
  validation: {
    rules: ["required", "number", "min:-100", "max:100"],
    messages: {
      number: "Must be a valid number",
      min: "Must be at least -100",
      max: "Must be at most 100"
    }
  }
}

// Range Slider
{
  settingKey: "range_field",
  label: "Range Field",
  type: "range",
  value: 50,
  min: 0,
  max: 100,
  step: 5,
  showValue: true,               // Show current value
  marks: {                       // Show marks at specific values
    0: "Min",
    50: "Mid",
    100: "Max"
  }
}
```

#### Selection Fields

```javascript
// Select Dropdown
{
  settingKey: "select_field",
  label: "Select Field",
  type: "select",
  value: "option1",
  placeholder: "Choose an option",
  multiple: false,               // Allow multiple selections
  searchable: true,              // Enable search filtering
  clearable: true,               // Allow clearing selection
  loading: false,                // Show loading state
  options: [
    { value: "option1", label: "Option 1", disabled: false },
    { value: "option2", label: "Option 2", icon: "star" },
    { 
      value: "group1", 
      label: "Group 1", 
      children: [                // Grouped options
        { value: "sub1", label: "Sub Option 1" }
      ]
    }
  ],
  // Dynamic options from API
  optionsUrl: "/api/dynamic-options",
  optionsTransform: (data) => data.map(item => ({
    value: item.id,
    label: item.name
  }))
}

// Radio Buttons
{
  settingKey: "radio_field",
  label: "Radio Field",
  type: "radio",
  value: "left",
  layout: "horizontal",          // horizontal, vertical, grid
  options: [
    { 
      value: "left", 
      label: "Left", 
      icon: "align-left",
      description: "Align content to the left"
    },
    { 
      value: "center", 
      label: "Center", 
      icon: "align-center",
      description: "Center the content"
    },
    { 
      value: "right", 
      label: "Right", 
      icon: "align-right",
      description: "Align content to the right"
    }
  ]
}

// Checkbox
{
  settingKey: "checkbox_field",
  label: "Checkbox Field",
  type: "checkbox",
  value: false,
  description: "Enable this feature",
  indeterminate: false,          // Show indeterminate state
  size: "default"                // small, default, large
}

// Checkbox Group
{
  settingKey: "checkbox_group",
  label: "Checkbox Group",
  type: "checkbox-group",
  value: ["option1", "option3"],
  layout: "vertical",            // horizontal, vertical, grid
  options: [
    { value: "option1", label: "Option 1" },
    { value: "option2", label: "Option 2" },
    { value: "option3", label: "Option 3" }
  ]
}
```

#### Media Fields

```javascript
// Color Picker
{
  settingKey: "color_field",
  label: "Color Field",
  type: "color",
  value: "#2563EB",
  format: "hex",                 // hex, rgb, hsl
  alpha: true,                   // Allow transparency
  presets: [                     // Predefined colors
    "#FF0000", "#00FF00", "#0000FF"
  ],
  swatches: {                    // Color palette groups
    "Brand Colors": ["#2563EB", "#DC2626"],
    "Grays": ["#6B7280", "#9CA3AF"]
  }
}

// Image Upload
{
  settingKey: "image_field",
  label: "Image Field",
  type: "image",
  value: null,                   // File object or URL
  accept: "image/*",             // Accepted file types
  maxSize: 5242880,              // Max file size in bytes (5MB)
  maxWidth: 1920,                // Max image width
  maxHeight: 1080,               // Max image height
  crop: true,                    // Enable image cropping
  aspectRatio: 16/9,             // Crop aspect ratio
  preview: true,                 // Show image preview
  withFetch: true,               // Fetch image blob for preview
  uploadUrl: "/api/upload",      // Custom upload endpoint
  onUpload: (file) => {          // Custom upload handler
    return uploadToCustomService(file)
  }
}

// File Upload
{
  settingKey: "file_field",
  label: "File Field",
  type: "file",
  value: null,
  accept: ".pdf,.doc,.docx",
  maxSize: 10485760,             // 10MB
  multiple: false,               // Allow multiple files
  drag: true,                    // Enable drag & drop
  preview: true                  // Show file preview
}
```

#### Layout Fields

```javascript
// Container for grouped fields
{
  type: "container",
  label: "Padding Settings",
  description: "Configure padding for all sides",
  class: "grid grid-cols-2 gap-4 md:grid-cols-4",
  collapsed: false,              // Start collapsed
  collapsible: true,             // Allow collapse/expand
  children: [
    {
      settingKey: "padding.top",
      label: "Top",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.right",
      label: "Right",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.bottom",
      label: "Bottom",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    },
    {
      settingKey: "padding.left",
      label: "Left",
      type: "input",
      inputType: "number",
      value: 0,
      suffix: "px"
    }
  ]
}

// Tabs Container
{
  type: "tabs",
  tabs: [
    {
      key: "general",
      label: "General",
      icon: "cog",
      children: [
        // Fields for general tab
      ]
    },
    {
      key: "advanced",
      label: "Advanced",
      icon: "sliders",
      children: [
        // Fields for advanced tab
      ]
    }
  ]
}

// Section Divider
{
  type: "divider",
  label: "Advanced Settings",
  description: "Configure advanced options below"
}

// Info/Help Text
{
  type: "info",
  content: "This setting affects how emails are displayed.",
  variant: "info",               // info, warning, error, success
  icon: "info-circle",
  dismissible: false
}
```

#### Advanced Fields

```javascript
// Repeater Field (Dynamic List)
{
  settingKey: "repeater_field",
  label: "Repeater Field",
  type: "repeater",
  value: [],
  addLabel: "Add Item",
  removeLabel: "Remove",
  minItems: 1,
  maxItems: 10,
  sortable: true,                // Allow drag & drop reordering
  template: [                    // Fields for each item
    {
      settingKey: "name",
      label: "Name",
      type: "input",
      value: ""
    },
    {
      settingKey: "value",
      label: "Value",
      type: "input",
      inputType: "number",
      value: 0
    }
  ]
}

// Code Editor
{
  settingKey: "code_field",
  label: "Custom CSS",
  type: "code",
  value: "",
  language: "css",               // css, javascript, html, json
  theme: "dark",                 // light, dark
  lineNumbers: true,
  wordWrap: true,
  height: "200px",
  placeholder: "/* Enter your CSS here */"
}

// Rich Text Editor (EditorJS)
{
  settingKey: "content_field",
  label: "Rich Content",
  type: "editor",
  value: null,                   // EditorJS data object
  placeholder: "Start typing...",
  tools: ["paragraph", "header", "list", "table"],
  magicTags: true,               // Enable magic tag support
  minHeight: "200px",
  maxHeight: "600px"
}

// Date/Time Picker
{
  settingKey: "date_field",
  label: "Date Field",
  type: "date",
  value: null,
  format: "YYYY-MM-DD",
  placeholder: "Select date",
  clearable: true,
  showTime: false,               // Include time picker
  disabledDates: {               // Disable specific dates
    before: new Date(),          // Disable past dates
    after: new Date("2025-12-31"), // Disable future dates
    dates: ["2024-12-25"]        // Disable specific dates
  }
}
```

### Field Validation System

#### Built-in Validation Rules

```javascript
{
  validation: {
    rules: [
      "required",                          // Field is required
      "email",                            // Valid email format
      "url",                              // Valid URL format
      "number",                           // Valid number
      "integer",                          // Valid integer
      "min:5",                            // Minimum value/length
      "max:100",                          // Maximum value/length
      "between:5,100",                    // Value between range
      "in:red,green,blue",               // Value in allowed list
      "regex:^[a-zA-Z0-9]+$",           // Custom regex pattern
      "confirmed:password_confirmation",  // Field confirmation
      "unique:users,email",              // Unique in database
      "exists:categories,id",            // Exists in database
      "file_size:5242880",               // Max file size (5MB)
      "file_types:jpg,png,gif",          // Allowed file types
      "image_dimensions:800,600",        // Image dimensions
      "alpha",                           // Only letters
      "alpha_num",                       // Letters and numbers
      "alpha_dash",                      // Letters, numbers, dashes
      "date",                            // Valid date
      "date_after:2024-01-01",          // Date after specified
      "date_before:2025-12-31"          // Date before specified
    ],
    messages: {
      required: "This field is required",
      email: "Please enter a valid email address",
      min: "Minimum {min} characters required",
      max: "Maximum {max} characters allowed",
      file_size: "File size must be less than {size}",
      custom_rule: "Custom validation message"
    }
  }
}
```

#### Custom Validation Functions

```javascript
{
  validation: {
    rules: ["required", "custom_validation"],
    customValidators: {
      custom_validation: {
        validate: (value, field, allValues) => {
          // Custom validation logic
          if (field.settingKey === "username" && value.length < 3) {
            return false
          }
          // Cross-field validation
          if (field.settingKey === "end_date" && allValues.start_date) {
            return new Date(value) > new Date(allValues.start_date)
          }
          return true
        },
        message: "Custom validation failed"
      }
    }
  }
}
```

### Conditional Field Display

```javascript
{
  settingKey: "notification_email",
  label: "Notification Email",
  type: "input",
  inputType: "email",
  value: "",
  showIf: (values) => {
    // Show only if notifications are enabled
    return values.enable_notifications === true
  },
  disableIf: (values) => {
    // Disable if in maintenance mode
    return values.maintenance_mode === true
  }
}

// Multiple conditions
{
  settingKey: "advanced_setting",
  label: "Advanced Setting",
  type: "input",
  showIf: (values) => {
    return values.user_role === "admin" && 
           values.enable_advanced === true
  }
}
```

### Field Dependencies and Reactions

```javascript
{
  settingKey: "logo_width",
  label: "Logo Width",
  type: "input",
  inputType: "number",
  value: 150,
  suffix: "px",
  reactions: {
    // React to changes in other fields
    logo_enabled: (value, fieldValue) => {
      // Hide/disable when logo is disabled
      return {
        visible: value === true,
        disabled: value === false
      }
    },
    // Update value based on other field
    container_width: (value, fieldValue) => {
      // Max logo width is 50% of container
      return {
        max: Math.floor(value * 0.5)
      }
    }
  }
}

// Supplementary fields (affect other fields without storing value)
{
  settingKey: null,                      // No storage
  supplementaryFor: "logo_width",        // Affects this field
  label: "Logo Visibility",
  type: "radio",
  value: 150,                           // Default for logo_width
  options: [
    { value: 0, label: "Hidden", icon: "eye-slash" },
    { value: 150, label: "Visible", icon: "eye" }
  ]
}
```

## EditorJS Deep Dive

### Understanding the Editor Lifecycle

#### 1. Initialization Phase

```javascript
// useEditorJS composable handles initialization
const editor = useEditorJS({
  elementId: "editor-container",
  tools: editorTools,
  data: initialData,
  placeholder: "Start typing...",
  onChange: (data) => {
    // Handle content changes
    emit("update:modelValue", data)
  }
})

// Lifecycle hooks
onMounted(async () => {
  await editor.initialize()
  // Editor is ready
})

onBeforeUnmount(() => {
  editor.destroy()
  // Cleanup resources
})
```

#### 2. Tool Registration and Configuration

```javascript
// Complete tool configuration
const editorTools = {
  // Basic tools
  paragraph: {
    class: Paragraph,
    inlineToolbar: true,
    config: {
      preserveBlank: true,        // Keep empty paragraphs
      placeholder: "Type here..."
    }
  },
  
  header: {
    class: Header,
    inlineToolbar: true,
    shortcut: "CMD+SHIFT+H",
    config: {
      levels: [2, 3, 4, 5, 6],
      defaultLevel: 2,
      allowAnchor: true,
      anchorLength: 100
    }
  },
  
  list: {
    class: List,
    inlineToolbar: true,
    shortcut: "CMD+SHIFT+L",
    config: {
      defaultStyle: "unordered"
    }
  },
  
  table: {
    class: Table,
    inlineToolbar: true,
    config: {
      rows: 2,
      cols: 3,
      maxRows: 10,
      maxCols: 8,
      withHeadings: true,
      stretch: false
    }
  },
  
  // Custom magic tag tool
  magicTag: {
    class: MagicTagInlineTool,
    config: {
      tags: availableTags,
      categories: tagCategories,
      validation: {
        required: false,
        allowNested: false
      }
    }
  },
  
  // Custom block tools
  customBlock: {
    class: CustomBlockTool,
    toolbox: {
      title: "Custom Block",
      icon: "<svg>...</svg>"
    },
    config: {
      // Custom configuration
    }
  }
}
```

#### 3. Data Flow Management

```javascript
// EditorJS data structure
const editorData = {
  time: 1672531200000,           // Timestamp
  blocks: [
    {
      id: "block-id-1",
      type: "paragraph",
      data: {
        text: "Hello [[%customer.name]]!"
      }
    },
    {
      id: "block-id-2", 
      type: "header",
      data: {
        text: "Invoice Details",
        level: 2
      }
    }
  ],
  version: "2.28.0"
}

// Reactive data management
const content = ref(null)
const editor = useEditorJS({
  data: content.value,
  onChange: (outputData) => {
    content.value = outputData
    // Auto-save or validation logic
    validateContent(outputData)
  }
})

// Watch for external changes
watch(() => props.modelValue, (newData) => {
  if (newData && editor.isReady.value) {
    editor.render(newData)
  }
}, { deep: true })
```

#### 4. Memory Management and Cleanup

```javascript
// Proper cleanup to prevent memory leaks
const editor = useEditorJS({
  // ... config
})

onBeforeUnmount(async () => {
  if (editor.instance.value) {
    try {
      // Save data before destroying
      const data = await editor.save()
      emit("beforeDestroy", data)
      
      // Destroy editor instance
      await editor.destroy()
      
      // Clear references
      editor.instance.value = null
    } catch (error) {
      console.error("Editor cleanup error:", error)
    }
  }
})

// Handle route changes
onBeforeRouteLeave(async (to, from, next) => {
  if (editor.hasUnsavedChanges.value) {
    const result = await confirmDialog("You have unsaved changes. Continue?")
    if (result) {
      await editor.destroy()
      next()
    } else {
      next(false)
    }
  } else {
    next()
  }
})
```

### Magic Tag Integration Deep Dive

#### MagicTagInlineTool Implementation

```javascript
// plugins/editorjs/MagicTagInlineTool.js
class MagicTagInlineTool {
  constructor({ api, config }) {
    this.api = api
    this.config = config
    this.tags = config.tags || []
    this.button = null
    this.state = false
  }
  
  static get isInline() {
    return true
  }
  
  static get title() {
    return "Magic Tag"
  }
  
  render() {
    this.button = document.createElement("button")
    this.button.type = "button"
    this.button.innerHTML = "🏷️"
    this.button.classList.add("ce-inline-tool")
    
    return this.button
  }
  
  surround(range) {
    if (this.state) {
      this.unwrap(range)
      return
    }
    
    this.wrap(range)
  }
  
  wrap(range) {
    const selectedText = range.extractContents()
    const span = document.createElement("span")
    
    span.classList.add("magic-tag")
    span.setAttribute("contenteditable", "false")
    span.appendChild(selectedText)
    
    range.insertNode(span)
    
    // Show tag selector
    this.showTagSelector(span)
  }
  
  unwrap(range) {
    const span = this.api.selection.findParentTag("SPAN", "magic-tag")
    if (span) {
      this.api.selection.expandToTag(span)
      this.api.caret.setToBlock(
        this.api.blocks.getCurrentBlockIndex(),
        span.textContent.length
      )
      span.outerHTML = span.textContent
    }
  }
  
  showTagSelector(element) {
    // Implementation for tag selection dropdown
    const dropdown = new TagDropdown({
      tags: this.tags,
      onSelect: (tag) => {
        element.textContent = tag.name
        element.setAttribute("data-tag", tag.name)
        element.title = tag.description
      }
    })
    
    dropdown.show(element)
  }
  
  checkState() {
    const span = this.api.selection.findParentTag("SPAN", "magic-tag")
    this.state = !!span
    
    if (this.state) {
      this.button.classList.add("ce-inline-tool--active")
    } else {
      this.button.classList.remove("ce-inline-tool--active")
    }
    
    return this.state
  }
}
```

#### MagicTagProcessor Implementation

```javascript
// plugins/editorjs/MagicTagProcessor.js
class MagicTagProcessor {
  constructor(config = {}) {
    this.tagPattern = /\[\[%([^%\]]+)%?\]\]/g
    this.config = {
      allowNested: false,
      caseSensitive: true,
      maxTagLength: 100,
      ...config
    }
  }
  
  // Process content when loading into editor
  processInbound(data) {
    if (!data || !data.blocks) return data
    
    return {
      ...data,
      blocks: data.blocks.map(block => this.processBlock(block))
    }
  }
  
  // Process content when saving from editor
  processOutbound(data) {
    if (!data || !data.blocks) return data
    
    return {
      ...data,
      blocks: data.blocks.map(block => this.extractTags(block))
    }
  }
  
  processBlock(block) {
    if (block.type === "paragraph" || block.type === "header") {
      return {
        ...block,
        data: {
          ...block.data,
          text: this.convertTagsToSpans(block.data.text)
        }
      }
    }
    
    return block
  }
  
  convertTagsToSpans(text) {
    if (!text) return text
    
    return text.replace(this.tagPattern, (match, tagContent) => {
      const tag = this.findTag(tagContent)
      const spanClass = tag ? "magic-tag" : "magic-tag invalid"
      const title = tag ? tag.description : "Invalid tag"
      
      return `<span class="${spanClass}" contenteditable="false" data-tag="${match}" title="${title}">${match}</span>`
    })
  }
  
  extractTags(block) {
    if (block.type === "paragraph" || block.type === "header") {
      return {
        ...block,
        data: {
          ...block.data,
          text: this.convertSpansToTags(block.data.text)
        }
      }
    }
    
    return block
  }
  
  convertSpansToTags(text) {
    if (!text) return text
    
    // Convert magic tag spans back to text format
    const tempDiv = document.createElement("div")
    tempDiv.innerHTML = text
    
    const spans = tempDiv.querySelectorAll(".magic-tag")
    spans.forEach(span => {
      const tagText = span.getAttribute("data-tag") || span.textContent
      span.outerHTML = tagText
    })
    
    return tempDiv.innerHTML
  }
  
  findTag(tagContent) {
    const fullTag = `[[%${tagContent}]]`
    return this.config.tags?.find(tag => 
      this.config.caseSensitive 
        ? tag.name === fullTag
        : tag.name.toLowerCase() === fullTag.toLowerCase()
    )
  }
  
  validateTags(data) {
    const errors = []
    
    if (!data || !data.blocks) return { valid: true, errors }
    
    data.blocks.forEach((block, blockIndex) => {
      if (block.type === "paragraph" || block.type === "header") {
        const text = block.data.text || ""
        const matches = text.match(this.tagPattern)
        
        if (matches) {
          matches.forEach(match => {
            const tagContent = match.replace(/\[\[%?|%?\]\]/g, "")
            const tag = this.findTag(tagContent)
            
            if (!tag) {
              errors.push({
                block: blockIndex,
                tag: match,
                error: "Invalid tag"
              })
            }
            
            if (match.length > this.config.maxTagLength) {
              errors.push({
                block: blockIndex,
                tag: match,
                error: "Tag too long"
              })
            }
          })
        }
      }
    })
    
    return {
      valid: errors.length === 0,
      errors
    }
  }
}
```

#### Advanced EditorJS Configuration

```javascript
// Complete EditorJS setup with error handling
const useAdvancedEditorJS = ({
  elementId,
  initialData,
  availableTags,
  onSave,
  onError
}) => {
  const editor = ref(null)
  const isReady = ref(false)
  const hasUnsavedChanges = ref(false)
  
  // Initialize tag processor
  const tagProcessor = new MagicTagProcessor({
    tags: availableTags,
    allowNested: false,
    caseSensitive: true
  })
  
  // Editor configuration
  const editorConfig = {
    holder: elementId,
    placeholder: "Start typing or insert a magic tag...",
    autofocus: true,
    hideToolbar: false,
    
    tools: {
      paragraph: {
        class: Paragraph,
        inlineToolbar: ["magicTag", "bold", "italic", "link"]
      },
      
      header: {
        class: Header,
        inlineToolbar: ["magicTag", "bold", "italic"],
        config: { levels: [2, 3, 4], defaultLevel: 2 }
      },
      
      list: {
        class: List,
        inlineToolbar: ["magicTag", "bold", "italic"]
      },
      
      table: {
        class: Table,
        inlineToolbar: ["magicTag", "bold", "italic"]
      },
      
      magicTag: {
        class: MagicTagInlineTool,
        config: {
          tags: availableTags,
          onInsert: (tag) => {
            console.log("Tag inserted:", tag)
          }
        }
      }
    },
    
    onChange: async (api, event) => {
      hasUnsavedChanges.value = true
      
      try {
        const data = await api.saver.save()
        const processedData = tagProcessor.processOutbound(data)
        
        // Validate tags
        const validation = tagProcessor.validateTags(processedData)
        if (!validation.valid) {
          console.warn("Invalid tags found:", validation.errors)
        }
        
        onSave?.(processedData)
      } catch (error) {
        console.error("Save error:", error)
        onError?.(error)
      }
    },
    
    onReady: () => {
      isReady.value = true
      console.log("EditorJS is ready")
    }
  }
  
  // Initialize editor
  const initialize = async () => {
    try {
      editor.value = new EditorJS(editorConfig)
      await editor.value.isReady
      
      // Load initial data
      if (initialData) {
        const processedData = tagProcessor.processInbound(initialData)
        await editor.value.render(processedData)
      }
      
      hasUnsavedChanges.value = false
    } catch (error) {
      console.error("Editor initialization error:", error)
      onError?.(error)
    }
  }
  
  // Save content
  const save = async () => {
    if (!editor.value) return null
    
    try {
      const data = await editor.value.save()
      const processedData = tagProcessor.processOutbound(data)
      hasUnsavedChanges.value = false
      return processedData
    } catch (error) {
      console.error("Save error:", error)
      onError?.(error)
      return null
    }
  }
  
  // Destroy editor
  const destroy = async () => {
    if (editor.value) {
      try {
        await editor.value.destroy()
        editor.value = null
        isReady.value = false
      } catch (error) {
        console.error("Destroy error:", error)
      }
    }
  }
  
  return {
    editor,
    isReady,
    hasUnsavedChanges,
    initialize,
    save,
    destroy
  }
}
```

### Performance Considerations

#### Large Document Optimization

```javascript
// Lazy loading for large documents
const useLazyEditorJS = ({ threshold = 1000 }) => {
  const shouldLazyLoad = computed(() => {
    return document.blocks?.length > threshold
  })
  
  const loadInChunks = async (data, chunkSize = 100) => {
    if (!shouldLazyLoad.value) {
      return editor.value.render(data)
    }
    
    const blocks = data.blocks || []
    const chunks = []
    
    for (let i = 0; i < blocks.length; i += chunkSize) {
      chunks.push(blocks.slice(i, i + chunkSize))
    }
    
    // Load first chunk immediately
    if (chunks[0]) {
      await editor.value.render({ blocks: chunks[0] })
    }
    
    // Load remaining chunks with delay
    for (let i = 1; i < chunks.length; i++) {
      await new Promise(resolve => setTimeout(resolve, 50))
      await editor.value.blocks.insert(chunks[i])
    }
  }
  
  return { loadInChunks, shouldLazyLoad }
}
```

#### Memory Management

```javascript
// Memory-efficient tag management
const useTagMemoryManagement = () => {
  const tagCache = new Map()
  const maxCacheSize = 1000
  
  const getCachedTag = (tagName) => {
    if (tagCache.has(tagName)) {
      // Move to end (LRU)
      const tag = tagCache.get(tagName)
      tagCache.delete(tagName)
      tagCache.set(tagName, tag)
      return tag
    }
    return null
  }
  
  const cacheTag = (tag) => {
    if (tagCache.size >= maxCacheSize) {
      // Remove oldest entry
      const firstKey = tagCache.keys().next().value
      tagCache.delete(firstKey)
    }
    tagCache.set(tag.name, tag)
  }
  
  const clearCache = () => {
    tagCache.clear()
  }
  
  return { getCachedTag, cacheTag, clearCache }
}
```

## Magic Tags System

### Complete Tag Structure and Validation

#### Tag Schema Definition

```javascript
// Complete tag structure
const tagDefinition = {
  // Required properties
  name: "[[%customer.email]]",           // The tag pattern used in content
  display: "Customer Email",             // Human-readable name
  
  // Optional properties
  description: "Customer's email address", // Tooltip/help text
  category: "customer",                   // Grouping category
  dataType: "string",                     // Data type for validation
  preview: "john@example.com",           // Preview value for testing
  icon: "envelope",                      // Icon for UI
  
  // Validation rules
  validation: {
    required: false,                     // Is this tag required in content?
    format: "email",                     // Expected format (email, url, number, etc.)
    maxLength: 255,                      // Maximum rendered length
    pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/ // Custom regex
  },
  
  // Access control
  permissions: {
    view: ["admin", "manager"],          // Who can see this tag
    use: ["admin", "manager", "user"]    // Who can use this tag
  },
  
  // Context information
  context: {
    entity: "customer",                  // Related entity
    field: "email",                      // Specific field
    relationship: null                   // Relationship path (e.g., "customer.company.name")
  },
  
  // Advanced options
  options: {
    fallback: "No email provided",       // Default value if data missing
    transform: "lowercase",              // Data transformation (lowercase, uppercase, capitalize)
    conditional: true,                   // Can be used in conditional logic
    sensitive: false                     // Contains sensitive data
  }
}
```

#### Tag Categories and Organization

```javascript
// Organizing tags by category
const tagCategories = {
  customer: {
    label: "Customer Information",
    icon: "user",
    description: "Tags related to customer data",
    color: "#3B82F6",
    tags: [
      {
        name: "[[%customer.name]]",
        display: "Customer Name",
        description: "Full name of the customer"
      },
      {
        name: "[[%customer.email]]",
        display: "Customer Email",
        description: "Customer's email address"
      },
      {
        name: "[[%customer.company]]",
        display: "Company Name",
        description: "Customer's company name"
      }
    ]
  },
  
  quotation: {
    label: "Quotation Details",
    icon: "file-invoice",
    description: "Tags related to quotation information",
    color: "#10B981",
    tags: [
      {
        name: "[[%quotation.id]]",
        display: "Quotation ID",
        description: "Unique quotation identifier"
      },
      {
        name: "[[%quotation.date]]",
        display: "Quotation Date",
        description: "Date when quotation was created",
        dataType: "date",
        options: {
          format: "YYYY-MM-DD"
        }
      },
      {
        name: "[[%quotation.total]]",
        display: "Total Amount",
        description: "Total quotation amount",
        dataType: "currency",
        options: {
          currency: "EUR",
          decimals: 2
        }
      }
    ]
  },
  
  company: {
    label: "Company Information",
    icon: "building",
    description: "Tags related to your company",
    color: "#8B5CF6",
    tags: [
      {
        name: "[[%company.name]]",
        display: "Company Name",
        description: "Your company name"
      },
      {
        name: "[[%company.address]]",
        display: "Company Address",
        description: "Company address",
        dataType: "multiline"
      }
    ]
  },
  
  system: {
    label: "System Information",
    icon: "cog",
    description: "System-generated tags",
    color: "#6B7280",
    tags: [
      {
        name: "[[%system.date]]",
        display: "Current Date",
        description: "Current system date",
        dataType: "date"
      },
      {
        name: "[[%system.time]]",
        display: "Current Time",
        description: "Current system time",
        dataType: "time"
      }
    ]
  }
}
```

#### Advanced Tag Features

```javascript
// Conditional tags
const conditionalTags = {
  name: "[[%if:customer.premium]]Premium Customer[[/if]]",
  display: "Premium Customer Badge",
  description: "Shows 'Premium Customer' only for premium customers",
  type: "conditional",
  condition: {
    field: "customer.premium",
    operator: "equals",
    value: true
  },
  content: {
    true: "Premium Customer",
    false: ""
  }
}

// Nested/composite tags
const compositeTags = {
  name: "[[%customer.full_address]]",
  display: "Full Customer Address",
  description: "Complete customer address (street, city, country)",
  type: "composite",
  components: [
    "[[%customer.street]]",
    "[[%customer.city]]", 
    "[[%customer.postal_code]]",
    "[[%customer.country]]"
  ],
  template: "{street}, {city} {postal_code}, {country}",
  separator: ", "
}

// Calculated tags
const calculatedTags = {
  name: "[[%quotation.tax_amount]]",
  display: "Tax Amount",
  description: "Calculated tax amount based on total and tax rate",
  type: "calculated",
  formula: "quotation.subtotal * quotation.tax_rate / 100",
  dependencies: ["quotation.subtotal", "quotation.tax_rate"],
  dataType: "currency"
}
```

### Tag Processing Lifecycle

#### 1. Tag Registration and Validation

```javascript
class TagRegistry {
  constructor() {
    this.tags = new Map()
    this.categories = new Map()
    this.validators = new Map()
  }
  
  register(tagDefinition) {
    // Validate tag definition
    const validation = this.validateTagDefinition(tagDefinition)
    if (!validation.valid) {
      throw new Error(`Invalid tag definition: ${validation.errors.join(", ")}`)
    }
    
    // Register tag
    this.tags.set(tagDefinition.name, tagDefinition)
    
    // Add to category
    if (tagDefinition.category) {
      if (!this.categories.has(tagDefinition.category)) {
        this.categories.set(tagDefinition.category, [])
      }
      this.categories.get(tagDefinition.category).push(tagDefinition)
    }
    
    // Register custom validator if provided
    if (tagDefinition.validation?.custom) {
      this.validators.set(tagDefinition.name, tagDefinition.validation.custom)
    }
  }
  
  validateTagDefinition(definition) {
    const errors = []
    
    // Required fields
    if (!definition.name) errors.push("Tag name is required")
    if (!definition.display) errors.push("Display name is required")
    
    // Name format validation
    if (definition.name && !/^\[\[%[^%\]]+\]\]$/.test(definition.name)) {
      errors.push("Tag name must match pattern [[%tag_name]]")
    }
    
    // Data type validation
    const validDataTypes = ["string", "number", "date", "time", "currency", "email", "url", "multiline"]
    if (definition.dataType && !validDataTypes.includes(definition.dataType)) {
      errors.push(`Invalid data type: ${definition.dataType}`)
    }
    
    return {
      valid: errors.length === 0,
      errors
    }
  }
  
  getTag(name) {
    return this.tags.get(name)
  }
  
  getTagsByCategory(category) {
    return this.categories.get(category) || []
  }
  
  getAllTags() {
    return Array.from(this.tags.values())
  }
  
  searchTags(query) {
    const results = []
    const lowerQuery = query.toLowerCase()
    
    for (const tag of this.tags.values()) {
      if (
        tag.name.toLowerCase().includes(lowerQuery) ||
        tag.display.toLowerCase().includes(lowerQuery) ||
        tag.description?.toLowerCase().includes(lowerQuery)
      ) {
        results.push(tag)
      }
    }
    
    return results
  }
}
```

#### 2. Tag Resolution and Data Binding

```javascript
class TagResolver {
  constructor(dataContext = {}) {
    this.dataContext = dataContext
    this.cache = new Map()
    this.resolvers = new Map()
  }
  
  registerResolver(pattern, resolver) {
    this.resolvers.set(pattern, resolver)
  }
  
  async resolveTag(tagName, options = {}) {
    // Check cache first
    const cacheKey = `${tagName}:${JSON.stringify(options)}`
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }
    
    // Extract tag path
    const tagPath = tagName.replace(/\[\[%?|%?\]\]/g, "")
    
    // Find appropriate resolver
    const resolver = this.findResolver(tagPath)
    if (!resolver) {
      throw new Error(`No resolver found for tag: ${tagName}`)
    }
    
    // Resolve value
    const value = await resolver(tagPath, this.dataContext, options)
    
    // Apply transformations
    const transformedValue = this.applyTransformations(value, options)
    
    // Cache result
    this.cache.set(cacheKey, transformedValue)
    
    return transformedValue
  }
  
  findResolver(tagPath) {
    for (const [pattern, resolver] of this.resolvers) {
      if (this.matchesPattern(tagPath, pattern)) {
        return resolver
      }
    }
    return null
  }
  
  matchesPattern(path, pattern) {
    // Simple pattern matching - can be enhanced
    if (pattern === "*") return true
    if (pattern.includes("*")) {
      const regexPattern = pattern.replace(/\*/g, ".*")
      return new RegExp(`^${regexPattern}$`).test(path)
    }
    return path.startsWith(pattern)
  }
  
  applyTransformations(value, options) {
    let result = value
    
    // Type-specific transformations
    if (options.dataType === "currency") {
      result = this.formatCurrency(result, options)
    } else if (options.dataType === "date") {
      result = this.formatDate(result, options)
    }
    
    // General transformations
    if (options.transform === "lowercase") {
      result = String(result).toLowerCase()
    } else if (options.transform === "uppercase") {
      result = String(result).toUpperCase()
    } else if (options.transform === "capitalize") {
      result = String(result).charAt(0).toUpperCase() + String(result).slice(1)
    }
    
    return result
  }
  
  formatCurrency(value, options) {
    const currency = options.currency || "EUR"
    const decimals = options.decimals || 2
    
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency,
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(value)
  }
  
  formatDate(value, options) {
    const format = options.format || "YYYY-MM-DD"
    const date = new Date(value)
    
    // Simple date formatting - use a proper library like date-fns in real implementation
    if (format === "YYYY-MM-DD") {
      return date.toISOString().split('T')[0]
    }
    
    return date.toLocaleDateString()
  }
}
```

#### 3. Tag Integration with EditorJS

```javascript
// Enhanced StudioEditorJS component
const StudioEditorJS = defineComponent({
  props: {
    modelValue: Object,
    tags: Array,
    placeholder: String,
    minHeight: String,
    maxHeight: String
  },
  
  setup(props, { emit }) {
    const editorRef = ref(null)
    const isReady = ref(false)
    
    // Initialize tag registry
    const tagRegistry = new TagRegistry()
    props.tags?.forEach(tag => tagRegistry.register(tag))
    
    // Initialize tag resolver
    const tagResolver = new TagResolver()
    
    // Register default resolvers
    tagResolver.registerResolver("customer.*", async (path, context) => {
      const [entity, field] = path.split(".")
      return context.customer?.[field] || ""
    })
    
    tagResolver.registerResolver("quotation.*", async (path, context) => {
      const [entity, field] = path.split(".")
      return context.quotation?.[field] || ""
    })
    
    tagResolver.registerResolver("system.*", async (path, context) => {
      const [entity, field] = path.split(".")
      if (field === "date") return new Date().toISOString().split('T')[0]
      if (field === "time") return new Date().toLocaleTimeString()
      return ""
    })
    
    // Enhanced magic tag tool configuration
    const magicTagTool = {
      class: MagicTagInlineTool,
      config: {
        tags: props.tags,
        registry: tagRegistry,
        resolver: tagResolver,
        onInsert: (tag) => {
          emit("tag-inserted", tag)
        },
        onValidation: (errors) => {
          emit("validation-errors", errors)
        }
      }
    }
    
    // Editor configuration
    const editorConfig = {
      holder: editorRef.value,
      placeholder: props.placeholder || "Start typing...",
      minHeight: parseInt(props.minHeight) || 200,
      tools: {
        paragraph: {
          class: Paragraph,
          inlineToolbar: ["magicTag", "bold", "italic", "link"]
        },
        header: {
          class: Header,
          inlineToolbar: ["magicTag", "bold", "italic"],
          config: { levels: [2, 3, 4], defaultLevel: 2 }
        },
        list: {
          class: List,
          inlineToolbar: ["magicTag", "bold", "italic"]
        },
        magicTag: magicTagTool
      },
      onChange: async (api, event) => {
        const data = await api.saver.save()
        emit("update:modelValue", data)
      }
    }
    
    // Initialize editor
    const initializeEditor = async () => {
      const { default: EditorJS } = await import("@editorjs/editorjs")
      
      const editor = new EditorJS(editorConfig)
      await editor.isReady
      
      if (props.modelValue) {
        await editor.render(props.modelValue)
      }
      
      isReady.value = true
    }
    
    onMounted(() => {
      initializeEditor()
    })
    
    return {
      editorRef,
      isReady
    }
  }
})
```

### Performance and Optimization

#### Tag Caching Strategy

```javascript
// Intelligent tag caching
class TagCache {
  constructor(options = {}) {
    this.maxSize = options.maxSize || 1000
    this.ttl = options.ttl || 300000 // 5 minutes
    this.cache = new Map()
    this.timers = new Map()
  }
  
  set(key, value, customTTL) {
    // Remove oldest if at capacity
    if (this.cache.size >= this.maxSize) {
      const firstKey = this.cache.keys().next().value
      this.delete(firstKey)
    }
    
    // Set value
    this.cache.set(key, {
      value,
      timestamp: Date.now()
    })
    
    // Set expiration timer
    const ttl = customTTL || this.ttl
    const timer = setTimeout(() => {
      this.delete(key)
    }, ttl)
    
    this.timers.set(key, timer)
  }
  
  get(key) {
    const entry = this.cache.get(key)
    if (!entry) return null
    
    // Check if expired
    if (Date.now() - entry.timestamp > this.ttl) {
      this.delete(key)
      return null
    }
    
    return entry.value
  }
  
  delete(key) {
    this.cache.delete(key)
    
    // Clear timer
    const timer = this.timers.get(key)
    if (timer) {
      clearTimeout(timer)
      this.timers.delete(key)
    }
  }
  
  clear() {
    // Clear all timers
    for (const timer of this.timers.values()) {
      clearTimeout(timer)
    }
    
    this.cache.clear()
    this.timers.clear()
  }
  
  stats() {
    return {
      size: this.cache.size,
      maxSize: this.maxSize,
      hitRate: this.hits / (this.hits + this.misses) || 0
    }
  }
}
```

## State Management

### Using Studio Settings

The `useStudioSettings` composable is the primary way to manage settings in the Studio:

```javascript
// Basic usage
const studio = useStudioSettings({
  namespace: "themes",           // Settings namespace
  area: "mail",                 // Settings area
  config: studioConfig,         // Configuration schema
  
  // Optional callbacks
  onSuccess: (data) => {
    addToast({ type: "success", message: "Settings saved!" })
  },
  onError: (error) => {
    addToast({ type: "error", message: "Failed to save settings" })
    console.error("Save error:", error)
  },
  onLoad: (data) => {
    console.log("Settings loaded:", data)
  },
  
  // Optional configuration
  autoSave: false,              // Enable auto-save
  autoSaveDelay: 2000,         // Auto-save delay in ms
  debug: true                   // Enable debug logging
})

// Reactive properties
const isLoading = studio.loading          // Boolean: Loading state
const isSaving = studio.saving            // Boolean: Saving state
const isDirty = studio.isDirty            // Boolean: Has unsaved changes
const values = studio.values              // Ref: Current field values
const originalValues = studio.originalValues // Ref: Original loaded values
const changedFields = studio.changedFields   // Computed: Array of changed field keys

// Methods
await studio.load()                       // Load settings from backend
studio.update("setting_key", newValue)   // Update a field value
const value = studio.getValue("setting_key", defaultValue) // Get reactive field value
await studio.save()                      // Save changes to backend
studio.reset()                           // Reset to original values
studio.setConfig(newConfig)              // Update configuration schema
```

### Advanced Settings Usage

#### Nested Field Values

```javascript
// Working with nested objects
const studio = useStudioSettings({
  namespace: "layout",
  area: "email",
  config: configSchema
})

// Update nested values
studio.update("padding.top", 20)
studio.update("padding.right", 15)
studio.update("padding.bottom", 20)
studio.update("padding.left", 15)

// Get nested values
const padding = computed(() => ({
  top: studio.getValue("padding.top", 0).value,
  right: studio.getValue("padding.right", 0).value,
  bottom: studio.getValue("padding.bottom", 0).value,
  left: studio.getValue("padding.left", 0).value
}))

// Bulk update nested values
const updatePadding = (newPadding) => {
  Object.entries(newPadding).forEach(([key, value]) => {
    studio.update(`padding.${key}`, value)
  })
}
```

#### Validation Integration

```javascript
const studio = useStudioSettings({
  namespace: "validation_example",
  area: "settings",
  config: configWithValidation,
  
  // Custom validation
  validate: (values) => {
    const errors = {}
    
    // Custom validation logic
    if (values.start_date && values.end_date) {
      if (new Date(values.start_date) > new Date(values.end_date)) {
        errors.end_date = "End date must be after start date"
      }
    }
    
    if (values.email && !isValidEmail(values.email)) {
      errors.email = "Invalid email format"
    }
    
    return {
      valid: Object.keys(errors).length === 0,
      errors
    }
  },
  
  // Prevent save if validation fails
  beforeSave: (values) => {
    const validation = studio.validate(values)
    if (!validation.valid) {
      throw new Error("Validation failed: " + Object.values(validation.errors).join(", "))
    }
    return values
  }
})

// Reactive validation state
const validationErrors = computed(() => studio.validate(studio.values.value).errors)
const isValid = computed(() => studio.validate(studio.values.value).valid)
```

#### Auto-Save Implementation

```javascript
const studio = useStudioSettings({
  namespace: "autosave_example",
  area: "settings",
  config: configSchema,
  autoSave: true,
  autoSaveDelay: 3000,
  
  onAutoSave: (data) => {
    // Show subtle auto-save indicator
    showToast("Auto-saved", { type: "info", duration: 1000 })
  }
})

// Manual control over auto-save
const pauseAutoSave = () => studio.pauseAutoSave()
const resumeAutoSave = () => studio.resumeAutoSave()

// Watch for specific fields to trigger immediate save
watch(() => studio.getValue("critical_setting").value, (newValue) => {
  if (newValue !== null) {
    studio.save() // Save immediately for critical settings
  }
})
```

### Using Studio Lexicons

For multilingual content with EditorJS support:

```javascript
const studio = useStudioLexicons({
  namespace: "emails",          // Lexicon namespace
  area: "quotation",           // Lexicon area
  language: "en",              // Current language
  config: lexiconConfig,       // Configuration schema
  
  // Language change callback
  onLanguageChange: (language) => {
    console.log("Language changed to:", language)
  },
  
  // Save callback
  onSuccess: () => {
    addToast({ type: "success", message: "Content saved!" })
  }
})

// Language management
const availableLanguages = ["en", "nl", "fr", "de"]
const currentLanguage = studio.language

// Change language
await studio.changeLanguage("nl")

// Get EditorJS content
const emailSubject = computed({
  get: () => studio.getValue("email.subject").value,
  set: (value) => studio.update("email.subject", value)
})

const emailBody = computed({
  get: () => studio.getValue("email.body").value,
  set: (value) => studio.update("email.body", value)
})
```

#### Multi-Language Workflow

```javascript
// Complete multi-language setup
const useMultiLanguageStudio = (config) => {
  const languages = ref(["en", "nl", "fr", "de"])
  const currentLanguage = ref("en")
  const studios = ref({})
  
  // Initialize studio for each language
  const initializeLanguages = async () => {
    for (const lang of languages.value) {
      studios.value[lang] = useStudioLexicons({
        ...config,
        language: lang
      })
      await studios.value[lang].load()
    }
  }
  
  // Get current studio
  const currentStudio = computed(() => studios.value[currentLanguage.value])
  
  // Change language
  const changeLanguage = async (newLanguage) => {
    if (languages.value.includes(newLanguage)) {
      currentLanguage.value = newLanguage
      
      // Load if not already loaded
      if (!studios.value[newLanguage]) {
        studios.value[newLanguage] = useStudioLexicons({
          ...config,
          language: newLanguage
        })
        await studios.value[newLanguage].load()
      }
    }
  }
  
  // Save all languages
  const saveAll = async () => {
    const promises = Object.values(studios.value).map(studio => studio.save())
    await Promise.all(promises)
  }
  
  // Check if any language has changes
  const hasChanges = computed(() => {
    return Object.values(studios.value).some(studio => studio.isDirty.value)
  })
  
  return {
    languages,
    currentLanguage,
    currentStudio,
    changeLanguage,
    saveAll,
    hasChanges,
    initializeLanguages
  }
}
```

### Global Studio State

For sharing state across Studio pages:

```javascript
// useStudioState.js
const globalState = reactive({
  unsavedChanges: false,
  currentPage: null,
  theme: "light",
  sidebarCollapsed: false,
  recentColors: [],
  recentImages: []
})

export const useStudioState = () => {
  // Update global state
  const setUnsavedChanges = (hasChanges) => {
    globalState.unsavedChanges = hasChanges
  }
  
  const setCurrentPage = (page) => {
    globalState.currentPage = page
  }
  
  const addRecentColor = (color) => {
    if (!globalState.recentColors.includes(color)) {
      globalState.recentColors.unshift(color)
      // Keep only last 10 colors
      if (globalState.recentColors.length > 10) {
        globalState.recentColors.pop()
      }
    }
  }
  
  const addRecentImage = (image) => {
    const existing = globalState.recentImages.find(img => img.url === image.url)
    if (!existing) {
      globalState.recentImages.unshift(image)
      if (globalState.recentImages.length > 20) {
        globalState.recentImages.pop()
      }
    }
  }
  
  // Getters
  const hasUnsavedChanges = computed(() => globalState.unsavedChanges)
  const currentPage = computed(() => globalState.currentPage)
  const recentColors = computed(() => globalState.recentColors)
  const recentImages = computed(() => globalState.recentImages)
  
  return {
    // State
    globalState: readonly(globalState),
    
    // Getters
    hasUnsavedChanges,
    currentPage,
    recentColors,
    recentImages,
    
    // Actions
    setUnsavedChanges,
    setCurrentPage,
    addRecentColor,
    addRecentImage
  }
}

// Usage in components
export default {
  setup() {
    const studioState = useStudioState()
    const studio = useStudioSettings({
      namespace: "example",
      area: "settings",
      config: configSchema
    })
    
    // Sync local changes with global state
    watchEffect(() => {
      studioState.setUnsavedChanges(studio.isDirty.value)
    })
    
    // Add color to recent when changed
    watch(() => studio.getValue("primary_color").value, (color) => {
      if (color) {
        studioState.addRecentColor(color)
      }
    })
    
    return {
      studio,
      studioState
    }
  }
}
```

## Creating New Studio Pages

### Complete Page Creation Workflow

#### Step 1: Plan Your Page Structure

Before coding, define:

1. **Purpose**: What will this page customize?
2. **Settings**: What configuration options are needed?
3. **Preview**: How will changes be visualized?
4. **Navigation**: Where does it fit in the Studio structure?

Example planning for a "Newsletter Template" page:

```javascript
// Page planning
const pageDefinition = {
  purpose: "Customize newsletter email templates",
  namespace: "newsletters",
  area: "templates",
  
  settings: [
    "header_color",
    "logo_position", 
    "content_width",
    "footer_text",
    "social_links"
  ],
  
  preview: "Live newsletter preview with sample content",
  
  navigation: {
    section: "Email Templates",
    position: "after emails.vue"
  }
}
```

#### Step 2: Create Configuration Schema

```javascript
// config/newsletter-config.js
export const newsletterConfig = [
  {
    id: "header",
    icon: "header",
    displayName: "Header Settings",
    description: "Configure newsletter header appearance",
    fields: [
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        description: "Background color for the newsletter header"
      },
      {
        settingKey: "header.text_color", 
        label: "Header Text Color",
        type: "color",
        value: "#1F2937"
      },
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "content",
    icon: "file-text",
    displayName: "Content Settings",
    fields: [
      {
        settingKey: "content.width",
        label: "Content Width",
        type: "input",
        inputType: "number",
        value: 600,
        min: 400,
        max: 800,
        suffix: "px"
      },
      {
        type: "container",
        label: "Content Padding",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "content.padding.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          // ... other padding fields
        ]
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.text",
        label: "Footer Text",
        type: "editor",
        value: null,
        placeholder: "Enter footer content...",
        tools: ["paragraph", "bold", "italic", "link"],
        magicTags: true
      },
      {
        settingKey: "social_links.enabled",
        label: "Show Social Links",
        type: "checkbox",
        value: false
      },
      {
        settingKey: "social_links.links",
        label: "Social Links",
        type: "repeater",
        value: [],
        addLabel: "Add Social Link",
        template: [
          {
            settingKey: "platform",
            label: "Platform",
            type: "select",
            options: [
              { value: "facebook", label: "Facebook", icon: "facebook" },
              { value: "twitter", label: "Twitter", icon: "twitter" },
              { value: "linkedin", label: "LinkedIn", icon: "linkedin" }
            ]
          },
          {
            settingKey: "url",
            label: "URL",
            type: "input",
            inputType: "url",
            placeholder: "https://..."
          }
        ],
        showIf: (values) => values["social_links.enabled"] === true
      }
    ]
  }
]
```

#### Step 3: Create Preview Component

```vue
<!-- components/studio/preview/NewsletterPreview.vue -->
<template>
  <div class="newsletter-preview">
    <!-- Newsletter container with dynamic styling -->
    <div 
      class="newsletter-container"
      :style="containerStyle"
    >
      <!-- Header -->
      <header 
        class="newsletter-header"
        :style="headerStyle"
      >
        <div v-if="logoEnabled" class="logo-container" :class="logoAlignmentClass">
          <img v-if="logoBlob" :src="logoBlob" alt="Logo" :style="logoStyle" />
          <div v-else class="logo-placeholder">Logo</div>
        </div>
        
        <h1 class="newsletter-title" :style="titleStyle">
          Weekly Newsletter
        </h1>
      </header>
      
      <!-- Content -->
      <main class="newsletter-content" :style="contentStyle">
        <div class="content-section">
          <h2>Featured Article</h2>
          <p>
            This is a sample newsletter content. Your actual content will appear here
            when you send the newsletter to your subscribers.
          </p>
        </div>
        
        <div class="content-section">
          <h3>Quick Updates</h3>
          <ul>
            <li>Update item 1</li>
            <li>Update item 2</li>
            <li>Update item 3</li>
          </ul>
        </div>
      </main>
      
      <!-- Footer -->
      <footer class="newsletter-footer" :style="footerStyle">
        <div v-if="footerText" class="footer-content">
          <StudioEditorRenderer :content="footerText" />
        </div>
        
        <div v-if="socialLinksEnabled && socialLinks.length" class="social-links">
          <a 
            v-for="link in socialLinks" 
            :key="link.platform"
            :href="link.url"
            class="social-link"
            :class="`social-${link.platform}`"
          >
            <FontAwesome :icon="getSocialIcon(link.platform)" />
          </a>
        </div>
        
        <div class="unsubscribe-link">
          <a href="#" style="color: #6B7280; font-size: 12px;">
            Unsubscribe from this newsletter
          </a>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  headerBackgroundColor: String,
  headerTextColor: String,
  logoEnabled: Boolean,
  logoPosition: String,
  logoBlob: String,
  contentWidth: Number,
  contentPadding: Object,
  footerText: Object,
  socialLinksEnabled: Boolean,
  socialLinks: Array
})

// Computed styles
const containerStyle = computed(() => ({
  width: `${props.contentWidth}px`,
  maxWidth: "100%",
  margin: "0 auto",
  backgroundColor: "#FFFFFF",
  border: "1px solid #E5E7EB",
  borderRadius: "8px",
  overflow: "hidden"
}))

const headerStyle = computed(() => ({
  backgroundColor: props.headerBackgroundColor,
  color: props.headerTextColor,
  padding: "20px",
  textAlign: props.logoPosition
}))

const logoAlignmentClass = computed(() => ({
  "text-left": props.logoPosition === "left",
  "text-center": props.logoPosition === "center", 
  "text-right": props.logoPosition === "right"
}))

const logoStyle = computed(() => ({
  maxHeight: "60px",
  width: "auto"
}))

const titleStyle = computed(() => ({
  color: props.headerTextColor,
  margin: props.logoEnabled ? "10px 0 0 0" : "0",
  fontSize: "24px",
  fontWeight: "bold"
}))

const contentStyle = computed(() => ({
  padding: `${props.contentPadding?.top || 20}px ${props.contentPadding?.right || 20}px ${props.contentPadding?.bottom || 20}px ${props.contentPadding?.left || 20}px`
}))

const footerStyle = computed(() => ({
  backgroundColor: "#F9FAFB",
  padding: "20px",
  borderTop: "1px solid #E5E7EB",
  textAlign: "center"
}))

// Helper methods
const getSocialIcon = (platform) => {
  const icons = {
    facebook: "facebook-f",
    twitter: "twitter", 
    linkedin: "linkedin-in",
    instagram: "instagram"
  }
  return icons[platform] || "link"
}
</script>

<style scoped>
.newsletter-preview {
  padding: 20px;
  background: #F3F4F6;
  min-height: 600px;
}

.content-section {
  margin-bottom: 20px;
}

.content-section h2 {
  color: #1F2937;
  font-size: 18px;
  margin-bottom: 8px;
}

.content-section h3 {
  color: #374151;
  font-size: 16px;
  margin-bottom: 8px;
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 15px;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #4B5563;
  color: white;
  border-radius: 50%;
  text-decoration: none;
  transition: background-color 0.2s;
}

.social-link:hover {
  background: #374151;
}

.footer-content {
  margin-bottom: 15px;
}
</style>
```

#### Step 4: Create the Main Page Component

```vue
<!-- pages/studio/newsletter.vue -->
<template>
  <StudioWrapper>
    <StudioHeader 
      subtitle="Newsletter Templates"
      :settings-changed="hasChanges"
      :saving="isSaving"
      @save-changes="save"
      @discard-changes="reset"
    />
    
    <StudioContainer>
      <template #navigation>
        <StudioTree :tree-config="treeConfig" />
      </template>
      
      <template #content>
        <StudioContent :loading="studio.loading.value">
          <template #sidebar>
            <StudioConfig
              :config="newsletterConfig"
              :values="studio.values.value"
              @field-update="studio.update"
            />
          </template>
          
          <template #content>
            <StudioConfigPreview>
              <NewsletterPreview
                :header-background-color="headerBackgroundColor"
                :header-text-color="headerTextColor"
                :logo-enabled="logoEnabled"
                :logo-position="logoPosition"
                :logo-blob="logoBlob"
                :content-width="contentWidth"
                :content-padding="contentPadding"
                :footer-text="footerText"
                :social-links-enabled="socialLinksEnabled"
                :social-links="socialLinks"
              />
            </StudioConfigPreview>
          </template>
        </StudioContent>
      </template>
    </StudioContainer>
  </StudioWrapper>
</template>

<script setup>
import { newsletterConfig } from "~/config/newsletter-config"

// Page metadata
definePageMeta({
  title: "Newsletter Templates - Studio",
  description: "Customize your newsletter email templates"
})

// Page state
const hasChanges = ref(false)
const isSaving = ref(false)

// Navigation configuration
const treeConfig = ref([
  {
    key: "templates",
    title: "Email Templates",
    icon: "envelope",
    expanded: true,
    children: [
      {
        key: "newsletters",
        displayName: "Newsletter",
        icon: "newspaper",
        route: "/manage/studio/newsletter"
      }
    ]
  }
])

// Initialize studio
const studio = useStudioSettings({
  namespace: "newsletters",
  area: "templates", 
  config: newsletterConfig,
  onSuccess: () => {
    addToast({ 
      type: "success", 
      message: "Newsletter template settings saved!" 
    })
  },
  onError: (error) => {
    addToast({ 
      type: "error", 
      message: "Failed to save newsletter settings" 
    })
    console.error("Save error:", error)
  }
})

// Reactive values for preview
const headerBackgroundColor = computed(() => 
  studio.getValue("header.background_color").value
)
const headerTextColor = computed(() => 
  studio.getValue("header.text_color").value
)
const logoEnabled = computed(() => 
  studio.getValue("logo.enabled").value
)
const logoPosition = computed(() => 
  studio.getValue("logo.position").value
)
const contentWidth = computed(() => 
  studio.getValue("content.width").value
)
const contentPadding = computed(() => ({
  top: studio.getValue("content.padding.top").value,
  right: studio.getValue("content.padding.right").value,
  bottom: studio.getValue("content.padding.bottom").value,
  left: studio.getValue("content.padding.left").value
}))
const footerText = computed(() => 
  studio.getValue("footer.text").value
)
const socialLinksEnabled = computed(() => 
  studio.getValue("social_links.enabled").value
)
const socialLinks = computed(() => 
  studio.getValue("social_links.links").value
)

// Handle logo image
const logoField = computed(() => studio.values.value["logo.image"])
const { blob: logoBlob } = useLoadImage().getPreview(logoField)

// Sync state
watchEffect(() => {
  hasChanges.value = studio.isDirty.value
  isSaving.value = studio.saving.value
})

// Methods
const save = () => studio.save()
const reset = () => studio.reset()

// Load settings on mount
onMounted(() => studio.load())
</script>
```

#### Step 5: Add to Main Studio Navigation

Update `pages/studio.vue`:

```javascript
// Add to tree configuration
const treeConfig = ref([
  // ... existing configuration
  {
    key: "email-templates",
    title: "Email Templates",
    icon: "envelope",
    expanded: true,
    children: [
      {
        key: "newsletters",
        route: "/manage/studio/newsletter",
        displayName: "Newsletter",
        icon: "newspaper"
      },
      {
        key: "notifications",
        route: "/manage/studio/notifications", 
        displayName: "Notifications",
        icon: "bell"
      }
      // ... other email templates
    ]
  }
])
```

### Advanced Page Features

#### Real-time Collaboration

```javascript
// Enable real-time updates for collaborative editing
const useCollaborativeStudio = (options) => {
  const studio = useStudioSettings(options)
  const { $socket } = useNuxtApp()
  
  // Listen for changes from other users
  $socket.on(`studio:${options.namespace}:${options.area}:updated`, (data) => {
    if (data.userId !== getCurrentUserId()) {
      // Show notification about external changes
      addToast({
        type: "info",
        message: `${data.userName} made changes to this page`,
        action: {
          label: "Refresh",
          handler: () => studio.load()
        }
      })
    }
  })
  
  // Broadcast changes to other users
  const originalUpdate = studio.update
  studio.update = (key, value) => {
    originalUpdate(key, value)
    
    // Debounced broadcast
    debouncedBroadcast({
      namespace: options.namespace,
      area: options.area,
      field: key,
      value: value,
      userId: getCurrentUserId(),
      userName: getCurrentUserName()
    })
  }
  
  return studio
}

// Usage
const studio = useCollaborativeStudio({
  namespace: "newsletters",
  area: "templates",
  config: newsletterConfig
})
```

#### Advanced Validation

```javascript
// Cross-field validation with async checks
const useAdvancedValidation = (studio) => {
  const validationState = ref({
    isValidating: false,
    errors: {},
    warnings: {}
  })
  
  // Async validation rules
  const asyncValidators = {
    "email.domain": async (value) => {
      if (!value) return { valid: true }
      
      try {
        const response = await $fetch(`/api/validate-domain/${value}`)
        return {
          valid: response.valid,
          message: response.valid ? null : "Domain is not reachable"
        }
      } catch (error) {
        return {
          valid: false,
          message: "Unable to validate domain"
        }
      }
    },
    
    "template.name": async (value) => {
      if (!value) return { valid: true }
      
      const exists = await $fetch(`/api/templates/exists?name=${encodeURIComponent(value)}`)
      return {
        valid: !exists,
        message: exists ? "Template name already exists" : null
      }
    }
  }
  
  // Run validation
  const validate = async (values = studio.values.value) => {
    validationState.value.isValidating = true
    const errors = {}
    const warnings = {}
    
    // Run async validations
    const validationPromises = Object.entries(asyncValidators).map(async ([field, validator]) => {
      try {
        const result = await validator(values[field])
        if (!result.valid) {
          errors[field] = result.message
        }
      } catch (error) {
        warnings[field] = "Validation failed - please check manually"
      }
    })
    
    await Promise.all(validationPromises)
    
    validationState.value.errors = errors
    validationState.value.warnings = warnings
    validationState.value.isValidating = false
    
    return {
      valid: Object.keys(errors).length === 0,
      errors,
      warnings
    }
  }
  
  // Auto-validate on field changes
  let validationTimeout
  watch(() => studio.values.value, () => {
    clearTimeout(validationTimeout)
    validationTimeout = setTimeout(() => {
      validate()
    }, 1000)
  }, { deep: true })
  
  return {
    validationState: readonly(validationState),
    validate
  }
}
```

#### Import/Export Functionality

```javascript
// Add import/export capabilities to any Studio page
const useStudioImportExport = (studio, options = {}) => {
  const isExporting = ref(false)
  const isImporting = ref(false)
  
  // Export current settings
  const exportSettings = async () => {
    isExporting.value = true
    
    try {
      const data = {
        namespace: studio.namespace,
        area: studio.area,
        settings: studio.values.value,
        metadata: {
          exportedAt: new Date().toISOString(),
          version: "2.0",
          user: getCurrentUserName()
        }
      }
      
      const blob = new Blob([JSON.stringify(data, null, 2)], {
        type: "application/json"
      })
      
      const url = URL.createObjectURL(blob)
      const link = document.createElement("a")
      link.href = url
      link.download = `${studio.namespace}-${studio.area}-settings.json`
      link.click()
      
      URL.revokeObjectURL(url)
      
      addToast({
        type: "success",
        message: "Settings exported successfully"
      })
    } catch (error) {
      addToast({
        type: "error",
        message: "Failed to export settings"
      })
    } finally {
      isExporting.value = false
    }
  }
  
  // Import settings from file
  const importSettings = async (file) => {
    isImporting.value = true
    
    try {
      const text = await file.text()
      const data = JSON.parse(text)
      
      // Validate import data
      if (!data.settings || data.namespace !== studio.namespace) {
        throw new Error("Invalid settings file")
      }
      
      // Confirm import
      const confirmed = await confirmDialog({
        title: "Import Settings",
        message: "This will replace all current settings. Continue?",
        confirmText: "Import",
        cancelText: "Cancel"
      })
      
      if (!confirmed) return
      
      // Apply imported settings
      Object.entries(data.settings).forEach(([key, value]) => {
        studio.update(key, value)
      })
      
      addToast({
        type: "success",
        message: "Settings imported successfully"
      })
    } catch (error) {
      addToast({
        type: "error",
        message: "Failed to import settings: " + error.message
      })
    } finally {
      isImporting.value = false
    }
  }
  
  // File input handler
  const handleFileImport = (event) => {
    const file = event.target.files[0]
    if (file) {
      importSettings(file)
    }
  }
  
  return {
    isExporting,
    isImporting,
    exportSettings,
    importSettings,
    handleFileImport
  }
}

// Usage in component
const { exportSettings, importSettings, handleFileImport } = useStudioImportExport(studio)
```

#### Custom Field Types

```javascript
// Create custom field types for specific use cases
const customFieldTypes = {
  // Color palette field
  colorPalette: {
    component: defineComponent({
      props: {
        modelValue: Array,
        max: { type: Number, default: 5 }
      },
      setup(props, { emit }) {
        const colors = ref(props.modelValue || [])
        
        const addColor = (color) => {
          if (colors.value.length < props.max) {
            colors.value.push(color)
            emit("update:modelValue", colors.value)
          }
        }
        
        const removeColor = (index) => {
          colors.value.splice(index, 1)
          emit("update:modelValue", colors.value)
        }
        
        return { colors, addColor, removeColor }
      },
      template: `
        <div class="color-palette-field">
          <div class="color-list">
            <div 
              v-for="(color, index) in colors" 
              :key="index"
              class="color-item"
              :style="{ backgroundColor: color }"
              @click="removeColor(index)"
            >
              <span class="remove-icon">×</span>
            </div>
          </div>
          <ColorPicker @color-selected="addColor" />
        </div>
      `
    })
  },
  
  // Font selector with preview
  fontSelector: {
    component: defineComponent({
      props: {
        modelValue: String,
        category: { type: String, default: "all" }
      },
      setup(props, { emit }) {
        const availableFonts = ref([
          { name: "Arial", family: "Arial, sans-serif", category: "sans-serif" },
          { name: "Times New Roman", family: "Times New Roman, serif", category: "serif" },
          { name: "Courier New", family: "Courier New, monospace", category: "monospace" }
        ])
        
        const filteredFonts = computed(() => {
          if (props.category === "all") return availableFonts.value
          return availableFonts.value.filter(font => font.category === props.category)
        })
        
        return {
          filteredFonts,
          selectFont: (font) => emit("update:modelValue", font.family)
        }
      },
      template: `
        <div class="font-selector">
          <div 
            v-for="font in filteredFonts"
            :key="font.name"
            class="font-option"
            :class="{ active: modelValue === font.family }"
            :style="{ fontFamily: font.family }"
            @click="selectFont(font)"
          >
            {{ font.name }}
          </div>
        </div>
      `
    })
  }
}

// Register custom field types
const registerCustomFieldTypes = (studio) => {
  Object.entries(customFieldTypes).forEach(([type, definition]) => {
    studio.registerFieldType(type, definition)
  })
}
```

## Email Templates

### Complete Email Configuration

```javascript
// Complete email template configuration schema
const emailTemplateConfig = [
  {
    id: "theme",
    icon: "palette",
    displayName: "Color Theme",
    description: "Configure the overall color scheme",
    fields: [
      {
        settingKey: "theme.primary_color",
        label: "Primary Color",
        type: "color",
        value: "#2563EB",
        description: "Main brand color used for buttons and accents"
      },
      {
        settingKey: "theme.secondary_color",
        label: "Secondary Color", 
        type: "color",
        value: "#64748B",
        description: "Secondary color for less prominent elements"
      },
      {
        settingKey: "theme.background_color",
        label: "Background Color",
        type: "color",
        value: "#FFFFFF",
        description: "Main background color for the email"
      },
      {
        settingKey: "theme.text_color",
        label: "Text Color",
        type: "color",
        value: "#1F2937",
        description: "Primary text color"
      },
      {
        settingKey: "theme.link_color",
        label: "Link Color",
        type: "color",
        value: "#2563EB",
        description: "Color for hyperlinks"
      }
    ]
  },
  
  {
    id: "layout",
    icon: "layout",
    displayName: "Layout Settings",
    fields: [
      {
        settingKey: "layout.width",
        label: "Email Width",
        type: "input",
        inputType: "number",
        value: 600,
        min: 400,
        max: 800,
        suffix: "px",
        description: "Maximum width of the email content"
      },
      {
        settingKey: "layout.alignment",
        label: "Content Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ]
      },
      {
        type: "container",
        label: "Content Padding",
        description: "Spacing around the main content area",
        class: "grid grid-cols-2 gap-4 md:grid-cols-4",
        children: [
          {
            settingKey: "layout.padding.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.right", 
            label: "Right",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.bottom",
            label: "Bottom", 
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.left",
            label: "Left",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          }
        ]
      }
    ]
  },
  
  {
    id: "header",
    icon: "header",
    displayName: "Header Configuration",
    fields: [
      {
        settingKey: "header.enabled",
        label: "Show Header",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_bottom",
        label: "Bottom Border",
        type: "checkbox",
        value: true,
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_color",
        label: "Border Color",
        type: "color",
        value: "#E5E7EB",
        showIf: (values) => values["header.enabled"] === true && values["header.border_bottom"] === true
      }
    ]
  },
  
  {
    id: "logo",
    icon: "image",
    displayName: "Logo Settings",
    fields: [
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox", 
        value: true
      },
      {
        settingKey: "logo.image",
        label: "Logo Image",
        type: "image",
        value: null,
        accept: "image/*",
        maxSize: 2097152, // 2MB
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.width",
        label: "Logo Width",
        type: "input",
        inputType: "number",
        value: 150,
        min: 50,
        max: 400,
        suffix: "px",
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "header",
        options: [
          { value: "header", label: "Header", description: "Logo in email header" },
          { value: "content", label: "Content", description: "Logo in main content area" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.alignment",
        label: "Logo Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "typography",
    icon: "font",
    displayName: "Typography",
    fields: [
      {
        settingKey: "typography.font_family",
        label: "Font Family",
        type: "fontSelector",
        value: "Arial, sans-serif",
        category: "web-safe"
      },
      {
        settingKey: "typography.font_size",
        label: "Base Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24,
        suffix: "px"
      },
      {
        settingKey: "typography.line_height",
        label: "Line Height",
        type: "input",
        inputType: "number",
        value: 1.5,
        min: 1.0,
        max: 2.0,
        step: 0.1
      },
      {
        settingKey: "typography.heading_font_family",
        label: "Heading Font Family",
        type: "fontSelector",
        value: "Arial, sans-serif",
        category: "web-safe"
      }
    ]
  },
  
  {
    id: "buttons", 
    icon: "mouse-pointer",
    displayName: "Button Styling",
    fields: [
      {
        settingKey: "buttons.style",
        label: "Button Style",
        type: "radio",
        value: "filled",
        options: [
          { value: "filled", label: "Filled", description: "Solid background color" },
          { value: "outlined", label: "Outlined", description: "Border with transparent background" },
          { value: "text", label: "Text Only", description: "No background or border" }
        ]
      },
      {
        settingKey: "buttons.background_color",
        label: "Button Background",
        type: "color",
        value: "#2563EB",
        showIf: (values) => values["buttons.style"] === "filled"
      },
      {
        settingKey: "buttons.text_color",
        label: "Button Text Color",
        type: "color", 
        value: "#FFFFFF"
      },
      {
        settingKey: "buttons.border_color",
        label: "Border Color",
        type: "color",
        value: "#2563EB",
        showIf: (values) => ["outlined", "filled"].includes(values["buttons.style"])
      },
      {
        type: "container",
        label: "Button Padding",
        class: "grid grid-cols-2 gap-4",
        children: [
          {
            settingKey: "buttons.padding.vertical",
            label: "Vertical",
            type: "input",
            inputType: "number",
            value: 12,
            suffix: "px"
          },
          {
            settingKey: "buttons.padding.horizontal",
            label: "Horizontal",
            type: "input", 
            inputType: "number",
            value: 24,
            suffix: "px"
          }
        ]
      },
      {
        type: "container",
        label: "Border Radius",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "buttons.border_radius.top_left",
            label: "TL",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.top_right",
            label: "TR",
            type: "input",
            inputType: "number", 
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.bottom_right",
            label: "BR",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.bottom_left",
            label: "BL",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          }
        ]
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.enabled",
        label: "Show Footer",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "footer.background_color",
        label: "Footer Background",
        type: "color",
        value: "#F3F4F6",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.text_color",
        label: "Footer Text Color",
        type: "color",
        value: "#6B7280",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.font_size",
        label: "Footer Font Size",
        type: "input",
        inputType: "number",
        value: 12,
        min: 10,
        max: 16,
        suffix: "px",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.alignment",
        label: "Footer Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["footer.enabled"] === true
      }
    ]
  }
]
```

### Using the Email Editor Modal

```vue
<!-- Example usage of StudioEmailModal -->
<template>
  <div>
    <button @click="openEmailEditor" class="btn btn-primary">
      Edit Email Template
    </button>
    
    <StudioEmailModal
      v-if="showEmailModal"
      type="quotation"
      :entity-id="quotationId"
      :initial-subject="emailSubject"
      :initial-content="emailContent"
      :available-tags="quotationTags"
      @on-send="handleEmailSend"
      @on-save="handleEmailSave"
      @on-close="closeEmailModal"
    />
  </div>
</template>

<script setup>
const showEmailModal = ref(false)
const quotationId = ref(123)
const emailSubject = ref("Your Quotation #[[%quotation.id]]")
const emailContent = ref(null)

// Available magic tags for quotation emails
const quotationTags = [
  {
    name: "[[%quotation.id]]",
    display: "Quotation ID",
    description: "Unique quotation identifier",
    category: "quotation"
  },
  {
    name: "[[%quotation.date]]",
    display: "Quotation Date", 
    description: "Date when quotation was created",
    category: "quotation"
  },
  {
    name: "[[%customer.name]]",
    display: "Customer Name",
    description: "Full name of the customer",
    category: "customer"
  },
  {
    name: "[[%customer.email]]",
    display: "Customer Email",
    description: "Customer's email address",
    category: "customer"
  },
  {
    name: "[[%company.name]]",
    display: "Company Name",
    description: "Your company name",
    category: "company"
  }
]

const openEmailEditor = () => {
  showEmailModal.value = true
}

const closeEmailModal = () => {
  showEmailModal.value = false
}

const handleEmailSend = async (emailData) => {
  try {
    await $fetch("/api/emails/send", {
      method: "POST",
      body: {
        type: "quotation",
        entityId: quotationId.value,
        subject: emailData.subject,
        content: emailData.content,
        recipients: emailData.recipients
      }
    })
    
    addToast({
      type: "success",
      message: "Email sent successfully!"
    })
    
    closeEmailModal()
  } catch (error) {
    addToast({
      type: "error",
      message: "Failed to send email"
    })
  }
}

const handleEmailSave = async (emailData) => {
  try {
    await $fetch("/api/templates/save", {
      method: "POST",
      body: {
        type: "quotation",
        subject: emailData.subject,
        content: emailData.content
      }
    })
    
    addToast({
      type: "success",
      message: "Email template saved!"
    })
  } catch (error) {
    addToast({
      type: "error",
      message: "Failed to save template"
    })
  }
}
</script>
```

## PDF Templates

### PDF Template Configuration

```javascript
// Complete PDF template configuration
const pdfTemplateConfig = [
  {
    id: "document",
    icon: "file-pdf",
    displayName: "Document Settings",
    fields: [
      {
        settingKey: "document.page_size",
        label: "Page Size",
        type: "select",
        value: "A4",
        options: [
          { value: "A4", label: "A4 (210 × 297 mm)" },
          { value: "Letter", label: "Letter (8.5 × 11 in)" },
          { value: "Legal", label: "Legal (8.5 × 14 in)" }
        ]
      },
      {
        settingKey: "document.orientation",
        label: "Orientation",
        type: "radio",
        value: "portrait",
        options: [
          { value: "portrait", label: "Portrait", icon: "portrait" },
          { value: "landscape", label: "Landscape", icon: "landscape" }
        ]
      },
      {
        type: "container",
        label: "Page Margins",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "document.margin.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.right",
            label: "Right", 
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.bottom",
            label: "Bottom",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.left",
            label: "Left",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          }
        ]
      }
    ]
  },
  
  {
    id: "header",
    icon: "header",
    displayName: "Header Configuration",
    fields: [
      {
        settingKey: "header.enabled",
        label: "Show Header",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "header.height",
        label: "Header Height",
        type: "input",
        inputType: "number",
        value: 80,
        suffix: "px",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_bottom",
        label: "Bottom Border",
        type: "checkbox",
        value: true,
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_color",
        label: "Border Color",
        type: "color",
        value: "#E5E7EB",
        showIf: (values) => values["header.enabled"] === true && values["header.border_bottom"] === true
      }
    ]
  },
  
  {
    id: "logo", 
    icon: "image",
    displayName: "Logo Settings",
    fields: [
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "logo.image",
        label: "Logo Image",
        type: "image",
        value: null,
        accept: "image/*",
        maxSize: 2097152, // 2MB
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.width",
        label: "Logo Width",
        type: "input",
        inputType: "number",
        value: 120,
        min: 50,
        max: 300,
        suffix: "px",
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "header-left",
        options: [
          { value: "header-left", label: "Header Left" },
          { value: "header-center", label: "Header Center" },
          { value: "header-right", label: "Header Right" },
          { value: "content-top", label: "Content Top" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "typography",
    icon: "font",
    displayName: "Typography",
    fields: [
      {
        settingKey: "typography.font_family",
        label: "Font Family",
        type: "select",
        value: "Arial",
        options: [
          { value: "Arial", label: "Arial" },
          { value: "Helvetica", label: "Helvetica" },
          { value: "Times", label: "Times New Roman" },
          { value: "Courier", label: "Courier New" }
        ]
      },
      {
        settingKey: "typography.font_size",
        label: "Base Font Size",
        type: "input",
        inputType: "number",
        value: 12,
        min: 8,
        max: 16,
        suffix: "pt"
      },
      {
        settingKey: "typography.line_height",
        label: "Line Height",
        type: "input",
        inputType: "number",
        value: 1.4,
        min: 1.0,
        max: 2.0,
        step: 0.1
      },
      {
        settingKey: "typography.heading_font_size",
        label: "Heading Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24,
        suffix: "pt"
      }
    ]
  },
  
  {
    id: "colors",
    icon: "palette",
    displayName: "Color Scheme",
    fields: [
      {
        settingKey: "colors.primary",
        label: "Primary Color",
        type: "color",
        value: "#2563EB",
        description: "Used for headings and accents"
      },
      {
        settingKey: "colors.secondary",
        label: "Secondary Color",
        type: "color",
        value: "#64748B",
        description: "Used for secondary text and borders"
      },
      {
        settingKey: "colors.text",
        label: "Text Color",
        type: "color",
        value: "#1F2937",
        description: "Main text color"
      },
      {
        settingKey: "colors.background",
        label: "Background Color",
        type: "color",
        value: "#FFFFFF",
        description: "Page background color"
      },
      {
        settingKey: "colors.table_header",
        label: "Table Header Background",
        type: "color",
        value: "#F3F4F6",
        description: "Background color for table headers"
      },
      {
        settingKey: "colors.table_border",
        label: "Table Border Color",
        type: "color",
        value: "#E5E7EB",
        description: "Color for table borders"
      }
    ]
  },
  
  {
    id: "layout",
    icon: "layout",
    displayName: "Layout & Spacing",
    fields: [
      {
        settingKey: "layout.content_width",
        label: "Content Width",
        type: "radio",
        value: "full",
        options: [
          { value: "narrow", label: "Narrow", description: "70% of page width" },
          { value: "normal", label: "Normal", description: "85% of page width" },
          { value: "full", label: "Full", description: "95% of page width" }
        ]
      },
      {
        settingKey: "layout.section_spacing",
        label: "Section Spacing",
        type: "input",
        inputType: "number",
        value: 20,
        min: 10,
        max: 40,
        suffix: "px",
        description: "Space between major sections"
      },
      {
        settingKey: "layout.table_spacing",
        label: "Table Spacing",
        type: "input",
        inputType: "number", 
        value: 15,
        min: 5,
        max: 30,
        suffix: "px",
        description: "Space around tables"
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.enabled",
        label: "Show Footer",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "footer.height",
        label: "Footer Height",
        type: "input",
        inputType: "number",
        value: 60,
        suffix: "px",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.background_color",
        label: "Footer Background",
        type: "color",
        value: "#F9FAFB",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.show_page_numbers",
        label: "Show Page Numbers",
        type: "checkbox",
        value: true,
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.page_number_position",
        label: "Page Number Position",
        type: "radio",
        value: "right",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["footer.enabled"] === true && values["footer.show_page_numbers"] === true
      }
    ]
  }
]
```

### Advanced Image Handling for PDFs

```javascript
// Enhanced image loading for PDF templates
const useAdvancedImageLoading = () => {
  const imageCache = new Map()
  const loadingStates = reactive({})
  
  const loadImage = async (source, options = {}) => {
    const {
      maxWidth = 800,
      maxHeight = 600,
      quality = 0.9,
      format = "image/jpeg"
    } = options
    
    // Check cache first
    const cacheKey = `${source}-${maxWidth}-${maxHeight}-${quality}`
    if (imageCache.has(cacheKey)) {
      return imageCache.get(cacheKey)
    }
    
    loadingStates[cacheKey] = true
    
    try {
      let blob
      
      if (source instanceof File) {
        blob = source
      } else if (typeof source === "string") {
        const response = await fetch(source)
        blob = await response.blob()
      } else {
        throw new Error("Invalid image source")
      }
      
      // Create image element for processing
      const img = new Image()
      const canvas = document.createElement("canvas")
      const ctx = canvas.getContext("2d")
      
      await new Promise((resolve, reject) => {
        img.onload = resolve
        img.onerror = reject
        img.src = URL.createObjectURL(blob)
      })
      
      // Calculate new dimensions
      let { width, height } = img
      
      if (width > maxWidth) {
        height = (height * maxWidth) / width
        width = maxWidth
      }
      
      if (height > maxHeight) {
        width = (width * maxHeight) / height
        height = maxHeight
      }
      
      // Resize image
      canvas.width = width
      canvas.height = height
      ctx.drawImage(img, 0, 0, width, height)
      
      // Convert to blob
      const processedBlob = await new Promise(resolve => {
        canvas.toBlob(resolve, format, quality)
      })
      
      // Create object URL
      const url = URL.createObjectURL(processedBlob)
      
      // Cache result
      const result = {
        url,
        width,
        height,
        size: processedBlob.size,
        blob: processedBlob
      }
      
      imageCache.set(cacheKey, result)
      
      // Cleanup
      URL.revokeObjectURL(img.src)
      
      return result
    } catch (error) {
      console.error("Image loading failed:", error)
      throw error
    } finally {
      loadingStates[cacheKey] = false
    }
  }
  
  const clearCache = () => {
    // Revoke all object URLs
    for (const cached of imageCache.values()) {
      if (cached.url) {
        URL.revokeObjectURL(cached.url)
      }
    }
    imageCache.clear()
  }
  
  // Cleanup on unmount
  onBeforeUnmount(() => {
    clearCache()
  })
  
  return {
    loadImage,
    clearCache,
    loadingStates: readonly(loadingStates),
    cacheSize: computed(() => imageCache.size)
  }
}
```

## Best Practices

### 1. State Management Best Practices

#### Always Use Studio Composables

```javascript
// ✅ Good: Using Studio composables
const studio = useStudioSettings({
  namespace: "emails",
  area: "quotation",
  config: configSchema
})

// ❌ Bad: Manual state management
const settings = ref({})
const hasChanges = ref(false)
```

#### Proper Reactive Value Management

```javascript
// ✅ Good: Reactive computed properties
const emailStyle = computed(() => ({
  backgroundColor: studio.getValue("background_color").value,
  color: studio.getValue("text_color").value,
  fontSize: studio.getValue("font_size").value + 'px'
}))

// ❌ Bad: Manual reactive management
const emailStyle = ref({})
watch([bgColor, textColor, fontSize], ([bg, text, size]) => {
  emailStyle.value = { backgroundColor: bg, color: text, fontSize: size + 'px' }
})
```

#### Change Tracking

```javascript
// ✅ Good: Let Studio handle change tracking
watchEffect(() => {
  hasChanges.value = studio.isDirty.value
})

// ❌ Bad: Manual change tracking
const hasChanges = ref(false)
watch(someValue, () => {
  hasChanges.value = true
})
```

### 2. Component Structure Best Practices

#### Consistent Page Structure

```vue
<!-- ✅ Good: Consistent structure -->
<template>
  <StudioWrapper>
    <StudioHeader 
      :subtitle="pageTitle"
      :settings-changed="hasChanges"
      :saving="isSaving"
      @save-changes="save"
      @discard-changes="reset"
    />
    <StudioContainer>
      <template #navigation>
        <StudioTree :tree-config="treeConfig" />
      </template>
      <template #content>
        <StudioContent :loading="studio.loading.value">
          <template #sidebar>
            <StudioConfig
              :config="studioConfig"
              :values="studio.values.value"
              @field-update="studio.update"
            />
          </template>
          <template #content>
            <StudioConfigPreview>
              <!-- Preview content -->
            </StudioConfigPreview>
          </template>
        </StudioContent>
      </template>
    </StudioContainer>
  </StudioWrapper>
</template>
```

#### Proper Error Handling

```javascript
// ✅ Good: Comprehensive error handling
const studio = useStudioSettings({
  namespace: "example",
  area: "settings",
  config: studioConfig,
  onError: (error) => {
    // Log error for debugging
    console.error("Studio error:", error)
    
    // Show user-friendly message
    addToast({
      type: "error",
      message: "Failed to save settings. Please try again."
    })
    
    // Report to error tracking service
    reportError(error, {
      context: "studio_settings",
      namespace: "example",
      area: "settings"
    })
  }
})
```

### 3. Configuration Schema Best Practices

#### Clear and Descriptive Labels

```javascript
// ✅ Good: Clear, descriptive configuration
{
  settingKey: "email.header.background_color",
  label: "Header Background Color",
  type: "color",
  value: "#FFFFFF",
  description: "Background color for the email header section",
  validation: {
    rules: ["required"],
    messages: {
      required: "Header background color is required"
    }
  }
}

// ❌ Bad: Unclear configuration
{
  settingKey: "bg",
  label: "BG",
  type: "color",
  value: "#FFF"
}
```

#### Logical Field Grouping

```javascript
// ✅ Good: Logical grouping
const emailConfig = [
  {
    id: "appearance",
    displayName: "Appearance",
    icon: "palette",
    fields: [
      // All appearance-related fields
    ]
  },
  {
    id: "layout",
    displayName: "Layout & Spacing",
    icon: "layout",
    fields: [
      // All layout-related fields
    ]
  },
  {
    id: "content",
    displayName: "Content Settings",
    icon: "file-text",
    fields: [
      // All content-related fields
    ]
  }
]
```

#### Conditional Logic

```javascript
// ✅ Good: Clear conditional logic
{
  settingKey: "logo.width",
  label: "Logo Width",
  type: "input",
  inputType: "number",
  value: 150,
  suffix: "px",
  showIf: (values) => values["logo.enabled"] === true,
  validation: {
    rules: ["required", "min:50", "max:500"],
    messages: {
      required: "Logo width is required when logo is enabled",
      min: "Logo width must be at least 50px",
      max: "Logo width cannot exceed 500px"
    }
  }
}
```

### 4. Performance Best Practices

#### Efficient Image Handling

```javascript
// ✅ Good: Optimized image loading
const { getPreview } = useLoadImage()
const logoField = computed(() => studio.values.value["logo.image"])
const { blob: logoBlob, loading: logoLoading } = getPreview(logoField)

// Use loading states
const logoStyle = computed(() => ({
  opacity: logoLoading.value ? 0.5 : 1,
  transition: "opacity 0.2s ease"
}))
```

#### Debounced Updates

```javascript
// ✅ Good: Debounced validation
const debouncedValidation = debounce(async () => {
  const validation = await validateSettings(studio.values.value)
  validationErrors.value = validation.errors
}, 500)

watch(() => studio.values.value, debouncedValidation, { deep: true })
```

#### Memory Management

```javascript
// ✅ Good: Proper cleanup
onBeforeUnmount(() => {
  // Clear timers
  if (autoSaveTimer) {
    clearTimeout(autoSaveTimer)
  }
  
  // Revoke object URLs
  if (imageUrls.length > 0) {
    imageUrls.forEach(url => URL.revokeObjectURL(url))
  }
  
  // Cancel pending requests
  if (pendingRequest) {
    pendingRequest.abort()
  }
})
```

### 5. User Experience Best Practices

#### Loading States

```vue
<!-- ✅ Good: Clear loading states -->
<template>
  <StudioContent :loading="studio.loading.value">
    <template #sidebar>
      <StudioConfig
        :config="studioConfig"
        :values="studio.values.value"
        :disabled="studio.saving.value"
        @field-update="studio.update"
      />
    </template>
    <template #content>
      <StudioConfigPreview>
        <div v-if="studio.loading.value" class="preview-loading">
          <div class="spinner"></div>
          <p>Loading preview...</p>
        </div>
        <YourPreviewComponent v-else v-bind="previewProps" />
      </StudioConfigPreview>
    </template>
  </StudioContent>
</template>
```

#### Helpful Validation Messages

```javascript
// ✅ Good: Helpful validation messages
const validation = {
  rules: ["required", "email", "unique:users,email"],
  messages: {
    required: "Email address is required for notifications",
    email: "Please enter a valid email address (e.g., user@example.com)",
    unique: "This email address is already in use. Please choose another."
  }
}
```

#### Accessibility

```vue
<!-- ✅ Good: Accessible components -->
<template>
  <div class="studio-field" :class="{ 'has-error': hasError }">
    <label 
      :for="fieldId" 
      class="field-label"
      :class="{ required: field.required }"
    >
      {{ field.label }}
      <span v-if="field.required" class="required-indicator" aria-label="required">*</span>
    </label>
    
    <input
      :id="fieldId"
      v-model="value"
      :type="field.inputType || 'text'"
      :required="field.required"
      :aria-describedby="hasError ? `${fieldId}-error` : undefined"
      :aria-invalid="hasError"
      class="field-input"
    />
    
    <div v-if="field.description" class="field-description">
      {{ field.description }}
    </div>
    
    <div v-if="hasError" :id="`${fieldId}-error`" class="field-error" role="alert">
      {{ errorMessage }}
    </div>
  </div>
</template>
```

### 6. Code Organization Best Practices

#### File Structure

```
pages/studio/
├── emails/
│   ├── index.vue           # Main email templates page
│   ├── quotation.vue       # Specific template
│   └── invoice.vue         # Specific template
├── pdf/
│   ├── index.vue           # Main PDF templates page
│   ├── invoice.vue         # Invoice PDF template
│   └── quotation.vue       # Quotation PDF template
└── themes.vue              # Theme settings
```

#### Composable Organization

```javascript
// composables/studio/useEmailTemplate.js
export const useEmailTemplate = (type) => {
  const studio = useStudioSettings({
    namespace: "emails",
    area: type,
    config: getEmailConfig(type)
  })
  
  // Email-specific logic
  const sendTestEmail = async () => {
    // Implementation
  }
  
  const previewEmail = () => {
    // Implementation
  }
  
  return {
    ...studio,
    sendTestEmail,
    previewEmail
  }
}
```

#### Configuration Management

```javascript
// config/studio/email-templates.js
export const getEmailConfig = (type) => {
  const baseConfig = getBaseEmailConfig()
  const specificConfig = getEmailTypeConfig(type)
  
  return mergeConfigs(baseConfig, specificConfig)
}

const getBaseEmailConfig = () => [
  // Common email fields
]

const getEmailTypeConfig = (type) => {
  switch (type) {
    case "quotation":
      return getQuotationEmailConfig()
    case "invoice":
      return getInvoiceEmailConfig()
    default:
      return []
  }
}
```

## Testing

### Unit Testing Studio Components

```javascript
// tests/components/StudioConfig.test.js
import { mount } from "@vue/test-utils"
import { describe, it, expect, vi } from "vitest"
import StudioConfig from "~/components/studio/StudioConfig.vue"

describe("StudioConfig", () => {
  const mockConfig = [
    {
      id: "general",
      displayName: "General Settings",
      fields: [
        {
          settingKey: "test_field",
          label: "Test Field",
          type: "input",
          value: "default value"
        }
      ]
    }
  ]
  
  const mockValues = {
    test_field: "current value"
  }
  
  it("renders configuration fields correctly", () => {
    const wrapper = mount(StudioConfig, {
      props: {
        config: mockConfig,
        values: mockValues
      }
    })
    
    expect(wrapper.find(".studio-config-section").exists()).toBe(true)
    expect(wrapper.find("input[type='text']").element.value).toBe("current value")
  })
  
  it("emits field-update when input changes", async () => {
    const wrapper = mount(StudioConfig, {
      props: {
        config: mockConfig,
        values: mockValues
      }
    })
    
    const input = wrapper.find("input[type='text']")
    await input.setValue("new value")
    
    expect(wrapper.emitted("field-update")).toHaveLength(1)
    expect(wrapper.emitted("field-update")[0]).toEqual(["test_field", "new value"])
  })
  
  it("handles conditional field display", async () => {
    const conditionalConfig = [
      {
        id: "conditional",
        displayName: "Conditional Test",
        fields: [
          {
            settingKey: "enable_feature",
            label: "Enable Feature",
            type: "checkbox",
            value: false
          },
          {
            settingKey: "feature_setting",
            label: "Feature Setting",
            type: "input",
            value: "",
            showIf: (values) => values.enable_feature === true
          }
        ]
      }
    ]
    
    const wrapper = mount(StudioConfig, {
      props: {
        config: conditionalConfig,
        values: { enable_feature: false, feature_setting: "" }
      }
    })
    
    // Feature setting should be hidden
    expect(wrapper.find("[data-field='feature_setting']").exists()).toBe(false)
    
    // Enable the checkbox
    await wrapper.setProps({
      values: { enable_feature: true, feature_setting: "" }
    })
    
    // Feature setting should now be visible
    expect(wrapper.find("[data-field='feature_setting']").exists()).toBe(true)
  })
})
```

### Testing Studio Composables

```javascript
// tests/composables/useStudioSettings.test.js
import { describe, it, expect, vi, beforeEach } from "vitest"
import { useStudioSettings } from "~/composables/useStudioSettings"

// Mock the API
const mockApi = {
  get: vi.fn(),
  post: vi.fn()
}

vi.mock("~/composables/useApi", () => ({
  useApi: () => mockApi
}))

describe("useStudioSettings", () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })
  
  const mockConfig = [
    {
      id: "test",
      fields: [
        {
          settingKey: "test_setting",
          type: "input",
          value: "default"
        }
      ]
    }
  ]
  
  it("loads settings from API", async () => {
    mockApi.get.mockResolvedValue({
      data: {
        test_setting: "loaded value"
      }
    })
    
    const studio = useStudioSettings({
      namespace: "test",
      area: "settings",
      config: mockConfig
    })
    
    await studio.load()
    
    expect(mockApi.get).toHaveBeenCalledWith("/api/studio/settings", {
      params: {
        namespace: "test",
        area: "settings"
      }
    })
    
    expect(studio.getValue("test_setting").value).toBe("loaded value")
  })
  
  it("tracks changes correctly", () => {
    const studio = useStudioSettings({
      namespace: "test",
      area: "settings", 
      config: mockConfig
    })
    
    expect(studio.isDirty.value).toBe(false)
    
    studio.update("test_setting", "new value")
    
    expect(studio.isDirty.value).toBe(true)
    expect(studio.changedFields.value).toContain("test_setting")
  })
  
  it("saves changes to API", async () => {
    mockApi.post.mockResolvedValue({ success: true })
    
    const studio = useStudioSettings({
      namespace: "test",
      area: "settings",
      config: mockConfig
    })
    
    studio.update("test_setting", "new value")
    await studio.save()
    
    expect(mockApi.post).toHaveBeenCalledWith("/api/studio/settings", {
      namespace: "test",
      area: "settings",
      settings: {
        test_setting: "new value"
      }
    })
    
    expect(studio.isDirty.value).toBe(false)
  })
  
  it("resets to original values", () => {
    const studio = useStudioSettings({
      namespace: "test",
      area: "settings",
      config: mockConfig
    })
    
    // Set initial value
    studio.values.value = { test_setting: "original" }
    studio.originalValues.value = { test_setting: "original" }
    
    // Make changes
    studio.update("test_setting", "changed")
    expect(studio.getValue("test_setting").value).toBe("changed")
    
    // Reset
    studio.reset()
    expect(studio.getValue("test_setting").value).toBe("original")
    expect(studio.isDirty.value).toBe(false)
  })
})
```

### Integration Testing

```javascript
// tests/integration/studio-pages.test.js
import { mount } from "@vue/test-utils"
import { describe, it, expect, vi } from "vitest"
import { createTestingPinia } from "@pinia/testing"
import EmailTemplatesPage from "~/pages/studio/emails.vue"

describe("Studio Email Templates Page", () => {
  const createWrapper = (props = {}) => {
    return mount(EmailTemplatesPage, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })],
        stubs: {
          StudioWrapper: true,
          StudioHeader: true,
          StudioContainer: true,
          StudioContent: true,
          StudioConfig: true,
          StudioConfigPreview: true
        }
      },
      props
    })
  }
  
  it("loads email template settings on mount", async () => {
    const mockLoad = vi.fn().mockResolvedValue()
    
    // Mock the composable
    vi.mock("~/composables/useStudioSettings", () => ({
      useStudioSettings: () => ({
        load: mockLoad,
        loading: ref(false),
        isDirty: ref(false),
        values: ref({}),
        update: vi.fn(),
        save: vi.fn(),
        reset: vi.fn()
      })
    }))
    
    const wrapper = createWrapper()
    
    await wrapper.vm.$nextTick()
    
    expect(mockLoad).toHaveBeenCalled()
  })
  
  it("enables save button when changes are made", async () => {
    const isDirty = ref(false)
    
    vi.mock("~/composables/useStudioSettings", () => ({
      useStudioSettings: () => ({
        load: vi.fn(),
        loading: ref(false),
        isDirty,
        values: ref({}),
        update: vi.fn(),
        save: vi.fn(),
        reset: vi.fn()
      })
    }))
    
    const wrapper = createWrapper()
    
    // Initially no changes
    expect(wrapper.vm.hasChanges).toBe(false)
    
    // Simulate changes
    isDirty.value = true
    await wrapper.vm.$nextTick()
    
    expect(wrapper.vm.hasChanges).toBe(true)
  })
})
```

### End-to-End Testing

```javascript
// tests/e2e/studio-workflow.spec.js
import { test, expect } from "@playwright/test"

test.describe("Studio Workflow", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/manage/studio")
  })
  
  test("can navigate to email templates and make changes", async ({ page }) => {
    // Navigate to email templates
    await page.click("text=Email Templates")
    await page.click("text=Quotation")
    
    // Wait for page to load
    await expect(page.locator(".studio-content")).toBeVisible()
    
    // Change a color setting
    await page.click("[data-field='theme.primary_color']")
    await page.fill("[data-field='theme.primary_color'] input", "#FF0000")
    
    // Check that save button is enabled
    await expect(page.locator(".save-button")).not.toBeDisabled()
    
    // Preview should update
    await expect(page.locator(".email-preview")).toBeVisible()
    
    // Save changes
    await page.click(".save-button")
    
    // Should show success message
    await expect(page.locator(".toast-success")).toBeVisible()
    
    // Save button should be disabled again
    await expect(page.locator(".save-button")).toBeDisabled()
  })
  
  test("can discard changes", async ({ page }) => {
    await page.click("text=Email Templates")
    await page.click("text=Quotation")
    
    // Make a change
    await page.fill("[data-field='theme.primary_color'] input", "#FF0000")
    await expect(page.locator(".save-button")).not.toBeDisabled()
    
    // Discard changes
    await page.click(".discard-button")
    
    // Should show confirmation dialog
    await expect(page.locator(".confirm-dialog")).toBeVisible()
    await page.click("text=Discard Changes")
    
    // Changes should be reverted
    await expect(page.locator(".save-button")).toBeDisabled()
  })
  
  test("can use magic tags in editor", async ({ page }) => {
    await page.goto("/manage/studio/emails")
    
    // Open editor
    await page.click(".editor-trigger")
    
    // Wait for EditorJS to load
    await expect(page.locator(".codex-editor")).toBeVisible()
    
    // Click in editor
    await page.click(".ce-paragraph")
    
    // Type content with magic tag
    await page.type(".ce-paragraph", "Hello [[%customer.name]]!")
    
    // Check that magic tag is rendered correctly
    await expect(page.locator(".magic-tag")).toBeVisible()
    await expect(page.locator(".magic-tag")).toHaveText("[[%customer.name]]")
  })
  
  test("pdf preview scales correctly", async ({ page }) => {
    await page.goto("/manage/studio/invoice")
    
    // Wait for PDF preview to load
    await expect(page.locator(".pdf-preview")).toBeVisible()
    
    // Check initial scale
    const initialScale = await page.locator(".zoom-display").textContent()
    expect(initialScale).toContain("%")
    
    // Zoom in
    await page.click(".zoom-in-button")
    
    // Check that scale increased
    const newScale = await page.locator(".zoom-display").textContent()
    expect(newScale).not.toBe(initialScale)
    
    // Test fit to width
    await page.click(".fit-width-button")
    
    // Preview should fit container width
    await expect(page.locator(".pdf-document")).toBeVisible()
  })
})
```

### Performance Testing

```javascript
// tests/performance/studio-performance.test.js
import { describe, it, expect } from "vitest"
import { measurePerformance } from "~/tests/utils/performance"

describe("Studio Performance", () => {
  it("loads large configuration quickly", async () => {
    const largeConfig = generateLargeConfig(1000) // 1000 fields
    
    const { duration } = await measurePerformance(async () => {
      const studio = useStudioSettings({
        namespace: "performance_test",
        area: "large_config",
        config: largeConfig
      })
      
      await studio.load()
    })
    
    // Should load within 2 seconds
    expect(duration).toBeLessThan(2000)
  })
  
  it("handles rapid field updates efficiently", async () => {
    const studio = useStudioSettings({
      namespace: "performance_test",
      area: "rapid_updates",
      config: basicConfig
    })
    
    const { duration } = await measurePerformance(async () => {
      // Simulate rapid typing
      for (let i = 0; i < 100; i++) {
        studio.update("text_field", `value_${i}`)
        await nextTick()
      }
    })
    
    // Should handle 100 updates within 1 second
    expect(duration).toBeLessThan(1000)
  })
})
```

## Troubleshooting

### Common Issues and Solutions

#### 1. Changes Not Being Tracked

**Problem**: The save button doesn't enable when making changes.

**Symptoms:**
- `studio.isDirty.value` remains `false` after field updates
- Save button stays disabled
- No change indicators visible

**Solutions:**

```javascript
// ✅ Correct: Use studio's update method
studio.update("setting_key", value)

// ❌ Incorrect: Direct mutation
studio.values.value.setting_key = value

// ✅ Check if studio is properly initialized
if (!studio.isInitialized.value) {
  console.warn("Studio not initialized, call studio.load() first")
}

// ✅ Verify field key exists in config
const fieldExists = studio.config.value.some(section => 
  section.fields.some(field => field.settingKey === "setting_key")
)
if (!fieldExists) {
  console.error("Field not found in configuration:", "setting_key")
}
```

#### 2. Settings Not Loading

**Problem**: Settings show default values instead of saved values.

**Symptoms:**
- All fields show default values from config
- `studio.loading.value` is `false` but no data loaded
- API calls not being made

**Solutions:**

```javascript
// ✅ Ensure load() is called on mount
onMounted(async () => {
  try {
    await studio.load()
  } catch (error) {
    console.error("Failed to load settings:", error)
  }
})

// ✅ Check namespace and area configuration
const studio = useStudioSettings({
  namespace: "correct_namespace", // Must match backend
  area: "correct_area",          // Must match backend
  config: studioConfig
})

// ✅ Verify API endpoint
// Check network tab for calls to /api/studio/settings
// Ensure 200 status and correct response format

// ✅ Check for loading errors
watch(() => studio.error.value, (error) => {
  if (error) {
    console.error("Studio loading error:", error)
    addToast({
      type: "error",
      message: "Failed to load settings"
    })
  }
})
```

#### 3. Images Not Displaying

**Problem**: Uploaded images don't show in preview.

**Symptoms:**
- Image field shows upload succeeded
- Preview shows placeholder or broken image
- `logoBlob` is `null` or `undefined`

**Solutions:**

```javascript
// ✅ Use the getPreview composable correctly
const { getPreview } = useLoadImage()
const logoField = computed(() => studio.values.value["logo.image"])
const { blob: logoBlob, loading: logoLoading, error: logoError } = getPreview(logoField)

// ✅ Check for loading errors
watch(logoError, (error) => {
  if (error) {
    console.error("Image loading error:", error)
  }
})

// ✅ Verify image field configuration
{
  settingKey: "logo.image",
  type: "image",
  value: null,
  withFetch: true,  // Important: enables blob fetching
  accept: "image/*",
  maxSize: 5242880  // 5MB limit
}

// ✅ Handle different image value types
const logoSrc = computed(() => {
  if (logoBlob.value) {
    return logoBlob.value // Blob URL
  } else if (logoField.value?.url) {
    return logoField.value.url // Direct URL
  } else if (typeof logoField.value === "string") {
    return logoField.value // String URL
  }
  return null
})
```

#### 4. Save Fails Silently

**Problem**: Save completes but changes aren't persisted.

**Symptoms:**
- Save button becomes disabled after click
- No error messages shown
- Changes revert after page reload

**Solutions:**

```javascript
// ✅ Add comprehensive error handling
const studio = useStudioSettings({
  namespace: "your_namespace",
  area: "your_area",
  config: studioConfig,
  onError: (error) => {
    console.error("Studio error:", error)
    
    // Check specific error types
    if (error.status === 403) {
      addToast({
        type: "error",
        message: "You don't have permission to save these settings"
      })
    } else if (error.status === 422) {
      addToast({
        type: "error", 
        message: "Invalid data provided. Please check your inputs."
      })
    } else {
      addToast({
        type: "error",
        message: "Failed to save settings. Please try again."
      })
    }
  }
})

// ✅ Validate before saving
const save = async () => {
  // Client-side validation
  const validation = await validateSettings(studio.values.value)
  if (!validation.valid) {
    addToast({
      type: "error",
      message: "Please fix validation errors before saving"
    })
    return
  }
  
  try {
    await studio.save()
    addToast({
      type: "success",
      message: "Settings saved successfully"
    })
  } catch (error) {
    // Error already handled by onError callback
  }
}
```

#### 5. EditorJS Not Initializing

**Problem**: EditorJS fails to load or initialize.

**Symptoms:**
- Empty editor container
- Console errors about EditorJS
- Editor tools not loading

**Solutions:**

```javascript
// ✅ Ensure proper async loading
const initializeEditor = async () => {
  try {
    // Dynamic import to ensure module is loaded
    const { default: EditorJS } = await import("@editorjs/editorjs")
    const { default: Header } = await import("@editorjs/header")
    const { default: Paragraph } = await import("@editorjs/paragraph")
    
    const editor = new EditorJS({
      holder: "editor-container",
      tools: {
        header: Header,
        paragraph: Paragraph
      },
      onReady: () => {
        console.log("EditorJS is ready")
      }
    })
    
    await editor.isReady
    return editor
  } catch (error) {
    console.error("EditorJS initialization failed:", error)
    throw error
  }
}

// ✅ Check DOM element exists
onMounted(async () => {
  await nextTick() // Ensure DOM is rendered
  
  const container = document.getElementById("editor-container")
  if (!container) {
    console.error("Editor container not found")
    return
  }
  
  await initializeEditor()
})

// ✅ Handle tool loading errors
const editorConfig = {
  holder: "editor-container",
  tools: {
    paragraph: {
      class: Paragraph,
      inlineToolbar: true
    }
  },
  onReady: () => {
    console.log("Editor ready")
  },
  onChange: (api, event) => {
    console.log("Editor changed", event)
  }
}
```

#### 6. Magic Tags Not Working

**Problem**: Magic tags don't render or function correctly.

**Symptoms:**
- Tags appear as plain text
- Tag dropdown doesn't show
- Tags not being converted to spans

**Solutions:**

```javascript
// ✅ Verify tag configuration
const availableTags = [
  {
    name: "[[%customer.name]]",      // Correct format
    display: "Customer Name",
    description: "Customer's full name",
    category: "customer"
  }
]

// ✅ Check MagicTagInlineTool registration
const editorTools = {
  magicTag: {
    class: MagicTagInlineTool,
    config: {
      tags: availableTags,
      onInsert: (tag) => {
        console.log("Tag inserted:", tag)
      }
    }
  }
}

// ✅ Verify tag processor
const tagProcessor = new MagicTagProcessor({
  tags: availableTags,
  caseSensitive: true
})

// Test tag processing
const testText = "Hello [[%customer.name]]!"
const processed = tagProcessor.convertTagsToSpans(testText)
console.log("Processed text:", processed)
```

#### 7. PDF Preview Not Scaling

**Problem**: PDF preview doesn't fit container or scale properly.

**Symptoms:**
- PDF appears too large or too small
- Scroll bars appear unexpectedly
- Zoom controls don't work

**Solutions:**

```javascript
// ✅ Implement proper scaling composable
const { scaleStyle, initializeScaling } = useElementScaling({
  containerRef: container,
  headerRef: header,
  targetWidth: 794,    // A4 width in pixels
  targetHeight: 1123,  // A4 height in pixels
  runTwice: true      // Fix for timing issues
})

// ✅ Initialize scaling after mount
onMounted(async () => {
  await nextTick()
  initializeScaling()
  
  // Re-initialize on window resize
  window.addEventListener("resize", initializeScaling)
})

// ✅ Clean up event listeners
onBeforeUnmount(() => {
  window.removeEventListener("resize", initializeScaling)
})

// ✅ Check container dimensions
const checkContainer = () => {
  if (!container.value) {
    console.error("PDF container not found")
    return
  }
  
  const rect = container.value.getBoundingClientRect()
  console.log("Container dimensions:", rect.width, "x", rect.height)
}
```

### Debugging Techniques

#### 1. Enable Debug Mode

```javascript
// Enable detailed logging
const studio = useStudioSettings({
  namespace: "debug_test",
  area: "settings",
  config: studioConfig,
  debug: true,  // Enables console logging
  onSuccess: (data) => {
    console.log("Save successful:", data)
  },
  onError: (error) => {
    console.error("Save failed:", error)
  }
})
```

#### 2. Inspector Tools

```javascript
// Add to any Studio page for debugging
if (process.env.NODE_ENV === "development") {
  // Expose studio instance to window for debugging
  window.studioDebug = {
    studio,
    values: studio.values,
    isDirty: studio.isDirty,
    changedFields: studio.changedFields
  }
}

// In browser console:
// studioDebug.studio.getValue("setting_key")
// studioDebug.values.value
// studioDebug.changedFields.value
```

#### 3. Network Debugging

```javascript
// Log all API calls
const apiInterceptor = {
  request: (config) => {
    console.log("API Request:", config.method?.toUpperCase(), config.url, config.data)
    return config
  },
  response: (response) => {
    console.log("API Response:", response.status, response.data)
    return response
  },
  error: (error) => {
    console.error("API Error:", error.response?.status, error.response?.data)
    return Promise.reject(error)
  }
}
```

#### 4. State Inspection

```vue
<!-- Debug panel component -->
<template>
  <div v-if="showDebug" class="debug-panel">
    <h3>Studio Debug Info</h3>
    
    <div class="debug-section">
      <h4>State</h4>
      <ul>
        <li>Loading: {{ studio.loading.value }}</li>
        <li>Saving: {{ studio.saving.value }}</li>
        <li>Dirty: {{ studio.isDirty.value }}</li>
        <li>Error: {{ studio.error.value }}</li>
      </ul>
    </div>
    
    <div class="debug-section">
      <h4>Changed Fields</h4>
      <ul>
        <li v-for="field in studio.changedFields.value" :key="field">
          {{ field }}: {{ studio.getValue(field).value }}
        </li>
      </ul>
    </div>
    
    <div class="debug-section">
      <h4>All Values</h4>
      <pre>{{ JSON.stringify(studio.values.value, null, 2) }}</pre>
    </div>
  </div>
</template>

<script setup>
const showDebug = ref(process.env.NODE_ENV === "development")
</script>
```

### Getting Help

If you encounter issues not covered here:

1. **Check Browser Console**: Look for JavaScript errors or warnings
2. **Network Tab**: Verify API calls are being made with correct data
3. **Vue DevTools**: Inspect component state and props
4. **Test in Isolation**: Create a minimal reproduction case
5. **Check Documentation**: Review this documentation for similar examples
6. **Ask for Help**: Reach out to senior developers with specific error messages and steps to reproduce

---

## Conclusion

The Prindustry Studio 2.0 is a comprehensive and powerful system for customizing various aspects of your application. This documentation has covered everything from basic setup to advanced features, providing you with the knowledge and tools needed to create effective Studio pages and extend the system's functionality.

### Key Takeaways

1. **Use Studio Composables**: Always leverage `useStudioSettings` and `useStudioLexicons` for state management
2. **Follow Established Patterns**: Stick to the proven component structure and configuration schemas
3. **Test Thoroughly**: Write unit tests, integration tests, and E2E tests for your Studio features
4. **Handle Errors Gracefully**: Implement comprehensive error handling and user feedback
5. **Optimize Performance**: Use efficient image handling, debounced updates, and proper memory management
6. **Maintain Accessibility**: Ensure your Studio pages are accessible to all users
7. **Document Extensions**: Document any new features or patterns you create

### Best Practices Summary

- **Configuration**: Use clear, descriptive labels and logical field grouping
- **State Management**: Let Studio composables handle state and change tracking
- **Error Handling**: Provide meaningful error messages and recovery options
- **Performance**: Optimize image loading, use debounced updates, and clean up resources
- **Testing**: Maintain good test coverage for reliability
- **User Experience**: Provide loading states, helpful validation, and intuitive interfaces

### Future Enhancements

The Studio system is designed to be extensible. Consider these areas for future development:

- **Custom Field Types**: Create specialized field components for specific use cases
- **Advanced Validation**: Implement more sophisticated validation rules and cross-field validation
- **Real-time Collaboration**: Add multi-user editing capabilities
- **Version Control**: Implement settings versioning and rollback functionality
- **Import/Export**: Add more comprehensive import/export features
- **Performance Monitoring**: Add analytics and performance tracking
- **Accessibility Improvements**: Continue enhancing accessibility features

Remember that the Studio is a living system that will evolve with your application's needs. By following the patterns and best practices outlined in this documentation, you'll be able to create maintainable, efficient, and user-friendly customization interfaces that empower your users to make the application truly their own.

Happy coding! 🚀

### Advanced Page Features

#### Real-time Collaboration

```javascript
// Enable real-time updates for collaborative editing
const useCollaborativeStudio = (options) => {
  const studio = useStudioSettings(options)
  const { $socket } = useNuxtApp()
  
  // Listen for changes from other users
  $socket.on(`studio:${options.namespace}:${options.area}:updated`, (data) => {
    if (data.userId !== getCurrentUserId()) {
      // Show notification about external changes
      addToast({
        type: "info",
        message: `${data.userName} made changes to this page`,
        action: {
          label: "Refresh",
          handler: () => studio.load()
        }
      })
    }
  })
  
  // Broadcast changes to other users
  const originalUpdate = studio.update
  studio.update = (key, value) => {
    originalUpdate(key, value)
    
    // Debounced broadcast
    debouncedBroadcast({
      namespace: options.namespace,
      area: options.area,
      field: key,
      value: value,
      userId: getCurrentUserId(),
      userName: getCurrentUserName()
    })
  }
  
  return studio
}

// Usage
const studio = useCollaborativeStudio({
  namespace: "newsletters",
  area: "templates",
  config: newsletterConfig
})
```

#### Advanced Validation

```javascript
// Cross-field validation with async checks
const useAdvancedValidation = (studio) => {
  const validationState = ref({
    isValidating: false,
    errors: {},
    warnings: {}
  })
  
  // Async validation rules
  const asyncValidators = {
    "email.domain": async (value) => {
      if (!value) return { valid: true }
      
      try {
        const response = await $fetch(`/api/validate-domain/${value}`)
        return {
          valid: response.valid,
          message: response.valid ? null : "Domain is not reachable"
        }
      } catch (error) {
        return {
          valid: false,
          message: "Unable to validate domain"
        }
      }
    },
    
    "template.name": async (value) => {
      if (!value) return { valid: true }
      
      const exists = await $fetch(`/api/templates/exists?name=${encodeURIComponent(value)}`)
      return {
        valid: !exists,
        message: exists ? "Template name already exists" : null
      }
    }
  }
  
  // Run validation
  const validate = async (values = studio.values.value) => {
    validationState.value.isValidating = true
    const errors = {}
    const warnings = {}
    
    // Run async validations
    const validationPromises = Object.entries(asyncValidators).map(async ([field, validator]) => {
      try {
        const result = await validator(values[field])
        if (!result.valid) {
          errors[field] = result.message
        }
      } catch (error) {
        warnings[field] = "Validation failed - please check manually"
      }
    })
    
    await Promise.all(validationPromises)
    
    validationState.value.errors = errors
    validationState.value.warnings = warnings
    validationState.value.isValidating = false
    
    return {
      valid: Object.keys(errors).length === 0,
      errors,
      warnings
    }
  }
  
  // Auto-validate on field changes
  let validationTimeout
  watch(() => studio.values.value, () => {
    clearTimeout(validationTimeout)
    validationTimeout = setTimeout(() => {
      validate()
    }, 1000)
  }, { deep: true })
  
  return {
    validationState: readonly(validationState),
    validate
  }
}
```

#### Import/Export Functionality

```javascript
// Add import/export capabilities to any Studio page
const useStudioImportExport = (studio, options = {}) => {
  const isExporting = ref(false)
  const isImporting = ref(false)
  
  // Export current settings
  const exportSettings = async () => {
    isExporting.value = true
    
    try {
      const data = {
        namespace: studio.namespace,
        area: studio.area,
        settings: studio.values.value,
        metadata: {
          exportedAt: new Date().toISOString(),
          version: "2.0",
          user: getCurrentUserName()
        }
      }
      
      const blob = new Blob([JSON.stringify(data, null, 2)], {
        type: "application/json"
      })
      
      const url = URL.createObjectURL(blob)
      const link = document.createElement("a")
      link.href = url
      link.download = `${studio.namespace}-${studio.area}-settings.json`
      link.click()
      
      URL.revokeObjectURL(url)
      
      addToast({
        type: "success",
        message: "Settings exported successfully"
      })
    } catch (error) {
      addToast({
        type: "error",
        message: "Failed to export settings"
      })
    } finally {
      isExporting.value = false
    }
  }
  
  // Import settings from file
  const importSettings = async (file) => {
    isImporting.value = true
    
    try {
      const text = await file.text()
      const data = JSON.parse(text)
      
      // Validate import data
      if (!data.settings || data.namespace !== studio.namespace) {
        throw new Error("Invalid settings file")
      }
      
      // Confirm import
      const confirmed = await confirmDialog({
        title: "Import Settings",
        message: "This will replace all current settings. Continue?",
        confirmText: "Import",
        cancelText: "Cancel"
      })
      
      if (!confirmed) return
      
      // Apply imported settings
      Object.entries(data.settings).forEach(([key, value]) => {
        studio.update(key, value)
      })
      
      addToast({
        type: "success",
        message: "Settings imported successfully"
      })
    } catch (error) {
      addToast({
        type: "error",
        message: "Failed to import settings: " + error.message
      })
    } finally {
      isImporting.value = false
    }
  }
  
  // File input handler
  const handleFileImport = (event) => {
    const file = event.target.files[0]
    if (file) {
      importSettings(file)
    }
  }
  
  return {
    isExporting,
    isImporting,
    exportSettings,
    importSettings,
    handleFileImport
  }
}

// Usage in component
const { exportSettings, importSettings, handleFileImport } = useStudioImportExport(studio)
```

#### Custom Field Types

```javascript
// Create custom field types for specific use cases
const customFieldTypes = {
  // Color palette field
  colorPalette: {
    component: defineComponent({
      props: {
        modelValue: Array,
        max: { type: Number, default: 5 }
      },
      setup(props, { emit }) {
        const colors = ref(props.modelValue || [])
        
        const addColor = (color) => {
          if (colors.value.length < props.max) {
            colors.value.push(color)
            emit("update:modelValue", colors.value)
          }
        }
        
        const removeColor = (index) => {
          colors.value.splice(index, 1)
          emit("update:modelValue", colors.value)
        }
        
        return { colors, addColor, removeColor }
      },
      template: `
        <div class="color-palette-field">
          <div class="color-list">
            <div 
              v-for="(color, index) in colors" 
              :key="index"
              class="color-item"
              :style="{ backgroundColor: color }"
              @click="removeColor(index)"
            >
              <span class="remove-icon">×</span>
            </div>
          </div>
          <ColorPicker @color-selected="addColor" />
        </div>
      `
    })
  },
  
  // Font selector with preview
  fontSelector: {
    component: defineComponent({
      props: {
        modelValue: String,
        category: { type: String, default: "all" }
      },
      setup(props, { emit }) {
        const availableFonts = ref([
          { name: "Arial", family: "Arial, sans-serif", category: "sans-serif" },
          { name: "Times New Roman", family: "Times New Roman, serif", category: "serif" },
          { name: "Courier New", family: "Courier New, monospace", category: "monospace" }
        ])
        
        const filteredFonts = computed(() => {
          if (props.category === "all") return availableFonts.value
          return availableFonts.value.filter(font => font.category === props.category)
        })
        
        return {
          filteredFonts,
          selectFont: (font) => emit("update:modelValue", font.family)
        }
      },
      template: `
        <div class="font-selector">
          <div 
            v-for="font in filteredFonts"
            :key="font.name"
            class="font-option"
            :class="{ active: modelValue === font.family }"
            :style="{ fontFamily: font.family }"
            @click="selectFont(font)"
          >
            {{ font.name }}
          </div>
        </div>
      `
    })
  }
}

// Register custom field types
const registerCustomFieldTypes = (studio) => {
  Object.entries(customFieldTypes).forEach(([type, definition]) => {
    studio.registerFieldType(type, definition)
  })
}
```

## Email Templates

### Complete Email Configuration

```javascript
// Complete email template configuration schema
const emailTemplateConfig = [
  {
    id: "theme",
    icon: "palette",
    displayName: "Color Theme",
    description: "Configure the overall color scheme",
    fields: [
      {
        settingKey: "theme.primary_color",
        label: "Primary Color",
        type: "color",
        value: "#2563EB",
        description: "Main brand color used for buttons and accents"
      },
      {
        settingKey: "theme.secondary_color",
        label: "Secondary Color", 
        type: "color",
        value: "#64748B",
        description: "Secondary color for less prominent elements"
      },
      {
        settingKey: "theme.background_color",
        label: "Background Color",
        type: "color",
        value: "#FFFFFF",
        description: "Main background color for the email"
      },
      {
        settingKey: "theme.text_color",
        label: "Text Color",
        type: "color",
        value: "#1F2937",
        description: "Primary text color"
      },
      {
        settingKey: "theme.link_color",
        label: "Link Color",
        type: "color",
        value: "#2563EB",
        description: "Color for hyperlinks"
      }
    ]
  },
  
  {
    id: "layout",
    icon: "layout",
    displayName: "Layout Settings",
    fields: [
      {
        settingKey: "layout.width",
        label: "Email Width",
        type: "input",
        inputType: "number",
        value: 600,
        min: 400,
        max: 800,
        suffix: "px",
        description: "Maximum width of the email content"
      },
      {
        settingKey: "layout.alignment",
        label: "Content Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ]
      },
      {
        type: "container",
        label: "Content Padding",
        description: "Spacing around the main content area",
        class: "grid grid-cols-2 gap-4 md:grid-cols-4",
        children: [
          {
            settingKey: "layout.padding.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.right", 
            label: "Right",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.bottom",
            label: "Bottom", 
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          },
          {
            settingKey: "layout.padding.left",
            label: "Left",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "px"
          }
        ]
      }
    ]
  },
  
  {
    id: "header",
    icon: "header",
    displayName: "Header Configuration",
    fields: [
      {
        settingKey: "header.enabled",
        label: "Show Header",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_bottom",
        label: "Bottom Border",
        type: "checkbox",
        value: true,
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_color",
        label: "Border Color",
        type: "color",
        value: "#E5E7EB",
        showIf: (values) => values["header.enabled"] === true && values["header.border_bottom"] === true
      }
    ]
  },
  
  {
    id: "logo",
    icon: "image",
    displayName: "Logo Settings",
    fields: [
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox", 
        value: true
      },
      {
        settingKey: "logo.image",
        label: "Logo Image",
        type: "image",
        value: null,
        accept: "image/*",
        maxSize: 2097152, // 2MB
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.width",
        label: "Logo Width",
        type: "input",
        inputType: "number",
        value: 150,
        min: 50,
        max: 400,
        suffix: "px",
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "header",
        options: [
          { value: "header", label: "Header", description: "Logo in email header" },
          { value: "content", label: "Content", description: "Logo in main content area" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.alignment",
        label: "Logo Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "typography",
    icon: "font",
    displayName: "Typography",
    fields: [
      {
        settingKey: "typography.font_family",
        label: "Font Family",
        type: "fontSelector",
        value: "Arial, sans-serif",
        category: "web-safe"
      },
      {
        settingKey: "typography.font_size",
        label: "Base Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24,
        suffix: "px"
      },
      {
        settingKey: "typography.line_height",
        label: "Line Height",
        type: "input",
        inputType: "number",
        value: 1.5,
        min: 1.0,
        max: 2.0,
        step: 0.1
      },
      {
        settingKey: "typography.heading_font_family",
        label: "Heading Font Family",
        type: "fontSelector",
        value: "Arial, sans-serif",
        category: "web-safe"
      }
    ]
  },
  
  {
    id: "buttons", 
    icon: "mouse-pointer",
    displayName: "Button Styling",
    fields: [
      {
        settingKey: "buttons.style",
        label: "Button Style",
        type: "radio",
        value: "filled",
        options: [
          { value: "filled", label: "Filled", description: "Solid background color" },
          { value: "outlined", label: "Outlined", description: "Border with transparent background" },
          { value: "text", label: "Text Only", description: "No background or border" }
        ]
      },
      {
        settingKey: "buttons.background_color",
        label: "Button Background",
        type: "color",
        value: "#2563EB",
        showIf: (values) => values["buttons.style"] === "filled"
      },
      {
        settingKey: "buttons.text_color",
        label: "Button Text Color",
        type: "color", 
        value: "#FFFFFF"
      },
      {
        settingKey: "buttons.border_color",
        label: "Border Color",
        type: "color",
        value: "#2563EB",
        showIf: (values) => ["outlined", "filled"].includes(values["buttons.style"])
      },
      {
        type: "container",
        label: "Button Padding",
        class: "grid grid-cols-2 gap-4",
        children: [
          {
            settingKey: "buttons.padding.vertical",
            label: "Vertical",
            type: "input",
            inputType: "number",
            value: 12,
            suffix: "px"
          },
          {
            settingKey: "buttons.padding.horizontal",
            label: "Horizontal",
            type: "input", 
            inputType: "number",
            value: 24,
            suffix: "px"
          }
        ]
      },
      {
        type: "container",
        label: "Border Radius",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "buttons.border_radius.top_left",
            label: "TL",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.top_right",
            label: "TR",
            type: "input",
            inputType: "number", 
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.bottom_right",
            label: "BR",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          },
          {
            settingKey: "buttons.border_radius.bottom_left",
            label: "BL",
            type: "input",
            inputType: "number",
            value: 6,
            suffix: "px"
          }
        ]
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.enabled",
        label: "Show Footer",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "footer.background_color",
        label: "Footer Background",
        type: "color",
        value: "#F3F4F6",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.text_color",
        label: "Footer Text Color",
        type: "color",
        value: "#6B7280",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.font_size",
        label: "Footer Font Size",
        type: "input",
        inputType: "number",
        value: 12,
        min: 10,
        max: 16,
        suffix: "px",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.alignment",
        label: "Footer Alignment",
        type: "radio",
        value: "center",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["footer.enabled"] === true
      }
    ]
  }
]
```

### Email Preview Component

```vue
<!-- components/studio/preview/EmailTemplatePreview.vue -->
<template>
  <div class="email-template-preview">
    <div class="email-container" :style="containerStyle">
      <!-- Header -->
      <header v-if="headerEnabled" class="email-header" :style="headerStyle">
        <div v-if="logoEnabled && logoPosition === 'header'" class="logo-container" :style="logoContainerStyle">
          <img v-if="logoBlob" :src="logoBlob" alt="Logo" :style="logoStyle" />
          <div v-else class="logo-placeholder" :style="logoPlaceholderStyle">
            Your Logo
          </div>
        </div>
      </header>
      
      <!-- Main Content -->
      <main class="email-content" :style="contentStyle">
        <!-- Logo in content area -->
        <div v-if="logoEnabled && logoPosition === 'content'" class="logo-container" :style="logoContainerStyle">
          <img v-if="logoBlob" :src="logoBlob" alt="Logo" :style="logoStyle" />
          <div v-else class="logo-placeholder" :style="logoPlaceholderStyle">
            Your Logo
          </div>
        </div>
        
        <!-- Sample Content -->
        <div class="content-section">
          <h1 :style="headingStyle">Sample Email Template</h1>
          <p :style="paragraphStyle">
            This is a preview of how your email template will look. 
            The actual content will be replaced with your email content when sending.
          </p>
          
          <p :style="paragraphStyle">
            You can customize colors, fonts, spacing, and layout using the settings panel.
            Changes are reflected in real-time in this preview.
          </p>
          
          <!-- Sample Button -->
          <div class="button-container" :style="buttonContainerStyle">
            <a href="#" class="email-button" :style="buttonStyle">
              Call to Action
            </a>
          </div>
          
          <p :style="{ ...paragraphStyle, marginTop: '20px' }">
            Links will appear in your configured <a href="#" :style="linkStyle">link color</a> 
            throughout the email content.
          </p>
        </div>
      </main>
      
      <!-- Footer -->
      <footer v-if="footerEnabled" class="email-footer" :style="footerStyle">
        <p :style="footerTextStyle">
          © 2024 Your Company Name. All rights reserved.
        </p>
        <p :style="footerTextStyle">
          You received this email because you subscribed to our newsletter.
          <a href="#" :style="footerLinkStyle">Unsubscribe</a>
        </p>
      </footer>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  // Theme colors
  primaryColor: String,
  secondaryColor: String,
  backgroundColor: String,
  textColor: String,
  linkColor: String,
  
  // Layout
  emailWidth: Number,
  contentAlignment: String,
  contentPadding: Object,
  
  // Header
  headerEnabled: Boolean,
  headerBackgroundColor: String,
  headerBorderBottom: Boolean,
  headerBorderColor: String,
  
  // Logo
  logoEnabled: Boolean,
  logoImage: String,
  logoWidth: Number,
  logoPosition: String,
  logoAlignment: String,
  logoBlob: String,
  
  // Typography
  fontFamily: String,
  fontSize: Number,
  lineHeight: Number,
  headingFontFamily: String,
  
  // Buttons
  buttonStyle: String,
  buttonBackgroundColor: String,
  buttonTextColor: String,
  buttonBorderColor: String,
  buttonPadding: Object,
  buttonBorderRadius: Object,
  
  // Footer
  footerEnabled: Boolean,
  footerBackgroundColor: String,
  footerTextColor: String,
  footerFontSize: Number,
  footerAlignment: String
})

// Computed styles
const containerStyle = computed(() => ({
  width: `${props.emailWidth}px`,
  maxWidth: "100%",
  margin: "0 auto",
  backgroundColor: props.backgroundColor,
  fontFamily: props.fontFamily,
  fontSize: `${props.fontSize}px`,
  lineHeight: props.lineHeight,
  color: props.textColor,
  border: "1px solid #E5E7EB",
  borderRadius: "8px",
  overflow: "hidden"
}))

const headerStyle = computed(() => ({
  backgroundColor: props.headerBackgroundColor,
  borderBottom: props.headerBorderBottom ? `1px solid ${props.headerBorderColor}` : "none",
  padding: "20px"
}))

const logoContainerStyle = computed(() => ({
  textAlign: props.logoAlignment,
  marginBottom: props.logoPosition === "content" ? "20px" : "0"
}))

const logoStyle = computed(() => ({
  width: `${props.logoWidth}px`,
  height: "auto",
  maxWidth: "100%"
}))

const logoPlaceholderStyle = computed(() => ({
  display: "inline-block",
  width: `${props.logoWidth}px`,
  height: "60px",
  backgroundColor: "#F3F4F6",
  border: "2px dashed #D1D5DB",
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  color: "#6B7280",
  fontSize: "14px",
  borderRadius: "4px"
}))

const contentStyle = computed(() => {
  const padding = props.contentPadding || {}
  return {
    padding: `${padding.top || 20}px ${padding.right || 20}px ${padding.bottom || 20}px ${padding.left || 20}px`,
    textAlign: props.contentAlignment
  }
})

const headingStyle = computed(() => ({
  fontFamily: props.headingFontFamily,
  fontSize: `${props.fontSize * 1.5}px`,
  color: props.textColor,
  margin: "0 0 16px 0",
  fontWeight: "bold"
}))

const paragraphStyle = computed(() => ({
  margin: "0 0 16px 0",
  color: props.textColor
}))

const linkStyle = computed(() => ({
  color: props.linkColor,
  textDecoration: "underline"
}))

const buttonContainerStyle = computed(() => ({
  margin: "24px 0",
  textAlign: props.contentAlignment
}))

const buttonStyle = computed(() => {
  const padding = props.buttonPadding || {}
  const borderRadius = props.buttonBorderRadius || {}
  
  let styles = {
    display: "inline-block",
    padding: `${padding.vertical || 12}px ${padding.horizontal || 24}px`,
    borderRadius: `${borderRadius.top_left || 6}px ${borderRadius.top_right || 6}px ${borderRadius.bottom_right || 6}px ${borderRadius.bottom_left || 6}px`,
    textDecoration: "none",
    fontWeight: "bold",
    fontSize: `${props.fontSize}px`,
    cursor: "pointer",
    transition: "all 0.2s ease"
  }
  
  if (props.buttonStyle === "filled") {
    styles.backgroundColor = props.buttonBackgroundColor
    styles.color = props.buttonTextColor
    styles.border = `1px solid ${props.buttonBorderColor}`
  } else if (props.buttonStyle === "outlined") {
    styles.backgroundColor = "transparent"
    styles.color = props.buttonBorderColor
    styles.border = `2px solid ${props.buttonBorderColor}`
  } else if (props.buttonStyle === "text") {
    styles.backgroundColor = "transparent"
    styles.color = props.linkColor
    styles.border = "none"
  }
  
  return styles
})

const footerStyle = computed(() => ({
  backgroundColor: props.footerBackgroundColor,
  padding: "20px",
  textAlign: props.footerAlignment,
  borderTop: "1px solid #E5E7EB"
}))

const footerTextStyle = computed(() => ({
  margin: "0 0 8px 0",
  fontSize: `${props.footerFontSize}px`,
  color: props.footerTextColor,
  lineHeight: 1.4
}))

const footerLinkStyle = computed(() => ({
  color: props.footerTextColor,
  textDecoration: "underline"
}))
</script>

<style scoped>
.email-template-preview {
  padding: 20px;
  background: #F9FAFB;
  min-height: 600px;
}

.content-section {
  max-width: 100%;
}

.email-button:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}
</style>
```

### Using the Email Editor Modal

```vue
<!-- Example usage of StudioEmailModal -->
<template>
  <div>
    <button @click="openEmailEditor" class="btn btn-primary">
      Edit Email Template
    </button>
    
    <StudioEmailModal
      v-if="showEmailModal"
      type="quotation"
      :entity-id="quotationId"
      :initial-subject="emailSubject"
      :initial-content="emailContent"
      :available-tags="quotationTags"
      @on-send="handleEmailSend"
      @on-save="handleEmailSave"
      @on-close="closeEmailModal"
    />
  </div>
</template>

<script setup>
const showEmailModal = ref(false)
const quotationId = ref(123)
const emailSubject = ref("Your Quotation #[[%quotation.id]]")
const emailContent = ref(null)

// Available magic tags for quotation emails
const quotationTags = [
  {
    name: "[[%quotation.id]]",
    display: "Quotation ID",
    description: "Unique quotation identifier",
    category: "quotation"
  },
  {
    name: "[[%quotation.date]]",
    display: "Quotation Date", 
    description: "Date when quotation was created",
    category: "quotation"
  },
  {
    name: "[[%customer.name]]",
    display: "Customer Name",
    description: "Full name of the customer",
    category: "customer"
  },
  {
    name: "[[%customer.email]]",
    display: "Customer Email",
    description: "Customer's email address",
    category: "customer"
  },
  {
    name: "[[%company.name]]",
    display: "Company Name",
    description: "Your company name",
    category: "company"
  }
]

const openEmailEditor = () => {
  showEmailModal.value = true
}

const closeEmailModal = () => {
  showEmailModal.value = false
}

const handleEmailSend = async (emailData) => {
  try {
    await $fetch("/api/emails/send", {
      method: "POST",
      body: {
        type: "quotation",
        entityId: quotationId.value,
        subject: emailData.subject,
        content: emailData.content,
        recipients: emailData.recipients
      }
    })
    
    addToast({
      type: "success",
      message: "Email sent successfully!"
    })
    
    closeEmailModal()
  } catch (error) {
    addToast({
      type: "error",
      message: "Failed to send email"
    })
  }
}

const handleEmailSave = async (emailData) => {
  try {
    await $fetch("/api/templates/save", {
      method: "POST",
      body: {
        type: "quotation",
        subject: emailData.subject,
        content: emailData.content
      }
    })
    
    addToast({
      type: "success",
      message: "Email template saved!"
    })
  } catch (error) {
    addToast({
      type: "error",
      message: "Failed to save template"
    })
  }
}
</script>
```

## PDF Templates

### PDF Template Configuration

```javascript
// Complete PDF template configuration
const pdfTemplateConfig = [
  {
    id: "document",
    icon: "file-pdf",
    displayName: "Document Settings",
    fields: [
      {
        settingKey: "document.page_size",
        label: "Page Size",
        type: "select",
        value: "A4",
        options: [
          { value: "A4", label: "A4 (210 × 297 mm)" },
          { value: "Letter", label: "Letter (8.5 × 11 in)" },
          { value: "Legal", label: "Legal (8.5 × 14 in)" }
        ]
      },
      {
        settingKey: "document.orientation",
        label: "Orientation",
        type: "radio",
        value: "portrait",
        options: [
          { value: "portrait", label: "Portrait", icon: "portrait" },
          { value: "landscape", label: "Landscape", icon: "landscape" }
        ]
      },
      {
        type: "container",
        label: "Page Margins",
        class: "grid grid-cols-4 gap-2",
        children: [
          {
            settingKey: "document.margin.top",
            label: "Top",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.right",
            label: "Right", 
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.bottom",
            label: "Bottom",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          },
          {
            settingKey: "document.margin.left",
            label: "Left",
            type: "input",
            inputType: "number",
            value: 20,
            suffix: "mm"
          }
        ]
      }
    ]
  },
  
  {
    id: "header",
    icon: "header",
    displayName: "Header Configuration",
    fields: [
      {
        settingKey: "header.enabled",
        label: "Show Header",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "header.height",
        label: "Header Height",
        type: "input",
        inputType: "number",
        value: 80,
        suffix: "px",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.background_color",
        label: "Header Background",
        type: "color",
        value: "#FFFFFF",
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_bottom",
        label: "Bottom Border",
        type: "checkbox",
        value: true,
        showIf: (values) => values["header.enabled"] === true
      },
      {
        settingKey: "header.border_color",
        label: "Border Color",
        type: "color",
        value: "#E5E7EB",
        showIf: (values) => values["header.enabled"] === true && values["header.border_bottom"] === true
      }
    ]
  },
  
  {
    id: "logo", 
    icon: "image",
    displayName: "Logo Settings",
    fields: [
      {
        settingKey: "logo.enabled",
        label: "Show Logo",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "logo.image",
        label: "Logo Image",
        type: "image",
        value: null,
        accept: "image/*",
        maxSize: 2097152, // 2MB
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.width",
        label: "Logo Width",
        type: "input",
        inputType: "number",
        value: 120,
        min: 50,
        max: 300,
        suffix: "px",
        showIf: (values) => values["logo.enabled"] === true
      },
      {
        settingKey: "logo.position",
        label: "Logo Position",
        type: "radio",
        value: "header-left",
        options: [
          { value: "header-left", label: "Header Left" },
          { value: "header-center", label: "Header Center" },
          { value: "header-right", label: "Header Right" },
          { value: "content-top", label: "Content Top" }
        ],
        showIf: (values) => values["logo.enabled"] === true
      }
    ]
  },
  
  {
    id: "typography",
    icon: "font",
    displayName: "Typography",
    fields: [
      {
        settingKey: "typography.font_family",
        label: "Font Family",
        type: "select",
        value: "Arial",
        options: [
          { value: "Arial", label: "Arial" },
          { value: "Helvetica", label: "Helvetica" },
          { value: "Times", label: "Times New Roman" },
          { value: "Courier", label: "Courier New" }
        ]
      },
      {
        settingKey: "typography.font_size",
        label: "Base Font Size",
        type: "input",
        inputType: "number",
        value: 12,
        min: 8,
        max: 16,
        suffix: "pt"
      },
      {
        settingKey: "typography.line_height",
        label: "Line Height",
        type: "input",
        inputType: "number",
        value: 1.4,
        min: 1.0,
        max: 2.0,
        step: 0.1
      },
      {
        settingKey: "typography.heading_font_size",
        label: "Heading Font Size",
        type: "input",
        inputType: "number",
        value: 16,
        min: 12,
        max: 24,
        suffix: "pt"
      }
    ]
  },
  
  {
    id: "colors",
    icon: "palette",
    displayName: "Color Scheme",
    fields: [
      {
        settingKey: "colors.primary",
        label: "Primary Color",
        type: "color",
        value: "#2563EB",
        description: "Used for headings and accents"
      },
      {
        settingKey: "colors.secondary",
        label: "Secondary Color",
        type: "color",
        value: "#64748B",
        description: "Used for secondary text and borders"
      },
      {
        settingKey: "colors.text",
        label: "Text Color",
        type: "color",
        value: "#1F2937",
        description: "Main text color"
      },
      {
        settingKey: "colors.background",
        label: "Background Color",
        type: "color",
        value: "#FFFFFF",
        description: "Page background color"
      },
      {
        settingKey: "colors.table_header",
        label: "Table Header Background",
        type: "color",
        value: "#F3F4F6",
        description: "Background color for table headers"
      },
      {
        settingKey: "colors.table_border",
        label: "Table Border Color",
        type: "color",
        value: "#E5E7EB",
        description: "Color for table borders"
      }
    ]
  },
  
  {
    id: "layout",
    icon: "layout",
    displayName: "Layout & Spacing",
    fields: [
      {
        settingKey: "layout.content_width",
        label: "Content Width",
        type: "radio",
        value: "full",
        options: [
          { value: "narrow", label: "Narrow", description: "70% of page width" },
          { value: "normal", label: "Normal", description: "85% of page width" },
          { value: "full", label: "Full", description: "95% of page width" }
        ]
      },
      {
        settingKey: "layout.section_spacing",
        label: "Section Spacing",
        type: "input",
        inputType: "number",
        value: 20,
        min: 10,
        max: 40,
        suffix: "px",
        description: "Space between major sections"
      },
      {
        settingKey: "layout.table_spacing",
        label: "Table Spacing",
        type: "input",
        inputType: "number", 
        value: 15,
        min: 5,
        max: 30,
        suffix: "px",
        description: "Space around tables"
      }
    ]
  },
  
  {
    id: "footer",
    icon: "align-bottom",
    displayName: "Footer Settings",
    fields: [
      {
        settingKey: "footer.enabled",
        label: "Show Footer",
        type: "checkbox",
        value: true
      },
      {
        settingKey: "footer.height",
        label: "Footer Height",
        type: "input",
        inputType: "number",
        value: 60,
        suffix: "px",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.background_color",
        label: "Footer Background",
        type: "color",
        value: "#F9FAFB",
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.show_page_numbers",
        label: "Show Page Numbers",
        type: "checkbox",
        value: true,
        showIf: (values) => values["footer.enabled"] === true
      },
      {
        settingKey: "footer.page_number_position",
        label: "Page Number Position",
        type: "radio",
        value: "right",
        options: [
          { value: "left", label: "Left", icon: "align-left" },
          { value: "center", label: "Center", icon: "align-center" },
          { value: "right", label: "Right", icon: "align-right" }
        ],
        showIf: (values) => values["footer.enabled"] === true && values["footer.show_page_numbers"] === true
      }
    ]
  }
]
```

### PDF Preview with Advanced Scaling

```vue
<!-- Enhanced PDF preview component -->
<template>
  <div ref="container" class="pdf-preview-container">
    <div class="pdf-controls">
      <div class="zoom-controls">
        <button @click="zoomOut" :disabled="zoomLevel <= 0.5" class="zoom-btn">
          <FontAwesome icon="minus" />
        </button>
        <span class="zoom-display">{{ Math.round(zoomLevel * 100) }}%</span>
        <button @click="zoomIn" :disabled="zoomLevel >= 2" class="zoom-btn">
          <FontAwesome icon="plus" />
        </button>
      </div>
      
      <div class="fit-controls">
        <button @click="fitToWidth" class="fit-btn">Fit Width</button>
        <button @click="fitToHeight" class="fit-btn">Fit Height</button>
        <button @click="resetZoom" class="fit-btn">Reset</button>
      </div>
    </div>
    
    <div class="pdf-preview-wrapper" :style="wrapperStyle">
      <StudioEntityPDFInvoice
        ref="pdfComponent"
        :font-size="computedFontSize"
        :font-family="fontFamily"
        :logo="logoBlob"
        :colors="colors"
        :layout="layout"
        :header-config="headerConfig"
        :footer-config="footerConfig"
        :style="pdfStyle"
        class="pdf-document"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  // PDF configuration props
  fontSize: Number,
  fontFamily: String,
  logoBlob: String,
  colors: Object,
  layout: Object,
  headerConfig: Object,
  footerConfig: Object,
  
  // Preview options
  autoScale: { type: Boolean, default: true },
  minZoom: { type: Number, default: 0.5 },
  maxZoom: { type: Number, default: 2.0 }
})

const container = ref(null)
const pdfComponent = ref(null)
const zoomLevel = ref(1)
const containerSize = reactive({ width: 0, height: 0 })

// A4 dimensions in pixels (at 96 DPI)
const A4_WIDTH = 794
const A4_HEIGHT = 1123

// Computed styles
const computedFontSize = computed(() => {
  return Math.round(props.fontSize * zoomLevel.value)
})

const pdfStyle = computed(() => ({
  width: `${A4_WIDTH * zoomLevel.value}px`,
  height: `${A4_HEIGHT * zoomLevel.value}px`,
  transform: `scale(1)`, // Reset any scaling
  transformOrigin: "top left"
}))

const wrapperStyle = computed(() => ({
  overflow: "auto",
  height: "100%",
  display: "flex",
  justifyContent: "center",
  alignItems: "flex-start",
  padding: "20px"
}))

// Zoom controls
const zoomIn = () => {
  if (zoomLevel.value < props.maxZoom) {
    zoomLevel.value = Math.min(zoomLevel.value + 0.1, props.maxZoom)
  }
}

const zoomOut = () => {
  if (zoomLevel.value > props.minZoom) {
    zoomLevel.value = Math.max(zoomLevel.value - 0.1, props.minZoom)
  }
}

const fitToWidth = () => {
  const availableWidth = containerSize.width - 40 // Account for padding
  zoomLevel.value = Math.min(availableWidth / A4_WIDTH, props.maxZoom)
}

const fitToHeight = () => {
  const availableHeight = containerSize.height - 80 // Account for controls and padding
  zoomLevel.value = Math.min(availableHeight / A4_HEIGHT, props.maxZoom)
}

const resetZoom = () => {
  zoomLevel.value = 1
}

// Auto-fit logic
const autoFit = () => {
  if (!props.autoScale || !container.value) return
  
  const rect = container.value.getBoundingClientRect()
  containerSize.width = rect.width
  containerSize.height = rect.height
  
  // Choose the most restrictive dimension
  const widthRatio = (containerSize.width - 40) / A4_WIDTH
  const heightRatio = (containerSize.height - 80) / A4_HEIGHT
  
  zoomLevel.value = Math.min(
    Math.min(widthRatio, heightRatio),
    props.maxZoom
  )
}

// Resize observer
let resizeObserver
onMounted(() => {
  if (window.ResizeObserver && container.value) {
    resizeObserver = new ResizeObserver(autoFit)
    resizeObserver.observe(container.value)
  }
  
  // Initial auto-fit
  nextTick(autoFit)
})

onBeforeUnmount(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

// Keyboard shortcuts
onMounted(() => {
  const handleKeydown = (event) => {
    if (event.ctrlKey || event.metaKey) {
      switch (event.key) {
        case "+":
        case "=":
          event.preventDefault()
          zoomIn()
          break
        case "-":
          event.preventDefault()
          zoomOut()
          break
        case "0":
          event.preventDefault()
          resetZoom()
          break
      }
    }
  }
  
  document.addEventListener("keydown", handleKeydown)
  
  onBeforeUnmount(() => {
    document.removeEventListener("keydown", handleKeydown)
  })
})
</script>

<style scoped>
.pdf-preview-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #F3F4F6;
}

.pdf-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  background: white;
  border-bottom: 1px solid #E5E7EB;
  flex-shrink: 0;
}

.zoom-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.zoom-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #D1D5DB;
  background: white;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.zoom-btn:hover:not(:disabled) {
  background: #F3F4F6;
  border-color: #9CA3AF;
}

.zoom-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.zoom-display {
  font-weight: 500;
  min-width: 50px;
  text-align: center;
}

.fit-controls {
  display: flex;
  gap: 8px;
}

.fit-btn {
  padding: 6px 12px;
  border: 1px solid #D1D5DB;
  background: white;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.fit-btn:hover {
  background: #F3F4F6;
  border-color: #9CA3AF;
}

.pdf-preview-wrapper {
  flex: 1;
  position: relative;
}

.pdf-document {
  background: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-radius: 4px;
}
</style>
```

### Advanced Image Handling for PDFs

```javascript
// Enhanced image loading for PDF templates
const useAdvancedImageLoading = () => {
  const imageCache = new Map()
  const loadingStates = reactive({})
  
  const loadImage = async (source, options = {}) => {
    const {
      maxWidth = 800,
      maxHeight = 600,
      quality = 0.9,
      format = "image/jpeg"
    } = options
    
    // Check cache first
    const cacheKey = `${source}-${maxWidth}-${maxHeight}-${quality}`
    if (imageCache.has(cacheKey)) {
      return imageCache.get(cacheKey)
    }
    
    loadingStates[cacheKey] = true
    
    try {
      let blob
      
      if (source instanceof File) {
        blob = source
      } else if (typeof source === "string") {
        const response = await fetch(source)
        blob = await response.blob()
      } else {
        throw new Error("Invalid image source")
      }
      
      // Create image element for processing
      const img = new Image()
      const canvas = document.createElement("canvas")
      const ctx = canvas.getContext("2d")
      
      await new Promise((resolve, reject) => {
        img.onload = resolve
        img.onerror = reject
        img.src = URL.createObjectURL(blob)
      })
      
      // Calculate new dimensions
      let { width, height } = img
      
      if (width > maxWidth) {
        height = (height * maxWidth) / width
        width = maxWidth
      }
      
      if (height > maxHeight) {
        width = (width * maxHeight) / height
        height = maxHeight
      }
      
      // Resize image
      canvas.width = width
      canvas.height = height
      ctx.drawImage(img, 0, 0, width, height)
      
      // Convert to blob
      const processedBlob = await new Promise(resolve => {
        canvas.toBlob(resolve, format, quality)
      })
      
      // Create object URL
      const url = URL.createObjectURL(processedBlob)
      
      // Cache result
      const result = {
        url,
        width,
        height,
        size: processedBlob.size,
        blob: processedBlob
      }
      
      imageCache.set(cacheKey, result)
      
      // Cleanup
      URL.revokeObjectURL(img.src)
      
      return result
    } catch (error) {
      console.error("Image loading failed:", error)
      throw error
    } finally {
      loadingStates[cacheKey] = false
    }
  }
  
  const clearCache = () => {
    // Revoke all object URLs
    for (const cached of imageCache.values()) {
      if (cached.url) {
        URL.revokeObjectURL(cached.url)
      }
    }
    imageCache.clear()
  }
  
  // Cleanup on unmount
  onBeforeUnmount(() => {
    clearCache()
  })
  
  return {
    loadImage,
    clearCache,
    loadingStates: readonly(loadingStates),
    cacheSize: computed(() => imageCache.size)
  }
}
```