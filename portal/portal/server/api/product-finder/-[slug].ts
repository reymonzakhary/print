interface Run {
  quantity: number;
  price: string;
  deliveryTime: string;
}
interface PriceData {
  qty: number | string;
  p: string;
  display_p: string;
  dlv: { actual_days: number };
  id?: string;
  tenant_name?: string;
  tenant_logo?: string;
}
interface ApiResponse {
  data?: Array<ResultItem>;
  results?: {
    prices?: PriceData[];
    tenant_name?: string;
    tenant_logo?: string;
    external_name?: string;
    calculation?: Array<{ price: PriceData[] }>;
    type?: string;
    items?: ProductItem[];
    product?: ProductItem[];
  };
  prices?: PriceData[];
  tenant_id?: string;
  tenant_name?: string;
  tenant_logo?: string;
  external_name?: string;
  calculation?: Array<{ price: PriceData[] }>;
}
interface ResultItem {
  tenant_id?: string;
  logo?: string;
  tenant_name?: string;
  tenant_logo?: string;
  results?: {
    prices?: PriceData[];
    tenant_name?: string;
    tenant_logo?: string;
    external_name?: string;
    calculation?: Array<{ price: PriceData[] }>;
    items?: ProductItem[];
    product?: ProductItem[];
    type?: string;
  };
  prices?: PriceData[];
  calculation?: Array<{ price: PriceData[] }>;
}
interface Producer {
  id: string;
  name: string;
  logo: string | null;
  partner: boolean;
  runs: Run[];
  productKeys: string[]; // Product keys are essential for grouping
  originalDataSource: ResultItem; // Keep track of where this producer came from
}
interface PayloadItem {
  [key: string]: unknown;
  suppliers?: { id: string; logo: string }[];
  product?: Record<string, unknown>;
  quantity?: number;
  type?: string;
}
interface Variation {
  id: string;
  availableQuantities: number[];
  bestPriceData: BestOfferData;
  bestDeliveryTimeData: BestOfferData;
  uniqueProducers: number;
  producers: Producer[];
  originalData: ResultItem | null; // Representative item for this variation group
  productKeys: string[];
  product: Record<string, unknown>;
  quantity: number;
  type: string; // Inherit non-producer specific fields
  // Include other fields from PayloadItem if necessary
  [key: string]: unknown; // Allow spreading original payload properties
}
interface BestOfferData {
  price: string | null;
  name: string | null;
  logo: string | null;
  id: string | null;
  deliveryTime: string | null;
  partner: boolean;
}
interface ExtractedProducerInfo {
  producer: Producer;
  sourceItem: ResultItem;
}

interface ProductItem {
  key: string;
  value: string;
  key_id?: string;
  value_id?: string;
  key_link?: string;
  value_link?: string;
  key_display_name?: string;
  value_display_name?: string;
  divider?: string;
  value_dynamic?: boolean;
  dynamic?: boolean;
  linked_key?: string;
  linked_value?: string;
  _?: Record<string, unknown>;
}

// --- Pure Helper Functions ---

const parseCurrencyString = (value: string | null | undefined): number => {
  if (!value) return Infinity; // Treat null/undefined as highest price
  // More robust parsing, handles potential errors
  const cleaned = String(value)
    .replace(/[^0-9.,]/g, "")
    .replace(",", ".");
  const number = parseFloat(cleaned);
  return isNaN(number) ? Infinity : number * 100; // Use Infinity for non-parseable strings
};

const parseDeliveryTime = (value: string | null | undefined): number => {
  if (!value) return Infinity; // Treat null/undefined as slowest delivery
  const days = parseInt(String(value).split("-")[0], 10);
  return isNaN(days) ? Infinity : days;
};

const calculateAvailableQuantities = (producers: Producer[]): number[] => {
  const quantities = new Set<number>();
  producers.forEach((p) => p.runs.forEach((r) => quantities.add(r.quantity)));
  return Array.from(quantities).sort((a, b) => a - b);
};

const extractProductKeys = (item: ResultItem | null): string[] => {
  if (!item?.results) return [];
  const keys: string[] = [];
  const productConfig = item.results.product || [];

  const itemsToProcess = productConfig;

  itemsToProcess.forEach((config: ProductItem) => {
    if (config.key && config.value) {
      keys.push(`${config.key}:${config.value}`);
    }
  });

  return keys.sort();
};

// Improved formatting logic within extractProductConfig
const formatDisplayName = (keyOrValue: string): string => {
  return keyOrValue
    .split("-")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

const extractProductConfig = (
  item: ResultItem | null,
  originalPayload: PayloadItem,
): Record<string, ProductItem> => {
  if (!item?.results) return {};
  const productConfig: Record<string, ProductItem> = {};
  const productArr = item.results.product || [];
  const productItems: ProductItem[] = item.results.items || [];

  // Extract the product objects from the payload for easier lookup
  const payloadProducts = originalPayload?.product ? Object.values(originalPayload.product) : [];

  if (productArr.length > 0) {
    productArr.forEach((config: ProductItem, index: number) => {
      if (!config.key || !config.value) return;

      // Step 1: Find the corresponding entries in the items array to get IDs
      const keyItem = productItems.find((item) => item.key === config.key);
      const valueItem = productItems.find(
        (item) => item.key === config.key && item.value === config.value,
      );

      // Extract key_id and value_id from items
      const key_id = keyItem?.key_link;
      const value_id = valueItem?.value_link;

      // Step 2: Find the matching payload product using the key and value IDs
      const payloadProductItem = payloadProducts.find((item) => {
        if (key_id && value_id) return item.linked_key === key_id;
        return item.key_link === config.key_link;
      });

      productConfig[String(index)] = {
        key: config.key,
        value: config.value,
        linked_key:
          keyItem?.key_link || (payloadProductItem ? payloadProductItem.linked_key : undefined),
        linked_value:
          valueItem?.value_link ||
          (payloadProductItem ? payloadProductItem.linked_value : undefined),

        key_display_name:
          config.key_display_name ||
          (payloadProductItem ? payloadProductItem.key_display_name : undefined) ||
          formatDisplayName(config.key),
        value_display_name:
          config.value_display_name ||
          (payloadProductItem ? payloadProductItem.value_display_name : undefined) ||
          formatDisplayName(config.value),
        divider:
          config.divider || (payloadProductItem ? payloadProductItem.divider : undefined) || "",
        dynamic:
          config.dynamic || (payloadProductItem ? payloadProductItem.dynamic : undefined) || false,
        _: config._ || (payloadProductItem ? payloadProductItem._ : undefined) || {},
      };
    });
  }

  return productConfig;
};

const extractPricesFromResult = (result: ResultItem): PriceData[] => {
  return (
    result.results?.prices ||
    result.prices ||
    result.results?.calculation?.[0]?.price ||
    result.calculation?.[0]?.price ||
    []
  );
};

const groupByQuantity = (prices: PriceData[]): Record<number, PriceData[]> => {
  return prices.reduce((groups: Record<number, PriceData[]>, price) => {
    const quantity = typeof price.qty === "string" ? parseInt(price.qty, 10) : price.qty;
    // Ensure quantity is a valid number before grouping
    if (!isNaN(quantity)) {
      if (!groups[quantity]) {
        groups[quantity] = [];
      }
      groups[quantity].push(price);
    }
    return groups;
  }, {});
};

const createRunsFromGroupedPrices = (groupedPrices: Record<number, PriceData[]>): Run[] => {
  return Object.entries(groupedPrices)
    .map(([quantityStr, prices]) => {
      const quantity = parseInt(quantityStr, 10);
      // Find best price (lowest numerical value)
      const sortedByPrice = [...prices].sort(
        (a, b) => parseCurrencyString(a.p) - parseCurrencyString(b.p),
      );
      const bestPriceData = sortedByPrice[0];

      // Calculate delivery range
      const deliveryDays = prices.map((p) => p.dlv.actual_days).filter((d) => !isNaN(d)); // Filter out NaN days
      const minDays = deliveryDays.length > 0 ? Math.min(...deliveryDays) : 0;
      const maxDays = deliveryDays.length > 0 ? Math.max(...deliveryDays) : 0;
      const deliveryTime =
        minDays === maxDays && minDays > 0
          ? `${minDays}`
          : minDays < maxDays
            ? `${minDays}-${maxDays}`
            : `N/A`; // Simplified delivery time string

      return {
        quantity,
        price: bestPriceData?.display_p || "N/A", // Use display price, fallback
        deliveryTime: deliveryTime,
      };
    })
    .sort((a, b) => a.quantity - b.quantity); // Ensure runs are sorted by quantity
};

// Extracts *all* producers found within a single ResultItem (often just one)
const extractProducersFromItem = (
  resultItem: ResultItem,
  suppliers: { id: string; logo: string }[],
): ExtractedProducerInfo[] => {
  if (!resultItem?.tenant_id) return [];

  const allPrices = extractPricesFromResult(resultItem);
  if (!allPrices.length) return [];

  const pricesByQuantity = groupByQuantity(allPrices);
  const runs = createRunsFromGroupedPrices(pricesByQuantity);

  // Only create producer if we have valid runs
  if (!runs.length || runs.every((r) => r.price === "N/A")) return [];

  const tenantId = resultItem.tenant_id;
  const supplierLogo = suppliers.find((s) => s.id === tenantId)?.logo;
  const productKeys = extractProductKeys(resultItem);

  const producer: Producer = {
    id: tenantId,
    name: resultItem.results?.external_name || resultItem.tenant_name || `Provider ${tenantId}`,
    logo: resultItem.logo || resultItem.tenant_logo || supplierLogo || null,
    partner: true, // Assume partner status based on this context, adjust if needed
    runs: runs,
    productKeys: productKeys,
    originalDataSource: resultItem, // Link back to the source
  };

  return [{ producer, sourceItem: resultItem }];
};

// Find the best offer based on a comparison function
const findBestOffer = (
  producers: Producer[],
  compareFn: (a: Run, b: Run) => number,
): BestOfferData => {
  if (!producers.length) {
    return { price: null, name: null, logo: null, id: null, deliveryTime: null, partner: false };
  }

  const bestProducerRun = producers
    .flatMap((p) => p.runs.map((run) => ({ producer: p, run }))) // Create pairs of producer and run
    .filter((item) => item.run.price !== "N/A" && item.run.deliveryTime !== "N/A") // Filter out invalid runs
    .sort((a, b) => compareFn(a.run, b.run))[0]; // Sort based on the compare function and take the best

  if (!bestProducerRun) {
    return { price: null, name: null, logo: null, id: null, deliveryTime: null, partner: false };
  }

  const { producer, run } = bestProducerRun;
  return {
    price: run.price,
    name: producer.name,
    logo: producer.logo,
    id: producer.id,
    deliveryTime: run.deliveryTime,
    partner: producer.partner,
  };
};

const getLowestPriceData = (producers: Producer[]): BestOfferData => {
  // Compare based on price (lower is better)
  return findBestOffer(
    producers,
    (a, b) => parseCurrencyString(a.price) - parseCurrencyString(b.price),
  );
};

const getFastestDeliveryData = (producers: Producer[]): BestOfferData => {
  // Compare based on the start of the delivery time range (lower is better)
  return findBestOffer(
    producers,
    (a, b) => parseDeliveryTime(a.deliveryTime) - parseDeliveryTime(b.deliveryTime),
  );
};

// Generates a deterministic-enough ID for variations
const generateVariationId = (index: number, keys: string[]): string => {
  // Combine index and sorted keys for a more stable ID than Math.random
  const keyString = keys.join("_");
  // Basic hash function (djb2) - replace with crypto if more robustness needed
  let hash = 5381;
  const combined = `${index}_${keyString}`;
  for (let i = 0; i < combined.length; i++) {
    hash = (hash * 33) ^ combined.charCodeAt(i);
  }
  return `var-${(hash >>> 0).toString(36)}`; // Use unsigned right shift for positive hash
};

// --- Main Event Handler ---

export default defineEventHandler(async (event) => {
  try {
    const originalPayloads = (await readBody(event)) as PayloadItem[];
    const slug = getRouterParam(event, "slug");
    const headers = getRequestHeaders(event);

    // Configure API client
    const config = useRuntimeConfig();
    const api = $fetch.create({
      baseURL: config.public.baseURL,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Referer: headers.referer || "", // Provide default if undefined
        Authorization: headers.authorization || "", // Provide default if undefined
      } as HeadersInit,
      credentials: "include",
    });

    // 1. Fetch all data concurrently using async/await
    // Create an array of promises for each API call
    const apiPromises = originalPayloads.map(async (payload) => {
      try {
        const response = await api<ApiResponse>(`/finder/categories/${slug}/products`, {
          method: "POST",
          body: payload,
        });

        return {
          originalPayload: payload,
          status: "fulfilled" as const,
          value: response,
        };
      } catch (error) {
        return {
          originalPayload: payload,
          status: "rejected" as const,
          error,
        };
      }
    });

    // Wait for all promises to settle
    const settledResults = await Promise.all(apiPromises);

    // 2. Extract all producers from all successful API responses
    const allExtractedProducers: ExtractedProducerInfo[] = settledResults.flatMap((result) => {
      if (result.status !== "fulfilled" || !result.value?.data) {
        return []; // Skip failed or empty results
      }
      const suppliers = result.originalPayload.suppliers || [];
      // Process each item within the 'data' array of a single API response
      return result.value.data.flatMap((item) => extractProducersFromItem(item, suppliers));
    });

    // 3. Group producers by their product configuration (fingerprint)
    const producersGroupedByProduct: Record<
      string,
      { producers: Producer[]; representativeItem: ResultItem | null }
    > = allExtractedProducers.reduce(
      (groups, { producer, sourceItem }) => {
        const fingerprint = producer.productKeys.join("|");
        if (!groups[fingerprint]) {
          groups[fingerprint] = { producers: [], representativeItem: sourceItem }; // Initialize group, store first source item
        }
        // Avoid duplicate producers within the same group
        if (!groups[fingerprint].producers.some((p) => p.id === producer.id)) {
          groups[fingerprint].producers.push(producer);
          // Update representative item if the current one is 'better' (e.g., more complete), simple logic for now: keep the first non-null one encountered
          if (!groups[fingerprint].representativeItem) {
            groups[fingerprint].representativeItem = sourceItem;
          }
        }
        return groups;
      },
      {} as Record<string, { producers: Producer[]; representativeItem: ResultItem | null }>,
    );

    // 4. Create Variation objects from grouped producers
    const variations: Variation[] = Object.entries(producersGroupedByProduct).map(
      ([fingerprint, group], index) => {
        const { producers, representativeItem } = group;
        const productKeys = fingerprint.split("|");
        const originalPayload = originalPayloads.find((payload) =>
          settledResults.some(
            (result) =>
              result.status === "fulfilled" &&
              result.originalPayload === payload &&
              result.value?.data?.some((item) =>
                extractProducersFromItem(item, payload.suppliers || []).some(
                  (info) => info.producer.productKeys.join("|") === fingerprint,
                ),
              ),
          ),
        );
        // Determine product config, quantity, type prioritizing representative item, then first producer, then defaults
        const productConfig = extractProductConfig(representativeItem, originalPayload);
        const defaultQuantity = producers[0]?.runs[0]?.quantity || 200; // Fallback quantity
        const defaultType = representativeItem?.results?.type || "print"; // Fallback type

        // Find original payload corresponding to this product group (if any)
        // This is heuristic: find the first original payload whose API call contained a producer in this group
        const originatingPayload = originalPayloads.find((payload) =>
          settledResults.some(
            (apiRes) =>
              apiRes.originalPayload === payload &&
              apiRes.status === "fulfilled" &&
              apiRes.value?.data?.some((item) =>
                extractProducersFromItem(item, payload.suppliers || []).some(
                  (info) => info.producer.productKeys.join("|") === fingerprint,
                ),
              ),
          ),
        );

        // Use quantity/type from originating payload if found, otherwise use defaults
        const quantity = originatingPayload?.quantity ?? defaultQuantity;
        const type = originatingPayload?.type ?? defaultType;

        return {
          // Base variation properties
          id: generateVariationId(index, productKeys), // Use deterministic ID
          ...originatingPayload, // Spread properties from the *most relevant* original payload
          suppliers: originatingPayload?.suppliers || [], // Ensure suppliers array exists
          productKeys: productKeys,
          producers: producers,
          originalData: representativeItem, // The source item representing this group
          product: productConfig, // Use extracted config
          quantity: quantity, // Use determined quantity
          type: type, // Use determined type

          // Calculated properties
          availableQuantities: calculateAvailableQuantities(producers),
          bestPriceData: getLowestPriceData(producers),
          bestDeliveryTimeData: getFastestDeliveryData(producers),
          uniqueProducers: producers.length,
          originalPayload: originatingPayload,
        };
      },
    );

    // 5. Create the list of unique producers across all variations with their variant counts
    const uniqueProducersWithCounts = Object.values(
      variations
        .flatMap((v) => v.producers)
        .reduce(
          (acc, producer) => {
            if (!acc[producer.id]) {
              acc[producer.id] = { ...producer, variants: 0 };
            }
            acc[producer.id].variants += 1;
            return acc;
          },
          {} as Record<string, Producer & { variants: number }>,
        ),
    );

    // Add handling for variations that *failed* the API call originally
    const failedVariations: Variation[] = settledResults
      .filter((result) => result.status === "rejected")
      .map((result, index) => {
        const originalPayload = result.originalPayload;
        // Use a fingerprint based on the payload itself if possible, or just index for uniqueness
        const fallbackKeys = Object.entries(originalPayload.product || {})
          .map(([k, v]) => `${k}:${v}`)
          .sort();
        const id = generateVariationId(index + variations.length, fallbackKeys); // Offset index

        return {
          id: id,
          ...originalPayload,
          suppliers: originalPayload.suppliers || [],
          availableQuantities: [],
          bestPriceData: getLowestPriceData([]), // Empty data
          bestDeliveryTimeData: getFastestDeliveryData([]), // Empty data
          uniqueProducers: 0,
          producers: [],
          originalData: null,
          productKeys: fallbackKeys,
          product: originalPayload.product || {},
          quantity: originalPayload.quantity || 0, // Default quantity if missing
          type: originalPayload.type || "unknown", // Default type if missing
          originalPayload,
        };
      });

    // Combine successful and failed variations
    const finalVariations = [...variations, ...failedVariations];

    // 6. Return the final structure
    return {
      results: finalVariations,
      producers: uniqueProducersWithCounts,
    };
  } catch (error: unknown) {
    console.error("API Endpoint Error:", error);
    // Type assertion for error object
    const errorObj = error as {
      statusCode?: number;
      statusMessage?: string;
      message?: string;
      data?: unknown;
    };
    // Try to extract meaningful details
    const statusCode = errorObj?.statusCode || (error instanceof Error ? 500 : 503);
    const message =
      errorObj?.statusMessage ||
      errorObj?.message ||
      (error instanceof Error ? error.message : "Internal Server Error");
    const responseData = errorObj?.data; // Include response data if available (e.g., from $fetch error)

    // Log more context if available
    if (responseData) {
      console.error("Error Response Data:", responseData);
    }

    throw createError({
      statusCode: statusCode,
      statusMessage: message, // Use statusMessage for Nuxt/H3 errors
      data: responseData, // Optionally pass through error data
    });
  }
});
