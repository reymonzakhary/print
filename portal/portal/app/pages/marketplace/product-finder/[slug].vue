<template>
  <div class="h-full space-y-5 2xl:container 2xl:space-y-6">
    <header class="mx-1 pt-5">
      <SkeletonLine v-if="pending" class="!h-5 w-96" />
      <MarketPlaceUIBreadcrumb v-if="success" :category="category.displayName" />
    </header>
    <div class="relative flex h-full gap-5 2xl:gap-8">
      <ProductFinderUISidebar v-model:close-sidebar="closeSidebar">
        <template #closed>
          <ProductFinderUICategoryImage
            :image="category?.image"
            :title="$display_name(category?.display_name)"
            :loading="pending"
          />
        </template>
        <template #default>
          <ProductFinderCategoryIndicator
            :title="$display_name(category?.display_name)"
            :image="category?.image"
            :loading="pending"
            class="sticky top-8 z-10 bg-white"
          />
          <ProductFinderCompareProducersList
            v-if="producers?.length > 0"
            :producers="producers"
            :disabled="calculationStatus.isCalculating"
            :un-selected-producers="unSelectedProducers"
            @toggle-producer="toggleProducer($event)"
          />
          <ProductFinderSidebarSelector
            :disabled="calculationStatus.isCalculating"
            :selected-options="selectedOptions"
            :filter-data="category?.boops"
            :available-options="allBoops"
            :manifest="allManifests"
            :supported-options="supportedOptions"
            @select-option="handleOptionChange"
          />
        </template>
      </ProductFinderUISidebar>

      <main class="w-full">
        <SkeletonLine v-if="pending" class="!h-full" />
        <template v-else-if="success">
          <Transition name="fading">
            <ProductFinderLoadingState
              v-if="calculationStatus.isCalculating"
              :loading-progress="calculationStatus.progress"
              :loading-status="calculationStatus.status"
            />
          </Transition>
          <ProductFinderViewWizard
            v-if="showWizard && !calculationStatus.isCalculating"
            :category="category"
            :selected-options="selectedOptions"
            :calculated-variation-count="combinationCount"
            :variation-limit="variationLimit"
            :manifests="allManifests"
            :calculated-all-variations-count="calculatedAllVariationsCount"
            :calculation-status="calculationStatus"
            :show-producers-to-connect="showProducersToConnect || false"
            @select-option="(option) => handleOptionChange(option, true)"
            @start-calculation="handleStartCalculation"
            @close-producers-connect="showProducersToConnect = false"
          />
          <ProductFinderViewCompare
            v-else-if="!showWizard && !calculationStatus.isCalculating"
            v-model:comparison-active="comparisonActive"
            :category="category"
            :products="filteredProducts"
            :selected-options="selectedOptions"
            :selected-boops="chosenBoops"
            :unselected-boops="unselectedBoops"
            :sidebar-closed="closeSidebar"
            :producers="producers"
            :un-selected-producers="unSelectedProducers"
            @toggle-producer="toggleProducer"
            @order-variant="orderVariant"
          />
        </template>
        <ProductFinderCompareError v-else-if="error" />
      </main>
    </div>
  </div>
</template>

<script setup>
import {
  addHashToProduct,
  addTenantsToCategory,
  createOrUpdateProduct,
  createProducerObject,
  extractProducersFromItems,
  getOptionByLinked,
  getSuppliersIdsFromCategory,
  formatProduct,
  getAvailableManifests,
} from "~/composables/market-place/helper.js"; // Import the helper function

const { t: $t } = useI18n(); // Get the $t function from the i18n plugin
const { confirm } = useConfirmation(); // Get the confirm function from the confirmation plugin
const { handleError } = useMessageHandler(); // Get the handleError function from the message handler
/**
 * Configuration
 */
const comparisonActive = ref(false); // Default comparison state
const variationLimit = ref(60); // Default variation limit
const calculationStatuses = ref([
  $t("Calculating variations") + "...",
  $t("Fetching producers") + "...",
  $t("Filtering products") + "...",
]); // Array of calculation statuses
const closeSidebar = ref(false);

/**
 * BooP Management
 */
const selectedOptions = ref({}); // { boopId: optionId }
const route = useRoute(); // Get the route object
const { slug } = route.params; // Extract the slug from the route parameters
const router = useRouter(); // Get the router object
const { chosenBoops: linkedBoops } = route.query; // Extract the linkedBoops query parameter
const quickChoiceRepository = useQuickChoiceRepository(); // Get the quickChoiceRepository

/**
 * Combination Management
 */
const allBoops = ref([]); // Array of all boops
const selectedBoops = computed(() => (success.value ? Object.keys(selectedOptions.value) : [])); // Get the selected boops
const unselectedBoops = computed(() =>
  success.value
    ? allBoops.value.filter((boop) => !selectedBoops?.value?.includes(boop.linked))
    : [],
); // Get the unselected boops
const chosenBoops = computed(() => allBoops.value.filter((b) => selectedOptions.value[b.linked])); // Get the chosen Options

const combinationCount = ref(0); // Default combination count
const calculatedAllVariationsCount = ref(0); // Default calculated all variations count
const hasTooManyVariations = ref(false); // FLag to track if has too many variations
const producers = ref([]); // Array of producers
const products = ref([]); // Array of products

/**
 * Fetching Logic
 */
const productFinderRepository = useProductFinderRepository();
const { combinate } = useCombinator(variationLimit.value);
const { addToast } = useToastStore();
const { data: category, status } = await useLazyAsyncData(
  "category",
  () => productFinderRepository.getSingleCategory(slug),
  { transform: ({ data }) => addTenantsToCategory(data) },
);

const allManifests = ref([]);
const pending = computed(() => status.value === "pending");
const error = computed(() => status.value === "error");
const success = computed(() => status.value === "success");

/**
 * Handles the change in selection of an option.
 * It updates the selected options, manages comparison view states, recalculates combinations, and updates the URL accordingly.
 *
 * @param {Object} args - The argument object.
 * @param {Object} args.boop - The object representing the current selection state.
 * @param {Object} args.option - The newly selected option.
 * @param {boolean} [onlyCount=false] - Determines if only the count operation should be performed.
 *
 * @return {Promise<void>} A promise that resolves when the option change handling is complete.
 */
async function handleOptionChange({ boop, option }, onlyCount = false) {
  const boopId = boop.linked ?? boop.id;
  try {
    const boxChanged = Object.keys(selectedOptions.value).includes(boopId);
    const boxDeselected =
      boxChanged &&
      (!option || selectedOptions.value[boopId].id === option.id) &&
      boop.type === "select";
    const boxIsNew = !boxChanged && !boxDeselected;
    const _onlyCount = onlyCount || showWizard.value;

    // If the box is deselected or changed while comparison is active, we need to confirm the change and close the comparison view
    if (boxDeselected || boxChanged) {
      if (comparisonActive.value) {
        await confirm({
          title: $t("Are you sure you want to change your selection?"),
          message: $t(
            "Changing this selection will close the comparison view and recalculate the combinations.",
          ),
          confirmOptions: {
            label: $t("Continue"),
            variant: "success",
          },
        });
      }
      comparisonActive.value = false;
      selectedOptions.value = Object.fromEntries(
        Object.entries(selectedOptions.value).filter(([key]) => key !== boopId),
      );
    }
    if ((boxIsNew || boxChanged) && !boxDeselected) {
      selectedOptions.value = {
        ...selectedOptions.value,
        [boopId]: option,
      };
    }
    category.value.properties_manifest = getAvailableManifests(
      allManifests.value,
      selectedOptions.value,
    );
    producers.value = category.value.properties_manifest.map((producer) =>
      createProducerObject(producer, category.value),
    );

    // update the url
    updateUrl();

    // Combination Calculation
    if (_onlyCount) return handleStartCalculation(true);
    if (boxIsNew && products.value.length) return filterProducts();

    // Existing box changed, so we need to recalculate the combinations
    handleStartCalculation();
  } catch (error) {
    if (error.cancelled) return;
  }
}

/**
 * Updates the URL with the query parameter `chosenBoops`. The `chosenBoops` parameter is populated based
 * on the `selectedOptions` object. If `selectedOptions` contains entries, it creates a JSON string
 * representation of the `boopId`-`option` mappings. If no entries exist in `selectedOptions`, the `chosenBoops`
 * query parameter is omitted.
 *
 * @return {void} This method does not return any value.
 */
const updateUrl = () =>
  router.push({
    query: {
      chosenBoops:
        Object.keys(selectedOptions.value).length > 0
          ? JSON.stringify(
              Object.fromEntries(
                Object.entries(selectedOptions.value).map(([boopId, option]) => [
                  boopId,
                  option.id ?? option,
                ]),
              ),
            )
          : undefined,
    },
  });
/**
 * Updates and initializes selected options using data parsed from a URL.
 *
 * This function processes `linkedBoops`, a JSON-encoded string, to extract linked options.
 * It maps these options to their corresponding values using `getOptionByLinked` for all keys
 * except "quantity". The resulting set of options is then merged into the existing `selectedOptions`.
 * Finally, a calculation process is triggered to handle the updates.
 *
 * Key functionalities:
 * - Parses and processes linked options from the input data.
 * - Updates the `selectedOptions` object with the processed data.
 * - Triggers a calculation process to finalize updates.
 */
const initializeFromUrl = () => {
  const linkedBoopsWithOptions = Object.fromEntries(
    Object.entries(JSON.parse(linkedBoops)).map(([boopLinked, optionLinked]) => {
      if (boopLinked !== "quantity")
        return [boopLinked, getOptionByLinked(boopLinked, optionLinked, category.value)];
      return [boopLinked, optionLinked];
    }),
  );
  selectedOptions.value = { ...selectedOptions.value, ...linkedBoopsWithOptions };
  // Calculate the total number of variations
  handleStartCalculation(true);
};

/**
 * Processes and converts smart selections from a query parameter into selected options.
 *
 * The function reads the `smart` parameter from the URL query, splits it into individual
 * selections, and processes them to identify and set the corresponding options in the
 * `selectedOptions` object. Each selection is compared against the options available in
 * the application's category structure.
 *
 * Workflow:
 * 1. Extracts and cleans the smart selections from the `route.query.smart` parameter.
 * 2. Iterates through category dividers and their nested `boop` items.
 * 3. Checks if the boop has available options and matches smart selections to those options.
 * 4. Updates the `selectedOptions` object associating the matched option with its linked key.
 *
 * Throws no errors, silently skips dividers or boops without `options`.
 */
const convertSmartSelections = () => {
  const smartSelections = route.query.smart.split(";").map((slug) => slug.trim().toLowerCase());
  category.value.boops.forEach((boop) => {
    if (!boop.ops) return;
    boop.ops.forEach((option) => {
      if (smartSelections.includes(option.slug.toLowerCase())) {
        selectedOptions.value[boop.linked] = option;
      }
    });
  });
};

watch(category, async () => {
  // If it comes from a smart selection, convert it to selected options
  if (category.value && route.query.smart) convertSmartSelections();
  // If it comes from a URL query parameter, initialize from it
  if (linkedBoops) initializeFromUrl();
  // Calculate the total number of variations if no options are selected
  if (category.value) {
    // Get the total number of variations combination
    [, calculatedAllVariationsCount.value] = combinate(category.value.boops, {}, [], true);
    await quickChoiceRepository.updateCount(category.value);
  }
  // Set the combination count to the total number of variations if no options are selected
  const optionsSelected = Object.keys(selectedOptions.value).length;
  if (!optionsSelected) combinationCount.value = calculatedAllVariationsCount.value;

  if (category.value?.properties_manifest) {
    allManifests.value = category.value.properties_manifest;
    category.value.properties_manifest = getAvailableManifests(allManifests.value, selectedOptions.value);
    producers.value = category.value.properties_manifest.map((producer) =>
      createProducerObject(producer, category.value),
    );
  }
});

watch(success, () => {
  if (success.value) allBoops.value = category.value.boops;
});

// Watch for changes in products and update producers accordingly
watch(products, () => {
  // Creates an array of unique producers
  producers.value = products.value.reduce((uniqueProducers, product) => {
    // For each producer in the product, check if it already exists in the unique producers array
    product.producers.forEach((producer) => {
      // Find the producer in the unique producers array
      const existingProducer = uniqueProducers.find((p) => p.id === producer.id);
      // If the producer already exists, update the existing producer
      if (existingProducer) existingProducer.variants += 1;
      // If the producer does not exist, create a new producer and push it to the unique producers array
      else uniqueProducers.push(createProducerObject(producer, category.value));
    });
    // Return the unique producers array and go to next product
    return uniqueProducers;
  }, []);
});

/**
 * Filter products based on the selected options
 */
const filterProducts = () => {
  if (!products.value.length || !Object.keys(selectedOptions.value).length) return;
  const { quantity, ...filterOptions } = selectedOptions.value;

  const requiredMatches = Object.entries(filterOptions).map(([key, value]) => ({
    key,
    value: value.linked || value.id || value,
  }));

  products.value = products.value.filter((product) => {
    const productOpts = new Map(product.product.map((box) => [box.linked_key, box.linked_value]));
    const boopsLength = allBoops.value.filter((boop) => boop.id !== "quantity").length;
    return (
      (requiredMatches.every(({ key, value }) => productOpts.get(key) === value) &&
        Array.from(productOpts.values()).length === boopsLength) ||
      (requiredMatches.some(({ key, value }) => productOpts.get(key) === value) &&
        Array.from(productOpts.values()).length < boopsLength)
    );
  });
};

/**
 * A computed value that filters and sorts the products based on unselected producers.
 *
 * If there are unselected producers, it filters out those producers from each product's producer list.
 * Then it sorts the products in descending order based on the number of remaining producers.
 *
 * If there are no unselected producers, it returns the products sorted in descending order
 * by the number of producers.
 *
 * @constant {ComputedRef<Array>} filteredProducts - The filtered and sorted list of products.
 */
const filteredProducts = computed(() => {
  if (unSelectedProducers.value?.length) {
    return [...products.value]
      .map((product) => {
        return {
          ...product,
          producers: product.producers.filter(
            (producer) => !unSelectedProducers.value.includes(producer.id),
          ),
        };
      })
      .sort((a, b) => b.producers.length - a.producers.length);
  }
  return [...products.value].sort((a, b) => b.producers.length - a.producers.length);
});

const calculationStatus = ref({
  isCalculating: false,
  progress: 0,
  status: "idle",
});
const contentContainer = ref(document.querySelector("#contentContainer"));
const originalOverflow = ref("");
watch(
  () => calculationStatus.value.isCalculating,
  (isCalculating) => {
    if (!isCalculating) return (contentContainer.value.style.overflow = originalOverflow.value);

    contentContainer.value.scrollTo(0, 0);
    originalOverflow.value = contentContainer.value.style.overflow;
    contentContainer.value.style.overflow = "hidden";
  },
  { immediate: true },
);
onBeforeUnmount(() => (contentContainer.value.style.overflow = originalOverflow.value));

const showWizard = computed(
  () =>
    !selectedOptions.value.quantity ||
    calculationStatus.value.progress < 100 ||
    hasTooManyVariations.value,
);

/**
 * Producer Management
 */
const unSelectedProducers = ref([]);
function toggleProducer(e) {
  const producerId = e.id ?? e;
  if (unSelectedProducers.value.includes(producerId)) {
    unSelectedProducers.value = unSelectedProducers.value.filter((id) => id !== producerId);
  } else {
    unSelectedProducers.value = [...unSelectedProducers.value, producerId];
  }
}

/**
 * Ordering Management
 */
const orderVariant = (vId) => {
  const selectedProduct = products.value.find((p) => p.id === vId);
  if (selectedProduct) {
    const productFinderStore = useProductFinderStore();
    productFinderStore.openBasketDialog(true, selectedProduct, category.value);
  }
};

const supportedOptions = ref([]);
const showProducersToConnect = ref(false);
const handleStartCalculation = async (onlyCount = false) => {
  try {
    // Get the suppliers ids from the category
    const suppliers = getSuppliersIdsFromCategory(category.value);
    // use the combinate composable to create combinations
    const [variantPayloads, count, error] = combinate(
      category.value.boops,
      selectedOptions.value,
      suppliers,
      onlyCount,
      category.value.properties_manifest,
    );
    // set the combination count to the total number of variations
    combinationCount.value = count;

    // if onlyCount is true stop the calculation
    if (onlyCount) return;

    // if there is an error, set the hasTooManyVariations to true making sure the wizard is shown
    if (error && error.statusCode === 400) {
      hasTooManyVariations.value = true;
      products.value = [];
      return;
    } else if (error) {
      console.error(error);
      return;
    }

    hasTooManyVariations.value = false;
    calculationStatus.value.isCalculating = true;
    /**
     * A variable that holds a recurring interval timer used to update the progress and status of a calculation process.
     *
     * The interval runs at a specified delay and updates:
     * - The `progress` value by incrementing it.
     * - The `status` value, which is updated based on the calculated progress and pre-defined statuses.
     *
     * Once the progress reaches or exceeds 100, the interval automatically clears itself to stop further execution.
     *
     * The interval delay is set in milliseconds.
     */
    const calculationInterval = setInterval(() => {
      calculationStatus.value.progress += 1;
      calculationStatus.value.status =
        calculationStatuses.value[Math.floor(calculationStatus.value.progress / 33)];
      if (calculationStatus.value.progress >= 100) {
        clearInterval(calculationInterval);
      }
    }, 50);

    const apiProducts = await productFinderRepository.getProducts(
      category.value.id,
      variantPayloads,
    );

    // If all products are errors, show a toast and stop the calculation
    if (apiProducts.every((p) => p.error) || !apiProducts.length) {
      showProducersToConnect.value = true;
      calculationStatus.value.progress = 100;
      setTimeout(() => {
        calculationStatus.value.isCalculating = false;
      }, 300);
      products.value = [];

      return addToast({
        type: "info",
        message: $t("Sadly no producers answered with a price. Please try a different selection."),
      });
    }

    // If at least a product is valid, extract the non-error products
    const validProducts = apiProducts.filter((p) => !p.error);
    // Extract Producers From Products To Update Producers list
    producers.value = extractProducersFromItems(validProducts, category.value);
    // Format the products to be used in the UI and add a hash to each product to compare it when user changes the selection
    const formattedProducts = validProducts
      .map((product) => formatProduct(product, category.value, selectedOptions.value))
      .map(addHashToProduct);

    // Now we have to actually combine the duplicate products.
    products.value = createOrUpdateProduct(formattedProducts);
    calculationStatus.value.progress = 100;
    setTimeout(() => {
      calculationStatus.value.isCalculating = false;
    }, 300);
  } catch (error) {
    supportedOptions.value = [];
    handleError(error);
    products.value = [];
  }
};
</script>
