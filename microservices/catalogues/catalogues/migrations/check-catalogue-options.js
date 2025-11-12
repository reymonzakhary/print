require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');
const SupplierCatalogue = require('../Models/SupplierCatalogue');

const ObjectId = mongoose.Types.ObjectId;

/**
 * Find option by link (grs_link or material_link)
 */
async function findOptionByLink(link, expectedType = null) {
    if (!link) return null;

    // Try multiple possible link field locations
    const query = {
        $or: [
            { 'additional.link': link },
            { 'additional.url': link },
            { link: link },
            { url: link }
        ]
    };

    // Filter by type if specified
    if (expectedType) {
        query.type = expectedType;
    }

    return await SupplierOption.findOne(query).lean();
}

/**
 * Find option by matching the value (grs number or material name)
 */
async function findOptionByValue(value, expectedType) {
    if (!value) return null;

    // For GRS, value might be a number like 150
    // For material, value might be a string like "Woodfree Coated Glossy Mc"

    const query = { type: expectedType };

    if (typeof value === 'number' || !isNaN(value)) {
        // For numeric values (GRS), try exact match or name match
        query.$or = [
            { value: Number(value) },
            { name: String(value) },
            { name: `${value}` }
        ];
    } else {
        // For string values (material), try case-insensitive match
        query.name = new RegExp(`^${value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`, 'i');
    }

    return await SupplierOption.findOne(query).lean();
}

/**
 * Check and update option's additional field if needed
 */
async function ensureOptionAdditionalField(optionId, optionType) {
    const option = await SupplierOption.findById(optionId).lean();

    if (!option) return { updated: false, reason: 'option_not_found' };

    // Determine expected additional field based on type
    const expectedAdditional = {
        grs: { calc_ref: 'weight', calc_ref_type: 'main' },
        material: { calc_ref: 'material', calc_ref_type: 'main' }
    };

    const expected = expectedAdditional[optionType];
    if (!expected) return { updated: false, reason: 'type_not_applicable' };

    // Check if additional field needs update
    const needsUpdate =
        !option.additional ||
        option.additional.calc_ref !== expected.calc_ref ||
        option.additional.calc_ref_type !== expected.calc_ref_type;

    if (needsUpdate) {
        // Preserve existing additional fields, but ensure calc_ref and calc_ref_type are correct
        const updatedAdditional = {
            ...(option.additional || {}),
            calc_ref: expected.calc_ref,
            calc_ref_type: expected.calc_ref_type
        };

        await SupplierOption.updateOne(
            { _id: optionId },
            { $set: { additional: updatedAdditional } }
        );

        return {
            updated: true,
            optionId: optionId.toString(),
            optionName: option.name,
            optionType: option.type,
            oldAdditional: option.additional,
            newAdditional: updatedAdditional
        };
    }

    return { updated: false, reason: 'already_correct' };
}

/**
 * Check and fix a single catalogue
 */
async function checkAndFixCatalogue(catalogue, verbose = false) {
    const issues = [];
    const fixes = [];
    const optionUpdates = [];
    let needsUpdate = false;
    const updates = {};

    // Check grs_id
    if (catalogue.grs_id) {
        const grsExists = await SupplierOption.findById(catalogue.grs_id).lean();

        if (!grsExists) {
            console.log(`\n❌ Catalogue ${catalogue._id}: grs_id ${catalogue.grs_id} NOT FOUND in supplier_options`);
            console.log(`   Catalogue details: Supplier=${catalogue.supplier}, Art#=${catalogue.art_nr}`);
            console.log(`   GRS value=${catalogue.grs}, GRS link=${catalogue.grs_link || 'none'}`);

            let replacement = null;
            let matchMethod = null;

            // Try to find by grs_link
            if (catalogue.grs_link) {
                console.log(`   → Trying to match by grs_link: ${catalogue.grs_link}`);
                replacement = await findOptionByLink(catalogue.grs_link, 'grs');
                if (replacement) {
                    matchMethod = 'grs_link';
                    console.log(`   ✓ Found via grs_link: ${replacement.name} (${replacement._id})`);
                } else {
                    console.log(`   ✗ No match found via grs_link`);
                }
            }

            // Try to find by grs value
            if (!replacement && catalogue.grs) {
                console.log(`   → Trying to match by grs value: ${catalogue.grs}`);
                replacement = await findOptionByValue(catalogue.grs, 'grs');
                if (replacement) {
                    matchMethod = 'grs_value';
                    console.log(`   ✓ Found via grs_value: ${replacement.name} (${replacement._id})`);
                } else {
                    console.log(`   ✗ No match found via grs_value`);
                }
            }

            if (replacement) {
                console.log(`   ✓ REPLACEMENT FOUND: ${replacement.name} (${replacement._id}) via ${matchMethod}`);
                updates.grs_id = new ObjectId(replacement._id);
                needsUpdate = true;

                fixes.push({
                    catalogueId: catalogue._id,
                    field: 'grs_id',
                    oldId: catalogue.grs_id.toString(),
                    newId: replacement._id.toString(),
                    newName: replacement.name,
                    matchMethod: matchMethod,
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    grs_value: catalogue.grs,
                    grs_link: catalogue.grs_link
                });

                // Check and update the replacement option's additional field
                const optionUpdate = await ensureOptionAdditionalField(replacement._id, 'grs');
                if (optionUpdate.updated) {
                    console.log(`   ✓ Updated option additional field: calc_ref=weight`);
                    optionUpdates.push(optionUpdate);
                }
            } else {
                console.log(`   ❌ NO REPLACEMENT FOUND - This will be reported as UNFIXABLE`);

                issues.push({
                    catalogueId: catalogue._id,
                    field: 'grs_id',
                    missingId: catalogue.grs_id.toString(),
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    grs_value: catalogue.grs,
                    grs_link: catalogue.grs_link,
                    material_value: catalogue.material,
                    material_link: catalogue.material_link,
                    reason: 'Option not found in supplier_options and no replacement available'
                });
            }
        } else {
            // Option exists, check and update its additional field
            const optionUpdate = await ensureOptionAdditionalField(catalogue.grs_id, 'grs');
            if (optionUpdate.updated) {
                if (verbose) console.log(`   ✓ Catalogue ${catalogue._id}: Updated grs option additional field`);
                optionUpdates.push(optionUpdate);
            }
        }
    }

    // Check material_id
    if (catalogue.material_id) {
        const materialExists = await SupplierOption.findById(catalogue.material_id).lean();

        if (!materialExists) {
            console.log(`\n❌ Catalogue ${catalogue._id}: material_id ${catalogue.material_id} NOT FOUND in supplier_options`);
            console.log(`   Catalogue details: Supplier=${catalogue.supplier}, Art#=${catalogue.art_nr}`);
            console.log(`   Material value=${catalogue.material}, Material link=${catalogue.material_link || 'none'}`);

            let replacement = null;
            let matchMethod = null;

            // Try to find by material_link
            if (catalogue.material_link) {
                console.log(`   → Trying to match by material_link: ${catalogue.material_link}`);
                replacement = await findOptionByLink(catalogue.material_link, 'material');
                if (replacement) {
                    matchMethod = 'material_link';
                    console.log(`   ✓ Found via material_link: ${replacement.name} (${replacement._id})`);
                } else {
                    console.log(`   ✗ No match found via material_link`);
                }
            }

            // Try to find by material name
            if (!replacement && catalogue.material) {
                console.log(`   → Trying to match by material name: ${catalogue.material}`);
                replacement = await findOptionByValue(catalogue.material, 'material');
                if (replacement) {
                    matchMethod = 'material_name';
                    console.log(`   ✓ Found via material_name: ${replacement.name} (${replacement._id})`);
                } else {
                    console.log(`   ✗ No match found via material_name`);
                }
            }

            if (replacement) {
                console.log(`   ✓ REPLACEMENT FOUND: ${replacement.name} (${replacement._id}) via ${matchMethod}`);
                updates.material_id = new ObjectId(replacement._id);
                needsUpdate = true;

                fixes.push({
                    catalogueId: catalogue._id,
                    field: 'material_id',
                    oldId: catalogue.material_id.toString(),
                    newId: replacement._id.toString(),
                    newName: replacement.name,
                    matchMethod: matchMethod,
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    material_value: catalogue.material,
                    material_link: catalogue.material_link
                });

                // Check and update the replacement option's additional field
                const optionUpdate = await ensureOptionAdditionalField(replacement._id, 'material');
                if (optionUpdate.updated) {
                    console.log(`   ✓ Updated option additional field: calc_ref=material`);
                    optionUpdates.push(optionUpdate);
                }
            } else {
                console.log(`   ❌ NO REPLACEMENT FOUND - This will be reported as UNFIXABLE`);

                issues.push({
                    catalogueId: catalogue._id,
                    field: 'material_id',
                    missingId: catalogue.material_id.toString(),
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    grs_value: catalogue.grs,
                    grs_link: catalogue.grs_link,
                    material_value: catalogue.material,
                    material_link: catalogue.material_link,
                    reason: 'Option not found in supplier_options and no replacement available'
                });
            }
        } else {
            // Option exists, check and update its additional field
            const optionUpdate = await ensureOptionAdditionalField(catalogue.material_id, 'material');
            if (optionUpdate.updated) {
                if (verbose) console.log(`   ✓ Catalogue ${catalogue._id}: Updated material option additional field`);
                optionUpdates.push(optionUpdate);
            }
        }
    }

    // Apply updates if needed
    if (needsUpdate) {
        await SupplierCatalogue.updateOne(
            { _id: catalogue._id },
            { $set: updates }
        );
        console.log(`✅ Catalogue ${catalogue._id} UPDATED`);
    } else if (issues.length > 0) {
        console.log(`⚠️ Catalogue ${catalogue._id} has UNFIXABLE issues`);
    }

    return { issues, fixes, optionUpdates, needsUpdate };
}

/**
 * Initial scan to identify all missing options
 */
async function scanForMissingOptions() {
    console.log('========================================');
    console.log('Phase 1: Scanning for Missing Options');
    console.log('========================================\n');

    const catalogues = await SupplierCatalogue.find({}).lean();
    const missingGrs = [];
    const missingMaterial = [];
    const uniqueMissingGrs = new Set();
    const uniqueMissingMaterial = new Set();

    console.log(`Scanning ${catalogues.length} catalogues...\n`);

    for (const catalogue of catalogues) {
        // Check grs_id
        if (catalogue.grs_id) {
            const grsExists = await SupplierOption.findById(catalogue.grs_id).lean();
            if (!grsExists) {
                uniqueMissingGrs.add(catalogue.grs_id.toString());
                missingGrs.push({
                    catalogueId: catalogue._id,
                    optionId: catalogue.grs_id,
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    grs_value: catalogue.grs,
                    grs_link: catalogue.grs_link
                });
            }
        }

        // Check material_id
        if (catalogue.material_id) {
            const materialExists = await SupplierOption.findById(catalogue.material_id).lean();
            if (!materialExists) {
                uniqueMissingMaterial.add(catalogue.material_id.toString());
                missingMaterial.push({
                    catalogueId: catalogue._id,
                    optionId: catalogue.material_id,
                    supplier: catalogue.supplier,
                    art_nr: catalogue.art_nr,
                    material_value: catalogue.material,
                    material_link: catalogue.material_link
                });
            }
        }
    }

    console.log('========================================');
    console.log('Scan Results:');
    console.log('========================================');
    console.log(`Total catalogues scanned: ${catalogues.length}`);
    console.log(`Catalogues with missing grs_id: ${missingGrs.length}`);
    console.log(`Unique missing grs options: ${uniqueMissingGrs.size}`);
    console.log(`Catalogues with missing material_id: ${missingMaterial.length}`);
    console.log(`Unique missing material options: ${uniqueMissingMaterial.size}`);
    console.log('========================================\n');

    if (missingGrs.length > 0) {
        console.log('Missing GRS Options:');
        for (const missing of missingGrs.slice(0, 10)) {
            console.log(`  ❌ Catalogue ${missing.catalogueId}`);
            console.log(`     grs_id: ${missing.optionId}`);
            console.log(`     Supplier: ${missing.supplier} | Art#: ${missing.art_nr}`);
            console.log(`     GRS value: ${missing.grs_value} | Link: ${missing.grs_link || 'none'}`);
            console.log('');
        }
        if (missingGrs.length > 10) {
            console.log(`  ... and ${missingGrs.length - 10} more\n`);
        }
    }

    if (missingMaterial.length > 0) {
        console.log('Missing Material Options:');
        for (const missing of missingMaterial.slice(0, 10)) {
            console.log(`  ❌ Catalogue ${missing.catalogueId}`);
            console.log(`     material_id: ${missing.optionId}`);
            console.log(`     Supplier: ${missing.supplier} | Art#: ${missing.art_nr}`);
            console.log(`     Material: ${missing.material_value} | Link: ${missing.material_link || 'none'}`);
            console.log('');
        }
        if (missingMaterial.length > 10) {
            console.log(`  ... and ${missingMaterial.length - 10} more\n`);
        }
    }

    console.log('========================================');
    console.log('Phase 2: Attempting to Fix Missing Options');
    console.log('========================================\n');

    return {
        missingGrs,
        missingMaterial,
        totalMissing: missingGrs.length + missingMaterial.length
    };
}

/**
 * Main validation function
 */
async function validateCatalogues() {
    console.log('========================================');
    console.log('Supplier Catalogues Option Validation');
    console.log('========================================\n');

    // Phase 1: Scan for missing options
    const scanResults = await scanForMissingOptions();

    const stats = {
        total: 0,
        checked: 0,
        withIssues: 0,
        fixed: 0,
        unfixable: 0,
        grsIssues: 0,
        grsFixed: 0,
        materialIssues: 0,
        materialFixed: 0,
        optionsUpdated: 0,
        grsOptionsUpdated: 0,
        materialOptionsUpdated: 0,
        // From scan
        initialMissingGrs: scanResults.missingGrs.length,
        initialMissingMaterial: scanResults.missingMaterial.length,
        initialTotalMissing: scanResults.totalMissing
    };

    const allIssues = [];
    const allFixes = [];
    const allOptionUpdates = [];

    // Get all catalogues
    const catalogues = await SupplierCatalogue.find({}).lean();
    stats.total = catalogues.length;

    console.log(`Found ${catalogues.length} catalogues to check\n`);

    // Process each catalogue
    for (const catalogue of catalogues) {
        stats.checked++;

        const { issues, fixes, optionUpdates, needsUpdate } = await checkAndFixCatalogue(catalogue);

        if (issues.length > 0 || fixes.length > 0) {
            stats.withIssues++;
        }

        if (needsUpdate) {
            stats.fixed++;
        }

        // Track issues by field
        for (const issue of issues) {
            allIssues.push(issue);
            stats.unfixable++;

            if (issue.field === 'grs_id') {
                stats.grsIssues++;
            } else if (issue.field === 'material_id') {
                stats.materialIssues++;
            }
        }

        // Track fixes by field
        for (const fix of fixes) {
            allFixes.push(fix);

            if (fix.field === 'grs_id') {
                stats.grsFixed++;
            } else if (fix.field === 'material_id') {
                stats.materialFixed++;
            }
        }

        // Track option additional field updates
        for (const optionUpdate of optionUpdates) {
            allOptionUpdates.push(optionUpdate);
            stats.optionsUpdated++;

            if (optionUpdate.optionType === 'grs') {
                stats.grsOptionsUpdated++;
            } else if (optionUpdate.optionType === 'material') {
                stats.materialOptionsUpdated++;
            }
        }

        // Progress indicator
        if (stats.checked % 500 === 0) {
            console.log(`\n📊 Progress: ${stats.checked}/${stats.total} catalogues processed...\n`);
        }
    }

    return { stats, allIssues, allFixes, allOptionUpdates };
}

/**
 * Generate report
 */
function generateReport(stats, allIssues, allFixes, allOptionUpdates) {
    console.log('\n========================================');
    console.log('📊 VALIDATION REPORT');
    console.log('========================================\n');

    console.log('Initial Scan Results:');
    console.log(`   Catalogues with missing grs_id: ${stats.initialMissingGrs}`);
    console.log(`   Catalogues with missing material_id: ${stats.initialMissingMaterial}`);
    console.log(`   Total catalogues with missing options: ${stats.initialTotalMissing}\n`);

    console.log('Overall Statistics:');
    console.log(`   Total catalogues: ${stats.total}`);
    console.log(`   Catalogues checked: ${stats.checked}`);
    console.log(`   Catalogues with issues: ${stats.withIssues}`);
    console.log(`   Catalogues fixed: ${stats.fixed}`);
    console.log(`   Unfixable issues: ${stats.unfixable}\n`);

    console.log('By Field:');
    console.log(`   GRS Issues: ${stats.grsIssues} | Fixed: ${stats.grsFixed}`);
    console.log(`   Material Issues: ${stats.materialIssues} | Fixed: ${stats.materialFixed}\n`);

    console.log('Option Additional Field Updates:');
    console.log(`   Total options updated: ${stats.optionsUpdated}`);
    console.log(`   GRS options (calc_ref=weight): ${stats.grsOptionsUpdated}`);
    console.log(`   Material options (calc_ref=material): ${stats.materialOptionsUpdated}\n`);

    // Show fixes by match method
    if (allFixes.length > 0) {
        console.log('========================================');
        console.log(`✅ FIXES APPLIED (${allFixes.length})`);
        console.log('========================================\n');

        const fixesByMethod = {};
        for (const fix of allFixes) {
            if (!fixesByMethod[fix.matchMethod]) {
                fixesByMethod[fix.matchMethod] = [];
            }
            fixesByMethod[fix.matchMethod].push(fix);
        }

        for (const [method, fixes] of Object.entries(fixesByMethod)) {
            console.log(`\n${method.toUpperCase()} (${fixes.length} fixes):`);

            for (const fix of fixes.slice(0, 10)) {
                console.log(`   ✓ ${fix.catalogueId}`);
                console.log(`     Field: ${fix.field}`);
                console.log(`     Supplier: ${fix.supplier} | Art#: ${fix.art_nr}`);
                console.log(`     Old ID: ${fix.oldId}`);
                console.log(`     New ID: ${fix.newId} (${fix.newName})`);

                if (fix.field === 'grs_id') {
                    console.log(`     GRS Value: ${fix.grs_value} | Link: ${fix.grs_link || 'none'}`);
                } else if (fix.field === 'material_id') {
                    console.log(`     Material: ${fix.material_value} | Link: ${fix.material_link || 'none'}`);
                }

                console.log('');
            }

            if (fixes.length > 10) {
                console.log(`   ... and ${fixes.length - 10} more\n`);
            }
        }
    }

    // Show option additional field updates
    if (allOptionUpdates.length > 0) {
        console.log('\n========================================');
        console.log(`🔧 OPTION ADDITIONAL FIELDS UPDATED (${allOptionUpdates.length})`);
        console.log('========================================\n');

        const updatesByType = {
            grs: allOptionUpdates.filter(u => u.optionType === 'grs'),
            material: allOptionUpdates.filter(u => u.optionType === 'material')
        };

        if (updatesByType.grs.length > 0) {
            console.log(`GRS Options (${updatesByType.grs.length} updated to calc_ref='weight'):`);
            for (const update of updatesByType.grs.slice(0, 5)) {
                console.log(`   ✓ ${update.optionName} (${update.optionId})`);
                console.log(`     Old: ${JSON.stringify(update.oldAdditional || {})}`);
                console.log(`     New: ${JSON.stringify(update.newAdditional)}`);
            }
            if (updatesByType.grs.length > 5) {
                console.log(`   ... and ${updatesByType.grs.length - 5} more`);
            }
            console.log('');
        }

        if (updatesByType.material.length > 0) {
            console.log(`Material Options (${updatesByType.material.length} updated to calc_ref='material'):`);
            for (const update of updatesByType.material.slice(0, 5)) {
                console.log(`   ✓ ${update.optionName} (${update.optionId})`);
                console.log(`     Old: ${JSON.stringify(update.oldAdditional || {})}`);
                console.log(`     New: ${JSON.stringify(update.newAdditional)}`);
            }
            if (updatesByType.material.length > 5) {
                console.log(`   ... and ${updatesByType.material.length - 5} more`);
            }
            console.log('');
        }
    }

    // Show unfixable issues
    if (allIssues.length > 0) {
        console.log('\n========================================');
        console.log(`❌ UNFIXABLE ISSUES (${allIssues.length})`);
        console.log('========================================');
        console.log('These catalogues need manual review:\n');

        for (const issue of allIssues) {
            console.log(`❌ Catalogue ID: ${issue.catalogueId}`);
            console.log(`   Field: ${issue.field}`);
            console.log(`   Supplier: ${issue.supplier}`);
            console.log(`   Art Number: ${issue.art_nr}`);
            console.log(`   Missing Option ID: ${issue.missingId}`);

            if (issue.field === 'grs_id') {
                console.log(`   GRS Value: ${issue.grs_value}`);
                console.log(`   GRS Link: ${issue.grs_link || 'not provided'}`);
            } else if (issue.field === 'material_id') {
                console.log(`   Material Value: ${issue.material_value}`);
                console.log(`   Material Link: ${issue.material_link || 'not provided'}`);
            }

            console.log(`   Reason: ${issue.reason}`);
            console.log('');
        }
    }

    console.log('========================================\n');
}

/**
 * Export report to JSON
 */
async function exportReport(stats, allIssues, allFixes, allOptionUpdates) {
    const fs = require('fs').promises;

    const report = {
        timestamp: new Date().toISOString(),
        stats,
        fixes: allFixes,
        optionAdditionalFieldUpdates: allOptionUpdates,
        unfixableIssues: allIssues
    };

    const filename = `catalogue-option-check-${Date.now()}.json`;
    await fs.writeFile(filename, JSON.stringify(report, null, 2));

    console.log(`📄 Detailed report exported to: ${filename}\n`);
    return filename;
}

/**
 * Export unfixable issues to CSV for easy review
 */
async function exportUnfixableToCSV(allIssues) {
    if (allIssues.length === 0) return null;

    const fs = require('fs').promises;

    // CSV header
    let csv = 'Catalogue ID,Field,Supplier,Art Number,Missing Option ID,Value,Link,Reason\n';

    // CSV rows
    for (const issue of allIssues) {
        const value = issue.field === 'grs_id' ? issue.grs_value : issue.material_value;
        const link = issue.field === 'grs_id' ? (issue.grs_link || '') : (issue.material_link || '');

        csv += `"${issue.catalogueId}","${issue.field}","${issue.supplier}","${issue.art_nr}","${issue.missingId}","${value}","${link}","${issue.reason}"\n`;
    }

    const filename = `unfixable-catalogues-${Date.now()}.csv`;
    await fs.writeFile(filename, csv);

    console.log(`📄 Unfixable issues exported to CSV: ${filename}\n`);
    return filename;
}

/**
 * Main function
 */
async function run() {
    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI);
        console.log('✅ Connected to MongoDB\n');

        // Run validation
        const { stats, allIssues, allFixes, allOptionUpdates } = await validateCatalogues();

        // Generate console report
        generateReport(stats, allIssues, allFixes, allOptionUpdates);

        // Export JSON report
        await exportReport(stats, allIssues, allFixes, allOptionUpdates);

        // Export CSV of unfixable issues
        if (allIssues.length > 0) {
            await exportUnfixableToCSV(allIssues);
        }

        console.log('========================================');
        console.log('✅ Validation Complete!');
        console.log('========================================');

        if (stats.unfixable > 0) {
            console.log(`\n⚠️  ${stats.unfixable} unfixable issue(s) require manual review.`);
            console.log('Please check the exported CSV and JSON files.\n');
        } else {
            console.log('\n🎉 All issues were fixed automatically!\n');
        }

        if (stats.optionsUpdated > 0) {
            console.log(`✅ ${stats.optionsUpdated} option(s) had their additional field updated.`);
            console.log(`   - ${stats.grsOptionsUpdated} GRS options → calc_ref='weight'`);
            console.log(`   - ${stats.materialOptionsUpdated} Material options → calc_ref='material'\n`);
        }

        // Close connection
        await mongoose.connection.close();
        console.log('✅ MongoDB connection closed\n');

        process.exit(0);
    } catch (error) {
        console.error('\n❌ Validation Failed!');
        console.error(error);

        if (mongoose.connection.readyState === 1) {
            await mongoose.connection.close();
        }

        process.exit(1);
    }
}

// Run the validation
run();