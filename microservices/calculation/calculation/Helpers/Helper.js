const crypto = require("crypto");

const DEFAULT_FORMAT = {
    'a0': {
        'width': 841,
        'height': 1188,
        'cm': 9999.49,
        'mm': 999949,
        'm': 0.999949,
        'in': 1549.924050,
        'ft': 10.763361,
        'yd': 1.195929
    },
    'a1': {
        'width': 594,
        'height': 841,
        'mm': 499554,
        'cm': 4995.54,
        'm': 0.499554,
        'in': 774.310249,
        'ft': 5.377155,
        'yd': 0.597462
    },
    'a2': {
        'width': 420,
        'height': 594,
        'mm': 249480,
        'cm': 2494.8,
        'm': 0.24948,
        'in': 386.694773,
        'ft': 2.685380,
        'yd': 0.298376
    },
    'a3': {
        'width': 297,
        'height': 420,
        'mm': 124740,
        'cm': 1247.4,
        'm': 0.12474,
        'in': 193.347387,
        'ft': 1.342690,
        'yd': 0.149188
    },
    'a4': {
        'width': 210,
        'height': 297,
        'mm': 62370,
        'cm': 623.7,
        'm': 0.06237,
        'in': 96.673693,
        'ft': 0.671345,
        'yd': 0.074594
    },
    'a5': {
        'width': 148,
        'height': 210,
        'mm': 31080,
        'cm': 310.8,
        'm': 0.03108,
        'in': 48.174096,
        'ft': 0.334542,
        'yd': 0.037171
    },
    'a6': {
        'width': 105,
        'height': 148,
        'mm': 15540,
        'cm': 155.4,
        'm': 0.01554,
        'in': 24.087048,
        'ft': 0.167271,
        'yd': 0.018586
    },
    'a7': {
        'width': 74,
        'height': 105,
        'mm': 7770,
        'cm': 77.7,
        'm': 0.00777,
        'in': 12.043524,
        'ft': 0.083636,
        'yd': 0.009293
    },
    'a8': {
        'width': 52,
        'height': 74,
        'mm': 3848,
        'cm': 38.48,
        'm': 0.003848,
        'in': 5.964412,
        'ft': 0.041420,
        'yd': 0.004602
    },
    'a9': {
        'width': 37,
        'height': 52,
        'mm': 1924,
        'cm': 19.24,
        'm': 0.001924,
        'in': 2.982206,
        'ft': 0.020710,
        'yd': 0.002301
    },
    'a10': {
        'width': 26,
        'height': 37,
        'mm': 962,
        'cm': 9.62,
        'm': 0.000962,
        'in': 1.491103,
        'ft': 0.010355,
        'yd': 0.001151
    }
};

/**
 * Generates a list of numbers based on the start, end, step, and limit parameters.
 *
 * @param {number} start - The starting value of the list.
 * @param {number} end - The ending value of the list.
 * @param {number} step - The increment or decrement value of each element in the list.
 * @param {number} [limit=Infinity] - The maximum number of elements in the list. Defaults to Infinity.
 * @returns {number[]} - The generated list of numbers.
 */
const generateList = (start, end, step, limit = Infinity) => {
    let adjustedStart = start === 0 ? start + step : start;
    limit = limit === 0 ? Infinity : limit;
    return Array.from({length: Math.min(Math.floor((end - adjustedStart) / step) + 1, limit)}, (_, i) => adjustedStart + i * step);
};

/**
 * Crosses multiple arrays to generate all possible combinations of their elements.
 *
 * @param {Array<Array<*>>} xss - The arrays to be crossed.
 * @returns {Array<Array<*>>} - The crossed combinations of elements.
 */
const crossMachine = (xss) =>
    xss.reduce((xs, ys) => xs.flatMap(x => ys.map(y => [...x, y])), [[]])

/**
 * Generates all possible combinations of values from an object.
 *
 * @param {Object} o - The object to generate combinations from.
 * @param {Array} [keys=Object.keys(o)] - The keys to use from the object.
 * @param {Array} [vals=Object.values(o)] - The values to use from the object.
 * @returns {Array} An array of objects representing the generated combinations.
 */
const combinations = (o, keys = Object.keys(o), vals = Object.values(o)) =>
    crossMachine(vals).map(xs => Object.fromEntries(xs.map((x, i) => [keys[i], x])))

/**
 * Finds the nearest number to a given number within a specified interval.
 *
 * @param {number} number - The number for which to find the nearest number.
 * @param {number} interval - The interval within which to find the nearest number.
 * @returns {number} The nearest number to the given number within the specified interval.
 */
const findUpperInRangeOrSelf = (
    number,
    interval
) => {
    const remainder = number % interval;
    const lowerBound = number - remainder;
    return interval === 1 ? number : lowerBound + interval;
    // return (number - lowerBound < upperBound - number) ? lowerBound : upperBound;
};

/**
 * Checks the difference between two lists based on a specific property.
 * @param {Array} list - The first list to compare.
 * @param {Array} second_list - The second list to compare.
 * @returns {Object} - An object containing the found and not found entries.
 */
const checkDiffList = (
    list,
    second_list
) => {
    const found = [];
    const notFound = [];

    second_list.forEach(entry => {
        if (list.some(item => item.pm === entry.slug)) {
            found.push(entry.slug);
        } else {
            notFound.push(entry.slug);
        }
    });

    return {found, notFound};
};

/**
 * Adds the nearest number to a range list if it is not already included.
 *
 * @param {number[]} rangeList - The list of numbers in a range.
 * @param {number} nearestNumber - The number to add to the range list.
 * @returns {number[]} - The updated range list.
 */
const addNearestNumberToRangeList = (
    rangeList,
    nearestNumber
) => {
    if (!rangeList.includes(nearestNumber)) {
        rangeList.push(nearestNumber);
        rangeList.sort((a, b) => a - b);
    }
    return rangeList;
};

/**
 * Updates the range list of an item by inserting a nearest number and its surrounding range.
 *
 * @param {Object} item - The item to update.
 * @param {number} nearestNumber - The nearest number to insert.
 * @param {number} rangeAround - The range of numbers to include before and after the nearest number.
 * @returns {Object} The updated item.
 */
const updateRangeList = (
    item,
    nearestNumber,
    rangeAround
) => {
    const index = item.range_list.indexOf(nearestNumber);
    if (index !== -1) {
        const before = item.range_list.slice(Math.max(0, index - rangeAround), index);
        const after = item.range_list.slice(index + 1, index + (rangeAround + 1));
        item.range_list = [...before, nearestNumber, ...after];
    } else {
        item.range_list = [];
    }
    return item;
};

/**
 * Determines if a request has a quantity.
 *
 * @param {string} key - The key to identify the item.
 * @param {Object[]} result - The list of items.
 * @param {number|null} quantity - The quantity of the item.
 * @param {Object[]} free_entry - The list of free entries.
 * @param {number} range_around - The range around the nearest number.
 * @returns {Object[]} - The updated list of items.
 */
const requestHasQuantity = (
    key = 'digital',
    result = [],
    quantity = null,
    free_entry = [],
    range_around = 2
) => {

    if (quantity) {
        result.forEach(item => {
            let nearestNumber;
            if (free_entry.length) {
                const free = free_entry.find(x => x.slug === item.pm);
                if (free && free.enable) {
                    nearestNumber = findUpperInRangeOrSelf(quantity, free.interval) || quantity;
                    item.range_list = addNearestNumberToRangeList(item.range_list, nearestNumber);
                    item = updateRangeList(item, nearestNumber, range_around);
                }
            }
        });

        const {notFound} = checkDiffList(result, free_entry);
        if (notFound.length > 0) {
            for (const notFoundElement of notFound) {
                const free = free_entry.find(x => x.slug === notFoundElement);
                if (free && free.enable) {
                    let number = findUpperInRangeOrSelf(quantity, free.interval) || quantity;
                    result.push({
                        [key]: notFoundElement,
                        quantity_range_start: 0,
                        quantity_range_end: 0,
                        quantity_incremental_by: 0,
                        range_list: [number]
                    })
                }
            }
        }
    }

    return result;
}

/**
 * Limits the range list of each item in the result array based on the specified limits.
 *
 * @param {Array} result - The result array to be processed.
 * @param {Array} limits_list - The list of limits to apply.
 * @param {number} limit - The default limit value if no ceiling is specified.
 * @returns {Array} The result array with the range list of each item limited.
 */
const limitResult = (
    result = [],
    limits_list = [],
    limit = Infinity
) => {
    result.forEach(item => {
        const ceiling = limits_list.filter(x => x.slug === item.pm)[0]?.ceiling || limit;
        // Limit the range list to the specified limit
        item.range_list = item.range_list.slice(0, ceiling);
    });
    return result;
};

/**
 * Iterates through a list of objects and filters each object's range_list property
 * based on the selectedValues array.
 *
 * @param {Array} results - The list of objects to be filtered.
 * @param {Array} selectedValues - The list of values to filter the range_list property with.
 * @return {Array}
 */
const selectNumbersFromRangeList = (
    results,
    selectedValues
) => {
    selectedValues.length > 0 ?
        results.forEach(item => {
            item.range_list = item.range_list.filter(num => selectedValues.includes(num));
        }) : results;
    return results;
};

/**
 * Merge objects in an array based on a dynamic key.
 *
 * @param {Array} data - The array of objects to be merged.
 * @param {string} key - The key to group the objects by.
 * @param {Array} limits_list - Optional. An array of limit objects.
 * @param {Number} limits_list.ceiling - Optional. An array of limit objects.
 * @param {number} limit - Optional. The maximum number of items in the range list.
 * @param {number|null} quantity - Optional. The quantity number what can be used to select only number before and after.
 * @param {number} range_around - Optional. The around value to list the quantity with amount of indexes before and after if exists.
 * @param {Array} free_entry - Optional. The interval range  what the quantity should hold on.
 * @param {Array} range_list - Optional. The list of values should be showing on the screen.
 * @returns {Array} - The merged array of objects.
 */
const mergeByDynamicKey = (
    data,
    key,
    limits_list = [],
    limit = Infinity,
    quantity = null,
    range_around = 2,
    free_entry = [],
    range_list = []
) => {
    const result = [];

    // Create a map to group objects by the specified key
    const keyMap = new Map();

    data.forEach(item => {
        const keyValue = item[key];
        const {quantity_range_start, quantity_range_end, quantity_incremental_by, range_list} = item;

        if (!keyMap.has(keyValue)) {
            // Initialize a new entry in the map
            keyMap.set(keyValue, {
                [key]: keyValue,
                quantity_range_start: Number.POSITIVE_INFINITY,
                quantity_range_end: Number.NEGATIVE_INFINITY,
                quantity_incremental_by: 0,
                range_list: []
            });
        }

        const mergedItem = keyMap.get(keyValue);

        // Update the range start and end
        mergedItem.quantity_range_start = Math.min(mergedItem.quantity_range_start, quantity_range_start);
        mergedItem.quantity_range_end = Math.max(mergedItem.quantity_range_end, quantity_range_end);

        // Update the range list and remove duplicates
        mergedItem.range_list = Array.from(new Set([...mergedItem.range_list, ...range_list])).sort((a, b) => a - b);

        // Update the incremental by value (assuming the smallest incremental_by is the correct one)
        if (mergedItem.quantity_incremental_by === 0 || quantity_incremental_by < mergedItem.quantity_incremental_by) {
            mergedItem.quantity_incremental_by = quantity_incremental_by;
        }
    });

    // Convert map back to an array
    keyMap.forEach((value) => {
        result.push(value);
    });


    return limitResult(
        requestHasQuantity(
            key,
            selectNumbersFromRangeList(result, range_list),
            quantity,
            free_entry,
            range_around
        ),
        limits_list,
        limit
    )
};

/**
 * Retrieves all unique keys from an array of objects while excluding specific keys.
 *
 * @param {Array} data - The array of objects to extract keys from.
 * @param {Array} exclude - An array of keys to exclude from the result.
 * @returns {Array} An array containing unique keys from the objects in the input array.
 * @throws {TypeError} If the data parameter is not an array or if any item in the array is not an object.
 */
const getAllKeysFromArrayObject = (
    data = [],
    exclude = []
) => {
    // Validate that data is an array of objects
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Expected each item in the array to be an object.");
    }

    // Collect all 'key' values, excluding the keys specified in 'exclude'
    const filteredKeys = data.map(item => {
        if (item.key && !exclude.includes(item.key)) {
            return item.key; // Return the value of the 'key' property
        }
        return null;
    }).filter(key => key !== null); // Remove null values

    return [...new Set(filteredKeys)]; // Return unique keys
};

/**
 * Extracts specified keys from an array of objects based on the selected keys and optional exclusion list.
 * @param {Array.<Object>} data - The array of objects to extract from.
 * @param {Array.<string>} selectedKeys - The keys to include in the extracted object.
 * @param {Array.<string>} exclude - The keys to exclude from the extracted object.
 * @returns {Object} - The extracted object containing the selected keys.
 */
const extractObjectFromArrayObject = (
    data = [],
    selectedKeys = [],
    exclude = []
) => {
    // Validate that 'data' is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected 'data' to be an array.");
    }

    // Validate that each item in 'data' is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Expected each item in 'data' to be a non-null object.");
    }

    // Validate that 'selectedKeys' is an array
    if (!Array.isArray(selectedKeys)) {
        throw new TypeError("Expected 'selectedKeys' to be an array.");
    }

    // Validate that 'exclude' is an array
    if (!Array.isArray(exclude)) {
        throw new TypeError("Expected 'exclude' to be an array.");
    }
    // Create an object to store the result
    const result = {};

    // Loop through each object in the array
    data.forEach(item => {
        // Filter keys based on the selectedKeys array
        selectedKeys.forEach(key => {
            if (!exclude.includes(key) && item[key] !== undefined) { // Ensure the key exists in the object
                result[key] = item[key]; // Add key-value pair to result if the key is selected
            }
        });
    });

    return result; // Return the final merged object
};

/**
 * Extracts key-value pairs from an array of objects based on specified criteria.
 *
 * @param {Array} data - The array of objects from which values are to be extracted.
 * @param {Array} exclude - An array of keys to be excluded while extracting values.
 * @returns {Object} - An object containing key-value pairs extracted based on the criteria.
 */
const extractAllValuesFromArrayObject = (
    data = [],
    exclude = []
) => {
    // Validate that 'data' is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected 'data' to be an array.");
    }

    // Validate that each item in 'data' is an object and has a 'key' and 'value'
    if (!data.every(item => typeof item === 'object' && item !== null && 'key' in item && 'value' in item)) {
        throw new TypeError("Each item in the array must be an object with 'key' and 'value' properties.");
    }

    // Validate that 'exclude' is an array
    if (!Array.isArray(exclude)) {
        throw new TypeError("Expected 'exclude' to be an array.");
    }

    // Create an object to store the result
    const result = {};

    // Loop through each object in the array
    data.forEach(item => {
        // Ensure we exclude 'dynamic' and 'divider', as well as any keys specified in the exclude array
        if (!exclude.includes(item.key) && item.value !== undefined) {
            result[item.key] = item.value; // Add key-value pair to the result
        }
    });

    return result; // Return the final merged object with all key-value pairs
};

/**
 * Extracts selected values from an array of objects based on provided keys.
 *
 * @param {Array} data - The array of objects to extract values from.
 * @param {Array} selectedKeys - The keys to extract values for.
 * @returns {Array} - An array of values extracted from the objects based on the selected keys.
 * @throws {TypeError} - If data is not an array or if any item in data is not an object.
 */
const getSelectedValuesFromArrayObject = (
    data = [],
    selectedKeys = []
) => {

    // Validate that data is an array of objects
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Expected each item in the array to be an object.");
    }

    // Collect values for the selected keys
    const values = data.map(item => {
        return selectedKeys.map(key => item[key]).filter(val => val !== undefined);
    });

    // Flatten the array and return the result
    return values.flat();
};

/**
 * Extracts the value associated with the underscore key '_' in an object from an array of objects based on a specified value to match.
 * If the value is not found or the input data is invalid, it returns null.
 *
 * @param {Array.<Object>} data - The array of objects to search through.
 * @param {string} valueToMatch - The value to match within the objects in the array.
 * @returns {any|null} - The value associated with the underscore key '_' in the object that matches the 'valueToMatch', or null if not found.
 * @throws {TypeError} - If 'data' is not an array or if any item in 'data' is not an object.
 */
const extractObjectWithUnderscoreByValue = (
    data = [],
    valueToMatch = ''
) => {
    // Validate that 'data' is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected 'data' to be an array.");
    }

    // Validate that each item in 'data' is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Each item in the array must be an object.");
    }

    // Find the first object where 'value' matches the 'valueToMatch' and it has the '_'
    const foundObject = data.find(item => item.value === valueToMatch && item['_']);

    // Return the object with '_' or null if not found
    return foundObject ? foundObject['_'] : null;
};

/**
 * Returns the divider value associated with a specific key in a given array of objects.
 *
 * @param {Array} data - The array of objects to search through.
 * @param {string} key - The key to match against in each object.
 * @param {string} slug - The value of the key to search for in the objects.
 * @returns {any} The divider value corresponding to the matched key and slug, or null if not found.
 * @throws {TypeError} If data is not an array, if any item in the array is not an object, or if slug is not a non-empty string.
 */
const getDividerByKey = (
    data = [],
    slug,
    key = 'slug'
) => {
    // Validate that data is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    // Validate that each item in the array is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Each item in the array must be an object.");
    }

    // Validate that slug is a string
    if (typeof slug !== 'string' || !slug.trim()) {
        throw new TypeError("Slug must be a non-empty string.");
    }

    // Initialize dividerValue to null
    let dividerValue = null;

    // Loop through the array to find the matching slug and return the divider value
    data.forEach(item => {
        if (item[key] === slug) {
            dividerValue = item.divider !== undefined ? item.divider : null; // Capture the divider if it exists
        }
    });

    // Return the divider value or null if not found
    return dividerValue;
};

/**
 * Group the data array based on a specified divider key in each object.
 * @param {Array} data - The array of objects to be grouped.
 * @throws {TypeError} - If data is not an array or if any item in the array is not an object.
 * @returns {Object} - An object where keys are divider values and values are arrays with items grouped under each divider.
 */
const groupByDivider = (
    data = []
) => {
    // Validate that data is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    // Validate that each item in the array is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Each item in the array must be an object.");
    }

    // Initialize an empty object to hold the grouped results
    const grouped = {};

    // Loop through each item in the data array
    data.forEach(item => {
        const {divider} = item; // Destructure the divider from the item

        // If the divider is not already a key in the grouped object, initialize it as an empty array
        if (!grouped[divider]) {
            grouped[divider] = [];
        }

        // Push the current item into the appropriate divider group
        grouped[divider].push(item);
    });

    return grouped; // Return the grouped object
};

/**
 * Function that groups an array of objects based on a divider property, with calculations and copying reference items.
 * @param {Array<Object>} data - The array of objects to be grouped
 * @param {Array<Object>} keysToCheck - The array of objects to be grouped
 * @throws {TypeError} If data is not an array or if any item in the array is not an object
 * @returns {Object} Grouped object where each key corresponds to a divider, with flags for calculations and copied reference items
 */
const groupByDividerWithCalcRefCopy = (data = [], keysToCheck = []) => {
    // Validate that data is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    // Validate that each item in the array is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Each item in the array must be an object.");
    }

    // Validate that keysToCheck is an array of strings
    if (!Array.isArray(keysToCheck) || !keysToCheck.every(key => typeof key === 'string')) {
        throw new TypeError("Expected keysToCheck to be an array of strings.");
    }

    // Initialize an empty object to hold the grouped results
    const grouped = {};

    // Loop through each item in the data array
    data.forEach(item => {
        const {divider, box} = item; // Destructure the divider and box from the item

        // If the divider is not already a key in the grouped object, initialize it
        if (!grouped[divider]) {
            grouped[divider] = {has_calc_ref: {}, data: []};

            // Initialize has_calc_ref for each key we're checking
            keysToCheck.forEach(key => {
                grouped[divider].has_calc_ref[key] = false;
            });
        }

        // Push item to the corresponding group
        grouped[divider].data.push(item);

        // Update has_calc_ref flags for relevant keys
        keysToCheck.forEach(key => {
            if (box?.calc_ref === key) {
                grouped[divider].has_calc_ref[key] = true;
            }
        });
    });

    // Get all dividers for later reference
    const dividers = Object.keys(grouped);
    if (dividers.length < 2) {
        throw new Error(
            "You need to add more than one unique divider or disable the divider functionality."
        );
    }
    // Loop through each group to check for missing calc_ref
    dividers.forEach(currentDivider => {
        const currentGroup = grouped[currentDivider];
        const hasWeight = currentGroup.data.some(item => item.box?.calc_ref === 'weight');
        const hasMaterial = currentGroup.data.some(item => item.box?.calc_ref === 'material');

        if (hasWeight && hasMaterial) {

            keysToCheck.forEach(key => {
                // If the current group does not have box.calc_ref equal to the current key
                if (!currentGroup.has_calc_ref[key]) {
                    // Look for a box.calc_ref equal to the current key in other groups
                    for (const otherDivider of dividers) {
                        if (otherDivider !== currentDivider) { // Skip the current group
                            const otherGroup = grouped[otherDivider];

                            // Find an item in the other group that has box.calc_ref equal to the current key
                            const refItem = otherGroup.data.find(i => i.box?.calc_ref === key);
                            if (refItem) {
                                // Copy the reference item to the current group
                                currentGroup.data.push({...refItem}); // Add a copy of refItem to current group
                                currentGroup.has_calc_ref[key] = true; // Update the flag since we copied a reference
                                break; // Stop searching once we've found a reference
                            }
                        }
                    }
                }
            });
        }
        // Update calculateable status if the group has both weight and material in box.calc_ref
        currentGroup.calculateable = hasWeight && hasMaterial;


        // Track what’s missing
        currentGroup.missings = [];
        if (!hasWeight){
            currentGroup.missings.push("weight");
        }
        if (!hasMaterial){
            currentGroup.missings.push("material");
        }
    });

    return grouped; // Return the grouped object
};

/**
 * Combines multiple arrays of data into a single merged result object.
 * @param {Array} collection - An array of items to be combined.
 * @returns {object} - The merged result object with combined data from all items in the collection.
 */
const combineMultipleArrays = (
    collection
) => {
    // Initialize an empty object to store merged results
    const mergedResult = {
        category: {},
        items: [],
        objects: [],
        margins: [],
        dlv: [],
        machine: {},
        color: {},
        row_price: 0,
        duration: {},
        calculation: {},
        price: 0,
        error: {message: '', status: 200}
    };

    // Iterate through each item in the collection
    collection.forEach(item => {
        // Merge category
        mergedResult.category = {
            ...mergedResult.category,
            ...item.category,
            // Merge arrays if needed (e.g., ranges)
            ranges: [
                ...(mergedResult.category.ranges || []),
                ...(item.category.ranges || [])
            ],
            limits: [
                ...(mergedResult.category.limits || []),
                ...(item.category.limits || [])
            ],
            boops: [
                ...(mergedResult.category.boops || []),
                ...(item.category.boops || [])
            ]
        };

        // Merge items
        mergedResult.items.push(...item.items);

        // Merge objects
        mergedResult.objects.push(...item.objects);

        // Merge margins
        if (Array.isArray(item.margins)) {
            mergedResult.margins.push(...item.margins);
        } else {
            mergedResult.margins.push(item.margins);
        }


        // Merge dlv
        mergedResult.dlv.push(...item.dlv);

        // Merge machine (assuming only one machine, you can customize this logic)
        mergedResult.machine = {
            ...mergedResult.machine,
            ...item.machine
        };

        // Merge color
        mergedResult.color = {
            ...mergedResult.color,
            ...item.color,
            run: [
                ...(mergedResult.color.run || []),
                ...(item.color.run || [])
            ],
            runs: [
                ...(mergedResult.color.runs || []),
                ...(item.color.runs || [])
            ],
            dlv: [
                ...(mergedResult.color.dlv || []),
                ...(item.color.dlv || [])
            ],
            price_list: [
                ...(mergedResult.color.price_list || []),
                ...(item.color.price_list || [])
            ]
        };

        // Sum up prices and row prices
        mergedResult.row_price += item.row_price;
        mergedResult.price += item.price;

        // Merge duration
        mergedResult.duration = {
            ...mergedResult.duration,
            ...item.duration
        };

        // Merge calculation
        mergedResult.calculation = {
            ...mergedResult.calculation,
            ...item.calculation,
            // Example: You can sum the fields you need to aggregate
            // Example for total area calculation
            exact_used_amount_and_area_of_sheet: (mergedResult.calculation.exact_used_amount_and_area_of_sheet || 0) + (item.calculation.exact_used_amount_and_area_of_sheet || 0),
            amount_of_sheets_needed: (mergedResult.calculation.amount_of_sheets_needed || 0) + (item.calculation.amount_of_sheets_needed || 0)
        };
    });

    return mergedResult;
};

/**
 * Check if the value is a valid number
 *
 * @param {any} value - The value to be checked
 * @returns {boolean} - Returns true if the value is a valid number, false otherwise
 */
const isNumber = (value) => {
    return typeof value === 'number' && !Number.isNaN(value);
}

/**
 * Calculates the area of a rectangle based on the provided width and height.
 * @param {number} width - The width of the rectangle.
 * @param {number} height - The height of the rectangle.
 * @returns {object} An object containing the area calculated in different units:
 * - cm: Area in square centimeters (cm²)
 * - mm: Area in square millimeters (mm²)
 * - m: Area in square meters (m²)
 * - in: Area in square inches (in²)
 * - ft: Area in square feet (ft²)
 * - yd: Area in square yards (yd²)
 */
const calculateArea = (
    width,
    height
) => {
    // Area in sq mm
    const areaMm2 = width * height;

    // Convert to other units
    const areaCm2 = areaMm2 / 100; // 1 cm² = 100 mm²
    const areaM2 = areaMm2 / 1_000_000; // 1 m² = 1,000,000 mm²
    const areaIn2 = areaMm2 / 645.16; // 1 in² = 645.16 mm²
    const areaFt2 = areaIn2 / 144; // 1 ft² = 144 in²
    const areaYd2 = areaFt2 / 9; // 1 yd² = 9 ft²

    // Return the areas in different units
    return {
        cm: areaCm2,
        mm: areaMm2,
        m: areaM2,
        in: areaIn2,
        ft: areaFt2,
        yd: areaYd2,
    };
};

/**
 * Calculates the final price after applying margins based on the specified type.
 *
 * @param {number} price - The base price before applying margins.
 * @param {Array} margins - Object containing information about the margins applied.
 * @param {string} margins.type - The type of margin calculation, can be "fixed" or any other value.
 * @param {number} margins.value - The value of the margin to be applied (can be percentage or fixed amount based on the type).
 * @param {Boolean} divided - The value of the margin to be applied (can be percentage or fixed amount based on the type).
 * @param {number} divided_by - The value of the margin to be applied (can be percentage or fixed amount based on the type).
 * @returns {number} The final price after applying margins.
 */
const calculateMarginFullCalculation = (
    price,
    margins,
    divided = false,
    divided_by = 1
) => {
    if (margins.type === "fixed") {
        let value = divided? margins.value/divided_by:margins.value;
        return Number((Number(price) + Number(value/1000)).toFixed(2));
    } else {
        let percentage = 100 + Number(margins.value ?? 0.0)
        return Number((percentage * Number(price) / 100).toFixed(2));
    }
}

/**
 * Finds the appropriate discount slot for a given category and quantity
 * @param {Object} contract - The contract object containing general and categories discounts
 * @param {string} categoryId - The category ID to search for
 * @param {number} quantity - The quantity to check against slot ranges
 * @returns {Object|null} - Returns the matching slot object or null if no match found
 */
const findDiscountSlot = (contract, categoryId, quantity) => {
    // Helper function to find matching slot in an array of slots
    function findMatchingSlot(slots, quantity) {
        const slot = slots.find(slot => {
            const matchesFrom = quantity >= slot.from;
            const matchesTo = slot.to === -1 || quantity <= slot.to; // -1 means infinity
            return matchesFrom && matchesTo;
        });

        return slot || null;
    }
    // First, check if category-specific discount exists and is active
    if (contract && contract.categories && Array.isArray(contract.categories)) {
        const categoryDiscount = contract.categories.find(cat =>
            cat.id === categoryId && cat.status === true && cat.mode === "run"
        );

        if (categoryDiscount && categoryDiscount.slots) {
            const matchingSlot = findMatchingSlot(categoryDiscount.slots, quantity);
            if (matchingSlot) {
                return matchingSlot;
            }
        }
    }


    // Fallback to general discount if category-specific not found or doesn't match
    if (contract &&
        contract.general &&
        contract.general.status === true &&
        contract.general.mode === "run" &&
        contract.general.slots) {
        const matchingSlot = findMatchingSlot(contract.general.slots, quantity);
        if (matchingSlot) {
            return matchingSlot;
        }
    }

    // Return null if no matching slot found
    return null;
}

/**
 * Calculates the discounted price based on the given price and discount parameters.
 * If the discount type is 'fixed', it adds the discount value to the price after dividing by a specified value.
 * If the discount type is 'percentage', it calculates the discounted price by applying the percentage discount to the price.
 *
 * @param {number} price - The original price before discount.
 * @param {Array} discount - An object representing the discount applied.
 * @param {string} discount.type - The type of the discount ('fixed' or 'percentage').
 * @param {number} discount.value - The value of the discount to be applied.
 * @param {boolean} [divided=false] - Specifies if the discount value should be divided by another value before applying.
 * @param {number} [divided_by=1] - The value by which the discount value should be divided.
 *
 * @returns {number} The discounted price after applying the discount.
 */
const _calculateDiscountFullCalculation = (
    price,
    discount,
    divided = false,
    divided_by = 1
) => {
    if (discount) {
        if (discount.type === "fixed") {
            let value = divided ? discount.value / divided_by : discount.value;
            return Number((Number(price) - Number(value / 1000)).toFixed(2));
        } else {
            let percentage = 100 - Number(discount.value ?? 0.0);
            return Number((percentage * Number(price) / 100).toFixed(2));
        }
    }

    return price;

}

/**
 * Calculates the profit based on the given price and margins.
 *
 * @param {number} price - The price of the product.
 * @param {Array} margins - The margins object containing type and value.
 * @param {string} margins.type - The type of margins ("fixed" or other).
 * @param {number} margins.value - The value of the margins.
 * @param {boolean} divided - The value of the margins.
 * @param {number} divided_by - The value of the margins.
 * @returns {string} The calculated profit with 2 decimal points.
 */
const calculateProfitFullCalculation = (
    price,
    margins,
    divided = false,
    divided_by = 1
) => {
    if (margins.type === "fixed") {
        let value = divided? margins.value/divided_by:margins.value;
        return (Number(value/1000)).toFixed(2)
    } else {
        return (Number(margins.value ?? 0.0) * price /100).toFixed(2)
    }
}

/**
 * Format the price object based on provided inputs.
 * @param {Array} category - Category information.
 * @param {number} quantity - Quantity of items.
 * @param {number} price - Price of the item.
 * @param {Array} dlv - Delivery information.
 * @param {Object} machine - Machine details.
 * @param {Array} margins - Array of margins.
 * @param {Array} discount - Array of discounts.
 * @param {number|null} dlv_query - Query for delivery.
 * @param {boolean} internal - Type of pricing.
 * @param {number} vat - Type of pricing.
 * @param {boolean} vat_override - Type of pricing.
 * @param {boolean} divided - Type of pricing.
 * @param {Number} divided_by - Type of pricing.
 * @returns {Array} - Array of formatted price objects.
 */
const formatPriceObject = (
    category,
    quantity,
    price,
    dlv,
    machine,
    margins = [],
    discount = [],
    dlv_query = null,
    internal = false,
    vat = 0,
    vat_override = false,
    divided = false,
    divided_by = 1,
) => {
    const output = [];

    // Default delivery if `dlv` is empty
    let delivery = dlv.length === 0
        ? [
            {days: 0, value: 0, mode: 'percentage'},
            {days: 1, value: 0, mode: 'percentage'},
            {days: 2, value: 0, mode: 'percentage'}
        ]
        : dlv;

    if (dlv_query || dlv_query === 0) {
        const filteredDlv = delivery.filter(item => item.days === dlv_query);
        delivery = filteredDlv.length === 0 ? [delivery[delivery.length - 1]] : filteredDlv;
        delivery[0].days = dlv_query;
    }

    const now = new Date(); // Current date
    for (let d of delivery) {
        // const {title, date} = calculateDeliveryDay(category[0].production_days, now, d.days); // Calculate delivery day
        let prices = {};
        const p = d.mode === "fixed"
            ? price + d.value
            : ((parseInt(d.value) + 100) * price / 100).toFixed(2);

        // Category gets returned as a single object not as an array 
        // I'll just need to update it's usage in the semiCalculation fixed & Sliding
        const vat_value = vat_override ? vat : parseFloat(category?.vat ?? 0);
        const v = parseInt(vat_value)+ 100;
        const selling_price_ex =  _calculateDiscountFullCalculation(
            calculateMarginFullCalculation(p, margins, divided, divided_by), discount, divided, divided_by);

        const selling_price_inc =  selling_price_ex * v / 100;
        const gross_price = calculateMarginFullCalculation(p, margins, divided, divided_by);


        prices = {
            pm: machine.pm,
            qty: quantity,
            dlv: {
                days: parseInt(d.days),
                title: '', // Add the delivery day title
                // date: date // Add the delivery date
            },
            gross_price: gross_price,
            gross_ppp: gross_price / quantity,
            p: internal ? p : gross_price,
            ppp: (internal ? p : gross_price) / quantity,
            selling_price_ex: selling_price_ex,
            selling_price_inc: selling_price_inc,
            profit: internal
                ? (selling_price_ex-p).toFixed(2)
                : null,
            discount: internal? discount: [],
            margins: internal ? margins : [],
            vat: vat_value,
            vat_p: selling_price_ex * vat_value / 100,
            vat_ppp: (selling_price_ex * vat_value / 100) / quantity,
        };

        output.push(prices);
    }
    return output;
};

/**
 * Takes an array of price objects and a tenant ID to merge and refactor the data.
 *
 * @param {Array} array - Array of price objects to be refactored
 * @param {string} tenant_id - The ID of the tenant
 * @returns {Array} - A new array of refactored price objects with hashed IDs
 */
const refactorPriceObject = (
    array,
    tenant_id
) => {
    // Initialize the merged output
    const mergedOutput = [];

    // Iterate through the maximum length
    for (const object of array) {
        let id = `${process.env.APP_KEY}_${object.p}_${object.dlv.days}_${object.qty}_${tenant_id}_${object.ppp}_${process.env.APP_KEY}`
        mergedOutput.push({
            id: crypto.createHash(process.env.HASH_TYPE).update(id).digest('hex'),
            qty: object.qty,
            dlv: object.dlv,
            pm: object.pm,
            gross_price: object.gross_price,
            gross_ppp: object.gross_ppp,
            p: object.p,
            ppp: object.ppp,
            selling_price_ex: object.selling_price_ex,
            selling_price_inc: object.selling_price_inc,
            profit: object.profit,
            discount: object.discount,
            margins: object.margins,
            vat: object.vat,
            vat_p: object.vat_p,
            vat_ppp: object.vat_ppp,
        });
    }

    return mergedOutput;
}

/**
 * Merges input price objects into a single output object based on quantity and prices.
 *
 * @param {Array<Array<Object>>} input - The input array of arrays of price objects to merge.
 * @param {string} tenant_id - The ID of the tenant for identification.
 * @returns {Array<Object>} - The merged output array of price objects.
 */
const mergePriceObject = (
    input,
    tenant_id
) => {
    // Initialize the merged output
    const mergedOutput = [];
    const maxLength = Math.max(...input.map(arr => arr.length));

    // Get last entries from each array for fallback
    const lastEntries = input.map(arr => arr[arr.length - 1]);

    // Iterate through the maximum length
    for (let i = 0; i < maxLength; i++) {
        const mergedObject = {
            id: null,
            qty: 0,
            dlv: {},
            gross_price: '0.00',
            gross_ppp: '0.000',
            p: '0.00',
            ppp: '0.0000',
            selling_price_ex: '0.00',
            selling_price_inc: '0.00',
            profit: '0.00',
            discount: [],
            margins: {},
            vat: 0, // Changed to a number for simplicity
            vat_p: 0, // Changed to a number for simplicity
            vat_ppp: 0
        };

        // Iterate through all input arrays
        for (let j = 0; j < input.length; j++) {
            const obj = input[j][i] || {}; // Get the current object or an empty object if undefined

            // If there are properties to sum
            if (obj) {
                // Set quantity from the first available object
                if (mergedObject.qty === 0 && obj.qty) {
                    mergedObject.qty = obj.qty; // Keep the first non-zero qty
                    mergedObject.dlv = obj.dlv; // Keep the dlv the same
                    mergedObject.discount = obj.discount; // Keep the discount the same
                    mergedObject.margins = obj.margins; // Keep the discount the same
                    mergedObject.vat = obj.vat; // Keep the discount the same
                }

                // Accumulate prices
                mergedObject.gross_price = (parseFloat(mergedObject.gross_price) + parseFloat(obj.gross_price || '0')).toFixed(2);
                mergedObject.gross_ppp = (parseFloat(mergedObject.gross_ppp) + parseFloat(obj.gross_ppp || '0')).toFixed(3);
                mergedObject.p = (parseFloat(mergedObject.p) + parseFloat(obj.p || '0')).toFixed(2);
                mergedObject.ppp = (parseFloat(mergedObject.ppp) + parseFloat(obj.ppp || '0')).toFixed(4);
                mergedObject.selling_price_ex = (parseFloat(mergedObject.selling_price_ex) + parseFloat(obj.selling_price_ex || '0')).toFixed(2);
                mergedObject.selling_price_inc = (parseFloat(mergedObject.selling_price_inc) + parseFloat(obj.selling_price_inc || '0')).toFixed(2);
                mergedObject.profit = (parseFloat(mergedObject.profit) + parseFloat(obj.profit || '0')).toFixed(2);
                mergedObject.vat_p = (parseFloat(mergedObject.vat_p) + parseFloat(obj.vat_p || '0')).toFixed(2);
                mergedObject.vat_ppp = (parseFloat(mergedObject.vat_ppp) + parseFloat(obj.vat_ppp || '0')).toFixed(2);
            }
        }

        // If one of the arrays is shorter, repeat the last known price from the shorter array
        for (let j = 0; j < input.length; j++) {
            if (input[j][i] === undefined) {
                // For the longer array, use the last known object
                const lastObject = lastEntries[j];

                if (lastObject) {
                    // Quantity remains as is
                    // Accumulate prices
                    mergedObject.gross_price = (parseFloat(mergedObject.gross_price) + parseFloat(lastObject.gross_price || '0')).toFixed(2);
                    mergedObject.gross_ppp = (parseFloat(mergedObject.gross_ppp) + parseFloat(lastObject.gross_ppp || '0')).toFixed(3);
                    mergedObject.p = (parseFloat(mergedObject.p) + parseFloat(lastObject.p || '0')).toFixed(2);
                    mergedObject.ppp = (parseFloat(mergedObject.ppp) + parseFloat(lastObject.ppp || '0')).toFixed(4);
                    mergedObject.selling_price_ex = (parseFloat(mergedObject.selling_price_ex) + parseFloat(lastObject.selling_price_ex || '0')).toFixed(2);
                    mergedObject.selling_price_inc = (parseFloat(mergedObject.selling_price_inc) + parseFloat(lastObject.selling_price_inc || '0')).toFixed(2);
                    mergedObject.profit = (parseFloat(mergedObject.profit) + parseFloat(lastObject.profit || '0')).toFixed(2);
                    mergedObject.vat_p = (parseFloat(mergedObject.vat_p) + parseFloat(lastObject.vat_p || '0')).toFixed(2);
                    mergedObject.vat_ppp = (parseFloat(mergedObject.vat_ppp) + parseFloat(lastObject.vat_ppp || '0')).toFixed(2);
                }
            }
        }
        let id = `${process.env.APP_KEY}_${mergedObject.p}_${mergedObject.dlv.days}_${mergedObject.qty}_${tenant_id}_${mergedObject.ppp}_${process.env.APP_KEY}`
        mergedObject.id = crypto.createHash(process.env.HASH_TYPE).update(id).digest('hex');
        mergedOutput.push(mergedObject);
    }

    return mergedOutput;
};

/**
 * Calculate the delivery day based on production days, start date, and days to add.
 * @param {Array<Object>} productionDays - List of production days with their properties.
 * @param {Date} startDate - The start date for calculating delivery day.
 * @param {number} daysToAdd - Number of days to add for delivery.
 * @returns {Object} - An object containing the title and formatted date of the delivery day.
 */
const calculateDeliveryDay = (
    productionDays,
    startDate,
    daysToAdd
) => {
    let deliveryDate = new Date(startDate);
    let daysAdded = 0;

    // Get current local time in UTC+2
    const currentTime = new Date();
    const localOffset = 2 * 60 * 60 * 1000; // UTC+2 in milliseconds
    let cutoffHour = 12; // Cutoff hour (12:00)
    let run = true;

    while (run) {
        deliveryDate.setDate(deliveryDate.getDate() + 1); // Move to the next day
        const dayName = deliveryDate.toLocaleDateString('en-US', {weekday: 'short'}).toLowerCase();
        const productionDay = productionDays.find(d => d.day === dayName);
        cutoffHour = parseInt(productionDay.deliver_before)

        // Check if this day is active
        if (productionDay && productionDay.active) {
            const cutoffTime = new Date(deliveryDate); // Create a new date for the cutoff time
            cutoffTime.setHours(cutoffHour + 2, 0, 0, 0); // Set the cutoff time to UTC+2

            // Adjust the current time for comparison
            const adjustedCurrentTime = new Date(currentTime.getTime() + localOffset);

            // If it's the first day we're checking and current time is before cutoff time
            if (adjustedCurrentTime < cutoffTime) {
                run = true
                // This day is still valid for delivery
                return {
                    title: deliveryDate.toLocaleDateString('en-US', {weekday: 'long'}),
                    date: formatDate(deliveryDate) // Return formatted date
                };
            }

            daysAdded++; // Count this day as a valid delivery day
        }
    }

    // If daysAdded == daysToAdd, we return the final delivery day
    return {
        title: deliveryDate.toLocaleDateString('en-US', {weekday: 'long'}),
        date: formatDate(deliveryDate) // Return formatted date
    };
};

/**
 * Formats a given date into a string in the format 'DD-MM-YYYY'.
 *
 * @param {Date} date - The date object to be formatted.
 * @returns {string} The formatted date string.
 */
const formatDate = (date) => {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};

/**
 * Checks if a given string is empty or contains only whitespaces.
 *
 * @param {string} str - The string to be checked.
 * @returns {boolean} - True if the string is empty or only contains whitespaces, false otherwise.
 */
const isEmptyString = (str) =>
    typeof str === 'string' && str.trim() === '' || str === undefined;

/**
 * Function to get unique item and box IDs based on provided items and boops data
 * @param {Array} items - An array of items containing key and value properties
 * @param {Array} boops - An array of objects representing boxes and their options
 * @returns {Object} An object containing unique item and box IDs in arrays
 */
const getUniqueIds = (items, boops) => {
    const box_ids = new Set();  // Store unique box IDs
    const option_ids = new Set();  // Store unique item IDs

    items.forEach((item) => {
        // Find all boxes with the matching slug
        const matchingBoxes = boops.filter((bo) => bo.slug === item.key);

        matchingBoxes.forEach((box) => {
            const option = box.ops.find((op) => op.slug === item.value);
            if (option) {
                option_ids.add(option.id);  // Add unique item ID
            }
            box_ids.add(box.id);  // Add unique box ID
        });
    });

    // Convert Sets to arrays before returning
    return {
        options: [...option_ids],
        boxes: [...box_ids]
    };
};

/**
 * Extracts unique box and option IDs directly from items that contain key_id and value_id.
 * Returns MongoDB ObjectId arrays ready for aggregation queries.
 *
 * @param {Array} items - Array of items with key_id and value_id properties
 * @returns {{options: Array, boxes: Array}} Object containing arrays of unique ObjectIds
 */
const getUniqueIdsFromDirectIds = (items) => {
    const { ObjectId } = require('mongodb');

    const box_ids = new Set();
    const option_ids = new Set();

    items.forEach((item) => {
        if (item.key_id) {
            try {
                box_ids.add(new ObjectId(item.key_id));
            } catch (e) { /* ignore invalid ids here; will be handled upstream */ }
        }
        if (item.value_id) {
            try {
                option_ids.add(new ObjectId(item.value_id));
            } catch (e) { /* ignore invalid ids here; will be handled upstream */ }
        }
    });

    return {
        options: [...option_ids],
        boxes: [...box_ids]
    };
};

/**
 * Calculates the thickness of paper in millimeters based on the given GSM (Grams per square meter) and density.
 *
 * @param {number} gsm - The GSM (Grams per square meter) of the paper.
 * @param {number} [density=0.0] - The density of the paper material (default is 0.0 if not provided).
 * @returns {number} The thickness of the paper in millimeters rounded to 2 decimal places.
 */
const calculatePaperThickness = (
    gsm,
    density = 0
) => {
    if (density < 0) {
        throw new Error("Density must be greater than 0");
    }
    const thickness = gsm / (density * 1000);

    return parseFloat(thickness.toFixed(2)) === Infinity?
        0:
        parseFloat(thickness.toFixed(2));  // Round to 2 decimal places
};

// Function to calculate the total softcover width
const calculateCoverWidth = (
    coverThickness,
    paperThickness,
    pageCount
) => {
    const sheets = pageCount / 2; // Each sheet has 2 pages
    const totalPaperThickness = sheets * paperThickness; // Total thickness of the pages
    // Cover thickness (front + back)
    const totalWidth = totalPaperThickness + (coverThickness*2); // Total softcover width
    return totalWidth.toFixed(2); // Rounded to 2 decimal places
};

/**
 * Calculates the fit of a given format within a catalogue and a machine, considering the provided content and binding requirements.
 * @param {Object} format - The format of the item to be printed, including size and sides information.
 * @param {Object} catalogue - The specifications of the catalogue including width, height, density, grams, and thickness.
 * @param {Object} machine - The dimensions of the printing machine.
 * @param {Object} [content={}] - Additional content considerations such as thickness and pages.
 * @returns {Object} - An object containing information about the fit of the format within the catalogue and machine.
 */
const calculateFit = (
    format,
    catalogue,
    machine,
    content = {}
) => {
    let { width_with_bleed, height_with_bleed, is_sides, pages } = format.size;
    const { width, height, density, grs, thickness} = catalogue;
    const { width: machine_width, height: machine_height } = machine;


    if(is_sides) {
        // check binding here
        width_with_bleed = parseFloat(width_with_bleed) + parseFloat(calculateCoverWidth(thickness,content.thickness,content.pages));
    }
    // Check if the format fits directly in the catalogue
    const directFit = width_with_bleed <= width && height_with_bleed <= height;

    // Check if the format fits when rotated
    const rotatedFit = width_with_bleed <= height && height_with_bleed <= width;

    // Check if the catalogue fits in the machine without rotation
    const machineFits = width <= machine_width && height <= machine_height;

    // Check if the catalogue fits in the machine when rotated
    const rotatedCatalogue = width <= machine_height && height <= machine_width;

    const rotate_format = !directFit && rotatedFit; // Format requires rotation
    const rotate_catalogue = !machineFits && rotatedCatalogue; // Catalogue requires rotation

    return {
        width_with_bleed: width_with_bleed,
        catalogue_check: directFit || rotate_format, // Fits in catalogue directly or rotated
        catalogue_default_fit: directFit, // Fits directly without rotation
        rotate_format, // Flag if the format requires rotation

        machine_check: machineFits || rotate_catalogue, // Fits in the machine directly or rotated
        machine_default_fit: machineFits, // Fits directly without rotation
        rotate_catalogue, // Flag if the catalogue requires rotation
    };
};

/**
 * Filters an array of items based on a specified target calculation reference.
 *
 * @param {Array} items - The array of items to filter
 * @param {string} targetCalcRef - The target calculation reference to match against
 * @returns {Array} - An array containing items that have a box property with a calculation reference matching the targetCalcRef
 */
const filterByCalcRef = (data, targetCalcRef, otherCalcRefs = []) => {
    // Filter items with the target calc_ref (e.g., 'pages')
    const targetItems = data.filter(item => item.box?.calc_ref === targetCalcRef);

    // If no target calc_ref items are found, return an empty array
    if (targetItems.length === 0) return [];

    // Collect items matching the additional calc_refs (e.g., 'material', 'printing_colors')
    const otherItems = data.filter(item =>
        otherCalcRefs.includes(item.box?.calc_ref)
    );

    // Return both the target items and the selected additional items
    return [...targetItems, ...otherItems];
};

/**
 * Retrieves the value of the first property in the object that has a 'data' key
 *
 * @param {object} obj - The object from which to fetch the 'data' key
 * @returns {*} The value associated with the 'data' key found in the object
 * @throws {Error} If no 'data' key is found in any property of the object
 */
const fetchDataKey = (obj) => {
    for (const key in obj) {
        if (obj[key] && obj[key].data) {
            return obj[key].data;
        }
    }
    throw new Error("No 'data' key found.");
};


/**
 * Calculates the number of pages based on the input object.
 *
 * @param {Array} object - The object containing information for page calculation.
 * @returns {number} The number of pages calculated based on the input object.
 */
const calculatePages = (object) =>
{
    let pages,num_pages = 0;
    if(object.length > 0) {
        if(object[0].dynamic) {
            pages = object[0].option._.pages;
            if(!isNumber(pages)) {
                return 0;
            }
            num_pages = pages
        }else{
            num_pages = parseInt(object[0].option.name.match(/\d+/) ? object[0].option.name.match(/\d+/)[0] : '0', 10)
        }
    }
    return num_pages
}

/**
 * Retrieves the calculation reference data from the provided array of objects.
 * @param {Array} data - The array containing objects from which to retrieve calculation reference data.
 * @throws {TypeError} If the data parameter is not an array or if any item in the array is not an object.
 * @returns {Object} An object containing the name, slug, runs, start cost, and calculation reference of the option data.
 */
const getCalculationRefFromOption = (data) => {
    // Validate that data is an array
    if (!Array.isArray(data)) {
        throw new TypeError("Expected data to be an array.");
    }

    // Validate that each item in the array is an object
    if (!data.every(item => typeof item === 'object' && item !== null)) {
        throw new TypeError("Each item in the array must be an object.");
    }

    const option = data[0].option;
    if(!option) {
        return {

        }
    }

    return {
        name: option.name,
        slug: option.slug,
        runs: option.runs,
        start_cost: option.start_cost,
        calc_ref: option.additional.calc_ref,

    }
}

/**
 * Checks if the given value is a number or a string that represents a valid number.
 *
 * @param {*} value - The value to be checked.
 * @returns {boolean} - true if the value is a number or a string that represents a valid number, false otherwise.
 */
const  isNumberOrStringNumber =  (value) => {
    return (
        (typeof value === "number" && !isNaN(value)) || // Check for numbers
        (typeof value === "string" && value.trim() !== "" && !isNaN(value)) // Check for strings that are numbers
    );
}

const rangeListFromCategory = (category) => {
    let range = [];
    if (category.ranges.length > 0) {
        for (const method of category.ranges) {
            range.push({
                pm: method.slug,
                quantity_range_start: method.from,
                quantity_range_end: method.to,
                quantity_incremental_by: method.incremental_by,
                range_list: generateList(method.from, method.to, method.incremental_by)
            })
        }

        return mergeByDynamicKey(
            range,
            'pm',
            category.limits,
            30,
            1,
            category.range_around,
            category.free_entry,
            category.range_list
        );
    }

    throwError({
        message: "The price list is empty.",
        status: 422
    }, "The price list is empty.")

}

/**
 * Retrieves the default format for a given option.
 * If the option provided is empty or does not contain a valid format, the original option is returned.
 * The default format includes dimensions (width, height) and unit (e.g. mm) based on the provided option.
 * If the unit specified in the option does not exist in the default format, 'mm' is used as the default unit.
 *
 * @param {Array} option - The option containing format details.
 * @returns {Array} - The option with default format properties applied.
 */
const getDefaultFormat = (option = []) => {
    if (option.length === 0) return option;

    const found_option = option[0]?.option;
    if (!found_option) return option;

    let { slug, unit = 'mm' } = found_option;
    unit = unit?.trim() || 'mm';

    // Ensure DEFAULT_FORMAT exists and contains the slug
    const default_format = DEFAULT_FORMAT?.[slug] || {};

    // Fallback to 0 if width/height are missing
    const width = found_option?.width || default_format.width || 0;
    const height = found_option?.height || default_format.height || 0;

    // Ensure unit exists in the default format; otherwise, fallback to 'mm'
    if (!(unit in default_format)) unit = 'mm';

    found_option.width = Number(width);
    found_option.height = Number(height);
    found_option.unit = unit;

    return option;
};

/**
 * Calculates the delivery schedule based on the quantity and production delivery configurations.
 * It determines delivery days and proportion based on the given quantity and maximum quantities allowed on specific days.
 *
 * @param {number} quantity - The quantity of the item to be delivered.
 * @param {Array} production_dlv - An array of objects, where each object contains delivery day details.
 * This includes properties such as days, max_qty (maximum quantity allowed), mode (delivery mode), and value.
 * @return {Array} Returns an array of objects representing the calculated delivery schedule.
 */
const calculateDeliveryDays = (
    quantity,
    production_dlv,
    production_days,
    request
) => {
    if (!production_dlv || production_dlv.length === 0) {
        throwError({status: 422, message: "The delivery schedule is empty."}, "The delivery schedule is empty.")
    }
    // Sort by days
    production_dlv.sort((a, b) => parseInt(a.days) - parseInt(b.days));

    let days = [];

    // If quantity is smaller than or equal to the smallest max_qty, return all days
    const maxQty = Math.max(...production_dlv.map(day => day.max_qty));
    const maxDays = Math.max(...production_dlv.map(day => day.days));

    if (request.quantity > maxQty) {
        throwError({status: 422, message: "Runs are not available with the specified quantity."}, "Runs are not available with the specified quantity.")
        return ;
    }

    // Check if production day higher than maximum or quantity higher than maximum update with max production days and max quantity
    if (request.dlv > maxDays || quantity > maxQty) {
        const day = production_dlv[production_dlv.length - 1];
        days.push({
            days: day.days,
            quantity: Math.min(quantity, day.max_qty),
            max_qty: day.max_qty,
            mode: day.mode,
            value: day.mode === 'fixed' ? day.value / 1000 : day.value
        });
        return days;
    }

    // Check now time to configure with configuration production days
    if (!_isBeforeTodayDeadline(production_days)) {
        const index = production_dlv.findIndex((day) => day.days === 0);
        production_dlv.splice(index, 1);
    }

    // Put all available production days
    production_dlv.forEach(day => {
        if (quantity <= day.max_qty) {
            days.push({
                days: day.days,
                quantity: Math.min(quantity, day.max_qty),
                max_qty: day.max_qty,
                mode: day.mode,
                value: day.mode === 'fixed' ? day.value / 1000 : day.value
            });
        }
    });

    // Check available production days
    if (JSON.stringify(request.dlv)) {
        const selectedDay = days.find(day => day.days === request.dlv);
        days = selectedDay ? [selectedDay] : days;
    }
    return days;
}

/**
 * Calculates the final value of p based on margins and type.
 *
 * @param price - The base price value `p`.
 * @param margins - An object containing the margin configuration.
 */
const calculateMarginsPrice = (price, margins) => {
    if (margins.type === "fixed") {
        return price + margins.value / 1000;
    } else {
        return (((100 + parseInt(margins.value ?? 0)) * price) / 100).toFixed(2)
    }
};


/**
 * Calculates the profit based on the provided margin type and price.
 *
 * @param price - The base price value `p`.
 * @param margins - An object containing the margin configuration.
 */
const calculateProfit = (price, margins) => {
    if (margins.type === "fixed") {
        return margins.value / 1000;
    } else {
        return ((parseInt(margins.value ?? 0) * price) / 100).toFixed(2)
    }
};



/**
 * Function that sets a custom message and status for an error object, then throws a new Error with the provided message.
 *
 * @param {{message: string, status: number}} error - The error object to modify.
 * @param {string} message - The custom error message to assign to the error object.
 */
const throwError = (error, message) => {
    error.message = message;
    error.status = 422;
    throw new Error(message);
}


/**
 * Determines if the current time is before today's delivery deadline based on the provided schedules.
 *
 * @param {Array<{day: string, active: boolean, deliver_before: string}>} schedules - An array of schedule objects where each object defines the day, whether the schedule is active, and the deadline time in "HH:MM" format.
 * @return {boolean} Returns true if the current time is before today's deadline and there is an active schedule for today, otherwise returns false.
 */
const _isBeforeTodayDeadline = (schedules) => {
    const now = new Date();
    const todayName = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][now.getDay()];

    const todaySchedule = schedules.find(s => s.day === todayName && s.active);
    if (!todaySchedule) return false;

    const [hour, minute] = todaySchedule.deliver_before.split(':').map(Number);
    const deadline = new Date();

    // Set local time for deadline (no UTC involved)
    deadline.setHours(hour, minute, 0, 0);

    return now < deadline;
}
/**
 * Retrieves discount details from a given contract based on the category ID and the quantity provided.
 *
 * This function first attempts to find a discount from the specified category within the contract.
 * If the category-specific discount is not available or invalid, it falls back to the general discount in the contract.
 * It then identifies the appropriate discount slot based on the quantity range defined in the contract.
 *
 * @param {Object} contract - The contract object containing discount details.
 * @param {number} categoryId - The unique identifier for the category to look up within the contract.
 * @param {number} quantity - The quantity against which the discount will be evaluated.
 * @returns {object|null} An object containing the discount type and value if a valid discount is found, or null if no discount matching the criteria exists.
 */
const getDiscountFromContract = (contract, categoryId,quantity) => {
    let discount = contract.categories.find(category => {
        return category.id === categoryId && category.status
    })?.slots;

    if (!discount || discount.length === 0) {
        if (contract.general.status && contract.general.slots.length > 0) {
            discount = contract.general?.slots;
        }
    }

    const selectedDiscount = discount?.find(discount => {
        return discount.from <= quantity && discount.to >= quantity
    });

    return {
        type: selectedDiscount?.type,
        value: selectedDiscount?.value,
    } ?? null;

}

module.exports = {
    generateList, crossMachine,
    combinations, mergeByDynamicKey,
    findUpperInRangeOrSelf, getAllKeysFromArrayObject,
    getSelectedValuesFromArrayObject, extractObjectFromArrayObject,
    extractObjectWithUnderscoreByValue, extractAllValuesFromArrayObject,
    getDividerByKey, groupByDivider, combineMultipleArrays, groupByDividerWithCalcRefCopy,
    isNumber, calculateArea, formatPriceObject, calculateDeliveryDay, formatDate, mergePriceObject,
    refactorPriceObject, throwError, isEmptyString, getUniqueIds, getUniqueIdsFromDirectIds, calculatePaperThickness,calculateCoverWidth,
    calculateFit, fetchDataKey, filterByCalcRef,calculatePages, isNumberOrStringNumber, rangeListFromCategory,getDefaultFormat,
    calculateDeliveryDays, findDiscountSlot, getDiscountFromContract

};