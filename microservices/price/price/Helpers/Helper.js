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
const findUpperInRangeOrSelf = (number, interval) => {
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
const checkDiffList = (list, second_list) => {
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
const addNearestNumberToRangeList = (rangeList, nearestNumber) => {
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
const updateRangeList = (item, nearestNumber, rangeAround) => {
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
const selectNumbersFromRangeList = (results, selectedValues) => {
    selectedValues.length > 0 ?
    results.forEach(item => {
        item.range_list = item.range_list.filter(num => selectedValues.includes(num));
    }): results;
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

module.exports = {generateList, crossMachine, combinations, mergeByDynamicKey, findUpperInRangeOrSelf};