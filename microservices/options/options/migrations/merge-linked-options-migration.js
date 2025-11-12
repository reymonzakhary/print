require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');
const SupplierBoop = require('../Models/SupplierBoops');
const SupplierCatalogue = require('../Models/SupplierCatalogue');
const SupplierMachine = require('../Models/SupplierMachine');

const ObjectId = mongoose.Types.ObjectId;

/**
 * Merge sheet_runs arrays - avoid duplicate machines
 */
function mergeSheetRuns(baseSheetRuns, newSheetRuns) {
    // Ensure both are arrays
    const base = Array.isArray(baseSheetRuns) ? baseSheetRuns : [];
    const newItems = Array.isArray(newSheetRuns) ? newSheetRuns : [];

    if (newItems.length === 0) {
        return base;
    }

    const merged = [...base];
    const existingMachineIds = new Set(
        base.map(sr => sr.machine.toString())
    );

    for (const sheetRun of newItems) {
        const machineId = sheetRun.machine.toString();
        if (!existingMachineIds.has(machineId)) {
            merged.push(sheetRun);
            existingMachineIds.add(machineId);
        }
    }

    return merged;
}

/**
 * Merge configure arrays - avoid duplicate category_ids
 */
function mergeConfigure(baseConfigure, newConfigure) {
    // Ensure both are arrays
    const base = Array.isArray(baseConfigure) ? baseConfigure : [];
    const newItems = Array.isArray(newConfigure) ? newConfigure : [];

    if (newItems.length === 0) {
        return base;
    }

    const merged = [...base];
    const existingCategoryIds = new Set(
        base.map(c => c.category_id.toString())
    );

    for (const config of newItems) {
        const categoryId = config.category_id.toString();
        if (!existingCategoryIds.has(categoryId)) {
            merged.push(config);
            existingCategoryIds.add(categoryId);
        }
    }

    return merged;
}

/**
 * Merge runs arrays - avoid duplicate category_ids
 */
function mergeRuns(baseRuns, newRuns) {
    // Ensure both are arrays
    const base = Array.isArray(baseRuns) ? baseRuns : [];
    const newItems = Array.isArray(newRuns) ? newRuns : [];

    if (newItems.length === 0) {
        return base;
    }

    const merged = [...base];
    const existingCategoryIds = new Set(
        base.map(r => r.category_id.toString())
    );

    for (const run of newItems) {
        const categoryId = run.category_id.toString();
        if (!existingCategoryIds.has(categoryId)) {
            merged.push(run);
            existingCategoryIds.add(categoryId);
        }
    }

    return merged;
}

/**
 * Merge display_name arrays - keep all unique language combinations
 */
function mergeDisplayNames(baseDisplayNames, newDisplayNames) {
    // Ensure both are arrays
    const base = Array.isArray(baseDisplayNames) ? baseDisplayNames : [];
    const newItems = Array.isArray(newDisplayNames) ? newDisplayNames : [];

    if (newItems.length === 0) {
        return base;
    }

    const merged = [...base];
    const existingIsos = new Set(
        base.map(dn => dn.iso)
    );

    for (const displayName of newItems) {
        if (!existingIsos.has(displayName.iso)) {
            merged.push(displayName);
            existingIsos.add(displayName.iso);
        }
    }

    return merged;
}

/**
 * Merge two SupplierOptions - base (newest) + source (older)
 */
function mergeOptions(baseOption, sourceOption) {
    const merged = { ...baseOption };

    // Merge sheet_runs
    merged.sheet_runs = mergeSheetRuns(
        baseOption.sheet_runs,
        sourceOption.sheet_runs
    );

    // Merge configure
    merged.configure = mergeConfigure(
        baseOption.configure,
        sourceOption.configure
    );

    // Merge runs
    merged.runs = mergeRuns(
        baseOption.runs,
        sourceOption.runs
    );

    // Merge display_names
    merged.display_name = mergeDisplayNames(
        baseOption.display_name,
        sourceOption.display_name
    );

    // Merge boxes if needed
    const baseBoxes = Array.isArray(baseOption.boxes) ? baseOption.boxes : [];
    const sourceBoxes = Array.isArray(sourceOption.boxes) ? sourceOption.boxes : [];

    if (sourceBoxes.length > 0) {
        merged.boxes = [...baseBoxes, ...sourceBoxes];
    } else {
        merged.boxes = baseBoxes;
    }

    // Merge additional field (preserve calc_ref, calc_ref_type, etc.)
    if (baseOption.additional || sourceOption.additional) {
        merged.additional = {
            ...(sourceOption.additional || {}),
            ...(baseOption.additional || {})
        };

        // Log if we're preserving additional data
        if (merged.additional.calc_ref || merged.additional.calc_ref_type) {
            console.log(`     ✓ Preserving additional.calc_ref: ${merged.additional.calc_ref}, calc_ref_type: ${merged.additional.calc_ref_type}`);
        }
    }

    return merged;
}

/**
 * Check if an option ID is referenced anywhere
 */
async function findReferencesToOption(optionId) {
    const references = {
        catalogues: 0,
        boops: 0,
        machines: 0
    };

    const optionIdStr = optionId.toString();

    // Check catalogues
    const catalogueFields = ['grs_id', 'material_id', 'format_id', 'color_id', 'finishing_id'];
    for (const field of catalogueFields) {
        const count = await SupplierCatalogue.countDocuments({ [field]: new ObjectId(optionIdStr) });
        references.catalogues += count;
    }

    // Check boops (ops.id and excludes)
    const boops = await SupplierBoop.find({
        $or: [
            { 'boops.ops.id': new ObjectId(optionIdStr) },
            { 'boops.ops.excludes': optionIdStr }
        ]
    }).lean();
    references.boops = boops.length;

    // Check machines (colors.mode_id and nested IDs)
    const machines = await SupplierMachine.find({
        $or: [
            { 'colors.mode_id': optionIdStr },
            { 'colors.display_name.id': optionIdStr },
            { 'colors.display_name.current_supplier.id': optionIdStr },
            { 'materials.id': optionIdStr }
        ]
    }).lean();
    references.machines = machines.length;

    return references;
}

/**
 * Update references in supplier_catalogues
 */
async function updateCatalogueReferences(idMapping) {
    console.log('\n📚 Updating supplier_catalogues references...');

    const fieldNames = ['grs_id', 'material_id', 'format_id', 'color_id', 'finishing_id'];
    let totalUpdated = 0;

    for (const [oldId, newId] of Object.entries(idMapping)) {
        for (const fieldName of fieldNames) {
            const query = { [fieldName]: new ObjectId(oldId) };
            const update = { $set: { [fieldName]: new ObjectId(newId) } };

            const result = await SupplierCatalogue.updateMany(query, update);

            if (result.modifiedCount > 0) {
                console.log(`   ✓ Updated ${result.modifiedCount} catalogues: ${fieldName} ${oldId} → ${newId}`);
                totalUpdated += result.modifiedCount;
            }
        }
    }

    console.log(`   ✅ Total catalogue records updated: ${totalUpdated}`);
    return totalUpdated;
}

/**
 * Update references in supplier_boops (ops.id and ops.excludes)
 */
async function updateBoopReferences(idMapping) {
    console.log('\n📦 Updating supplier_boops references...');

    let totalBoopsUpdated = 0;
    let totalOpsUpdated = 0;
    let totalExcludesUpdated = 0;

    // Get all boops
    const allBoops = await SupplierBoop.find({}).lean();

    for (const boop of allBoops) {
        let boopModified = false;

        if (boop.boops && Array.isArray(boop.boops)) {
            for (let boopItem of boop.boops) {
                let boopItemModified = false;

                if (boopItem.ops && Array.isArray(boopItem.ops)) {
                    for (let op of boopItem.ops) {
                        // Update op.id if it's in the mapping
                        if (op.id && idMapping[op.id.toString()]) {
                            const oldId = op.id.toString();
                            const newId = idMapping[oldId];
                            op.id = new ObjectId(newId);
                            boopItemModified = true;
                            totalOpsUpdated++;
                            console.log(`   ✓ Updated op.id: ${oldId} → ${newId}`);
                        }

                        // Update excludes array
                        if (op.excludes && Array.isArray(op.excludes)) {
                            let excludesModified = false;
                            op.excludes = op.excludes.map(excludeGroup => {
                                if (Array.isArray(excludeGroup)) {
                                    return excludeGroup.map(excludeId => {
                                        const excludeIdStr = excludeId.toString();
                                        if (idMapping[excludeIdStr]) {
                                            excludesModified = true;
                                            totalExcludesUpdated++;
                                            return new ObjectId(idMapping[excludeIdStr]);
                                        }
                                        return excludeId;
                                    });
                                }
                                return excludeGroup;
                            });

                            if (excludesModified) {
                                boopItemModified = true;
                            }
                        }
                    }
                }

                if (boopItemModified) {
                    boopModified = true;
                }
            }
        }

        // Update the document if modified
        if (boopModified) {
            await SupplierBoop.updateOne(
                { _id: boop._id },
                { $set: { boops: boop.boops } }
            );
            totalBoopsUpdated++;
        }
    }

    console.log(`   ✅ Total boop documents updated: ${totalBoopsUpdated}`);
    console.log(`   ✅ Total ops.id updated: ${totalOpsUpdated}`);
    console.log(`   ✅ Total excludes references updated: ${totalExcludesUpdated}`);

    return { totalBoopsUpdated, totalOpsUpdated, totalExcludesUpdated };
}

/**
 * Update references in supplier_machines (colors[].mode_id and nested IDs)
 */
async function updateMachineReferences(idMapping) {
    console.log('\n🔧 Updating supplier_machines references...');

    let totalMachinesUpdated = 0;
    let totalModeIdsUpdated = 0;
    let totalNestedIdsUpdated = 0;

    // Get all machines
    const allMachines = await SupplierMachine.find({}).lean();

    for (const machine of allMachines) {
        let machineModified = false;

        if (machine.colors && Array.isArray(machine.colors)) {
            for (let color of machine.colors) {
                // Update mode_id if it's in the mapping
                if (color.mode_id && idMapping[color.mode_id.toString()]) {
                    const oldId = color.mode_id.toString();
                    const newId = idMapping[oldId];
                    color.mode_id = newId;
                    machineModified = true;
                    totalModeIdsUpdated++;
                    console.log(`   ✓ Updated color.mode_id: ${oldId} → ${newId}`);
                }

                // Update display_name.id if it exists and is in the mapping
                if (color.display_name && color.display_name.id) {
                    const displayNameId = color.display_name.id.toString();
                    if (idMapping[displayNameId]) {
                        const newId = idMapping[displayNameId];
                        color.display_name.id = newId;
                        machineModified = true;
                        totalNestedIdsUpdated++;
                        console.log(`   ✓ Updated color.display_name.id: ${displayNameId} → ${newId}`);
                    }
                }

                // Update display_name.current_supplier.id if it exists and is in the mapping
                if (color.display_name && color.display_name.current_supplier && color.display_name.current_supplier.id) {
                    const currentSupplierId = color.display_name.current_supplier.id.toString();
                    if (idMapping[currentSupplierId]) {
                        const newId = idMapping[currentSupplierId];
                        color.display_name.current_supplier.id = newId;
                        machineModified = true;
                        totalNestedIdsUpdated++;
                        console.log(`   ✓ Updated color.display_name.current_supplier.id: ${currentSupplierId} → ${newId}`);
                    }
                }
            }
        }

        // Update materials array if it contains option references
        if (machine.materials && Array.isArray(machine.materials)) {
            for (let material of machine.materials) {
                // If materials have an ID field that references options
                if (material.id && idMapping[material.id.toString()]) {
                    const oldId = material.id.toString();
                    const newId = idMapping[oldId];
                    material.id = newId;
                    machineModified = true;
                    totalNestedIdsUpdated++;
                    console.log(`   ✓ Updated material.id: ${oldId} → ${newId}`);
                }
            }
        }

        // Update the document if modified
        if (machineModified) {
            await SupplierMachine.updateOne(
                { _id: machine._id },
                { $set: { colors: machine.colors, materials: machine.materials } }
            );
            totalMachinesUpdated++;
        }
    }

    console.log(`   ✅ Total machine documents updated: ${totalMachinesUpdated}`);
    console.log(`   ✅ Total mode_id updated: ${totalModeIdsUpdated}`);
    console.log(`   ✅ Total nested IDs updated: ${totalNestedIdsUpdated}`);

    return { totalMachinesUpdated, totalModeIdsUpdated, totalNestedIdsUpdated };
}

/**
 * Update ops in supplier_boops with configure data from merged options
 * matching the supplier_category
 */
async function updateOpsWithConfigureData(idMapping) {
    console.log('\n🔄 Updating ops with configure data from merged options...');

    let totalBoopsProcessed = 0;
    let totalOpsUpdated = 0;
    let totalOpsSkipped = 0;

    // Get all supplier_boops
    const allBoops = await SupplierBoop.find({}).lean();

    for (const boopDoc of allBoops) {
        let boopModified = false;

        if (!boopDoc.supplier_category) {
            console.log(`   ⚠️  Skipping boop ${boopDoc._id} - no supplier_category`);
            continue;
        }

        const supplierCategoryId = boopDoc.supplier_category.toString();

        if (boopDoc.boops && Array.isArray(boopDoc.boops)) {
            for (let boopItem of boopDoc.boops) {
                if (boopItem.ops && Array.isArray(boopItem.ops)) {
                    for (let op of boopItem.ops) {
                        if (!op.id) {
                            totalOpsSkipped++;
                            continue;
                        }

                        let optionIdToUse = op.id.toString();

                        // If this op.id was mapped to a new ID, use the new ID
                        if (idMapping[optionIdToUse]) {
                            optionIdToUse = idMapping[optionIdToUse];
                        }

                        // Fetch the (merged) option
                        const option = await SupplierOption.findById(optionIdToUse).lean();

                        if (!option) {
                            console.log(`   ⚠️  Option ${optionIdToUse} not found for op in boop ${boopDoc._id}`);
                            totalOpsSkipped++;
                            continue;
                        }

                        // Find configure entry matching this supplier_category
                        if (option.configure && Array.isArray(option.configure)) {
                            const matchingConfigure = option.configure.find(
                                config => config.category_id && config.category_id.toString() === supplierCategoryId
                            );

                            if (matchingConfigure) {
                                // Update the op with configure data
                                // Extract all fields except category_id and _id
                                const configureData = { ...matchingConfigure };
                                delete configureData.category_id;
                                delete configureData._id;

                                // Apply configure data to op (preserving existing fields not in configure)
                                Object.assign(op, configureData);

                                boopModified = true;
                                totalOpsUpdated++;

                                console.log(`   ✓ Updated op ${op.id} in boop ${boopDoc._id} with configure for category ${supplierCategoryId}`);
                            } else {
                                console.log(`   ⚠️  No matching configure for category ${supplierCategoryId} in option ${optionIdToUse}`);
                                totalOpsSkipped++;
                            }
                        } else {
                            console.log(`   ⚠️  Option ${optionIdToUse} has no configure array`);
                            totalOpsSkipped++;
                        }
                    }
                }
            }
        }

        // Update the boop document if modified
        if (boopModified) {
            await SupplierBoop.updateOne(
                { _id: boopDoc._id },
                { $set: { boops: boopDoc.boops } }
            );
            totalBoopsProcessed++;
        }
    }

    console.log(`   ✅ Total boop documents processed: ${totalBoopsProcessed}`);
    console.log(`   ✅ Total ops updated with configure data: ${totalOpsUpdated}`);
    console.log(`   ⚠️  Total ops skipped: ${totalOpsSkipped}`);

    return { totalBoopsProcessed, totalOpsUpdated, totalOpsSkipped };
}

/**
 * Validate that options in supplier_catalogues exist in supplier_boops
 * and replace them if needed
 */
async function validateAndFixCatalogueBoopConsistency() {
    console.log('\n========================================');
    console.log('🔍 Validating Catalogue-Boop Consistency');
    console.log('========================================\n');

    let totalCataloguesChecked = 0;
    let totalCataloguesFixed = 0;
    let totalReferencesFixed = 0;
    const fixLog = [];

    // Get all catalogues
    const catalogues = await SupplierCatalogue.find({}).lean();
    console.log(`Found ${catalogues.length} catalogues to check\n`);

    for (const catalogue of catalogues) {
        totalCataloguesChecked++;

        if (!catalogue.supplier_category) {
            continue;
        }

        const categoryId = catalogue.supplier_category.toString();

        // Find the corresponding boop for this category
        const boop = await SupplierBoop.findOne({
            supplier_category: new ObjectId(categoryId)
        }).lean();

        if (!boop || !boop.boops || !Array.isArray(boop.boops)) {
            console.log(`⚠️  No boop found for category ${categoryId} (catalogue ${catalogue._id})`);
            continue;
        }

        // Collect all valid option IDs from this boop
        const validOptionIds = new Set();
        for (const boopItem of boop.boops) {
            if (boopItem.ops && Array.isArray(boopItem.ops)) {
                for (const op of boopItem.ops) {
                    if (op.id) {
                        validOptionIds.add(op.id.toString());
                    }
                }
            }
        }

        if (validOptionIds.size === 0) {
            console.log(`⚠️  Boop for category ${categoryId} has no ops (catalogue ${catalogue._id})`);
            continue;
        }

        // Check each option field in the catalogue
        const fieldsToCheck = ['grs_id', 'material_id', 'format_id', 'color_id', 'finishing_id'];
        const updates = {};
        let catalogueNeedsUpdate = false;

        for (const field of fieldsToCheck) {
            const optionId = catalogue[field];

            if (!optionId) {
                continue; // Field is null/undefined, skip it
            }

            const optionIdStr = optionId.toString();

            // Check if this option exists in the boop's valid options
            if (!validOptionIds.has(optionIdStr)) {
                console.log(`⚠️  Catalogue ${catalogue._id}: ${field} (${optionIdStr}) not found in boop ops`);

                // Try to find a replacement option of the same type
                const option = await SupplierOption.findById(optionIdStr).lean();

                if (!option) {
                    console.log(`❌  Option ${optionIdStr} no longer exists!`);
                    fixLog.push({
                        catalogueId: catalogue._id,
                        field,
                        oldOptionId: optionIdStr,
                        status: 'OPTION_NOT_FOUND',
                        action: 'SET_TO_NULL'
                    });
                    updates[field] = null;
                    catalogueNeedsUpdate = true;
                    totalReferencesFixed++;
                    continue;
                }

                // Find a replacement: look for an option of the same type in the valid options
                let replacement = null;
                let bestMatch = null;

                for (const validId of validOptionIds) {
                    const validOption = await SupplierOption.findById(validId).lean();

                    if (validOption && validOption.type === option.type) {
                        // Prefer options with the same name
                        if (validOption.name === option.name) {
                            replacement = validOption;
                            break;
                        }

                        // Prefer options with the same linked ID
                        if (validOption.linked?.toString() === option.linked?.toString()) {
                            replacement = validOption;
                            break;
                        }

                        // Keep the first match as fallback
                        if (!bestMatch) {
                            bestMatch = validOption;
                        }
                    }
                }

                if (!replacement && bestMatch) {
                    replacement = bestMatch;
                }

                if (replacement) {
                    console.log(`✓ Found replacement: ${option.name} (${optionIdStr}) → ${replacement.name} (${replacement._id})`);
                    updates[field] = new ObjectId(replacement._id.toString());
                    catalogueNeedsUpdate = true;
                    totalReferencesFixed++;

                    fixLog.push({
                        catalogueId: catalogue._id,
                        field,
                        oldOptionId: optionIdStr,
                        oldOptionName: option.name,
                        newOptionId: replacement._id.toString(),
                        newOptionName: replacement.name,
                        optionType: option.type,
                        status: 'REPLACED'
                    });
                } else {
                    console.log(`⚠️  No replacement found for ${option.type} option: ${option.name}`);
                    // Set to first available option of any type as last resort
                    const firstValidId = Array.from(validOptionIds)[0];
                    const firstValidOption = await SupplierOption.findById(firstValidId).lean();

                    updates[field] = new ObjectId(firstValidId);
                    totalReferencesFixed++;

                    fixLog.push({
                        catalogueId: catalogue._id,
                        field,
                        oldOptionId: optionIdStr,
                        oldOptionName: option.name,
                        oldOptionType: option.type,
                        newOptionId: firstValidId,
                        newOptionName: firstValidOption?.name || 'Unknown',
                        newOptionType: firstValidOption?.type || 'Unknown',
                        status: 'REPLACED_WITH_FALLBACK'
                    });
                }
            }
        }

        // Update the catalogue if needed
        if (catalogueNeedsUpdate) {
            await SupplierCatalogue.updateOne(
                { _id: catalogue._id },
                { $set: updates }
            );
            totalCataloguesFixed++;
        }

        // Log progress every 100 catalogues
        if (totalCataloguesChecked % 100 === 0) {
            console.log(`Progress: ${totalCataloguesChecked}/${catalogues.length} catalogues checked...`);
        }
    }

    console.log('\n========================================');
    console.log('Validation Summary:');
    console.log('========================================');
    console.log(`✅ Catalogues checked: ${totalCataloguesChecked}`);
    console.log(`✅ Catalogues fixed: ${totalCataloguesFixed}`);
    console.log(`✅ References fixed: ${totalReferencesFixed}`);
    console.log('========================================\n');

    // Log detailed fixes
    if (fixLog.length > 0) {
        console.log('📋 Detailed Fix Log:');
        console.log('========================================');

        const replacedCount = fixLog.filter(f => f.status === 'REPLACED').length;
        const fallbackCount = fixLog.filter(f => f.status === 'REPLACED_WITH_FALLBACK').length;
        const nulledCount = fixLog.filter(f => f.status === 'OPTION_NOT_FOUND').length;

        console.log(`✓ Successfully replaced (same type): ${replacedCount}`);
        console.log(`⚠️  Replaced with fallback (different type): ${fallbackCount}`);
        console.log(`❌ Set to null (option deleted): ${nulledCount}\n`);

        for (const fix of fixLog) {
            if (fix.status === 'REPLACED') {
                console.log(`✓ ${fix.catalogueId} | ${fix.field}`);
                console.log(`  ${fix.oldOptionName} → ${fix.newOptionName} (type: ${fix.optionType})`);
            } else if (fix.status === 'OPTION_NOT_FOUND') {
                console.log(`❌ ${fix.catalogueId} | ${fix.field}: Option deleted, set to null`);
            } else if (fix.status === 'REPLACED_WITH_FALLBACK') {
                console.log(`⚠️  ${fix.catalogueId} | ${fix.field}`);
                console.log(`  ${fix.oldOptionName} (${fix.oldOptionType}) → ${fix.newOptionName} (${fix.newOptionType})`);
            }
        }
        console.log('========================================\n');
    }

    return { totalCataloguesChecked, totalCataloguesFixed, totalReferencesFixed, fixLog };
}

/**
 * Find and merge duplicate options based on linked field and tenant_id
 */
async function mergeLinkedOptions() {
    console.log('========================================');
    console.log('Starting Linked Options Merge Migration');
    console.log('========================================\n');

    try {
        // Find all options with linked field (not null)
        const options = await SupplierOption.find({
            linked: { $exists: true, $ne: null }
        }).lean();

        console.log(`Found ${options.length} options with linked field\n`);

        // Group options by linked ObjectId AND tenant_id
        const groupedByLinkedAndTenant = {};
        for (const option of options) {
            const linkedId = option.linked.toString();
            const tenantId = option.tenant_id || 'no-tenant';
            const groupKey = `${linkedId}:${tenantId}`;

            if (!groupedByLinkedAndTenant[groupKey]) {
                groupedByLinkedAndTenant[groupKey] = [];
            }
            groupedByLinkedAndTenant[groupKey].push(option);
        }

        console.log(`Found ${Object.keys(groupedByLinkedAndTenant).length} unique linked+tenant groups\n`);

        // Find groups with duplicates
        const duplicateGroups = Object.entries(groupedByLinkedAndTenant).filter(
            ([_, opts]) => opts.length > 1
        );

        console.log(`Found ${duplicateGroups.length} groups with duplicate options\n`);

        if (duplicateGroups.length === 0) {
            console.log('✅ No duplicate options found. Migration complete!');
            return { idMapping: {}, mergedCount: 0, deletedCount: 0, optionsToDelete: [], mergedOptions: [], errorCount: 0 };
        }

        let mergedCount = 0;
        let errorCount = 0;
        const idMapping = {}; // Maps old IDs to new IDs
        const mergedOptions = []; // Options that have been merged (to update)
        const optionsToDelete = []; // Options that should be deleted

        // PHASE 1: Build ID mapping and merge data (but don't delete yet)
        console.log('\n========================================');
        console.log('PHASE 1: Building ID Mappings & Merging Data');
        console.log('========================================\n');

        for (const [groupKey, duplicates] of duplicateGroups) {
            try {
                const [linkedId, tenantId] = groupKey.split(':');
                console.log(`\n📦 Processing group:`);
                console.log(`   Linked ID: ${linkedId}`);
                console.log(`   Tenant ID: ${tenantId}`);
                console.log(`   Found ${duplicates.length} duplicate options:`);

                // Sort by created_at (newest first)
                const sortedOptions = duplicates.sort((a, b) => {
                    const dateA = a.created_at ? new Date(a.created_at) : new Date(0);
                    const dateB = b.created_at ? new Date(b.created_at) : new Date(0);
                    return dateB - dateA;
                });

                // The newest option is the base
                const baseOption = sortedOptions[0];
                const optionsToMerge = sortedOptions.slice(1);

                console.log(`   ✓ Base option (newest): ${baseOption._id} - "${baseOption.name}" (${baseOption.created_at})`);
                console.log(`   ✓ Options to merge and delete: ${optionsToMerge.length}`);

                // Build ID mapping for references
                const baseIdStr = baseOption._id.toString();
                for (const opt of optionsToMerge) {
                    const oldIdStr = opt._id.toString();
                    idMapping[oldIdStr] = baseIdStr;
                    optionsToDelete.push(opt._id);
                    console.log(`     - Mapping: ${oldIdStr} → ${baseIdStr}`);
                }

                // Show details of what will be merged
                for (const opt of optionsToMerge) {
                    const sheetRunsCount = Array.isArray(opt.sheet_runs) ? opt.sheet_runs.length : 0;
                    const configureCount = Array.isArray(opt.configure) ? opt.configure.length : 0;
                    const runsCount = Array.isArray(opt.runs) ? opt.runs.length : 0;

                    console.log(`     - ${opt._id} - "${opt.name}" (${opt.created_at})`);
                    console.log(`       sheet_runs: ${sheetRunsCount}, configure: ${configureCount}, runs: ${runsCount}`);
                }

                // Merge all options into the base
                let mergedOption = { ...baseOption };
                for (const sourceOption of optionsToMerge) {
                    mergedOption = mergeOptions(mergedOption, sourceOption);
                }

                console.log(`\n   📊 Merge results:`);
                const baseSheetRunsCount = Array.isArray(baseOption.sheet_runs) ? baseOption.sheet_runs.length : 0;
                const baseConfigureCount = Array.isArray(baseOption.configure) ? baseOption.configure.length : 0;
                const baseRunsCount = Array.isArray(baseOption.runs) ? baseOption.runs.length : 0;
                const baseDisplayNameCount = Array.isArray(baseOption.display_name) ? baseOption.display_name.length : 0;

                console.log(`      sheet_runs: ${baseSheetRunsCount} → ${mergedOption.sheet_runs.length}`);
                console.log(`      configure: ${baseConfigureCount} → ${mergedOption.configure.length}`);
                console.log(`      runs: ${baseRunsCount} → ${mergedOption.runs.length}`);
                console.log(`      display_name: ${baseDisplayNameCount} → ${mergedOption.display_name.length}`);

                // Log additional field preservation
                if (mergedOption.additional) {
                    const hasCalcRef = mergedOption.additional.calc_ref ? '✓' : '✗';
                    const hasCalcRefType = mergedOption.additional.calc_ref_type ? '✓' : '✗';
                    console.log(`      additional: ${hasCalcRef} calc_ref, ${hasCalcRefType} calc_ref_type`);
                }

                mergedOptions.push({
                    _id: baseOption._id,
                    data: mergedOption
                });

                mergedCount++;
                console.log(`   ✅ Merged data prepared for base option`);

            } catch (error) {
                console.error(`   ❌ Error processing group ${groupKey}:`, error.message);
                errorCount++;
            }
        }

        console.log('\n========================================');
        console.log('Phase 1 Summary:');
        console.log('========================================');
        console.log(`✅ Groups processed: ${mergedCount}`);
        console.log(`✅ Options to delete: ${optionsToDelete.length}`);
        console.log(`📋 ID mappings created: ${Object.keys(idMapping).length}`);
        if (errorCount > 0) {
            console.log(`⚠️  Errors encountered: ${errorCount}`);
        }
        console.log('========================================\n');

        return { idMapping, mergedCount, optionsToDelete, mergedOptions, errorCount };

    } catch (error) {
        console.error('❌ Migration error:', error);
        throw error;
    }
}

/**
 * Main migration function
 */
async function runMigration() {
    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI, {
            // useNewUrlParser: true,
            // useUnifiedTopology: true,
        });
        console.log('✅ Connected to MongoDB\n');

        // PHASE 1: Build ID mappings and merge data
        const { idMapping, mergedCount, optionsToDelete, mergedOptions, errorCount } = await mergeLinkedOptions();

        if (Object.keys(idMapping).length === 0) {
            console.log('No duplicates found. Exiting.');
            await mongoose.connection.close();
            process.exit(0);
        }

        // First, update the base options with merged data BEFORE updating references
        console.log('\n========================================');
        console.log('PHASE 1.5: Updating Base Options with Merged Data');
        console.log('========================================\n');

        for (const merged of mergedOptions) {
            const updateFields = {
                sheet_runs: merged.data.sheet_runs,
                configure: merged.data.configure,
                runs: merged.data.runs,
                display_name: merged.data.display_name,
                boxes: merged.data.boxes
            };

            // Include additional field if it exists
            if (merged.data.additional) {
                updateFields.additional = merged.data.additional;
            }

            await SupplierOption.updateOne(
                { _id: merged._id },
                { $set: updateFields }
            );
        }
        console.log(`✅ Updated ${mergedOptions.length} base options with merged data\n`);

        // PHASE 2: Update all references in related collections
        console.log('\n========================================');
        console.log('PHASE 2: Updating References in Related Collections');
        console.log('========================================');

        let catalogueUpdates = 0;
        let boopUpdates = 0;
        let machineUpdates = 0;

        // Update catalogue references
        try {
            catalogueUpdates = await updateCatalogueReferences(idMapping);
        } catch (error) {
            console.error('❌ Error updating catalogue references:', error.message);
        }

        // Update boop references
        try {
            const boopResults = await updateBoopReferences(idMapping);
            boopUpdates = boopResults.totalBoopsUpdated;
        } catch (error) {
            console.error('❌ Error updating boop references:', error.message);
        }

        // Update machine references
        try {
            const machineResults = await updateMachineReferences(idMapping);
            machineUpdates = machineResults.totalMachinesUpdated;
        } catch (error) {
            console.error('❌ Error updating machine references:', error.message);
        }

        // PHASE 2.5: Update ops with configure data from merged options
        console.log('\n========================================');
        console.log('PHASE 2.5: Updating Ops with Configure Data');
        console.log('========================================');

        let opsConfigureUpdates = 0;
        let opsSkipped = 0;
        try {
            const opsResults = await updateOpsWithConfigureData(idMapping);
            opsConfigureUpdates = opsResults.totalOpsUpdated;
            opsSkipped = opsResults.totalOpsSkipped;
        } catch (error) {
            console.error('❌ Error updating ops with configure data:', error.message);
        }

        // PHASE 2.75: Validate and fix catalogue-boop consistency
        let catalogueBoopValidation = null;
        try {
            catalogueBoopValidation = await validateAndFixCatalogueBoopConsistency();
        } catch (error) {
            console.error('❌ Error validating catalogue-boop consistency:', error.message);
        }

        // PHASE 3: Verify no remaining references before deletion
        console.log('\n========================================');
        console.log('PHASE 3: Verifying References Before Deletion');
        console.log('========================================\n');

        const stillReferenced = [];
        for (const optionId of optionsToDelete) {
            const refs = await findReferencesToOption(optionId);
            const totalRefs = refs.catalogues + refs.boops + refs.machines;

            if (totalRefs > 0) {
                console.log(`   ⚠️  Option ${optionId} still has ${totalRefs} references:`);
                console.log(`      - Catalogues: ${refs.catalogues}`);
                console.log(`      - Boops: ${refs.boops}`);
                console.log(`      - Machines: ${refs.machines}`);
                stillReferenced.push(optionId);
            }
        }

        if (stillReferenced.length > 0) {
            console.error(`\n❌ SAFETY CHECK FAILED: ${stillReferenced.length} options still have references!`);
            console.error('Migration aborted to prevent data loss.');
            console.error('Please review the references above and fix them manually.');
            await mongoose.connection.close();
            process.exit(1);
        }

        console.log('   ✅ All references successfully updated. Safe to delete.');

        // PHASE 4: Delete old duplicate options
        console.log('\n========================================');
        console.log('PHASE 4: Deleting Duplicate Options');
        console.log('========================================\n');

        // Delete the duplicate options
        const deleteResult = await SupplierOption.deleteMany({
            _id: { $in: optionsToDelete }
        });
        console.log(`✅ Deleted ${deleteResult.deletedCount} duplicate options`);

        console.log('\n========================================');
        console.log('Migration Completed Successfully! 🎉');
        console.log('========================================');
        console.log('Summary:');
        console.log(`- Options merged: ${mergedCount}`);
        console.log(`- Options deleted: ${deleteResult.deletedCount}`);
        console.log(`- Catalogue records updated: ${catalogueUpdates}`);
        console.log(`- Boop documents updated: ${boopUpdates}`);
        console.log(`- Machine documents updated: ${machineUpdates}`);
        console.log(`- Ops updated with configure data: ${opsConfigureUpdates}`);
        console.log(`- Ops skipped (no matching configure): ${opsSkipped}`);
        if (catalogueBoopValidation) {
            console.log(`- Catalogues validated: ${catalogueBoopValidation.totalCataloguesChecked}`);
            console.log(`- Catalogues fixed for consistency: ${catalogueBoopValidation.totalCataloguesFixed}`);
            console.log(`- References replaced: ${catalogueBoopValidation.totalReferencesFixed}`);
        }
        console.log(`- References updated: ${Object.keys(idMapping).length} mappings`);
        if (errorCount > 0) {
            console.log(`- Errors: ${errorCount}`);
        }
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