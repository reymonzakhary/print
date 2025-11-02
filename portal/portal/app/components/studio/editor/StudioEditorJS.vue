<template>
  <div
    class="relative bg-white text-gray-900"
    :class="{
      'studio-editor--dark border-gray-700 !bg-gray-900 text-white': dark,
      'cursor-not-allowed opacity-60': disabled || readOnly,
    }"
  >
    <!-- Loading State -->
    <div v-if="loading" class="flex h-48 items-center justify-center">
      <UILoader />
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="flex h-48 flex-col items-center justify-center gap-4 text-red-500"
    >
      <p>{{ $t("Failed to load editor") }}</p>
      <UIButton size="sm" @click="retry">
        {{ $t("Try Again") }}
      </UIButton>
    </div>

    <!-- Editor Container -->
    <div v-show="!loading && !error" ref="editorElement" :class="{ 'studio-editor--dark': dark }" />
  </div>
</template>

<script setup>
import header from "@editorjs/header";
import list from "@editorjs/list";
import Paragraph from "@editorjs/paragraph";
import Delimiter from "@editorjs/delimiter";

import { MagicTagInlineTool } from "~/plugins/editorjs/magic-tag-inline-tool.js";
import { MagicTagProcessor } from "~/plugins/editorjs/magic-tag-block-processor.js";

const props = defineProps({
  placeholder: {
    type: String,
    default: "Start typing...",
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  tags: {
    type: Array,
    default: () => [],
  },
  dark: {
    type: Boolean,
    default: false,
  },
});

const modelValue = defineModel({
  type: Object,
  default: () => ({ blocks: [] }),
});

const emit = defineEmits(["ready", "change"]);

const editorElement = ref();
const loading = ref(true);
const error = ref(null);

const { createToastNotifier } = useEditorJS();
// Initialize editor
const { editor, isReady, initialize, save, render, destroy, toggleReadOnly } = useEditorJS({
  placeholder: props.placeholder,
  readOnly: props.readOnly || props.disabled,
  tools: {
    // Add default tools
    paragraph: {
      class: Paragraph,
      inlineToolbar: true,
    },
    delimiter: Delimiter,
    header: {
      class: header,
      inlineToolbar: true,
      config: {
        levels: [2, 3, 4],
        defaultLevel: 2,
      },
    },
    list: {
      class: list,
      inlineToolbar: true,
    },
    // Add magic tag tool if tags provided
    ...(props.tags.length > 0 && {
      magicTag: {
        class: MagicTagInlineTool,
        config: {
          tags: props.tags,
          notifier: createToastNotifier(), // Pass our custom notifier
        },
      },
    }),
  },
  onChange: async () => {
    if (props.readOnly || props.disabled) return;

    const data = await save();
    if (data) {
      isInternalUpdate.value = true;
      modelValue.value = data;
      emit("change");
      await nextTick();
      isInternalUpdate.value = false;
    }
  },
  onReady: (api) => {
    emit("ready", api);

    // Start magic tag processor if tags provided
    if (props.tags.length > 0) {
      tagProcessor.value = new MagicTagProcessor(api, props.tags, props.readOnly || props.disabled);
      tagProcessor.value.start();
    }
  },
});

const tagProcessor = ref(null);
const isInternalUpdate = ref(false);

// Initialize editor when element is ready
const initializeEditor = async () => {
  if (!editorElement.value) return;

  try {
    loading.value = true;
    error.value = null;

    await initialize(editorElement.value, toRaw(modelValue.value));
    loading.value = false;
  } catch (err) {
    error.value = err;
    loading.value = false;
  }
};

// Watch for external data changes
watch(
  () => modelValue.value,
  async (newValue) => {
    if (!editor.value || !isReady.value || isInternalUpdate.value) return;

    try {
      // Check if data actually changed
      const currentData = await save();
      if (JSON.stringify(currentData) !== JSON.stringify(newValue)) {
        await render(toRaw(newValue));
      }
    } catch (err) {
      console.error("Error updating editor content:", err);
    }
  },
  { deep: true },
);

// Watch for readOnly changes
watch(
  () => [props.readOnly, props.disabled],
  ([readOnly, disabled]) => toggleReadOnly(readOnly || disabled),
);

// Retry initialization
const retry = () => initializeEditor();

// Lifecycle
onMounted(() => nextTick(initializeEditor));

onBeforeUnmount(() => {
  tagProcessor.value?.stop();
  tagProcessor.value = null;
  destroy();
});

defineExpose({
  save,
  render,
  clear: async () => {
    if (editor.value && isReady.value) {
      await editor.value.clear();
    }
  },
  reset: async (data) => {
    if (editor.value && isReady.value) {
      try {
        // Clear the internal update flag
        isInternalUpdate.value = false;
        await nextTick(); // Ensure any pending updates are processed
        const dataToRender = data !== undefined ? data : toRaw(modelValue.value);
        await render(dataToRender);
      } catch (err) {
        console.error("Error resetting editor:", err);
      }
    }
  },
});
</script>

<style lang="scss" scoped>
/* EditorJS Overrides */
:deep(.codex-editor__redactor) {
  padding-bottom: 0 !important;
}

:deep(.ce-block__content) {
  max-width: 100% !important;
  @apply bg-transparent;
}

/* Heading styles */
:deep(.ce-header) {
  font-weight: 600;
  line-height: 1.2;
  @apply text-xl text-gray-500;
}

:deep(.studio-editor--dark .ce-toolbar__plus svg),
:deep(.studio-editor--dark .ce-toolbar__settings-btn svg) {
  color: white;
}

:deep(.studio-editor--dark .ce-toolbar__plus:hover svg),
:deep(.studio-editor--dark .ce-toolbar__settings-btn:hover svg) {
  color: #000;
}

/* Dark mode adjustments */
.studio-editor--dark :deep(.codex-editor) {
  color: white;
}

.studio-editor--dark :deep(.ce-header),
.studio-editor--dark :deep(.ce-paragraph) {
  color: white;
}

/* Magic tag styles */
:deep(.magic-tag) {
  @apply relative -top-0.5 inline-block cursor-pointer select-none rounded-md bg-theme-500 px-2 py-0.5 text-xs font-semibold text-white transition-all;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  pointer-events: auto;
}

:deep(.magic-tag:hover) {
  @apply bg-theme-600 shadow-md;
}

:deep(.magic-tag--selected) {
  @apply bg-theme-700 ring-2 ring-theme-300 ring-offset-1;
}

:deep(.magic-tag--readonly) {
  @apply cursor-default bg-gray-500;
  pointer-events: none;
}

:deep(.magic-tag--readonly:hover) {
  @apply bg-gray-500;
}

/* Magic tag menu styles */
:global(.magic-tag-menu) {
  @apply rounded-lg border border-gray-200 bg-white shadow-lg;
  max-width: 300px;
  max-height: 300px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

:global(.magic-tag-menu__search) {
  @apply border-b border-gray-200 p-2;
}

:global(.magic-tag-menu__search input) {
  @apply w-full rounded border border-gray-300 px-2 py-1 text-sm outline-none focus:border-theme-500;
}

:global(.magic-tag-menu__list) {
  @apply flex-1 overflow-y-auto;
}

:global(.magic-tag-menu__item) {
  @apply block w-full border-b border-gray-100 p-3 text-left transition-colors hover:bg-gray-50;
}

:global(.magic-tag-menu__item-name) {
  @apply block font-medium text-gray-900;
}

:global(.magic-tag-menu__item-desc) {
  @apply block text-xs text-gray-500;
}

/* Magic tag toolbar styles */
:global(.magic-tag-toolbar) {
  @apply flex items-center gap-1 rounded-md bg-gray-700 p-1 shadow-lg;
  z-index: 1002;
}

:global(.magic-tag-toolbar select) {
  @apply min-w-[180px] rounded border border-gray-600 bg-gray-800 px-2 py-1 text-xs text-white outline-none focus:border-theme-500;
}

:global(.magic-tag-toolbar button) {
  @apply flex h-7 w-7 items-center justify-center rounded border-0 bg-gray-600 text-white transition-colors hover:bg-gray-500;
}

:global(.magic-tag-toolbar button:hover) {
  @apply bg-gray-500;
}

:global(.magic-tag-toolbar button svg) {
  @apply h-3 w-3;
}
</style>
