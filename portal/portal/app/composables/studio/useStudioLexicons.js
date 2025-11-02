import { useStudioState } from "./useStudioState.js";

export function useStudioLexicons({ namespace, area, language, config, onSuccess, onError }) {
  const state = useStudioState();
  const repository = useLexiconRepository();
  const { handleError } = useMessageHandler();
  const { htmlToEditorJS, editorJSToHTML } = useEditorJSParser();

  // Track lexicon IDs for updates vs creates
  const lexiconIds = ref({});

  // Current language (reactive)
  const currentLanguage = ref(unref(language) || "en");
  // All fields from config (flattened)
  const allFields = computed(() => {
    const extractFields = (items) => {
      return items?.reduce((acc, item) => {
        if (item.fields) {
          // Recursively extract fields
          acc.push(...extractFields(item.fields));
        } else if (item.settingKey) {
          acc.push(item);
        }
        return acc;
      }, []);
    };

    return config?.value ? extractFields(config.value) : [];
  });

  // Initialize state from config when config changes
  watchEffect(() => {
    if (config?.value) {
      state.initialize(config.value);
    }
  });

  // Load lexicons from API
  const load = async () => {
    state.loading.value = true;
    lexiconIds.value = {}; // Reset IDs

    try {
      const response = await repository.index({
        namespace: unref(namespace),
        area: unref(area),
        language: currentLanguage.value,
        per_page: 99999,
      });

      // Flatten response structure (API returns nested by area)
      const lexicons = Object.values(response).flat();
      const lexiconValues = {};

      // Process each lexicon
      lexicons.forEach((lexicon) => {
        if (!lexicon.template) return;

        // Store the ID for future updates
        lexiconIds.value[`${lexicon.area}.${lexicon.template}`] = lexicon.id;

        // Find the field configuration
        const field = allFields.value.find(
          (f) => f.settingKey === `${lexicon.area}.${lexicon.template}`,
        );

        // Convert value based on field type
        let value = lexicon.value;
        if (field?.dataType === "editorjs" && value) {
          // Convert HTML to EditorJS format
          value = htmlToEditorJS(value);
        }

        lexiconValues[`${lexicon.area}.${lexicon.template}`] = value;
      });
      // Set all values at once
      state.setValues(lexiconValues);
    } catch (err) {
      console.error("Error loading lexicons:", err);
      const errorHandler = onError || handleError;
      errorHandler(err);
    } finally {
      state.loading.value = false;
    }
  };

  // Save changed lexicons to API
  const save = async () => {
    if (!state.isDirty.value) return;

    state.saving.value = true;
    try {
      const promises = [];

      for (const [key, { current }] of Object.entries(state.changedFields.value)) {
        const field = allFields.value.find((f) => f.settingKey === key);
        if (!field) continue;

        // Convert value based on field type
        let valueToSave = current;
        if (field.dataType === "editorjs" && current) {
          // Convert EditorJS to HTML for storage
          valueToSave = editorJSToHTML(current);
        }

        if (lexiconIds.value[key]) {
          // Update existing lexicon
          promises.push(
            repository.update(lexiconIds.value[key], {
              value: valueToSave,
            }),
          );
        } else {
          console.error("Error while saving lexicon:", key, "does not have an ID");
        }
      }

      await Promise.all(promises);
      state.commit();
      onSuccess?.();
    } catch (err) {
      console.error("Error saving lexicons:", err);
      const errorHandler = onError || handleError;
      errorHandler(err);
      throw err; // Re-throw for parent handling
    } finally {
      state.saving.value = false;
    }
  };

  // Change language and reload
  const changeLanguage = async (newLanguage) => {
    if (newLanguage === currentLanguage.value) return;

    // Clear any unsaved changes warning
    if (state.isDirty.value) {
      console.warn("Changing language with unsaved changes");
    }

    currentLanguage.value = newLanguage;
    await load();
  };

  // Get field value - simpler for lexicons (no nested keys)
  const getFieldValue = (key, defaultValue = "") => {
    return computed(() => state.values.value[key] ?? state.values.value[defaultValue]);
  };

  // Handle field update - simpler for lexicons (no nested keys)
  const handleFieldUpdate = ({ settingKey, value }) => {
    state.update(settingKey, value);
  };

  // Get field configuration by key
  const getField = (key) => {
    return allFields.value.find((f) => f.settingKey === key) || null;
  };

  return {
    // State
    loading: state.loading,
    saving: state.saving,
    isDirty: state.isDirty,
    changedFields: state.changedFields,
    changedSettings: state.changedFields, // Alias for backward compatibility
    currentLanguage: readonly(currentLanguage),
    values: state.values,

    // Methods
    update: state.update,
    reset: state.reset,
    getValue: getFieldValue,
    getFieldValue, // Alias
    getField,
    handleFieldUpdate, // For backward compatibility

    // Async methods
    load,
    save,
    changeLanguage,

    // Backward compatibility aliases
    initializeSettings: load,
    handleApplySettings: save,
    handleResetSettings: state.reset,
  };
}
