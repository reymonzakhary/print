import EditorJS from "@editorjs/editorjs";

export function useEditorJS(options = {}) {
  const editor = ref(null);
  const isReady = ref(false);
  const isLoading = ref(false);
  const error = ref(null);

  // Helper function to create a custom notifier using our toast system
  const createToastNotifier = () => {
    const { addToast } = useToastStore();

    return {
      show: ({ message, style }) => {
        // Map EditorJS notification styles to our toast types
        const typeMap = {
          error: "error",
          success: "success",
          warning: "warning",
          info: "info",
        };

        const type = typeMap[style] || "info";

        // Add appropriate icons based on type
        const iconMap = {
          error: ["fal", "triangle-exclamation"],
          success: ["fal", "check-circle"],
          warning: ["fal", "exclamation-triangle"],
          info: ["fal", "info-circle"],
        };

        addToast({
          icon: iconMap[type],
          message,
          type,
        });
      },
    };
  };

  const initialize = async (holder, initialData) => {
    try {
      isLoading.value = true;
      error.value = null;

      // Destroy existing instance
      if (editor.value) {
        await destroy();
      }

      editor.value = new EditorJS({
        holder,
        data: initialData,
        placeholder: options.placeholder || "Start typing...",
        readOnly: options.readOnly || false,
        minHeight: options.minHeight || 100,
        tools: options.tools || {},
        onChange: options.onChange,
        onReady: () => {
          isReady.value = true;
          options.onReady?.(editor.value);
        },
        logLevel: "ERROR",
      });

      await editor.value.isReady;
      isLoading.value = false;
    } catch (err) {
      error.value = err;
      isLoading.value = false;
      throw err;
    }
  };

  const save = async () => {
    if (!editor.value || !isReady.value) return null;

    try {
      return await editor.value.save();
    } catch (err) {
      console.error("Failed to save editor content:", err);
      return null;
    }
  };

  const render = async (data) => {
    if (!editor.value || !isReady.value) return;

    try {
      await editor.value.render(data);
    } catch (err) {
      console.error("Failed to render editor content:", err);
    }
  };

  const clear = async () => {
    if (!editor.value || !isReady.value) return;

    try {
      await editor.value.clear();
    } catch (err) {
      console.error("Failed to clear editor:", err);
    }
  };

  const destroy = async () => {
    if (!editor.value) return;

    try {
      editor.value.destroy();
      editor.value = null;
      isReady.value = false;
    } catch (err) {
      console.error("Failed to destroy editor:", err);
    }
  };

  const toggleReadOnly = async (state) => {
    if (!editor.value || !isReady.value) return;

    const readOnlyAPI = editor.value.readOnly;
    if (state !== undefined) {
      await readOnlyAPI.toggle(state);
    } else {
      await readOnlyAPI.toggle();
    }
  };

  const reset = async (data = { blocks: [] }) => {
    if (!editor.value || !isReady.value) return;

    try {
      await editor.value.render(data);
    } catch (err) {
      console.error("Failed to reset editor content:", err);
    }
  };

  // Cleanup on unmount
  const cleanup = () => {
    destroy();
  };

  return {
    editor,
    isReady,
    isLoading,
    error,
    initialize,
    save,
    render,
    clear,
    destroy,
    toggleReadOnly,
    reset,
    cleanup,
    createToastNotifier,
  };
}
