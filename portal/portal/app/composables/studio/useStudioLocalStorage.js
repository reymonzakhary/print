import { useStudioState } from "./useStudioState.js";

export function useStudioLocalStorage({ namespace, area, config }) {
  const state = useStudioState();

  // Generate localStorage key
  const getKey = (field) => `${namespace}:${area}:${field}`;

  // Initialize state from config
  watchEffect(() => {
    if (config?.value) {
      state.initialize(config.value);
    }
  });

  // Load from localStorage
  const load = () => {
    const loadedValues = {};

    for (const key in state.values.value) {
      try {
        const stored = localStorage.getItem(getKey(key));
        if (stored) {
          loadedValues[key] = JSON.parse(stored);
        }
      } catch (err) {
        console.warn(`Failed to load ${key} from localStorage:`, err);
      }
    }

    state.setValues(loadedValues);
  };

  // Save to localStorage
  const save = () => {
    if (!state.isDirty.value) return;

    for (const [key, { current }] of Object.entries(state.changedFields.value)) {
      try {
        localStorage.setItem(getKey(key), JSON.stringify(current));
      } catch (err) {
        console.error(`Failed to save ${key} to localStorage:`, err);
      }
    }

    state.commit();
  };

  return {
    // State
    loading: ref(false), // localStorage is sync
    saving: ref(false), // localStorage is sync
    isDirty: state.isDirty,
    changedFields: state.changedFields,

    // Methods
    update: state.update,
    reset: state.reset,
    getValue: state.getValue,
    load,
    save,
  };
}
