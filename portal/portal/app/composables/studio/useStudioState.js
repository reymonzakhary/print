export function useStudioState(initialConfig = {}) {
  // Core state
  const values = ref({});
  const original = ref({});
  const loading = ref(false);
  const saving = ref(false);

  // Computed states
  const isDirty = computed(() => {
    const current = JSON.stringify(values.value);
    const orig = JSON.stringify(original.value);
    return current !== orig;
  });

  const changedFields = computed(() => {
    const changes = {};
    for (const key in values.value) {
      if (JSON.stringify(values.value[key]) !== JSON.stringify(original.value[key])) {
        changes[key] = {
          current: values.value[key],
          original: original.value[key],
        };
      }
    }
    return changes;
  });

  // Initialize from config structure
  const initialize = (config) => {
    const initialValues = {};

    // Extract values from config structure
    const extractValues = (items) => {
      items.forEach((item) => {
        if (item.fields) {
          extractValues(item.fields);
        } else if (item.children) {
          extractValues(item.children);
        } else if (item.settingKey) {
          initialValues[item.settingKey] = item.value ?? null;
        }
      });
    };

    if (Array.isArray(config)) {
      extractValues(config);
    }

    values.value = { ...initialValues };
    original.value = { ...initialValues };
  };

  // Update a single value
  const update = (key, value) => {
    values.value[key] = value;
  };

  // Reset to original
  const reset = () => {
    values.value = { ...original.value };
  };

  // Apply changes (update original to match current)
  const commit = () => {
    original.value = { ...values.value };
  };

  // Get reactive value for a key
  const getValue = (key, defaultValue = null) => {
    return computed(() => values.value[key] ?? defaultValue);
  };

  // Batch update values (useful after loading from API)
  const setValues = (newValues) => {
    Object.assign(values.value, newValues);
    original.value = { ...values.value };
  };

  // Initialize on creation if config provided
  if (initialConfig && Object.keys(initialConfig).length > 0) {
    initialize(initialConfig);
  }

  return {
    // State
    values: readonly(values),
    loading,
    saving,

    // Computed
    isDirty,
    changedFields,

    // Methods
    initialize,
    update,
    reset,
    commit,
    getValue,
    setValues,
  };
}
