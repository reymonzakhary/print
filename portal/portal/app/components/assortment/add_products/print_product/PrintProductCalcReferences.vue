<template>
  <div class="mx-auto h-full w-full max-w-5xl overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Calculation References") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{
          $t(
            "Assign calculation references to boxes and options to enable proper price calculations",
          )
        }}
      </p>
    </div>

    <!-- Info Box -->
    <div
      class="mb-6 rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
    >
      <div class="flex items-start gap-3">
        <font-awesome-icon
          :icon="['fal', 'circle-info']"
          class="mt-1 text-xl text-blue-600 dark:text-blue-400"
        />
        <div class="flex-1">
          <h3 class="mb-2 font-bold text-blue-900 dark:text-blue-100">
            {{ $t("What are Calculation References?") }}
          </h3>
          <p class="text-sm text-blue-800 dark:text-blue-200">
            {{
              $t(
                "Calculation references link boxes and options to specific pricing calculations. For example, assign 'format' to a size box, or 'material' to a paper type option.",
              )
            }}
          </p>
        </div>
      </div>
    </div>

    <!-- Boxes List -->
    <div v-if="selected_boops && selected_boops.length > 0" class="space-y-4">
      <div
        v-for="(box, boxIndex) in selected_boops"
        :key="box.id || boxIndex"
        class="rounded-md border-2 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
      >
        <!-- Box Header -->
        <div
          class="flex cursor-pointer items-center justify-between border-b border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-750"
          @click="toggleBox(box.id || boxIndex)"
        >
          <div class="flex items-center gap-3">
            <!-- Icon Badge -->
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
              :class="
                expandedBoxes.includes(box.id || boxIndex)
                  ? 'dark:bg-theme-900/30 bg-theme-100'
                  : 'bg-gray-100 dark:bg-gray-700'
              "
            >
              <font-awesome-icon
                :icon="['fal', 'cube']"
                class="text-lg"
                :class="
                  expandedBoxes.includes(box.id || boxIndex)
                    ? 'text-theme-600 dark:text-theme-400'
                    : 'text-gray-600 dark:text-gray-400'
                "
              />
            </div>

            <!-- Box Info -->
            <div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $display_name(box.display_name) || box.name }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ box.ops?.length || 0 }} {{ $t("options") }}
              </p>
            </div>
          </div>

          <!-- Expand/Collapse Icon -->
          <font-awesome-icon
            :icon="[
              'fal',
              expandedBoxes.includes(box.id || boxIndex) ? 'chevron-up' : 'chevron-down',
            ]"
            class="text-gray-400"
          />
        </div>

        <!-- Box Content (Expanded) -->
        <transition name="fade">
          <div v-show="expandedBoxes.includes(box.id || boxIndex)" class="p-4">
            <!-- Box Calculation Reference -->
            <div class="mb-4 rounded-md bg-orange-50 p-4 dark:bg-orange-900/20">
              <div class="mb-2 flex items-center gap-2">
                <font-awesome-icon
                  :icon="['fal', 'cube']"
                  class="text-orange-600 dark:text-orange-400"
                />
                <label
                  class="text-sm font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                >
                  {{ $t("Box Calculation Reference") }}
                </label>
              </div>

              <select
                :value="box.calc_ref || ''"
                class="input w-full rounded border-orange-500 p-2 focus:border-orange-600 focus:ring-orange-200 dark:border-orange-600 dark:bg-gray-700 dark:text-white"
                @change="updateBoxCalcRef(box, $event.target.value)"
              >
                <option value="">{{ $t("Select calculation reference...") }}</option>
                <option value="other">{{ $t("none") }}</option>
                <option value="format">{{ $t("format") }}</option>
                <option value="weight">{{ $t("weight") }}</option>
                <option value="material">{{ $t("material") }}</option>
                <option value="printing_colors">{{ $t("printing colors") }}</option>
                <option value="lamination">{{ $t("lamination") }}</option>
                <option value="pages">{{ $t("pages") }}</option>
                <option v-if="selected_category?.price_build?.full_calculation" value="sides">
                  {{ $t("sides") }}
                </option>
                <option v-if="selected_category?.price_build?.full_calculation" value="cover">
                  {{ $t("cover") }}
                </option>
                <option
                  v-if="selected_category?.price_build?.full_calculation"
                  value="binding_direction"
                >
                  {{ $t("binding direction") }}
                </option>
                <option
                  v-if="selected_category?.price_build?.full_calculation"
                  value="binding_method"
                >
                  {{ $t("binding method") }}
                </option>
                <option
                  v-if="selected_category?.price_build?.full_calculation"
                  value="binding_color"
                >
                  {{ $t("binding color") }}
                </option>
                <option
                  v-if="selected_category?.price_build?.full_calculation"
                  value="binding_material"
                >
                  {{ $t("binding material") }}
                </option>
                <option v-if="selected_category?.price_build?.full_calculation" value="folding">
                  {{ $t("folding") }}
                </option>
                <option v-if="selected_category?.price_build?.full_calculation" value="endpapers">
                  {{ $t("endpapers") }}
                </option>
              </select>

              <!-- Missing Requirements Warning for Box -->
              <div
                v-if="
                  selected_category?.price_build?.full_calculation &&
                  (!box.calc_ref || box.calc_ref === '')
                "
                class="mt-2 flex items-start gap-2 rounded border border-amber-300 bg-amber-50 p-2 dark:border-amber-700 dark:bg-amber-900/20"
              >
                <font-awesome-icon
                  :icon="['fal', 'triangle-exclamation']"
                  class="mt-0.5 text-amber-600 dark:text-amber-400"
                />
                <span class="text-xs text-amber-800 dark:text-amber-200">
                  {{ $t("Calculation reference is required for full calculation products") }}
                </span>
              </div>
            </div>

            <!-- Options List -->
            <div v-if="box.ops && box.ops.length > 0" class="space-y-3">
              <h4
                class="text-sm font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              >
                {{ $t("Options") }}
              </h4>

              <div
                v-for="(option, optionIndex) in box.ops"
                :key="option.id || optionIndex"
                class="rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-750"
              >
                <!-- Option Header -->
                <div class="mb-3 flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <div
                      class="dark:bg-theme-900/30 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-theme-100"
                    >
                      <font-awesome-icon
                        :icon="['fal', 'list']"
                        class="text-sm text-theme-600 dark:text-theme-400"
                      />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">
                      {{ $display_name(option.display_name) || option.name }}
                    </span>
                  </div>

                  <!-- Inherited calc_ref badge -->
                  <div
                    v-if="box.calc_ref && !option.additional?.override_calc_ref"
                    class="flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                  >
                    <font-awesome-icon :icon="['fal', 'arrow-down']" class="text-xs" />
                    <span>{{ $t("Inherits") }}: {{ $t(box.calc_ref) }}</span>
                  </div>
                </div>

                <!-- Display inherited calc_ref and override option -->
                <div
                  class="mb-3 rounded-md border border-blue-200 bg-blue-50/50 p-3 dark:border-blue-800 dark:bg-blue-900/10"
                >
                  <div class="mb-2 flex items-start justify-between">
                    <div>
                      <label
                        class="mb-1 block text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                      >
                        {{ $t("Calculation Reference") }}
                      </label>
                      <p class="text-xs text-gray-600 dark:text-gray-400">
                        <template v-if="!option.additional?.override_calc_ref">
                          {{
                            box.calc_ref
                              ? $t("Inheriting from box") + ": " + $t(box.calc_ref)
                              : $t("No calc_ref set on box")
                          }}
                        </template>
                        <template v-else>
                          {{ $t("Using custom override") }}
                        </template>
                      </p>
                    </div>

                    <!-- Override toggle -->
                    <button
                      type="button"
                      class="rounded-md px-3 py-1 text-xs font-medium transition-colors"
                      :class="
                        option.additional?.override_calc_ref
                          ? 'bg-orange-500 text-white hover:bg-orange-600'
                          : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                      "
                      @click="toggleOverrideCalcRef(box, option)"
                    >
                      {{
                        option.additional?.override_calc_ref
                          ? $t("Remove Override")
                          : $t("Override")
                      }}
                    </button>
                  </div>

                  <!-- Override calc_ref selector -->
                  <div v-if="option.additional?.override_calc_ref" class="mt-2 space-y-3">
                    <!-- Reference Type Selector -->
                    <div>
                      <label
                        class="mb-1 block text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                      >
                        {{ $t("reference type") }}
                      </label>
                      <select
                        :value="option.additional?.calc_ref_type || ''"
                        class="input w-full rounded border-orange-500 p-2 text-sm focus:border-orange-600 focus:ring-orange-200 dark:border-orange-600 dark:bg-gray-700 dark:text-white"
                        @change="updateOptionCalcRefType(box, option, $event.target.value)"
                      >
                        <option value="">{{ $t("None") }}</option>
                        <option value="main">{{ $t("Main reference") }}</option>
                        <option value="binding_type">{{ $t("Binding Type") }}</option>
                        <option value="binding_direction">{{ $t("Binding Direction") }}</option>
                      </select>
                    </div>

                    <!-- Main Reference -->
                    <div v-if="option.additional?.calc_ref_type === 'main'">
                      <label
                        class="mb-1 block text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                      >
                        {{ $t("main reference") }}
                      </label>
                      <select
                        :value="option.additional?.calc_ref || ''"
                        class="input w-full rounded border-orange-500 p-2 text-sm focus:border-orange-600 focus:ring-orange-200 dark:border-orange-600 dark:bg-gray-700 dark:text-white"
                        @change="updateOptionCalcRef(box, option, $event.target.value)"
                      >
                        <option value="none">{{ $t("None") }}</option>
                        <option value="format">{{ $t("Format") }}</option>
                        <option value="material">{{ $t("Material") }}</option>
                        <option value="weight">{{ $t("Weight") }}</option>
                        <option value="printing_colors">{{ $t("Printing colors") }}</option>
                      </select>
                    </div>

                    <!-- Binding Type -->
                    <div v-if="option.additional?.calc_ref_type === 'binding_type'">
                      <label
                        class="mb-1 block text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                      >
                        {{ $t("binding type") }}
                      </label>
                      <select
                        :value="option.additional?.calc_ref || ''"
                        class="input w-full rounded border-orange-500 p-2 text-sm focus:border-orange-600 focus:ring-orange-200 dark:border-orange-600 dark:bg-gray-700 dark:text-white"
                        @change="updateOptionCalcRef(box, option, $event.target.value)"
                      >
                        <option value="">{{ $t("Select binding type...") }}</option>
                        <option value="saddle_stitch">{{ $t("Saddle Stitch") }}</option>
                        <option value="perfect_bound">{{ $t("Perfect Bound") }}</option>
                        <option value="case_bound">{{ $t("Case Bound") }}</option>
                        <option value="spiral_bound">{{ $t("Spiral Bound") }}</option>
                        <option value="wire_o">{{ $t("Wire-O") }}</option>
                        <option value="comb_bound">{{ $t("Comb Bound") }}</option>
                        <option value="section_sewn">{{ $t("Section Sewn") }}</option>
                        <option value="lay_flat">{{ $t("Lay Flat") }}</option>
                        <option value="thermal_binding">{{ $t("Thermal Binding") }}</option>
                        <option value="tape_binding">{{ $t("Tape Binding") }}</option>
                        <option value="coptic_stitch">{{ $t("Coptic Stitch") }}</option>
                        <option value="stab_binding">{{ $t("Stab Binding") }}</option>
                        <option value="pamphlet">{{ $t("Pamphlet") }}</option>
                        <option value="accordion_fold">{{ $t("Accordion Fold") }}</option>
                      </select>
                    </div>

                    <!-- Binding Direction -->
                    <div v-if="option.additional?.calc_ref_type === 'binding_direction'">
                      <label
                        class="mb-1 block text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300"
                      >
                        {{ $t("binding direction") }}
                      </label>
                      <select
                        :value="option.additional?.calc_ref || ''"
                        class="input w-full rounded border-orange-500 p-2 text-sm focus:border-orange-600 focus:ring-orange-200 dark:border-orange-600 dark:bg-gray-700 dark:text-white"
                        @change="updateOptionCalcRef(box, option, $event.target.value)"
                      >
                        <option value="">{{ $t("Select binding direction...") }}</option>
                        <option value="left">{{ $t("binding on left side") }}</option>
                        <option value="top">{{ $t("binding on top") }}</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Options Message -->
            <div
              v-else
              class="rounded-md border border-dashed border-gray-300 bg-gray-50 p-6 text-center dark:border-gray-600 dark:bg-gray-750"
            >
              <font-awesome-icon
                :icon="['fal', 'inbox']"
                class="mb-2 text-3xl text-gray-400 dark:text-gray-500"
              />
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{
                  $t(
                    "This box has no options. Add options in the 'Edit Boxes & Options' step to configure calculation references for them.",
                  )
                }}
              </p>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="flex flex-col items-center justify-center rounded-md border-2 border-dashed border-gray-300 bg-gray-50 p-12 dark:border-gray-700 dark:bg-gray-800"
    >
      <font-awesome-icon
        :icon="['fal', 'cube']"
        class="mb-4 text-6xl text-gray-400 dark:text-gray-600"
      />
      <h3 class="mb-2 text-xl font-bold text-gray-700 dark:text-gray-300">
        {{ $t("No Boxes Available") }}
      </h3>
      <p class="text-gray-500 dark:text-gray-400">
        {{ $t("Please configure boxes and options in the previous step") }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useProductWizardStore } from "@/stores/productWizard";

const emit = defineEmits(["step-completed", "step-validated"]);

// Composables
const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();
const productWizardStore = useProductWizardStore();

// Local state
const expandedBoxes = ref([]);
const loading = ref(false);
const localBoops = ref([]);

// Computed properties for data access (dual store pattern)
const selected_category = computed(() => {
  const categoryData = productWizardStore.getStepData("categorySearch");
  return (
    categoryData?.selected_category ||
    categoryData?.selectedCategory ||
    categoryData?.category ||
    {}
  );
});

const selected_boops = computed(() => {
  // First check if we have local modifications
  if (localBoops.value && localBoops.value.length > 0) {
    return localBoops.value;
  }

  // Otherwise get from wizard store
  const calcRefsData = productWizardStore.getStepData("calcReferences");
  const boopsData = productWizardStore.getStepData("editBoops");

  return calcRefsData?.boops || boopsData?.selectedBoops || [];
});

// Validation
const isStepValid = computed(() => {
  if (!selected_boops.value || selected_boops.value.length === 0) {
    return false;
  }

  // For full calculation products, ensure all boxes have calc_ref assigned
  if (selected_category.value?.price_build?.full_calculation) {
    const allBoxesHaveCalcRef = selected_boops.value.every(
      (box) => box.calc_ref && box.calc_ref !== "",
    );
    return allBoxesHaveCalcRef && !loading.value;
  }

  // For other products, step is always valid
  return !loading.value;
});

// Methods
function toggleBox(boxId) {
  const index = expandedBoxes.value.indexOf(boxId);
  if (index > -1) {
    expandedBoxes.value.splice(index, 1);
  } else {
    expandedBoxes.value.push(boxId);
  }
}

function updateBoxCalcRef(box, value) {
  // Find the box in localBoops and update it there (ensures reactivity)
  const boxIndex = localBoops.value.findIndex((b) => b.id === box.id);
  if (boxIndex > -1) {
    // Create a new object to ensure Vue detects the change
    localBoops.value[boxIndex] = {
      ...localBoops.value[boxIndex],
      calc_ref: value,
    };

    localBoops.value[boxIndex].ops.forEach((option, index) => {
      localBoops.value[boxIndex].ops[index].additional = {
        calc_ref: value,
        calc_ref_type: value === "other" ? "none" : "main",
      };
    });
  }

  // Save to wizard store
  saveCalcReferencesToStore();

  // Re-validate
  validateStep();
}

function updateOptionCalcRefType(box, option, value) {
  // Initialize additional object if it doesn't exist
  if (!option.additional) {
    option.additional = {};
  }

  option.additional.calc_ref_type = value;

  // Clear calc_ref when changing type
  option.additional.calc_ref = null;

  // Update local boops
  updateLocalBoops(box, option);

  // Save to wizard store
  saveCalcReferencesToStore();
}

function updateOptionCalcRef(box, option, value) {
  // Initialize additional object if it doesn't exist
  if (!option.additional) {
    option.additional = {};
  }

  option.additional.calc_ref = value;

  // Update local boops
  updateLocalBoops(box, option);

  // Save to wizard store
  saveCalcReferencesToStore();
}

function toggleOverrideCalcRef(box, option) {
  // Initialize additional object if it doesn't exist
  if (!option.additional) {
    option.additional = {};
  }

  // Toggle the override flag
  option.additional.override_calc_ref = !option.additional.override_calc_ref;

  // If disabling override, clear the additional properties
  if (!option.additional.override_calc_ref) {
    option.additional.calc_ref_type = null;
    option.additional.calc_ref = null;
  }

  // Update local boops
  updateLocalBoops(box, option);

  // Save to wizard store
  saveCalcReferencesToStore();
}

function updateLocalBoops(box, option) {
  const boxIndex = localBoops.value.findIndex((b) => b.id === box.id);
  if (boxIndex > -1) {
    const optionIndex = localBoops.value[boxIndex].ops.findIndex((o) => o.id === option.id);
    if (optionIndex > -1) {
      localBoops.value[boxIndex].ops[optionIndex] = { ...option };
    }
  }
}

function saveCalcReferencesToStore() {
  productWizardStore.updateStepData("calcReferences", {
    boops: localBoops.value,
    lastUpdated: Date.now(),
  });
  productWizardStore.isDirty = true;
}

function validateStep() {
  const valid = isStepValid.value;

  emit("step-validated", {
    isValid: valid,
    canProceed: valid,
    valid: valid,
    loading: loading.value,
  });
}

// Called by wizard when Next button is clicked
async function goNext() {
  if (!isStepValid.value) {
    return;
  }

  loading.value = true;

  try {
    // Save calc references via API
    await saveCalcReferences();

    // Mark step as completed
    const currentStep = productWizardStore.currentStep;
    productWizardStore.completeStep(currentStep);
    emit("step-completed", currentStep);

    // Navigate to next step
    productWizardStore.goToNextStep();
  } catch (error) {
    console.error("Error saving calculation references:", error);
    handleError(error);
  } finally {
    loading.value = false;
    validateStep();
  }
}

async function saveCalcReferences() {
  if (!selected_category.value?.slug) {
    throw new Error("No category selected");
  }

  try {
    // Save all boxes and options in parallel
    const savePromises = [];

    for (const box of localBoops.value) {
      savePromises.push(api.put(`boxes/${box.slug}`, box));

      for (const option of box.ops) {
        savePromises.push(
          api.put(`categories/${selected_category.value.id}/options/${option.id}`, option),
        );
      }
    }

    await Promise.all(savePromises);
  } catch (error) {
    console.error("Error saving calculation references:", error);
    handleError(error);
    throw error; // Re-throw to prevent inconsistent state
  }

  const response = await api.put(`categories/${selected_category.value.slug}/boops`, {
    id: selected_category.value.id,
    name: selected_category.value.name,
    slug: selected_category.value.slug,
    boops: localBoops.value,
    divided: selected_category.value.divided || false,
  });

  handleSuccess(response);

  // Update wizard store with the saved data
  productWizardStore.updateStepData("calcReferences", {
    boops: localBoops.value,
    lastUpdated: Date.now(),
    saved: true,
  });

  // Also update editBoops store to keep them in sync
  productWizardStore.updateStepData("editBoops", {
    selectedBoops: localBoops.value,
    lastUpdated: Date.now(),
  });

  return response;
}

// Lifecycle
onMounted(() => {
  // Initialize local boops from wizard store
  const calcRefsData = productWizardStore.getStepData("calcReferences");
  const boopsData = productWizardStore.getStepData("editBoops");

  localBoops.value = JSON.parse(
    JSON.stringify(calcRefsData?.boops || boopsData?.selectedBoops || []),
  );

  // Initialize step data
  if (!calcRefsData || Object.keys(calcRefsData).length === 0) {
    productWizardStore.updateStepData("calcReferences", {
      boops: localBoops.value,
      lastUpdated: Date.now(),
    });
  }

  // Expand first box by default
  if (localBoops.value.length > 0) {
    expandedBoxes.value.push(localBoops.value[0].id || 0);
  }

  // Initial validation
  validateStep();
});

// Expose goNext method for parent
defineExpose({ goNext });
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
