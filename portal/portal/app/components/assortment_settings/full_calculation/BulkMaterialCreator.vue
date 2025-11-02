<template>
  <ConfirmationModal @on-close="$emit('close')">
    <template #modal-header>
      {{ $t("add new material") }}
    </template>
    <template #modal-body>
      <main class="h-full">
        <section class="p-2">
          <h2 class="mb-2 text-sm font-bold uppercase tracking-wider">
            {{ $t("select from category") }}
          </h2>

          <!-- Bulk material settings form -->
          <div class="mt-4 space-y-3 rounded">
            <h3 class="text-sm font-bold uppercase tracking-wider">
              {{ $t("bulk material settings") }}
            </h3>

            <div class="flex items-center">
              <UIVSelect
                v-model="selectedCategory"
                :options="categories"
                :getOptionLabel="(option) => option.name"
                class="w-full rounded-r-none"
              />
              <UIButton
                :disabled="!selectedCategory"
                variant="theme"
                class="whitespace-nowrap rounded-none rounded-r !p-2"
                :title="$t('get options')"
                @click="fetchCategory(selectedCategory)"
              >
                {{ $t("get options") }}
              </UIButton>
            </div>

            <ul
              v-if="combinations.length"
              class="mt-4 rounded bg-gray-100 p-2 font-mono dark:bg-gray-700"
            >
              <li
                v-for="(combi, index) in combinations"
                :key="combi.id"
                class="flex w-full items-center justify-between pb-1 pt-2 text-xs"
                :class="{
                  'opacity-50': combinationExists(combi),
                }"
                v-tooltip="{
                  content: $t('already exists'),
                  disabled: !combinationExists(combi),
                }"
              >
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :id="`combo-${combi.id}`"
                    :checked="selectedCombinations.has(combi.id)"
                    :disabled="combinationExists(combi)"
                    @change="handleCombinationChange(combi, index, $event)"
                    @mousedown="handleCombinationMouseDown(combi, index, $event)"
                    class="h-3 w-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-30"
                  />
                  <label :for="`combo-${combi.id}`" class="flex-1 cursor-pointer">
                    {{ $display_name(combi.material.display_name) }}
                  </label>
                </div>
                <div class="flex-1 text-center">-</div>
                <div class="flex flex-1 items-center justify-end text-right">
                  {{ $display_name(combi.weight.display_name) }}
                  <font-awesome-icon
                    v-if="combinationExists(combi)"
                    :icon="['fal', 'check-circle']"
                    class="ml-2"
                    :title="$t('already exists')"
                  />
                  <font-awesome-icon
                    v-else
                    :icon="['fal', 'plus-circle']"
                    class="ml-2 text-green-500"
                    :title="$t('will be created')"
                  />
                </div>
              </li>
            </ul>

            <div v-if="loading" class="mt-4 text-center">
              <font-awesome-icon icon="spinner" spin class="fa-fw mr-2 text-gray-500" />
              {{ $t("loading") }}
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="col-span-2 mb-3 flex gap-2">
                <UIButton
                  variant="link"
                  size="sm"
                  @click="selectAllNewCombinations"
                  :disabled="newCombinationsCount === 0"
                  class="flex-1"
                >
                  {{ $t("select all") }}
                </UIButton>
                <UIButton
                  variant="inverted-danger"
                  size="sm"
                  @click="clearAllSelections"
                  :disabled="selectedCombinationsCount === 0"
                  class="flex-1"
                >
                  {{ $t("clear selection") }}
                </UIButton>
              </div>

              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">
                  {{ $t("supplier") }}
                </label>
                <UIInputText
                  v-model="bulkSettings.supplier"
                  :placeholder="$t('supplier name')"
                  class="w-full"
                  input-class="text-sm"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">
                  {{ $t("type") }}
                </label>
                <UIVSelect
                  v-model="bulkSettings.isSheet"
                  :options="[
                    { value: true, label: $t('sheet') },
                    { value: false, label: $t('roll') },
                  ]"
                  :getOptionLabel="(option) => option.label"
                  :getOptionValue="(option) => option.value"
                  class="w-full"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">
                  {{ $t("width") }} (mm)
                </label>
                <UIInputText
                  v-model.number="bulkSettings.width"
                  type="number"
                  :placeholder="$t('width in mm')"
                  class="w-full"
                  input-class="text-sm"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">
                  {{ $t("height") }} (mm)
                </label>
                <UIInputText
                  v-model.number="bulkSettings.height"
                  type="number"
                  :placeholder="$t('height in mm')"
                  :disabled="bulkSettings.isSheet?.value === false"
                  class="w-full"
                  input-class="text-sm"
                />
              </div>

              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-700">
                  {{ $t("length") }} (mm)
                </label>
                <UIInputText
                  v-model.number="bulkSettings.length"
                  type="number"
                  :placeholder="$t('length in mm')"
                  :disabled="bulkSettings.isSheet?.value === true"
                  class="w-full"
                  input-class="text-sm"
                />
              </div>
            </div>

            <UIButton
              :disabled="!canCreateBulkMaterials || selectedCombinationsCount === 0"
              variant="success"
              class="w-full"
              @click="createBulkMaterials"
            >
              <font-awesome-icon :icon="['fal', 'plus-circle']" class="mr-2" />
              <template v-if="selectedCombinationsCount > 0">
                {{ $t("create") }} {{ selectedCombinationsCount }} {{ $t("selected materials") }}
              </template>
              <template v-else-if="newCombinationsCount > 0">
                {{ $t("select materials to create") }}
              </template>
              <template v-else-if="combinations.length > 0">
                {{ $t("all materials already exist") }}
              </template>
              <template v-else>
                {{ $t("no combinations available") }}
              </template>
            </UIButton>
          </div>
        </section>

        <hr />

        <section class="p-2">
          <h2 class="mb-2 text-sm font-bold uppercase tracking-wider">
            {{ $t("add single material") }}
          </h2>
          <UIButton :icon="['fal', 'plus']" @click="$emit('addSingleMaterial')">
            {{ $t("add material") }}
          </UIButton>
        </section>
      </main>
    </template>
  </ConfirmationModal>
</template>

<script setup>
const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();
const { t } = useI18n();

const props = defineProps({
  catalogue: {
    type: Array,
    required: true,
  },
  materials: {
    type: Array,
    required: true,
  },
  grs: {
    type: Array,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["close", "addSingleMaterial", "addMaterial"]);

// refs
const selectedCategory = ref(null);
const combinations = ref([]);
const loading = ref(false);

// Selected combinations for bulk creation
const selectedCombinations = ref(new Set());
const lastClickedIndex = ref(-1);

// Bulk material settings - Default to most common sheet format (A1)
const bulkSettings = ref({
  supplier: "",
  width: 594, // A1 width in mm
  height: 841, // A1 height in mm
  length: 0, // For sheets, length is 0
  isSheet: { value: true, label: "Sheet" }, // Will be properly set on mount
});

// Used EAN codes for uniqueness
const usedEanCodes = new Set();

// Computed properties
const canCreateBulkMaterials = computed(() => {
  const isSheet = bulkSettings.value.isSheet?.value === true;
  const isRoll = bulkSettings.value.isSheet?.value === false;

  return (
    bulkSettings.value.supplier &&
    bulkSettings.value.width &&
    // For sheets: height must be > 0, for rolls: height can be 0
    (isSheet ? bulkSettings.value.height > 0 : bulkSettings.value.height >= 0) &&
    bulkSettings.value.length !== null &&
    bulkSettings.value.length !== undefined &&
    bulkSettings.value.length !== "" &&
    // For sheets: length can be 0, for rolls: length must be > 0
    (isSheet ? bulkSettings.value.length >= 0 : bulkSettings.value.length > 0) &&
    bulkSettings.value.isSheet &&
    combinations.value.length > 0
  );
});

// Helper function to check if a combination exists in the catalogue
const combinationExists = (combination) => {
  // Only check if all required fields are filled
  if (!canCreateBulkMaterials.value) {
    return false; // Don't mark as existing if form isn't complete
  }

  // Find linked material and weight IDs for this combination
  const linkedMaterial = findLinkedOption(combination.material.name, props.materials);
  const linkedWeight = findLinkedOption(combination.weight.name, props.grs);

  if (!linkedMaterial || !linkedWeight) {
    return false;
  }

  return props.catalogue.some((item) => {
    // Use the 'linked' field from the combination data to match with catalog's material_link and grs_link
    const materialMatch = item.material_link === linkedMaterial.linked;
    const weightMatch = item.grs_link === linkedWeight.linked;
    const supplierMatch =
      item.supplier?.toLowerCase().trim() === bulkSettings.value.supplier?.toLowerCase().trim();
    // Handle isSheet as object or boolean
    const isSheetValue = bulkSettings.value.isSheet?.value ?? bulkSettings.value.isSheet;
    const sheetMatch = Boolean(item.sheet) === Boolean(isSheetValue);
    const widthMatch = Number(item.width) === Number(bulkSettings.value.width);
    const heightMatch = Number(item.height) === Number(bulkSettings.value.height);
    const lengthMatch = Number(item.length) === Number(bulkSettings.value.length);

    return (
      materialMatch &&
      weightMatch &&
      supplierMatch &&
      sheetMatch &&
      widthMatch &&
      heightMatch &&
      lengthMatch
    );
  });
};

// Selection functions
const handleCombinationMouseDown = (combination, index, event) => {
  // Only handle shift-click range selection on mousedown
  if (event.shiftKey && lastClickedIndex.value !== -1) {
    event.preventDefault(); // Prevent the checkbox from being clicked normally

    if (combinationExists(combination)) {
      return; // Don't process disabled items
    }

    // Determine the range to select
    const startIndex = Math.min(lastClickedIndex.value, index);
    const endIndex = Math.max(lastClickedIndex.value, index);

    // Use the LAST CLICKED item's state to determine what action to apply to the range
    const lastClickedCombo = combinations.value[lastClickedIndex.value];
    const lastClickedIsSelected = selectedCombinations.value.has(lastClickedCombo.id);

    // If last clicked item is selected, we should select the range
    // If last clicked item is deselected, we should deselect the range
    const shouldSelect = lastClickedIsSelected;

    // Apply the same action to all items in the range
    for (let i = startIndex; i <= endIndex; i++) {
      const combo = combinations.value[i];
      if (!combinationExists(combo)) {
        // Only affect non-existing combinations
        if (shouldSelect) {
          selectedCombinations.value.add(combo.id);
        } else {
          selectedCombinations.value.delete(combo.id);
        }
      }
    }

    // For range selections, update lastClickedIndex to the END of the range
    lastClickedIndex.value = index;
    selectedCombinations.value = new Set(selectedCombinations.value); // Trigger reactivity
  }
};

const handleCombinationChange = (combination, index, event) => {
  if (combinationExists(combination)) {
    return; // Don't process disabled items
  }

  // Normal single-click toggle - let the checkbox work naturally
  // The checkbox state has already changed, so sync our state with it
  if (event.target.checked) {
    selectedCombinations.value.add(combination.id);
  } else {
    selectedCombinations.value.delete(combination.id);
  }

  // Remember this index for future shift-clicks
  lastClickedIndex.value = index;
  selectedCombinations.value = new Set(selectedCombinations.value); // Trigger reactivity
};

const selectAllNewCombinations = () => {
  const newCombos = combinations.value.filter((combo) => !combinationExists(combo));
  newCombos.forEach((combo) => selectedCombinations.value.add(combo.id));
  selectedCombinations.value = new Set(selectedCombinations.value);
};

const clearAllSelections = () => {
  selectedCombinations.value.clear();
  lastClickedIndex.value = -1; // Reset last clicked index
  selectedCombinations.value = new Set(selectedCombinations.value);
};

const selectedCombinationsCount = computed(() => {
  return selectedCombinations.value.size;
});

const newCombinationsCount = computed(() => {
  if (!combinations.value.length || !canCreateBulkMaterials.value) return 0;

  return combinations.value.filter((combination) => {
    return !combinationExists(combination);
  }).length;
});

// Helper functions
const resetBulkSettings = (useDefaults = true) => {
  if (useDefaults) {
    // Reset with A1 defaults for sheet
    bulkSettings.value = {
      supplier: "",
      width: 594, // A1 width in mm
      height: 841, // A1 height in mm
      length: 0, // For sheets, length is 0
      isSheet: { value: true, label: t("sheet") },
    };
  } else {
    // Complete reset
    bulkSettings.value = {
      supplier: "",
      width: null,
      height: null,
      length: null,
      isSheet: true,
    };
  }
};

const generateUniqueEan = () => {
  let ean;
  do {
    ean = Math.floor(Math.random() * 9000000000000) + 1000000000000; // 13 digit number
  } while (usedEanCodes.has(ean));
  usedEanCodes.add(ean);
  return ean.toString();
};

const generateArticleNumber = (supplier, material, weight, isSheet) => {
  const type = isSheet ? "SH" : "RL";
  const materialCode = material.name.substring(0, 3).toUpperCase();
  const weightCode = weight.name.replace(/[^0-9]/g, ""); // Extract numbers only
  const supplierCode = supplier.substring(0, 3).toUpperCase();

  return `${supplierCode}-${materialCode}-${weightCode}-${type}`;
};

const findLinkedOption = (optionName, optionsList) => {
  if (!optionName || !optionsList?.length) {
    return null;
  }

  const cleanOptionName = optionName.toLowerCase().trim();

  const found = optionsList.find((option) => {
    const nameMatch = option.name?.toLowerCase().trim() === cleanOptionName;
    // Also try partial matches for weight (e.g., "100 gr" matches "100")
    const namePartialMatch =
      option.name?.toLowerCase().includes(cleanOptionName) ||
      cleanOptionName.includes(option.name?.toLowerCase());

    return nameMatch || namePartialMatch;
  });

  return found;
};

const createBulkMaterials = async () => {
  if (!canCreateBulkMaterials.value || selectedCombinationsCount.value === 0) return;

  loading.value = true;

  try {
    const newMaterials = [];
    let successCount = 0;
    let skippedCount = 0;

    // Only process selected combinations
    const selectedCombos = combinations.value.filter((combo) =>
      selectedCombinations.value.has(combo.id),
    );

    for (const combination of selectedCombos) {
      // Check if this combination already exists using our existing function
      if (combinationExists(combination)) {
        skippedCount++;
        continue; // Skip this combination as it already exists
      }

      // Find linked material and weight IDs for this combination
      const linkedMaterial = findLinkedOption(combination.material.name, props.materials);
      const linkedWeight = findLinkedOption(combination.weight.name, props.grs);

      // Handle isSheet as object or boolean
      const isSheetValue = bulkSettings.value.isSheet?.value ?? bulkSettings.value.isSheet;

      const newMaterial = {
        art_nr: generateArticleNumber(
          bulkSettings.value.supplier,
          combination.material,
          combination.weight,
          isSheetValue,
        ),
        supplier: bulkSettings.value.supplier,
        material: combination.material.name,
        material_id: linkedMaterial?.id || null,
        material_link: linkedMaterial?.linked || null,
        sheet: isSheetValue,
        width: bulkSettings.value.width,
        height: bulkSettings.value.height,
        length: bulkSettings.value.length,
        density: 0, // Default value
        grs: combination.weight.name,
        grs_id: linkedWeight?.id || null,
        grs_link: linkedWeight?.linked || null,
        price: 0, // Price will be updated later
        ean: generateUniqueEan(),
        calc_type: "kg", // Default value
      };

      newMaterials.push(newMaterial);
    }

    // Emit event to add all materials
    for (const material of newMaterials) {
      try {
        emit("addMaterial", material);
        successCount++;
        await new Promise((resolve) => setTimeout(resolve, 50)); // Small delay
      } catch (error) {
        console.error("Failed to add material:", material.art_nr, error);
      }
    }

    // Show result message
    let message = "";
    if (successCount > 0 && skippedCount > 0) {
      message = `${successCount} materials created, ${skippedCount} skipped (already exist)`;
    } else if (successCount > 0) {
      message = `${successCount} materials created successfully`;
    } else if (skippedCount > 0) {
      message = `All ${skippedCount} materials already exist - nothing to create`;
    } else {
      message = "No materials to create";
    }

    handleSuccess({ message });

    // Reset form and close modal
    combinations.value = [];
    selectedCategory.value = null;
    resetBulkSettings(false); // Complete reset without defaults
    emit("close");
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
};

const fetchCategory = async (category) => {
  // Fetch options for the selected category
  loading.value = true;
  await api
    .get(`/categories/${category.slug}`)
    .then((response) => {
      // Format response
      const boops = response.data.boops[0].boops || [];

      // Filter options by calc_ref from nested ops and create combinations
      const weightOptions = boops
        .flatMap((box) => box.ops || [])
        .filter((option) => option.additional?.calc_ref === "weight");
      const materialOptions = boops
        .flatMap((box) => box.ops || [])
        .filter((option) => option.additional?.calc_ref === "material");

      if (weightOptions.length === 0 || materialOptions.length === 0) {
        handleError(t("No weight or material options reference found for this category"));
      }

      // Create all possible combinations of weight and material options
      const uniqueCombinations = new Map();

      weightOptions.forEach((weight) => {
        materialOptions.forEach((material) => {
          const combinationKey = `${weight.id}-${material.id}`;
          if (!uniqueCombinations.has(combinationKey)) {
            uniqueCombinations.set(combinationKey, {
              weight: weight,
              material: material,
              id: combinationKey,
              name: `${material.name} - ${weight.name}`,
            });
          }
        });
      });

      // Convert Map values to array and assign to combinations
      combinations.value = Array.from(uniqueCombinations.values());

      // Sort combinations by material name first, then by weight name
      combinations.value.sort((a, b) => {
        const materialComparison = a.material.name.localeCompare(b.material.name);
        if (materialComparison !== 0) {
          return materialComparison;
        }
        return a.weight.name.localeCompare(b.weight.name);
      });
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      loading.value = false;
    });
};

// watch selected category and clear combinations
watch(
  () => selectedCategory.value,
  () => {
    combinations.value = [];
    // Clear selected combinations when category changes
    clearAllSelections();
    // Reset bulk settings when category changes
    resetBulkSettings(true); // Reset with A1 defaults
  },
);

// Initialize used EAN codes from existing catalogue
watch(
  () => props.catalogue,
  (newCatalogue) => {
    usedEanCodes.clear();
    newCatalogue.forEach((item) => {
      if (item.ean) {
        usedEanCodes.add(parseInt(item.ean));
      }
    });
  },
  { immediate: true },
);

/**
 * Auto-adjust dimensions when switching between sheet and roll
 */
watch(
  () => bulkSettings.value.isSheet?.value,
  (isSheet) => {
    if (isSheet === true) {
      // Sheet format - use A1 dimensions
      bulkSettings.value.width = 594; // A1 width
      bulkSettings.value.height = 841; // A1 height
      bulkSettings.value.length = 0; // Sheets have no length
    } else if (isSheet === false) {
      // Roll format - use common roll dimensions
      bulkSettings.value.width = 1000; // Common roll width
      bulkSettings.value.height = 0; // Rolls have no height
      bulkSettings.value.length = 100000; // 100 meters in mm
    }
  },
);

/**
 * Auto-select new combinations when combinations list changes
 */
watch(
  combinations,
  () => {
    // Auto-select all new combinations when combinations are loaded/updated
    selectAllNewCombinations();
  },
  { flush: "post" },
);

onMounted(() => {
  // Set the proper isSheet value with translated label
  bulkSettings.value.isSheet = { value: true, label: t("sheet") }; // Default to sheet
});
</script>
