export function useCombinator(userDefinedLimit) {
  const { locale, t: $t } = useI18n();
  const { handleError } = useMessageHandler();

  /**
   * Configuration
   */
  const limit = userDefinedLimit || 40;
  const boxKey = "linked";
  const optionKey = "linked";

  /**
   * Formats the options of a box to only their IDs
   * @param {Array<{Object<any>}>} options - Array of box objects with id and options
   */
  const formatBoxOptions = (box) => box.ops.map((option) => `${box[boxKey]}-${option[optionKey]}`);

  /**
   * Creates a map of box IDs to their options
   * @param {Array<{linked: string, ops: Array<any>}>} boops - Array of box objects with id and options
   * @returns {Map<string, Array<any>>} Map of box IDs to their options
   */
  const generateBoxOptionMap = (boops) => {
    boops = boops.filter((box) => box?.ops?.length > 0);
    const map = new Map(boops.map((box) => [box[boxKey], formatBoxOptions(box)]));
    return map;
  };

  /**
   * Gets box identifiers from selected options
   * @param {Object<string, any>} selectedOptions - Map of selected options
   * @returns {Array<string>} Array of box identifiers
   */
  const getSelectedBoxes = (options) => Object.keys(options).filter((key) => key !== "quantity");

  /**
   * Removes already selected boxes from the box option map
   * @param {Map<string, Array<any>>} boxOptionMap - Map of box IDs to their options
   * @param {Object<string, Array<any>>} selectedBoxes - Object of selected box IDs to their options
   * @returns {Map<string, Array<any>>} Map of box IDs to their options
   */
  const removeSelectedBoxes = (boxOptionMap, selectedBoxes) => {
    const result = new Map();

    Array.from(boxOptionMap.entries()).forEach(([key, value]) => {
      if (!selectedBoxes.includes(key)) {
        result.set(key, value);
      }
    });

    return result;
  };

  /**
   * Generates the cartesian product of the options of the boxes
   * @param {Map<string, Array<any>>} boxOptionMap - Map of box IDs to their options
   * @returns {Array<any>} Cartesian product of the options of the boxes
   */
  const generateCombinations = (boxOptionMap) => {
    const arrays = Array.from(boxOptionMap.values());

    // If there are no arrays, return an empty array
    if (arrays.length === 0) return [];

    // If there is only one array, wrap each value in an array
    if (arrays.length === 1) return arrays[0].map((item) => [item]);

    // Normal cartesian product for multiple arrays
    return arrays.reduce((a, b) => a.flatMap((d) => b.map((e) => [d, e].flat())));
  };

  /**
   * Counts the cartesian product of the options of the boxes without generating the combinations
   * @param {Map<string, Array<any>>} boxOptionMap - Map of box IDs to their options
   * @returns {number} Count of combinations
   */
  const countCombinations = (boxOptionMap) => {
    const arrays = Array.from(boxOptionMap.values());
    return arrays.reduce((a, b) => a * b.length, 1);
  };

  /**
   * Links boxes back to their options
   * @param {Array<any>} combinations - Cartesian product of options
   * @returns {Array<Map<string, any>>} Array of combinations with box IDs to their option IDs
   */
  const addBoxesToCombinations = (combinations) => {
    return combinations.map((combination) => {
      const result = new Map();
      combination.forEach((option) => {
        const [boxId, optionId] = option.split("-");
        result.set(boxId, optionId);
      });
      return result;
    });
  };

  /**
   * Adds back the selected options to the combinations
   * @param {Array<Map<string, any>>} combinations - Cartesian product of options
   * @param {Object<string, any>} selectedOptions - Map of selected options
   * @returns {Array<Map<string, any>>} Array of combinations with selected options
   */
  const addSelectedOptions = (combinations, selectedOptions) => {
    const selectedOptionsArray = Object.entries(selectedOptions)
      .filter(([key]) => key !== "quantity")
      .map(([boxId, option]) => `${boxId}-${option[optionKey]}`);
    if (combinations.length === 0) return selectedOptionsArray.length ? [selectedOptionsArray] : [];
    return combinations.map((combination) => combination.concat(selectedOptionsArray));
  };

  /**
   * Get box data from box ID
   * @param {Map<string, Array<any>>} boxOptionMap - Map of box IDs to their options
   * @param {string} boxId - ID of the box
   * @returns {Object<{id: string, options: Array<any>}>} Box object with id and options
   */
  const getBoopBy = (boops, boxId) => {
    const box = boops.find((box) => box[boxKey] === boxId);
    if (!box) {
      throw createError({
        statusCode: 404,
        statusMessage: $t("Box with ID {id} not found.", { id: boxId }),
      });
    }

    return box;
  };

  /**
   * Get option data from option ID
   * @param {Array<{id: string, options: Array<any>}>} boops - Array of box objects with id and options
   * @param {string} optionId - ID of the option
   * @returns {Object<{id: string, options: Array<any>}>} Option object with id and options
   */
  const getOptionById = (box, optionId) => {
    return box.ops.find((option) => option[optionKey] === optionId);
  };

  /**
   * Formats a combination to a product
   * @param {Map<string, any>} combination - Combination of box IDs and option IDs
   * @param {Array<{id: string, options: Array<any>}>} boops - Array of box objects with id and options
   * @returns {Object<{id: string, options: Array<any>}>} Product object with id and options
   */
  function combinationToProduct(combination, boops) {
    return Array.from(combination.entries()).reduce((product, [boxId, optionId], index) => {
      const boop = getBoopBy(boops, boxId);
      const op = boop ? getOptionById(boop, optionId) : null;

      if (boop && op) {
        product[index] = {
          key: boop.slug,
          source_key: boop?.source_slug,
          value: op.slug,
          source_value: op?.source_slug,
          key_id: boop.id,
          value_id: op.id,
          linked_key: boop.linked,
          linked_value: op.linked,
          divider: boop.divider || "",
          dynamic: op.dynamic || false,
          _: {},
        };
      }
      return product;
    }, {});
  }

  /**
   * Sorts the product according to the order of the boxes
   * @param {Object<any>} product - Product
   * @param {Object<{id: string, linked: string}>} boops - Boops
   * @returns {Object<any>} Sorted product
   */
  const sortProduct = (product, boops) => {
    const sortedArray = Object.values(product).sort(
      (a, b) =>
        boops.findIndex((boop) => boop.linked === a.linked_key) -
        boops.findIndex((boop) => boop.linked === b.linked_key),
    );

    return sortedArray.reduce((acc, item, index) => {
      acc[index] = item;
      return acc;
    }, {});
  };

  /**
   * Formats a combination to a product
   * @param {Object<any>} combination - Combination
   * @returns {Object<{id: string, options: Array<any>}>} Product object with id and options
   */
  const filterDuplicateCombinations = (combinations) => {
    return [...new Set(combinations.map(JSON.stringify))].map(JSON.parse);
  };

  const formatCombination = (product, quantity, suppliers, boops, mainfest) => {
    const products = Array.from(Object.values(sortProduct(product, boops))).map(
      (item) => item.linked_key,
    );
    let selectedSuppliers = [];
    mainfest.forEach((singleMainfest) => {
      if (
        singleMainfest.properties.every((item) => {
          return (
            products.includes(item.linked) && products.length === singleMainfest.properties.length
          );
        })
      ) {
        selectedSuppliers.push(singleMainfest.tenant_id);
      }
    });
    const result = {
      type: "print",
      product: sortProduct(product, boops),
      quantity: quantity?.toString(),
      suppliers: selectedSuppliers,
      divided: false,
    };
    return result;
  };

  /**
   * Generates combinations of all possible options of the boxes
   * @param {Array<{id: string, options: Array<any>}>} boops - Array of box objects with id and options
   * @param {Object<string, any>} selectedOptions - Map of selected options
   * @param {boolean} onlyCount - Whether to only return the count of combinations
   * @returns {[Array<any>, number, Error|null]} Array containing formatted combinations, total count, and error if any
   */
  function combinate(boops, selectedOptions, suppliers, onlyCount = false, mainfest = []) {
    const selectedLinkedKeys = Object.keys(selectedOptions).filter((box) => box !== "quantity"); // Get selected linked keys without quantity
    let missedCombinations = [];
    const properties = mainfest.map((item) => item.properties);
    properties.forEach((property) => {
      if (
        property.every((item) => {
          return selectedLinkedKeys.includes(item.linked);
        })
      ) {
        const singleCombination = [];
        property.forEach((item) => {
          singleCombination.push(`${item.linked}-${selectedOptions[item.linked].linked}`);
        });
        missedCombinations.push(singleCombination);
      }
    });
    let count = 0; // Initialize count to 0
    const boxOptionMap = generateBoxOptionMap(boops); // Generate box option map
    const selectedBoxes = getSelectedBoxes(selectedOptions); // get selected boxes without quantity
    const cleanedBoxOptionMap = removeSelectedBoxes(boxOptionMap, selectedBoxes); // Remove selected boxes from boxOptionMap
    count = countCombinations(cleanedBoxOptionMap) + missedCombinations.length; // Count combinations without generating them

    if (onlyCount) return [[], count, null]; // Return count if onlyCount is true
    // Check if the count exceeds the limit ( 60 ) Variants
    if (count > limit) {
      throw createError({ statusCode: 400, statusMessage: $t("Too many combinations.") });
    }
    const fragCombinations = generateCombinations(cleanedBoxOptionMap); // Generate combinations
    let completeCombinations = addSelectedOptions(fragCombinations, selectedOptions, boops); // Add selected options to combinations
    completeCombinations = filterDuplicateCombinations([
      ...completeCombinations,
      ...missedCombinations,
    ]); // Get concat combinations with missed combinations and remove duplicates
    const combinationsWithBoxes = addBoxesToCombinations(completeCombinations, boxOptionMap); // Add boxes to combinations

    const products = combinationsWithBoxes.map((comb) => combinationToProduct(comb, boops));
    const qty = selectedOptions.quantity;
    const combinations = products.map((prod) =>
      formatCombination(prod, qty, suppliers, boops, mainfest),
    );

    return [combinations, count, null];
  }

  return { combinate };
}
