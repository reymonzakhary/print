export const useSalesStore = defineStore("sales", () => {
  const { settings } = storeToRefs(useAuthStore());

  // Flag to prevent state updates when navigating away from the page
  const leaving = ref(false);
  // Flags
  const changed = ref(false);
  const saving = ref(false);
  /**
   * Possible values:
   * - false: No critical flag
   * - 'locked': The sales entity is locked by another user
   * - 'trashed': The sales entity is trashed x_x
   * - 'archived': The sales entity is archived
   */
  const criticalFlag = ref(false);
  const isEditable = ref(false);
  const isExternal = ref(false);
  const multipleAddresses = ref(false);
  const noMembersAccess = ref(false);
  const salesContext = ref(null);
  const isHaveExternalItem = ref(false);

  // Data for Delivery Selector as it's used in multiple components
  const deliveryAddresses = ref([]);
  const pickupAddresses = ref([]);
  const teams = ref([]);

  // Open Product Caching
  const allOptions = ref([]);
  const allCategories = ref([]);
  const allBoxes = ref([]);

  // Warnings
  const _warnings = ref({});
  const warnings = computed(() => Object.values(_warnings.value));
  const addWarning = (key, message) => (_warnings.value[key] = message);
  const removeWarning = (key) => delete _warnings.value[key];

  const isUsingTeamAddresses = computed(() => {
    const useTeamAddress = settings.value.data.filter(
      (theSettings) => theSettings.key === "use_team_address",
    );
    return useTeamAddress.length > 0 && useTeamAddress[0].value;
  });

  function reset() {
    criticalFlag.value = false;
    isEditable.value = false;
    isExternal.value = false;
    saving.value = false;
    multipleAddresses.value = false;
    deliveryAddresses.value = [];
    pickupAddresses.value = [];
    teams.value = [];
    _warnings.value = {};
    noMembersAccess.value = false;
    salesContext.value = false;
  }

  return {
    criticalFlag,
    isEditable,
    isExternal,
    changed,
    saving,
    multipleAddresses,
    teams,
    deliveryAddresses,
    pickupAddresses,
    isUsingTeamAddresses,
    warnings,
    addWarning,
    removeWarning,
    reset,
    noMembersAccess,
    salesContext,
    leaving,
    isHaveExternalItem,
    // Open Product Caching
    allOptions,
    allCategories,
    allBoxes,
  };
});
