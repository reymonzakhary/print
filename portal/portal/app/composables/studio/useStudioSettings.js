import { useStudioState } from "./useStudioState.js";

export function useStudioSettings({ namespace, area, config, onSuccess, onError }) {
  const state = useStudioState();
  const repository = useSettingsRepository();
  const { handleError } = useMessageHandler();

  // Initialize state from config when config changes
  watchEffect(() => {
    if (config?.value) {
      state.initialize(config.value);
    }
  });

  // Load settings from API
  const load = async () => {
    state.loading.value = true;
    try {
      const settings = await repository.getSettings({
        namespace: unref(namespace),
        area: unref(area),
        per_page: 99999, // Get all settings
      });

      // Map API response to state values
      const apiValues = {};
      for (const [key, setting] of settings.entries()) {
        // Check if this is a combined field (has sub-fields in state with dot notation)
        const combinedFields = Object.keys(state.values.value).filter((stateKey) =>
          stateKey.startsWith(key + "."),
        );

        if (combinedFields.length > 0) {
          // This is a combined field, deserialize it
          const deserializedObj = deserializeValue(setting.value, {});

          // Map each property to the corresponding state key
          for (const stateKey of combinedFields) {
            const subKey = stateKey.substring(key.length + 1); // Remove "key." prefix
            if (deserializedObj && deserializedObj[subKey] !== undefined) {
              apiValues[stateKey] = deserializedObj[subKey];
            }
          }
        } else if (state.values.value[key] !== undefined) {
          // Regular field - direct match
          apiValues[key] = deserializeValue(setting.value, state.values.value[key]);
        }
      }

      state.setValues(apiValues);
    } catch (err) {
      const errorHandler = onError || handleError;
      errorHandler(err);
    } finally {
      state.loading.value = false;
    }
  };

  // Save changed settings to API
  const save = async () => {
    if (!state.isDirty.value) return;

    state.saving.value = true;
    try {
      const promises = [];
      const processedKeys = new Set();

      for (const [key, { current }] of Object.entries(state.changedFields.value)) {
        if (processedKeys.has(key)) continue;

        // Check if this is a combined field (contains dot notation)
        if (key.includes(".")) {
          const [mainKey] = key.split(".");

          // Skip if we've already processed this main key
          if (processedKeys.has(mainKey)) continue;

          // Find ALL sub-fields for this main key in the current state
          const allSubFields = Object.entries(state.values.value)
            .filter(([k]) => k.startsWith(mainKey + "."))
            .map(([k, v]) => [k.substring(mainKey.length + 1), v]);

          // Build the complete object from all current values
          const combinedObj = {};
          allSubFields.forEach(([subKey, value]) => {
            combinedObj[subKey] = value;
          });

          // Apply the changes on top of current values
          const changedSubFields = Object.entries(state.changedFields.value)
            .filter(([k]) => k.startsWith(mainKey + "."))
            .map(([k, v]) => [k.substring(mainKey.length + 1), v.current]);

          changedSubFields.forEach(([subKey, value]) => {
            combinedObj[subKey] = value;
            processedKeys.add(mainKey + "." + subKey);
          });

          promises.push(
            repository.updateSetting({
              namespace: unref(namespace),
              area: unref(area),
              key: mainKey,
              value: serializeValue(combinedObj),
            }),
          );

          processedKeys.add(mainKey);
        } else {
          // Regular field
          promises.push(
            repository.updateSetting({
              namespace: unref(namespace),
              area: unref(area),
              key,
              value: serializeValue(current),
            }),
          );
          processedKeys.add(key);
        }
      }

      await Promise.all(promises);
      state.commit();
      onSuccess?.();
    } catch (err) {
      const errorHandler = onError || handleError;
      errorHandler(err);
      throw err; // Re-throw for parent handling
    } finally {
      state.saving.value = false;
    }
  };

  // Helper to serialize combined fields (e.g., padding objects)
  const serializeValue = (value) => {
    if (typeof value === "object" && value !== null && !Array.isArray(value)) {
      // Check if this is an image field with path property
      if (value.path) {
        return value.path;
      }
      // Convert {pt: 10, pr: 20, pb: 10, pl: 20} to "pt:10,pr:20,pb:10,pl:20"
      return Object.entries(value)
        .filter(([, v]) => v !== undefined && v !== null)
        .map(([k, v]) => `${k}:${v}`)
        .join(",");
    }
    return value;
  };

  // Helper to deserialize combined fields
  const deserializeValue = (value, originalStructure) => {
    // If original structure is an object, try to deserialize
    if (
      typeof originalStructure === "object" &&
      originalStructure !== null &&
      !Array.isArray(originalStructure)
    ) {
      if (typeof value === "string" && value.includes(":")) {
        // Convert "pt:10,pr:20,pb:10,pl:20" to {pt: 10, pr: 20, pb: 10, pl: 20}
        const obj = {};
        value.split(",").forEach((pair) => {
          const [k, v] = pair.split(":");
          if (k && v !== undefined) {
            obj[k] = isNaN(v) ? v : Number(v);
          }
        });
        return obj;
      }
    }

    // Handle image fields that might have been stored as path strings
    if (typeof value === "string" && originalStructure === null) {
      // This might be an image path
      return value;
    }

    return value;
  };

  const handleFieldUpdate = ({ settingKey, value }) => {
    if (!settingKey) return console.warn("No setting key provided", { settingKey, value });

    state.update(settingKey, value);
  };

  const getFieldValue = (key, defaultValue = null) => {
    return computed(() => {
      if (!key) return defaultValue;

      return state.values.value[key] ?? defaultValue;
    });
  };

  return {
    // State
    loading: state.loading,
    saving: state.saving,
    isDirty: state.isDirty,
    changedFields: state.changedFields,
    values: state.values,

    // Methods
    update: handleFieldUpdate,
    reset: state.reset,
    getValue: getFieldValue,
    getFieldValue, // Alias for backward compatibility
    handleFieldUpdate, // Expose for components that expect this method

    // Async methods
    load,
    save,
    initializeSettings: load, // Alias for backward compatibility
    handleApplySettings: save, // Alias for backward compatibility
    handleResetSettings: state.reset, // Alias for backward compatibility
  };
}
