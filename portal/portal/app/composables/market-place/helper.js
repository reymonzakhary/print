/////////////////////////////////////////////////////  Helper Functions For Show Category  /////////////////////////////////////////////////////////////////
import { v4 as uuidv4 } from "uuid";

/**
 * Creates a producer object with specified properties or default values.
 *
 * @function
 * @param {Object} [producer={}] - The producer data object containing details such as id, logo, and name.
 * @param {Object} [category={}] - The category data object containing tenant information.
 * @returns {Object} - A new producer object with the following properties:
 *   - id: The producer's `id` or `tenant_id`.
 *   - logo: The producer's `logo`, or retrieves the `logo` from the corresponding tenant in the category value.
 *   - name: The producer's `name` or `tenant_name`.
 *   - variants: A fixed value of 1.
 */
const createProducerObject = (producer = {}, category = {}) => {
  return {
    id: producer.id ?? producer.tenant_id,
    logo:
      producer.logo ??
      category.properties_manifest.find((tenant) => tenant.tenant_id === producer.id)?.logo,
    name: producer.name ?? producer.tenant_name,
    variants: 1,
  };
};

/**
 * Retrieves a specific option by its ID within a linked boop in the provided category.
 *
 * @param {string} boopId - The ID of the linked boop to search for.
 * @param {string} optionId - The ID of the option to retrieve within the found boop.
 * @param {Object} category - The category object containing boops and their options.
 * @returns {Object|undefined} The option object matching the given optionId, or undefined if not found.
 */
const getOptionByLinked = (boopId, optionId, category) => {
  const boopByLinked = category.boops.find((boop) => boop.linked === boopId);
  return boopByLinked.ops.find((op) => op.id === optionId);
};

/**
 * Modifies the given category object by adding tenants to it based on the properties manifest.
 *
 * The function iterates over the `boops` array within the provided category object.
 * For each `boop` in the array, it checks the `properties_manifest` of the category
 * to find matching tenants. Tenants are added to the `tenants` property of the category.
 *
 * A tenant is considered a match if:
 * - The tenant has a `properties` array.
 * - The `boop.slug` exists within the tenant's `properties` array.
 *
 * Matching tenants are added as objects containing `name` (tenant name) and `id` (tenant id)
 * to the `tenants` array of the category object.
 *
 * @param {Object} category - The category object to modify.
 * @param {Array} category.boops - An array of `boop` objects, where each object contains a `slug` property.
 * @param {Array} [category.properties_manifest] - An optional array of tenant objects.
 * @param {Array} [category.properties_manifest[].properties] - An array of property slugs belonging to the tenant.
 * @param {string} [category.properties_manifest[].tenant_name] - The name of the tenant.
 * @param {string|number} [category.properties_manifest[].tenant_id] - The unique identifier of the tenant.
 * @returns {Object} The modified category object with added `tenants` property.
 */
const addTenantsToCategory = (category) => {
  category.boops.forEach((boop) => {
    boop.type = "select";
    if (category.properties_manifest && Array.isArray(category.properties_manifest)) {
      category.tenants = [];
      for (const tenant of category.properties_manifest) {
        if (
          tenant.properties &&
          Array.isArray(tenant.properties) &&
          tenant.properties.includes(boop.slug)
        ) {
          category.tenants.push({ name: tenant.tenant_name, id: tenant.tenant_id });
        }
      }
    }
  });
  // add quantity boop to the top of the boops array
  category.boops.unshift(_quantityBoop);

  // Check if the category image exists in the index
  category = addImageToCategory(category);
  return category;
};

/**
 * Extracts supplier IDs from a given category object.
 *
 * This function takes a category object as input and retrieves all supplier IDs
 * from its `properties_manifest` property. If `properties_manifest` is not
 * present or undefined, it returns an empty array.
 *
 * @param {Object} category - The category object containing supplier details.
 * @returns {Array} - An array of supplier IDs (`tenant_id`) extracted from
 * the `properties_manifest`.
 */
const getSuppliersIdsFromCategory = (category) => {
  return category.properties_manifest
    ? category.properties_manifest.map((tenant) => tenant.tenant_id)
    : [];
};

/**
 * Updates the image property of a given category object based on the slug.
 * If the slug exists in the `_categoryImagesBySlug` dataset with a `.svg` extension,
 * the image path is set to the corresponding path in `/img/categories/`.
 * If not, the image property is set to null.
 *
 * @param {Object} category - The category object to update.
 * @param {string} category.slug - The unique identifier used to locate the image file.
 * @returns {Object} The updated category object with the image property modified.
 */
const addImageToCategory = (category) => {
  category.image = _categoryImagesBySlug.includes(`${category.slug}.svg`)
    ? `/img/categories/${`${category.slug}.svg`}`
    : null;
  return category;
};

/**
 * Retrieves an array of exclusions for a specific option within a given manifest.
 *
 * @param {Object} manifest - The manifest object containing configuration data.
 * @param {string} optionLinked - The identifier for the specific option to retrieve exclusions for.
 * @param {string} boxLinked - The identifier for the linked box associated with the specific option.
 * @returns {Array} - An array of exclusions for the specific option. Returns an empty array if no exclusions are found.
 */
const getExcludesArrayForSpecificOption = (manifest, optionLinked, boxLinked) => {
  let excludes = [];
  manifest.boops.forEach((boop) => {
    if (boop.linked === boxLinked) {
      boop.ops.forEach((op) => {
        if (op.linked === optionLinked) {
          excludes = op.excludes;
        }
      });
    }
  });
  return excludes;
};

/**
 * Identifies and returns duplicate arrays from the provided list of arrays (excludes),
 * where duplicates are determined based on sorted and stringified representations
 * of the arrays' contents, irrespective of their initial order.
 *
 * This function checks for duplicates by normalizing the arrays using sorting
 * and stringification before comparison, ensuring that arrays with the same
 * elements in different orders are treated as duplicates.
 *
 * @param {Array<Array<any>>} excludes - An array of arrays to be checked for duplicates.
 * @returns {Array<Array<any>>} An array containing arrays from the input that are duplicates.
 */
const _removeExcludesDuplicates = (excludes) => {
  const seen = new Set();
  const nonUnique = [];

  for (const arr of excludes) {
    // Sort and stringify for comparison
    const key = JSON.stringify([...arr].sort());
    if (seen.has(key)) {
      nonUnique.push(arr);
    }
    seen.add(key);
  }

  return nonUnique || [];
};

/**
 * Determines if there is a general exclusion based on the provided options and manifest data.
 *
 * @param {string} optionLinked - The key of the currently linked option being checked.
 * @param {Object} selectedOptions - An object representing the currently selected options, where the keys are option names, and values might include a `linked` property.
 * @param {Array<Object>} allManifest - An array of manifest objects that contain exclusion data for specific options.
 * @param {boolean} boxLinked - A flag indicating whether the box is linked to the option.
 * @returns {boolean} - Returns `true` if a general exclusion exists; otherwise, returns `false`.
 */
const checkIfHasGeneralExclude = (optionLinked, selectedOptions, allManifest, boxLinked) => {
  let flag = false;
  const excludesArray = allManifest
    .map((manifest) => getExcludesArrayForSpecificOption(manifest, optionLinked, boxLinked))
    .flatMap((excludes) => excludes);
  const uniqueExcludes = _removeExcludesDuplicates(excludesArray);
  const selectedLinked = Object.values(selectedOptions)
    .filter((option) => option.linked)
    .map((option) => option.linked);
  uniqueExcludes.forEach((excludes) => {
    if (
      excludes.every((exclude) => {
        return selectedLinked.includes(exclude);
      })
    ) {
      flag = true;
    }
  });
  return flag;
};

/**
 * Filters and returns a list of available manifests based on the selected options and their linked configurations.
 *
 * @function
 * @param {Array<Object>} allManifests - A list of all available manifests to be filtered.
 * @param {Object} selectedOption - An object representing user-selected options with their respective configurations.
 *                                     Each property of the object includes details such as whether it is linked and its key.
 * @returns {Array<Object>} - A filtered list of manifests that do not have exclusions based on the selected options.
 */
const getAvailableManifests = (allManifests, selectedOption) => {
  const selectedLinked = Object.values(selectedOption)
    .filter((option) => option.linked)
    .map((option) => option.linked);

  const selectedBoxOption = Object.entries(selectedOption)
    .filter(([key]) => key !== "quantity") // skip "quantity"
    .map(([key, value]) => ({
      linked: value.linked,
      key_linked: key,
    }));

  return allManifests.filter((menifest) => {
    return !selectedBoxOption.some((option) => {
      const excludes = getExcludesArrayForSpecificOption(
        menifest,
        option.linked,
        option.key_linked,
      );
      return excludes.find((exclude) => exclude.every((e) => selectedLinked.includes(e)));
    });
  });
};

//////////////////////////////////////   Helper Functions For Format Data Returned Form Calculation Match Design  //////////////////////////////////////
/**
 * Calculates the best price from a given list of producers.
 *
 * @param {Array} producers - An array of producer objects, each containing a `prices` property.
 * @return {Object|null} Returns an object containing the best price and its associated tenant,
 * or `null` if there are no prices available.
 */
function calculateBestPrice(producers) {
  const all = producers.flatMap((producer) => producer.prices || []);
  if (all.length === 0) return null;
  const price = all.reduce((best, current) => (!best || current.p < best.p ? current : best), null);
  const tenant = producers.find((producer) => producer.prices.some((p) => p.id === price.id));
  return { price, tenant };
}

/**
 * Calculates the best delivery option based on the provided producers and their pricing details.
 *
 * @param {Array} producers - An array of producer objects, where each producer contains pricing information.
 * @return {Object|null} The best delivery option, including the price and the tenant (producer) associated with it.
 * Returns null if no pricing information is available.
 */
function calculateBestDelivery(producers) {
  const all = producers.flatMap((producer) => producer.prices || []);
  if (all.length === 0) return null;
  const price = all.reduce(
    (best, current) => (!best || current.dlv.actual_days < best.dlv.actual_days ? current : best),
    null,
  );
  const tenant = producers.find((producer) => producer.prices.some((p) => p.id === price.id));
  return { price, tenant };
}

/**
 * Processes a list of products, either creating new product entries or updating existing ones with new producer information.
 *
 * @param {Array} products An array of product objects to be processed. Each product object must include a `hash` property,
 *                         a `producers` array, and any additional required product details.
 * @return {Array} The updated array of processed product objects containing merged producer data for duplicates
 *                 and updated best price and best delivery information.
 */
function createOrUpdateProduct(products) {
  return products.reduce((acc, product) => {
    const existingProduct = acc.find((p) => p.hash === product.hash);
    if (existingProduct) {
      if (existingProduct.producers.some((p) => p.id === product.producers[0].id)) return acc;
      existingProduct.producers.push(...product.producers);
      existingProduct.bestPrice = calculateBestPrice(existingProduct.producers);
      existingProduct.bestDelivery = calculateBestDelivery(existingProduct.producers);
    } else {
      acc.push(product);
    }
    return acc;
  }, []);
}

// Helper function to add an items identifier to a product
/**
 * Adds a hash property to the given product object based on its 'product' property.
 *
 * @param {Object} product - The product object to which the hash will be added. It should contain a 'product' property.
 * @return {Object} Returns the updated product object with the added 'hash' property.
 */
function addHashToProduct(product) {
  product.hash = createHash(product.items);
  return product;
}

// Helper function to create a unique id for a product based on the items
/**
 * Creates a hash string by concatenating the keys and values of the given items.
 *
 * @param {Array<{key: string, value: string}>} items - An array of objects, each containing a `key` and a `value` property.
 * @return {string} A string representing the concatenated hash of keys and values from the items.
 */
function createHash(items) {
  return items.reduce((acc, item) => {
    acc += "-" + item.key + "-" + item.value;
    return acc;
  }, "");
}

/**
 * Extracts producer details from a list of products and returns them in a structured format
 * to update producers with producers which have products after make Calculation.
 *
 * @param {Array} products - An array of product objects, where each product contains tenant information.
 * @returns {Object} An object mapping producer IDs to their details, including `id`, `name`, and `logo`.
 */
function extractProducersFromItems(products, category) {
  return products.reduce((acc, product) => {
    product.forEach((tenant) => {
      if (!tenant.results.external_id) return;
      const tenantObject = {
        id: tenant.results.external_id,
        name: tenant.results.external_name,
        // Back-end returns logo null on all tenants, so we need to get it from the category
        logo: category.properties_manifest.find((t) => t.tenant_id === tenant.results.external_id)
          ?.logo,
      };
      acc[tenantObject.id] = tenantObject;
    });
    return acc;
  }, {});
}

function formatProduct(product, category, selectedOptions) {
  const producers = product.reduce((acc, tenant) => {
    if (tenant.results.prices?.length) {
      acc.push({
        ...tenant.results,
        id: tenant.tenant_id,
        name: tenant.results.external_name,
        logo: category.properties_manifest.find((t) => t.tenant_id === tenant.tenant_id)?.logo,
        partner: true,
      });
    }
    return acc;
  }, []);
  return {
    id: uuidv4(),
    items: producers[0].items.map((item) => ({
      ...item,
      // Set the linked_key and linked_value so we don't have to change the whole codebase
      linked_key: item.key_link,
      linked_value: item.value_link,
    })),
    producers: producers,
    bestPrice: calculateBestPrice(producers),
    bestDelivery: calculateBestDelivery(producers),
    quantity: selectedOptions.quantity,
  };
}

///////////////////////////////////////////////////////////// Export Helper Functions ///////////////////////////////////////////////////////////////////////////////////
export {
  createProducerObject,
  getOptionByLinked,
  addTenantsToCategory,
  getSuppliersIdsFromCategory,
  addImageToCategory,
  calculateBestPrice,
  calculateBestDelivery,
  createOrUpdateProduct,
  addHashToProduct,
  createHash,
  extractProducersFromItems,
  formatProduct,
  checkIfHasGeneralExclude,
  getExcludesArrayForSpecificOption,
  getAvailableManifests,
};

///////////////////////////////////////////////////////////////////// Variables Used For Helper Functions ///////////////////////////////////////////////////////////////////////////////
// Object of quantity boop for quantity input
const _quantityBoop = {
  id: "quantity",
  linked: "quantity",
  name: "Quantity",
  display_name: [
    {
      display_name: "Quantity",
      iso: "en",
    },
    {
      display_name: "Hoeveelheid",
      iso: "nl",
    },
    {
      display_name: "Menge",
      iso: "de",
    },
    {
      display_name: "Quantité",
      iso: "fr",
    },
  ],
  type: "number",
  required: true,
  min: 1,
  max: 10000,
  value: 0,
};

// Array of category images by slug
const _categoryImagesBySlug = [
  "banners.svg",
  "book.svg",
  "bookmark.svg",
  "bookmarks.svg",
  "box.svg",
  "brochures.svg",
  "business.svg",
  "coaster.svg",
  "compliment.svg",
  "compliments cards.svg",
  "coupon.svg",
  "coupons.svg",
  "default.svg",
  "envelop.svg",
  "envelopes.svg",
  "flags.svg",
  "flyer.svg",
  "flyers.svg",
  "folder.svg",
  "folders.svg",
  "hangtags.svg",
  "letterhead.svg",
  "memoblocks.svg",
  "photo.svg",
  "placemat.svg",
  "placemats.svg",
  "postcard.svg",
  "postcards.svg",
  "poster.svg",
  "posters.svg",
  "rollup banners.svg",
  "rollupbanner.svg",
  "sticker.svg",
  "stickers.svg",
  "wallpaper.svg",
  "writingpad.svg",
  "writingpads.svg",
];
