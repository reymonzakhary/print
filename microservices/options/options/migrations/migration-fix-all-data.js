require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');
const SupplierBoops = require('../Models/SupplierBoops');
const ObjectId = mongoose.Types.ObjectId;

/**
 * Clean additional field - remove calc_ref: null and ensure proper structure
 */
function cleanAdditional(additional) {
    if (!additional || typeof additional !== 'object') {
        return {};
    }

    // If calc_ref is null, undefined, or empty, return empty object
    if (!additional.calc_ref) {
        return {};
    }

    // Return with both calc_ref and calc_ref_type
    return {
        calc_ref: additional.calc_ref,
        calc_ref_type: additional.calc_ref_type || ''
    };
}

/**
 * Clean extended_fields - ensure it's always an array (any type of content)
 */
function cleanExtendedFields(extendedFields) {
    // If it's not an array, return empty array
    if (!Array.isArray(extendedFields)) {
        return [];
    }

    // Return as-is since it can contain any type
    return extendedFields;
}

/**
 * Fix display_name - ensure it's always an array with proper structure
 */
function cleanDisplayName(displayName, fallbackName = 'Unnamed') {
    // If it's already a valid array, return it
    if (Array.isArray(displayName) && displayName.length > 0) {
        // Ensure each item has the correct structure
        return displayName.map(item => {
            if (typeof item === 'object' && item.display_name && item.iso) {
                return item;
            }
            // Invalid item, create a default one
            return {
                display_name: fallbackName,
                iso: 'en'
            };
        });
    }

    // If it's a string, convert to array
    if (typeof displayName === 'string') {
        return [
            { display_name: displayName, iso: 'en' },
            { display_name: displayName, iso: 'fr' },
            { display_name: displayName, iso: 'nl' },
            { display_name: displayName, iso: 'de' }
        ];
    }

    // Otherwise, create default array
    return [
        { display_name: fallbackName, iso: 'en' },
        { display_name: fallbackName, iso: 'fr' },
        { display_name: fallbackName, iso: 'nl' },
        { display_name: fallbackName, iso: 'de' }
    ];
}

/**
 * Convert linked to ObjectId - handles strings, ObjectIds, DBRef, and empty strings
 */
function convertLinkedToObjectId(linked) {
    // Handle null, undefined, or empty string
    if (!linked || linked === '') {
        return null;
    }

    // Handle DBRef
    if (linked.constructor && linked.constructor.name === 'DBRef') {
        return linked.oid; // Extract ObjectId from DBRef
    }

    // Handle string - check if it's a valid ObjectId string
    if (typeof linked === 'string') {
        // Empty string check (already handled above, but double-check)
        if (linked.trim() === '') {
            return null;
        }

        try {
            return new ObjectId(linked);
        } catch (e) {
            console.warn(`Invalid ObjectId string: ${linked}`);
            return null;
        }
    }

    // Already ObjectId
    if (ObjectId.isValid(linked)) {
        return linked;
    }

    return null;
}

/**
 * Fix calculation_method - ensure it's a string, not an array
 */
function cleanCalculationMethod(calculationMethod) {
    if (Array.isArray(calculationMethod)) {
        return calculationMethod.length > 0 ? calculationMethod[0] : 'qty';
    }
    if (!calculationMethod || typeof calculationMethod !== 'string') {
        return 'qty';
    }
    return calculationMethod;
}

/**
 * Check if linked field needs conversion
 */
function needsLinkedConversion(linked) {
    // Empty string needs conversion to null
    if (linked === '') {
        return true;
    }

    if (!linked) {
        return false;
    }

    // Check if it's a DBRef
    if (linked.constructor && linked.constructor.name === 'DBRef') {
        return true;
    }

    // Check if it's a string
    if (typeof linked === 'string') {
        return true;
    }

    return false;
}

/**
 * Fix SupplierOption documents
 */
async function fixSupplierOptions() {
    console.log('Starting SupplierOption migration...');

    try {
        const options = await SupplierOption.find({}).lean();
        let updatedCount = 0;
        let errorCount = 0;

        console.log(`Found ${options.length} SupplierOption documents to process`);

        for (const optionData of options) {
            try {
                const updates = {};

                // Fix extended_fields - just ensure it's an array
                const cleanedExtendedFields = cleanExtendedFields(optionData.extended_fields);
                if (JSON.stringify(optionData.extended_fields) !== JSON.stringify(cleanedExtendedFields)) {
                    updates.extended_fields = cleanedExtendedFields;
                }

                // Fix root level additional
                const cleanedRootAdditional = cleanAdditional(optionData.additional);
                if (JSON.stringify(optionData.additional) !== JSON.stringify(cleanedRootAdditional)) {
                    updates.additional = cleanedRootAdditional;
                }

                // Fix linked field - ALWAYS check and convert (including empty strings)
                if (needsLinkedConversion(optionData.linked) || optionData.linked === '') {
                    const convertedLinked = convertLinkedToObjectId(optionData.linked);
                    // Update if it's different (null is acceptable)
                    if (optionData.linked !== convertedLinked) {
                        updates.linked = convertedLinked;
                    }
                }

                // Fix calculation_method at root level
                const cleanedCalcMethod = cleanCalculationMethod(optionData.calculation_method);
                if (optionData.calculation_method !== cleanedCalcMethod) {
                    updates.calculation_method = cleanedCalcMethod;
                }

                // Fix configure array
                if (optionData.configure && optionData.configure.length > 0) {
                    const cleanedConfigure = optionData.configure.map(config => {
                        const newConfig = { ...config };

                        if (config.configure) {
                            newConfig.configure = { ...config.configure };

                            // Clean additional in configure
                            const cleanedConfigAdditional = cleanAdditional(config.configure.additional);
                            newConfig.configure.additional = cleanedConfigAdditional;

                            // Clean calculation_method in configure
                            const cleanedConfigCalcMethod = cleanCalculationMethod(config.configure.calculation_method);
                            newConfig.configure.calculation_method = cleanedConfigCalcMethod;
                        }

                        return newConfig;
                    });

                    if (JSON.stringify(optionData.configure) !== JSON.stringify(cleanedConfigure)) {
                        updates.configure = cleanedConfigure;
                    }
                }

                // Only update if there are changes
                if (Object.keys(updates).length > 0) {
                    await SupplierOption.updateOne(
                        { _id: optionData._id },
                        { $set: updates }
                    );
                    updatedCount++;
                }
            } catch (error) {
                console.error(`Error updating option ${optionData._id}:`, error.message);
                errorCount++;
            }
        }

        console.log(`✅ SupplierOption migration completed: ${updatedCount}/${options.length} documents updated`);
        if (errorCount > 0) {
            console.log(`⚠️  ${errorCount} documents had errors`);
        }
    } catch (error) {
        console.error('❌ SupplierOption migration error:', error);
    }
}

/**
 * Fix SupplierBoops documents
 */
async function fixSupplierBoops() {
    console.log('Starting SupplierBoops migration...');

    try {
        const boops = await SupplierBoops.find({}).lean();
        let updatedCount = 0;
        let errorCount = 0;

        console.log(`Found ${boops.length} SupplierBoops documents to process`);

        for (const boopData of boops) {
            try {
                const updates = {};
                let needsUpdate = false;

                // Fix root level linked - ALWAYS check and convert (including empty strings)
                if (needsLinkedConversion(boopData.linked) || boopData.linked === '') {
                    const convertedRootLinked = convertLinkedToObjectId(boopData.linked);
                    if (boopData.linked !== convertedRootLinked) {
                        updates.linked = convertedRootLinked;
                        needsUpdate = true;
                    }
                }

                // Fix boops array
                if (boopData.boops && boopData.boops.length > 0) {
                    const cleanedBoops = boopData.boops.map(boop => {
                        const newBoop = { ...boop };

                        // Fix missing iso field
                        if (!newBoop.iso) {
                            newBoop.iso = 'en'; // Default to 'en'
                            needsUpdate = true;
                        }

                        // Fix display_name - ensure it's an array with proper structure
                        const cleanedBoopDisplayName = cleanDisplayName(boop.display_name, boop.name);
                        if (JSON.stringify(boop.display_name) !== JSON.stringify(cleanedBoopDisplayName)) {
                            newBoop.display_name = cleanedBoopDisplayName;
                            needsUpdate = true;
                        }

                        // Fix boop level linked - ALWAYS check and convert (including empty strings)
                        if (needsLinkedConversion(boop.linked) || boop.linked === '') {
                            const convertedBoopLinked = convertLinkedToObjectId(boop.linked);
                            if (boop.linked !== convertedBoopLinked) {
                                newBoop.linked = convertedBoopLinked;
                                needsUpdate = true;
                            }
                        }

                        // Fix ops array
                        if (boop.ops && boop.ops.length > 0) {
                            newBoop.ops = boop.ops.map(op => {
                                const newOp = { ...op };

                                // Fix linked field - ALWAYS check and convert (including empty strings)
                                if (needsLinkedConversion(op.linked) || op.linked === '') {
                                    const convertedOpLinked = convertLinkedToObjectId(op.linked);
                                    if (op.linked !== convertedOpLinked) {
                                        newOp.linked = convertedOpLinked;
                                        needsUpdate = true;
                                    }
                                }

                                // Fix additional field - remove _id and clean structure
                                if (op.additional) {
                                    let cleanedOpAdditional;

                                    // Check if additional has _id
                                    if (op.additional._id) {
                                        const { _id, ...rest } = op.additional;
                                        cleanedOpAdditional = cleanAdditional(rest);
                                    } else {
                                        cleanedOpAdditional = cleanAdditional(op.additional);
                                    }

                                    if (JSON.stringify(op.additional) !== JSON.stringify(cleanedOpAdditional)) {
                                        newOp.additional = cleanedOpAdditional;
                                        needsUpdate = true;
                                    }
                                } else {
                                    // Add additional field if missing
                                    newOp.additional = {};
                                    needsUpdate = true;
                                }

                                // Fix calculation_method
                                const cleanedCalcMethod = cleanCalculationMethod(op.calculation_method);
                                if (!op.calculation_method || op.calculation_method !== cleanedCalcMethod) {
                                    newOp.calculation_method = cleanedCalcMethod;
                                    needsUpdate = true;
                                }

                                // Add missing fields with defaults
                                if (!op.dynamic_keys) {
                                    newOp.dynamic_keys = [];
                                    needsUpdate = true;
                                }

                                if (op.start_on === undefined || op.start_on === null) {
                                    newOp.start_on = 0;
                                    needsUpdate = true;
                                }

                                if (op.end_on === undefined || op.end_on === null) {
                                    newOp.end_on = 0;
                                    needsUpdate = true;
                                }

                                if (!op.dynamic_type) {
                                    newOp.dynamic_type = 'integer';
                                    needsUpdate = true;
                                }

                                if (op.generate === undefined || op.generate === null) {
                                    newOp.generate = false;
                                    needsUpdate = true;
                                }

                                if (op.dynamic_object === undefined) {
                                    newOp.dynamic_object = null;
                                    needsUpdate = true;
                                }

                                if (op.incremental_by === undefined || op.incremental_by === null) {
                                    newOp.incremental_by = 0;
                                    needsUpdate = true;
                                }

                                return newOp;
                            });
                        }

                        return newBoop;
                    });

                    if (needsUpdate) {
                        updates.boops = cleanedBoops;
                    }
                }

                // Only update if there are changes
                if (needsUpdate && Object.keys(updates).length > 0) {
                    await SupplierBoops.updateOne(
                        { _id: boopData._id },
                        { $set: updates }
                    );
                    updatedCount++;
                }
            } catch (error) {
                console.error(`Error updating boop ${boopData._id}:`, error.message);
                errorCount++;
            }
        }

        console.log(`✅ SupplierBoops migration completed: ${updatedCount}/${boops.length} documents updated`);
        if (errorCount > 0) {
            console.log(`⚠️  ${errorCount} documents had errors`);
        }
    } catch (error) {
        console.error('❌ SupplierBoops migration error:', error);
    }
}

/**
 * Main migration function
 */
async function runMigration() {
    console.log('========================================');
    console.log('Starting Data Migration');
    console.log('========================================\n');

    try {
        // Connect to MongoDB using your existing connection string
        await mongoose.connect(process.env.mongoURI, {
            // useNewUrlParser: true,
            // useUnifiedTopology: true,
        });
        console.log('✅ Connected to MongoDB\n');

        // Run migrations
        await fixSupplierOptions();
        console.log('');
        await fixSupplierBoops();

        console.log('\n========================================');
        console.log('Migration Completed Successfully! 🎉');
        console.log('========================================');

        // Close connection
        await mongoose.connection.close();
        console.log('✅ MongoDB connection closed');

        process.exit(0);
    } catch (error) {
        console.error('\n========================================');
        console.error('❌ Migration Failed!');
        console.error('========================================');
        console.error(error);

        // Close connection
        if (mongoose.connection.readyState === 1) {
            await mongoose.connection.close();
        }

        process.exit(1);
    }
}

// Run the migration
runMigration();