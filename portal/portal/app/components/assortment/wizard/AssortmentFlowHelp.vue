<template>
  <aside
    class="w-80 overflow-y-auto border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
  >
    <div class="p-6">
      <!-- Header -->
      <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
          <font-awesome-icon :icon="['fal', 'info-circle']" class="text-blue-500" />
          {{ $t("Help & Tips") }}
        </h3>
      </div>

      <!-- Current step help -->
      <div class="mb-6">
        <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ getStepTitle(currentStep) }}
        </h4>
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
          {{ getStepDescription(currentStep) }}
        </p>
      </div>

      <div v-if="getStepTips(currentStep).length > 0" class="mb-6">
        <div
          class="rounded-lg border border-blue-200 bg-white p-4 shadow-sm dark:border-blue-700 dark:bg-blue-900/30"
        >
          <div class="mb-3 flex items-center gap-2">
            <font-awesome-icon :icon="['fal', 'route']" class="text-blue-500" />
            <h5 class="text-sm font-semibold text-blue-900 dark:text-blue-200">
              {{ $t("Step Guide") }}
            </h5>
          </div>
          <ol
            class="list-inside list-decimal space-y-2 pl-4 text-xs text-blue-900 dark:text-blue-100"
          >
            <li v-for="(tip, index) in getStepTips(currentStep)" :key="index" class="relative">
              <span class="ml-1">{{ tip }}</span>
            </li>
          </ol>
        </div>
      </div>

      <!-- Step-specific contextual help -->
      <div v-if="getStepContextualHelp(currentStep)" class="mb-6 rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
        <div class="mb-3 flex items-center gap-2">
          <font-awesome-icon :icon="getStepContextualHelp(currentStep).icon" class="text-purple-500" />
          <h5 class="text-sm font-semibold text-purple-900 dark:text-purple-200">
            {{ $t(getStepContextualHelp(currentStep).title) }}
          </h5>
        </div>
        <div v-html="getStepContextualHelp(currentStep).content"></div>
      </div>

      <!-- Calculation Methods Guide (for step 9 only) -->
      <div v-if="currentStep === 9" class="mb-6 rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
        <div class="mb-3 flex items-center gap-2">
          <font-awesome-icon :icon="['fal', 'calculator']" class="text-purple-500" />
          <h5 class="text-sm font-semibold text-purple-900 dark:text-purple-200">
            {{ $t("Calculation Methods") }}
          </h5>
        </div>
        <div class="space-y-4">
          <!-- Full Calculation -->
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <font-awesome-icon :icon="['fal', 'function']" class="text-purple-500" />
              {{ $t("Full Calculation") }}
            </h6>
            <p class="mb-2 text-xs text-purple-800 dark:text-purple-200">
              {{ $t("Prices are automatically calculated based on:") }}
            </p>
            <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Material costs (paper, ink, etc.)") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Machine tick prices and setup costs") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Production time and labor costs") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Quantity-based economies of scale") }}</span>
              </li>
            </ul>
            <p class="mt-2 text-xs font-semibold text-purple-700 dark:text-purple-300">
              {{ $t("Requirements:") }}
            </p>
            <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Complete calc_ref data for all options") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Machine configuration and tick prices") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Material definitions with accurate costs") }}</span>
              </li>
            </ul>
            <p class="mt-2 text-xs italic text-purple-600 dark:text-purple-400">
              {{ $t("Best for: Complex print products with variable material and production costs") }}
            </p>
          </div>

          <!-- Semi Calculation / Pricing per Option -->
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <font-awesome-icon :icon="['fal', 'tags']" class="text-purple-500" />
              {{ $t("Semi Calculation / Pricing per Option") }}
            </h6>
            <p class="mb-2 text-xs text-purple-800 dark:text-purple-200">
              {{ $t("Manual price entry for each option with optional base calculations:") }}
            </p>
            <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Set fixed prices for each product option") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Optionally use base calculations as starting point") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("Full control over final pricing structure") }}</span>
              </li>
              <li class="flex items-start gap-1">
                <font-awesome-icon :icon="['fal', 'circle']" class="mt-1 text-[6px]" />
                <span>{{ $t("No complex calc_ref configuration required") }}</span>
              </li>
            </ul>
            <p class="mt-2 text-xs italic text-purple-600 dark:text-purple-400">
              {{ $t("Best for: Standard products, custom items, or when you want direct control over pricing") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Validation errors -->
      <div v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mb-6">
        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <div class="flex items-start gap-2">
            <font-awesome-icon :icon="['fal', 'exclamation-triangle']" class="mt-1 text-red-500" />
            <div>
              <h5 class="mb-2 text-sm font-semibold text-red-900 dark:text-red-200">
                {{ $t("Validation Issues") }}
              </h5>
              <ul class="space-y-1 text-xs text-red-800 dark:text-red-300">
                <li v-for="(errors, key) in validationErrors" :key="key">
                  {{ Array.isArray(errors) ? errors.join(", ") : errors }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- General tips -->
      <div class="space-y-3">
        <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ $t("Quick Tips") }}
        </h5>
        <div
          v-for="tip in generalTips"
          :key="tip"
          class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400"
        >
          <font-awesome-icon :icon="['fal', 'check']" class="mt-0.5 flex-shrink-0 text-green-500" />
          <span>{{ tip }}</span>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  currentStep: {
    type: Number,
    required: true,
  },
  productType: {
    type: String,
    default: null,
  },
  pricingMethod: {
    type: String,
    default: null,
  },
  validationErrors: {
    type: Object,
    default: () => ({}),
  },
  productDefinition: {
    type: Object,
    default: () => ({}),
  },
});

const { t: $t } = useI18n();

const getStepTitle = (step) => {
  const titles = {
    1: $t("Product Overview"),
    2: $t("Category Search"),
    3: $t("Category Configuration"),
    4: $t("Select Producer"),
    5: $t("Producer Category"),
    6: $t("Edit Boxes & Options"),
    7: $t("Manage Excludes"),
    8: $t("Product Manifest"),
    9: $t("Calculation"),
    10: $t("Pricing Tables"),
    11: $t("Margins"),
    12: $t("Calculation References"),
  };
  return titles[step] || $t("Step {number}", { number: step });
};

const getStepDescription = (step) => {
  const descriptions = {
    1: $t(
      "Start by selecting the type of product you want to create. Choose between Prindustry Preset, Producer, or Blank Category.",
    ),
    2: $t(
      "Search and select a category that best matches your product. This helps organize your products and apply relevant settings.",
    ),
    3: $t(
      "Configure category details including name, description, visibility, and calculation settings.",
    ),
    4: $t(
      "Choose the producer who will manufacture this product. You can select from existing producers or add a new one.",
    ),
    5: $t("Select the specific category within the producer's catalog that matches your product."),
    6: $t(
      "Configure the boxes and options that define your product's variations. Each box represents a product attribute (like size, color, etc.).",
    ),
    7: $t(
      "Set up option combinations that should not be available together. This prevents invalid product configurations.",
    ),
    8: $t("Review your product configuration and finalize all settings before saving."),
    9: $t(
      "Set up pricing calculation methods, starting costs, and production schedules for your product.",
    ),
    10: $t(
      "Configure tiered pricing tables with quantity ranges and incremental steps for your product.",
    ),
    11: $t(
      "Define profit margins and markup percentages to ensure proper pricing strategy.",
    ),
    12: $t(
      "Assign calculation references to boxes and their options. This links your product variations to the pricing calculation system.",
    ),
  };
  return descriptions[step] || $t("Complete this step to continue.");
};

const getStepTips = (step) => {
  const tips = {
    1: [
      $t("Prindustry Preset: Start with a pre-configured category template."),
      $t("Producer: Import a category directly from a producer's catalog."),
      $t("Blank Category: Build a completely custom category from scratch."),
    ],
    2: [
      $t("Use specific keywords to narrow down category search results."),
      $t("Check multiple categories if your product fits in more than one."),
      $t(
        "The name is a unique, internal identifier used for system integration and standardization across the Prindustry platform. It ensures consistent referencing of entities (like boxes or options) in APIs, automation, and data exchange between services. The display name, on the other hand, is what users see in the interface and should always be localized for each supported language. For user-facing text, always use the display name.",
      ),
    ],
    3: [
      $t("Set visibility to control where your category appears (finder, webshops, all)."),
      $t("Configure display names for all supported languages."),
      $t("Choose the appropriate calculation method for pricing."),
    ],
    4: [
      $t("Review producer ratings and reviews before making a selection."),
      $t("Consider producers with experience in similar products."),
    ],
    5: [
      $t("Ensure the selected category matches all key attributes of your product."),
      $t("Consult with the producer if unsure about category selection."),
    ],
    6: [
      $t("Define clear and distinct options for each box to avoid confusion."),
      $t("Use images or descriptions to illustrate option differences."),
      $t("Ensure all boxes have complete material, format, and printing color information."),
    ],
    7: [
      $t("Think through all possible option combinations to identify conflicts."),
      $t("Regularly update excludes as new options are added."),
    ],
    8: [
      $t("Double-check all settings before finalizing the product."),
      $t("Preview the product as it will appear to customers."),
      $t("Verify that all required fields have been completed."),
    ],
    9: [
      $t("Set starting costs to establish a baseline for pricing calculations."),
      $t("Choose between semi-calculation and full-calculation methods."),
      $t("Configure production schedules to set realistic delivery timeframes."),
    ],
    10: [
      $t("Define quantity ranges with appropriate incremental steps."),
      $t("Pricing tables are optional but help provide clear pricing structure."),
      $t("Consider your target market when setting quantity tiers."),
    ],
    11: [
      $t("Set appropriate profit margins based on your business model."),
      $t("Consider market competition when defining markup percentages."),
      $t("Review margins regularly to ensure profitability."),
    ],
    12: [
      $t("Assign calc_ref to boxes to identify what aspect they control (format, material, etc.)."),
      $t("For full calculation products, all boxes must have a calculation reference assigned."),
      $t("Options can have additional references for binding type, direction, or main references."),
      $t("Use consistent references across similar product categories."),
    ],
  };
  return tips[step] || [];
};

const getStepContextualHelp = (step) => {
  const contextualHelp = {
    1: {
      icon: ['fal', 'route'],
      title: 'Product Origin Types',
      content: `
        <div class="space-y-3">
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <i class="fa fa-star text-purple-500"></i>
              ${$t("Prindustry Preset")}
            </h6>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Start with a pre-configured category from Prindustry's catalog. Includes complete setup with boxes, options, and calculation references. Perfect for standard print products.")}
            </p>
          </div>
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <i class="fa fa-user-tie text-purple-500"></i>
              ${$t("Producer")}
            </h6>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Import a category directly from a producer's catalog. Uses the producer's pricing and configuration. Ideal for reselling or white-label products.")}
            </p>
          </div>
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <i class="fa fa-file-blank text-purple-500"></i>
              ${$t("Blank Category")}
            </h6>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Build a completely custom category from scratch. Full control over all settings, boxes, and pricing. Best for unique or specialized products.")}
            </p>
          </div>
        </div>
      `
    },
    2: {
      icon: ['fal', 'info-circle'],
      title: 'Understanding Names vs Display Names',
      content: `
        <div class="space-y-2">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("The system uses two types of identifiers:")}
          </p>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Name (Internal)")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("A unique identifier used for system integration, APIs, and data exchange. Never shown to end users.")}
            </p>
          </div>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Display Name (User-facing)")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("The text shown to users in the interface. Should always be localized for each supported language.")}
            </p>
          </div>
        </div>
      `
    },
    3: {
      icon: ['fal', 'sliders'],
      title: 'Category Information',
      content: `
        <div class="space-y-2">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("Configure the basic information for your category:")}
          </p>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Category Details")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Set the name, description, and visibility settings for your category.")}
            </p>
          </div>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Display Names")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Provide localized names for each language your platform supports. This ensures customers see the category in their preferred language.")}
            </p>
          </div>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Visibility")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Control where your category appears: Product Finder only, Webshops only, or everywhere.")}
            </p>
          </div>
        </div>
      `
    },
    6: {
      icon: ['fal', 'cube'],
      title: 'Boxes & Options Structure',
      content: `
        <div class="space-y-2">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("Each box represents a product attribute that customers can choose:")}
          </p>
          <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Format: Size dimensions (A4, A5, custom sizes)")}</span>
            </li>
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Material: Paper type, weight, finish")}</span>
            </li>
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Printing Colors: CMYK, Pantone, spot colors")}</span>
            </li>
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Finishing: Lamination, binding, folding")}</span>
            </li>
          </ul>
        </div>
      `
    },
    10: {
      icon: ['fal', 'table'],
      title: 'Pricing Tables',
      content: `
        <div class="space-y-2">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("Pricing tables define quantity-based pricing tiers for your product. They help customers understand bulk discounts and pricing structure.")}
          </p>
          <div class="rounded border border-purple-200 bg-white p-2 dark:border-purple-700 dark:bg-purple-950/30">
            <p class="text-xs font-semibold text-purple-900 dark:text-purple-100 mb-1">
              ${$t("Optional Configuration")}
            </p>
            <p class="text-xs text-purple-700 dark:text-purple-300">
              ${$t("Pricing tables are optional. You can skip this step if you don't need tiered pricing or if prices will be calculated dynamically.")}
            </p>
          </div>
        </div>
      `
    },
    11: {
      icon: ['fal', 'percent'],
      title: 'Profit Margins',
      content: `
        <div class="space-y-2">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("Margins ensure profitability by adding a percentage markup to your base costs. Consider:")}
          </p>
          <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Operating costs (overhead, labor, utilities)")}</span>
            </li>
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Market competition and positioning")}</span>
            </li>
            <li class="flex items-start gap-1">
              <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
              <span>${$t("Volume expectations and economies of scale")}</span>
            </li>
          </ul>
        </div>
      `
    },
    12: {
      icon: ['fal', 'calculator'],
      title: 'Calculation References',
      content: `
        <div class="space-y-3">
          <p class="text-xs text-purple-700 dark:text-purple-300">
            ${$t("Calculation references link your product configuration to the pricing engine. They tell the system how to interpret each box and option.")}
          </p>
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <i class="fa fa-cube text-purple-500"></i>
              ${$t("Box References")}
            </h6>
            <p class="text-xs text-purple-700 dark:text-purple-300 mb-2">
              ${$t("Each box should have a calc_ref that describes what it controls:")}
            </p>
            <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("format - Size/dimensions")}</span>
              </li>
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("material - Paper type/substrate")}</span>
              </li>
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("weight - Paper weight/thickness")}</span>
              </li>
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("printing_colors - Color specification")}</span>
              </li>
            </ul>
          </div>
          <div class="rounded border border-purple-200 bg-white p-3 dark:border-purple-700 dark:bg-purple-950/30">
            <h6 class="mb-2 flex items-center gap-2 text-xs font-bold text-purple-900 dark:text-purple-100">
              <i class="fa fa-list text-purple-500"></i>
              ${$t("Option References")}
            </h6>
            <p class="text-xs text-purple-700 dark:text-purple-300 mb-2">
              ${$t("Options can have additional references for specific attributes:")}
            </p>
            <ul class="ml-4 space-y-1 text-xs text-purple-700 dark:text-purple-300">
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("Main reference - Core attributes like format, material, weight")}</span>
              </li>
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("Binding type - Specific binding methods (saddle stitch, perfect bound, etc.)")}</span>
              </li>
              <li class="flex items-start gap-1">
                <i class="fa fa-circle mt-1" style="font-size: 6px;"></i>
                <span>${$t("Binding direction - Left or top binding orientation")}</span>
              </li>
            </ul>
          </div>
          <div class="rounded border border-amber-200 bg-amber-50 p-2 dark:border-amber-700 dark:bg-amber-900/20">
            <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">
              ${$t("⚠️ For full calculation products: All boxes must have a calculation reference assigned.")}
            </p>
          </div>
        </div>
      `
    }
  };

  return contextualHelp[step] || null;
};

const generalTips = [
  $t("Save your progress frequently to avoid losing changes"),
  $t("You can navigate back to previous steps to make changes"),
  $t("Completed steps are marked with a green checkmark"),
  $t("Use the help panel for step-specific guidance"),
];
</script>
