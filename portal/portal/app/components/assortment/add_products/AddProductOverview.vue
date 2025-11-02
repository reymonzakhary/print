<template>
  <section class="flex min-h-full flex-col p-4">
    <!-- Header -->
    <div class="mb-8 text-center">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ $t("Choose Your Starting Point") }}
      </h1>
      <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
        {{ $t("Select how you'd like to create your product assortment") }}
      </p>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-6xl flex-1">
      <!-- Product Type Options -->
      <div class="grid gap-6 md:grid-cols-3">
        <!-- Prindustry Preset -->
        <button
          @click="selectPreset()"
          :class="[
            'group relative cursor-pointer rounded-lg border-2 p-8 transition-all duration-300',
            selectedOption === 'preset'
              ? 'border-prindustry-500 bg-prindustry-50 shadow-lg dark:bg-prindustry-900'
              : 'border-gray-200 bg-white hover:border-prindustry-300 hover:shadow-md dark:border-gray-900 dark:bg-gray-800 dark:hover:border-prindustry-700',
          ]"
        >
          <!-- Icon -->
          <div class="mb-6 flex justify-center">
            <div
              :class="[
                'flex h-20 w-20 items-center justify-center rounded-full',
                selectedOption === 'preset'
                  ? 'bg-prindustry-100 text-prindustry-600 dark:bg-prindustry-800/30 dark:text-prindustry-400'
                  : 'bg-prindustry-50 text-prindustry-500 group-hover:bg-prindustry-100 dark:bg-prindustry-900 dark:text-prindustry-400 dark:group-hover:bg-prindustry-800',
              ]"
            >
              <font-awesome-icon :icon="['fal', 'box-full']" class="text-3xl" />
            </div>
          </div>

          <!-- Content -->
          <div class="text-center">
            <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
              {{ $t("Prindustry Preset") }}
            </h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
              {{
                $t(
                  "Start with pre-configured products from our standard catalog with optimized boxes, options, and pricing.",
                )
              }}
            </p>

            <!-- Features List -->
            <div class="space-y-2 text-left text-xs text-gray-500 dark:text-gray-400">
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("Pre-built product structures") }}
              </div>
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("Optimized pricing models") }}
              </div>
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("Tested configurations") }}
              </div>
            </div>
          </div>

          <!-- Selection Indicator -->
          <div
            v-if="selectedOption === 'preset'"
            class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-theme-500 text-white"
          >
            <font-awesome-icon :icon="['fas', 'check']" class="text-sm" />
          </div>
        </button>

        <!-- External Producer -->
        <button
          @click="selectProducer()"
          :class="[
            'group relative rounded-lg border-2 p-8 transition-all duration-300',
            selectedOption === 'producer'
              ? 'border-purple-500 bg-purple-50 shadow-lg dark:bg-purple-900'
              : disabled
                ? 'cursor-not-allowed border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-800'
                : 'border-gray-200 bg-white hover:border-purple-300 hover:shadow-md dark:border-gray-900 dark:bg-gray-800',
          ]"
          disabled
        >
          <!-- Icon -->
          <div class="mb-6 flex justify-center">
            <div
              :class="[
                'flex h-20 w-20 items-center justify-center rounded-full',
                selectedOption === 'producer'
                  ? 'bg-purple-100 text-purple-600 dark:bg-purple-800/30 dark:text-purple-400'
                  : 'bg-purple-50 text-purple-500 group-hover:bg-purple-100 dark:bg-purple-900 dark:text-purple-400 dark:group-hover:bg-purple-800',
              ]"
            >
              <font-awesome-icon :icon="['fal', 'parachute-box']" class="text-3xl" />
            </div>
          </div>

          <!-- Content -->
          <div class="text-center">
            <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
              {{ $t("External Producer") }}
            </h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
              {{
                $t(
                  "Import product configurations from an external producer or supplier with their specifications.",
                )
              }}
            </p>

            <!-- Development Badge -->
            <div class="mb-4 flex items-center justify-center">
              <span
                class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
              >
                <font-awesome-icon :icon="['fas', 'code']" class="mr-1" />
                {{ $t("In Development") }}
              </span>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-500">
              {{ $t("Feature in development") }}
            </p>
          </div>
        </button>

        <!-- Start Blank -->
        <button
          @click="selectBlank()"
          :class="[
            'group relative cursor-pointer rounded-lg border-2 p-8 transition-all duration-300',
            selectedOption === 'blank'
              ? 'border-theme-500 bg-theme-50 shadow-lg dark:bg-theme-900'
              : 'border-gray-200 bg-white hover:border-theme-300 hover:shadow-md dark:border-gray-900 dark:bg-gray-800 dark:hover:border-theme-700',
          ]"
        >
          <!-- Icon -->
          <div class="mb-6 flex justify-center">
            <div
              :class="[
                'flex h-20 w-20 items-center justify-center rounded-full',
                selectedOption === 'blank'
                  ? 'bg-theme-100 text-theme-600 dark:bg-theme-800 dark:text-theme-400'
                  : 'bg-theme-50 text-theme-500 group-hover:bg-theme-100 dark:bg-theme-900 dark:text-theme-400 dark:group-hover:bg-theme-800',
              ]"
            >
              <font-awesome-icon :icon="['fal', 'box-open']" class="text-3xl" />
            </div>
          </div>

          <!-- Content -->
          <div class="text-center">
            <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
              {{ $t("Start Blank") }}
            </h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
              {{
                $t(
                  "Build everything from scratch with complete control over boxes, options, dividers, and pricing.",
                )
              }}
            </p>

            <!-- Features List -->
            <div class="space-y-2 text-left text-xs text-gray-500 dark:text-gray-400">
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("Complete customization") }}
              </div>
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("No pre-configurations") }}
              </div>
              <div class="flex items-center">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2 text-green-500" />
                {{ $t("Full creative control") }}
              </div>
            </div>
          </div>

          <!-- Selection Indicator -->
          <div
            v-if="selectedOption === 'blank'"
            class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 text-white"
          >
            <font-awesome-icon :icon="['fas', 'check']" class="text-sm" />
          </div>
        </button>
      </div>

      <!-- What Happens Next Section -->
      <div class="mt-12 rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
        <div class="flex items-start">
          <div
            class="mr-4 flex size-8 items-center justify-center rounded-full bg-blue-500 px-4 text-white"
          >
            <font-awesome-icon :icon="['fas', 'info']" class="text-xs" fixed-width />
          </div>
          <div>
            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
              {{ $t("What happens next?") }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ getNextStepDescription() }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, watch } from "vue";
import { useProductWizardStore } from "@/stores/productWizard";
import { disabled } from "happy-dom/lib/PropertySymbol.js";

const { t: $t } = useI18n();
const productWizardStore = useProductWizardStore();

const emit = defineEmits(["step-completed", "next-step", "step-validated"]);

// Data
const selectedOption = ref(null);

// Watch for selection changes to validate step
watch(
  selectedOption,
  (newValue) => {
    // Update step progress in store
    productWizardStore.updateStepProgress(1, "productOrigin", !!newValue);

    // Emit validation status to parent
    emit("step-validated", {
      stepNumber: 1,
      isValid: !!newValue,
      canProceed: !!newValue,
    });

    // If option is selected, complete the step
    if (newValue) {
      emit("step-completed", 1);
    }
  },
  { immediate: true },
);

// Methods
const selectPreset = () => {
  selectedOption.value = "preset";
  productWizardStore.setProductType("print");
  productWizardStore.updateStepData("productOrigin", {
    type: "preset",
    source: "prindustry_standard",
  });
};

const selectProducer = () => {
  selectedOption.value = "producer";
  productWizardStore.setProductType("print");
  productWizardStore.updateStepData("productOrigin", {
    type: "producer",
    source: "external_producer",
  });

  console.log("AddProductOverview - Producer flow selected, available steps will be: 1, 3, 4, 5+");
};

const selectBlank = () => {
  selectedOption.value = "blank";
  productWizardStore.setProductType("print");
  productWizardStore.updateStepData("productOrigin", {
    type: "blank",
    source: "custom",
  });
};

// Get dynamic description based on selection
const getNextStepDescription = () => {
  if (!selectedOption.value) {
    return $t("Select an option above to see what happens next in your product creation journey.");
  }

  switch (selectedOption.value) {
    case "preset":
      return $t(
        "You'll continue with the current flow, using category-based product templates. This includes pre-configured boxes, options, and pricing models that have been tested and proven effective.",
      );

    case "blank":
      return $t(
        "You'll start with a completely blank canvas where you can build your product structure from the ground up. This gives you full control over every aspect including boxes, options, excludes, and pricing strategies.",
      );

    case "producer":
      return $t(
        "You'll be able to import existing product configurations from external producers or suppliers, maintaining their specifications while adapting them to your business needs.",
      );

    default:
      return $t(
        "Select an option above to see what happens next in your product creation journey.",
      );
  }
};
</script>
