<template>
  <div class="h-full overflow-auto p-4">
    <!-- Step Header -->
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t("Calculation Settings") }}
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        {{ $t("Configure pricing calculation methods for your product") }}
      </p>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-5xl space-y-6">
      <!-- Starting Costs Section - Full Width -->
      <div
        class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex flex-col items-start gap-6 md:flex-row md:items-center md:justify-between">
          <div class="flex items-center gap-3">
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
            >
              <font-awesome-icon
                :icon="['fal', 'coins']"
                class="text-lg text-green-600 dark:text-green-400"
              />
            </div>
            <div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $t("startingcosts") }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{
                  $t(
                    "if this categories price calculation needs additional startcosts, you can add them here.",
                  )
                }}
              </p>
            </div>
          </div>

          <div class="w-full md:w-auto md:min-w-[300px]">
            <UICurrencyInput
              v-model="selectedCategory.start_cost"
              input-class="w-full rounded-md border border-green-500 px-4 py-3 text-lg font-semibold transition focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :options="{
                precision: 5,
              }"
            />
          </div>
        </div>
      </div>

      <!-- Row 2: Calculation Type & Production Schedule (2 columns) -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Calculation Type Section with integrated settings -->
        <div
          class="rounded-md border-2 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="border-b border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div
                class="dark:bg-theme-900/30 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-theme-100"
              >
                <font-awesome-icon
                  :icon="['fal', 'calculator-simple']"
                  class="text-lg text-theme-600 dark:text-theme-400"
                />
              </div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $t("calculation type") }}
              </h3>
            </div>
          </div>

          <div class="p-6">
            <CalculationType
              :price-build="selectedCategory.price_build"
              @update_price_build="updatePriceBuild"
            />
          </div>

          <!-- Calculation Display Method (Semi-calculation only) -->
          <transition name="fade">
            <div
              v-if="selectedCategory.price_build && !selectedCategory.price_build.full_calculation"
              class="border-t border-gray-200 bg-blue-50 p-6 dark:border-gray-700 dark:bg-blue-900/10"
            >
              <div class="mb-4 flex items-center gap-3">
                <div
                  class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30"
                >
                  <font-awesome-icon
                    :icon="['fal', 'chart-area']"
                    class="text-blue-600 dark:text-blue-400"
                  />
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ $t("calculation display method") }}
                </h4>
              </div>

              <CalculationDisplayMethod
                :item="selectedCategory.calculation_method"
                @update_calculation_method="updateCalculationMethod"
              />
            </div>
          </transition>

          <!-- Machine Selection (Full-calculation only) -->
          <transition name="fade">
            <div
              v-if="selectedCategory.price_build && selectedCategory.price_build.full_calculation"
              class="border-t border-gray-200 bg-purple-50 p-6 dark:border-gray-700 dark:bg-purple-900/10"
            >
              <div class="mb-4 flex items-center gap-3">
                <div
                  class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30"
                >
                  <font-awesome-icon
                    :icon="['fal', 'print']"
                    class="text-purple-600 dark:text-purple-400"
                  />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $t("machine") }}
                  </h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $t("select machine(s) to calculate prices with for this category") }}
                  </p>
                </div>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(catMachine, index) in selectedCategory.additional"
                  :key="catMachine.machine"
                  class="flex items-center gap-3"
                >
                  <v-select
                    v-model="selectedCategory.additional[index].machine"
                    :reduce="(machine) => machine.id"
                    :options="machines"
                    label="name"
                    class="input flex-1 rounded-md !bg-white !py-0 px-1 text-sm !text-theme-900 dark:!bg-gray-700 dark:!shadow-gray-300"
                    :class="`z-[${index}]`"
                  />

                  <button
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md text-red-600 transition hover:bg-red-100 dark:hover:bg-red-900/30"
                    :title="$t('Remove machine')"
                    @click="removeMachine(catMachine.machine)"
                  >
                    <font-awesome-icon :icon="['fal', 'trash']" />
                  </button>
                </div>

                <button
                  class="flex w-full items-center justify-center gap-2 rounded-md border-2 border-dashed border-purple-300 bg-white px-4 py-3 text-sm font-medium text-purple-600 transition hover:border-purple-500 hover:bg-purple-100 dark:border-purple-700 dark:bg-gray-800 dark:text-purple-400 dark:hover:border-purple-500 dark:hover:bg-purple-900/30"
                  @click="addMachine($event)"
                >
                  <font-awesome-icon :icon="['fal', 'plus']" />
                  <font-awesome-icon :icon="['fal', 'print']" />
                  {{ $t("add machine") }}
                </button>
              </div>
            </div>
          </transition>
        </div>

        <!-- Production Schedule -->
        <div
          class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="mb-4 flex items-center gap-3">
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30"
            >
              <font-awesome-icon
                :icon="['fal', 'calendar-circle-exclamation']"
                class="text-lg text-orange-600 dark:text-orange-400"
              />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
              {{ $t("production schedule") }}
            </h3>
          </div>

          <div
            class="rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900"
          >
            <CategoryProductionSchedule
              :production-days="selectedCategory.production_days"
              @on-update-production-days="updateProductionDays"
            />
          </div>
        </div>
      </div>

      <!-- Production Schedule Costs (Semi-calculation only) - Full Width -->
      <transition name="fade">
        <div
          v-if="selectedCategory.price_build && selectedCategory.price_build.semi_calculation"
          class="rounded-md border-2 border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="mb-4 flex items-center gap-3">
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
            >
              <font-awesome-icon
                :icon="['fal', 'dollar-sign']"
                class="text-lg text-amber-600 dark:text-amber-400"
              />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
              {{ $t("extra cost for production days") }}
            </h3>
          </div>

          <div
            class="rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900"
          >
            <CategoryProductionScheduleCosts
              :production-dlv="selectedCategory.production_dlv"
              @on-update-production-dlv="updateProductionDlv"
            />
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useStore } from "vuex";
import { useProductWizardStore } from "@/stores/productWizard";

// Import components
import CalculationType from "@/components/assortment/overview/edit/edit_categories/CalculationType.vue";
import CalculationDisplayMethod from "@/components/assortment/overview/edit/CalculationDisplayMethod.vue";
import CategoryProductionSchedule from "@/components/assortment/overview/edit/edit_categories/CategoryProductionSchedule.vue";
import CategoryProductionScheduleCosts from "@/components/assortment/overview/edit/edit_categories/categoryProductionScheduleCosts.vue";

const { t: $t } = useI18n();
const emit = defineEmits(["step-completed", "step-validated"]);

// Store access (both Vuex and Pinia for backwards compatibility)
const vuexStore = useStore();
const productWizardStore = useProductWizardStore();

// Get category from either store
const selectedCategory = computed(() => {
  // Try new Pinia store first
  const piniaCategory =
    productWizardStore.stepData?.categorySearch?.selected_category ||
    productWizardStore.selected_category;

  // Fallback to Vuex store
  const vuexCategory = vuexStore.state.product_wizard?.selected_category;

  return piniaCategory || vuexCategory || {};
});

// Machines data
const machines = ref([]);

// Load machines from API
const api = useAPI();
const { handleSuccess } = useMessageHandler();
onMounted(async () => {
  try {
    const response = await api.get("/machines");
    machines.value = response.data || [];
  } catch (error) {
    console.error("Failed to load machines:", error);
  }

  // Mark step as valid on mount if data exists
  validateStep();
});

// Methods
const updateCalculationMethod = (method) => {
  selectedCategory.value.calculation_method = method;
  validateStep();
};

const updatePriceBuild = (priceBuild) => {
  selectedCategory.value.price_build = priceBuild;
  validateStep();
};

const updateProductionDays = (days) => {
  selectedCategory.value.production_days = days;
  validateStep();
};

const updateProductionDlv = (dlv) => {
  selectedCategory.value.production_dlv = dlv;
  validateStep();
};

const addMachine = (event) => {
  event.preventDefault();
  if (!selectedCategory.value.additional) {
    selectedCategory.value.additional = [];
  }
  selectedCategory.value.additional.push({ machine: null });
  validateStep();
};

const removeMachine = (machineId) => {
  if (selectedCategory.value.additional) {
    selectedCategory.value.additional = selectedCategory.value.additional.filter(
      (item) => item.machine !== machineId,
    );
  }
  validateStep();
};

const capitalizeFirstLetter = (string) => {
  if (!string) return "";
  return string.charAt(0).toUpperCase() + string.slice(1);
};

// Validation
const validateStep = () => {
  // Basic validation: check if category has price_build configured
  const isValid = !!(selectedCategory.value && selectedCategory.value.price_build);

  emit("step-validated", {
    stepNumber: 9,
    isValid,
    canProceed: isValid,
  });

  return isValid;
};

// Watch for changes to auto-validate
watch(
  () => selectedCategory.value,
  () => {
    validateStep();
  },
  { deep: true },
);

// Method called by parent when Next is clicked
async function goNext() {
  if (validateStep()) {
    await api.put(`categories/${selectedCategory.value.slug}`, selectedCategory.value);
    handleSuccess({ message: "Category calculation saved successfully" });
    emit("step-completed", {
      stepNumber: 9,
      data: {
        calculation: {
          start_cost: selectedCategory.value.start_cost,
          price_build: selectedCategory.value.price_build,
          calculation_method: selectedCategory.value.calculation_method,
          additional: selectedCategory.value.additional,
          production_days: selectedCategory.value.production_days,
          production_dlv: selectedCategory.value.production_dlv,
        },
      },
    });
    productWizardStore.goToNextStep();
  }
}

defineExpose({ goNext });
</script>
