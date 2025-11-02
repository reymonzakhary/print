const slugify = require('slugify');
const mongoose = require('mongoose');
const SupplierOption = require("../Models/SupplierOption");
const Option = require("../Models/Option");
const SupplierBoops = require("../Models/SupplierBoops");
const ObjectId = mongoose.Types.ObjectId


/**
 * Generates an array of objects containing language ISO code and display name.
 * @param {Array<string>} language - The array of language ISO codes.
 * @param {string} name - The display name to be assigned to each language.
 * @returns {Array<object>} - The generated array of language objects with ISO code and display name.
 */
const generate_display_name = (language, name) => {
    return language.map((lang) => ({
        iso: lang,
        display_name: name

    }))
}

/**
 * Updates the display name for a specific language based on the given ISO code.
 * @param {Array<{display_name: string, iso: string}>} display_name - The array of language display names and ISO codes to update.
 * @param {string} name - The new display name to set.
 * @param {string} iso - The ISO code of the language to update.
 * @returns {Array<{display_name: string, iso: string}>} - The updated array of language display names and ISO codes.
 */
const update_display_name = (display_name, name, iso) => {
    return display_name.map(lang => {
        return lang.iso === iso ? {
            display_name: name,
            iso: iso,
        } : lang;
    })
}


/**
 * Merge display names from incoming array into default array.
 * @param {Array} defaultArray - The default array of objects containing ISO codes and display names.
 * @param {Array} incomingArray - The incoming array of objects containing ISO codes and display names to merge.
 * @returns {Array} - The merged array of objects with updated display names.
 */
const mergeDisplayNames = (defaultArray, incomingArray) => {
    // Convert default array to Map for O(1) lookups
    const map = new Map(defaultArray.map(item => [item.iso, item]));

    // Update/add incoming items
    incomingArray.forEach(incoming => {
        map.set(incoming.iso, {
            iso: incoming.iso,
            display_name: incoming.display_name
        });
    });

    // Convert back to array
    return Array.from(map.values());
}

module.exports = {
    generate_display_name,
    update_display_name,
    mergeDisplayNames
};

