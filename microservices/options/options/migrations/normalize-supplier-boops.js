require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');
const SupplierBoop = require('../Models/SupplierBoops');
const SupplierBox = require('../Models/SupplierBox');

const ObjectId = mongoose.Types.ObjectId;

/**
 * Validate and fix box references in supplier_boops (tenant-specific)
 */
async function validateAndFixBoxReferences() {
    console.log('\n📦 Validating Box References (Tenant-Specific)...');

    const allBoops = await SupplierBoop.find({}).lean();
    const allBoxes = await SupplierBox.find({}).lean();

    // Create tenant-specific lookup maps for boxes
    const boxesByIdAndTenant = new Map(); // Map<tenantId, Map<boxId, box>>
    const boxesByLinkedAndTenant = new Map(); // Map<tenantId, Map<linkedId, box[]>>

    for (const box of allBoxes) {
        const tenantId = box.tenant_id || 'no-tenant';

        // Index by ID and tenant
        if (!boxesByIdAndTenant.has(tenantId)) {
            boxesByIdAndTenant.set(tenantId, new Map());
        }
        boxesByIdAndTenant.get(tenantId).set(box._id.toString(), box);

        // Index by linked and tenant
        if (box.linked) {
            if (!boxesByLinkedAndTenant.has(tenantId)) {
                boxesByLinkedAndTenant.set(tenantId, new Map());
            }

            const linkedStr = box.linked.toString();
            const tenantLinkedMap = boxesByLinkedAndTenant.get(tenantId);

            if (!tenantLinkedMap.has(linkedStr)) {
                tenantLinkedMap.set(linkedStr, []);
            }
            tenantLinkedMap.get(linkedStr).push(box);
        }
    }

    const boxUpdates = [];
    const boxNotFound = [];
    let totalBoxesChecked = 0;
    let totalBoxesFixed = 0;

    for (const boopDoc of allBoops) {
        if (!boopDoc.boops || !Array.isArray(boopDoc.boops)) {
            continue;
        }

        const tenantId = boopDoc.tenant_id || 'no-tenant';
        const tenantBoxesById = boxesByIdAndTenant.get(tenantId);
        const tenantBoxesByLinked = boxesByLinkedAndTenant.get(tenantId);

        if (!tenantBoxesById) {
            console.log(`   ⚠️  No boxes found for tenant ${tenantId}`);
            continue;
        }

        let docModified = false;

        for (let boopItem of boopDoc.boops) {
            if (!boopItem.id) {
                continue;
            }

            totalBoxesChecked++;
            const boxId = boopItem.id.toString();

            // Check if box exists for this tenant
            if (tenantBoxesById.has(boxId)) {
                // Box exists, all good
                continue;
            }

            console.log(`   ⚠️  Box ${boxId} not found for tenant ${tenantId} in boop ${boopDoc._id}`);

            // Box doesn't exist, try to find by linked field (within same tenant)
            if (boopItem.linked && tenantBoxesByLinked) {
                const linkedId = boopItem.linked.toString();
                const linkedBoxes = tenantBoxesByLinked.get(linkedId);

                if (linkedBoxes && linkedBoxes.length > 0) {
                    // Found box(es) with same linked field in same tenant
                    const replacementBox = linkedBoxes[0]; // All are from same tenant

                    console.log(`   ✓ Found replacement box via linked field (tenant: ${tenantId}): ${boxId} → ${replacementBox._id}`);
                    console.log(`     Linked ID: ${linkedId}`);
                    console.log(`     Old box name: ${boopItem.name}`);
                    console.log(`     New box name: ${replacementBox.name}`);

                    boopItem.id = replacementBox._id;
                    docModified = true;
                    totalBoxesFixed++;

                    boxUpdates.push({
                        boopId: boopDoc._id,
                        tenantId: tenantId,
                        oldBoxId: boxId,
                        newBoxId: replacementBox._id.toString(),
                        linkedId: linkedId,
                        method: 'linked_match_tenant_specific'
                    });
                } else {
                    // No box found even with linked field in this tenant
                    console.log(`   ❌ No box found with linked field ${linkedId} for tenant ${tenantId}`);
                    boxNotFound.push({
                        boopId: boopDoc._id,
                        boopName: boopDoc.name,
                        boxId: boxId,
                        boxName: boopItem.name,
                        linkedId: linkedId,
                        tenantId: tenantId
                    });
                }
            } else {
                // No linked field, cannot find replacement
                console.log(`   ❌ Box has no linked field, cannot find replacement`);
                boxNotFound.push({
                    boopId: boopDoc._id,
                    boopName: boopDoc.name,
                    boxId: boxId,
                    boxName: boopItem.name,
                    linkedId: null,
                    tenantId: tenantId
                });
            }
        }

        // Update the document if modified
        if (docModified) {
            await SupplierBoop.updateOne(
                { _id: boopDoc._id },
                { $set: { boops: boopDoc.boops } }
            );
        }
    }

    console.log(`\n   📊 Box Validation Results:`);
    console.log(`      Total boxes checked: ${totalBoxesChecked}`);
    console.log(`      Boxes fixed via linked field: ${totalBoxesFixed}`);
    console.log(`      Boxes not found (manual review needed): ${boxNotFound.length}`);

    return { boxUpdates, boxNotFound, totalBoxesChecked, totalBoxesFixed };
}

/**
 * Validate and fix option references in supplier_boops (tenant-specific)
 */
async function validateAndFixOptionReferences() {
    console.log('\n🔧 Validating Option References (Tenant-Specific)...');

    const allBoops = await SupplierBoop.find({}).lean();
    const allOptions = await SupplierOption.find({}).lean();

    // Create tenant-specific lookup maps for options
    const optionsByIdAndTenant = new Map(); // Map<tenantId, Map<optionId, option>>
    const optionsByLinkedAndTenant = new Map(); // Map<tenantId, Map<linkedId, option[]>>

    for (const option of allOptions) {
        const tenantId = option.tenant_id || 'no-tenant';

        // Index by ID and tenant
        if (!optionsByIdAndTenant.has(tenantId)) {
            optionsByIdAndTenant.set(tenantId, new Map());
        }
        optionsByIdAndTenant.get(tenantId).set(option._id.toString(), option);

        // Index by linked and tenant
        if (option.linked) {
            if (!optionsByLinkedAndTenant.has(tenantId)) {
                optionsByLinkedAndTenant.set(tenantId, new Map());
            }

            const linkedStr = option.linked.toString();
            const tenantLinkedMap = optionsByLinkedAndTenant.get(tenantId);

            if (!tenantLinkedMap.has(linkedStr)) {
                tenantLinkedMap.set(linkedStr, []);
            }
            tenantLinkedMap.get(linkedStr).push(option);
        }
    }

    const optionUpdates = [];
    const optionNotFound = [];
    let totalOptionsChecked = 0;
    let totalOptionsFixed = 0;

    for (const boopDoc of allBoops) {
        if (!boopDoc.boops || !Array.isArray(boopDoc.boops)) {
            continue;
        }

        const tenantId = boopDoc.tenant_id || 'no-tenant';
        const tenantOptionsById = optionsByIdAndTenant.get(tenantId);
        const tenantOptionsByLinked = optionsByLinkedAndTenant.get(tenantId);

        if (!tenantOptionsById) {
            console.log(`   ⚠️  No options found for tenant ${tenantId}`);
            continue;
        }

        let docModified = false;

        for (let boopItem of boopDoc.boops) {
            if (!boopItem.ops || !Array.isArray(boopItem.ops)) {
                continue;
            }

            for (let op of boopItem.ops) {
                if (!op.id) {
                    continue;
                }

                totalOptionsChecked++;
                const optionId = op.id.toString();

                // Check if option exists for this tenant
                if (tenantOptionsById.has(optionId)) {
                    // Option exists, all good
                    continue;
                }

                console.log(`   ⚠️  Option ${optionId} not found for tenant ${tenantId} in boop ${boopDoc._id}`);

                // Option doesn't exist, try to find by linked field (within same tenant)
                if (op.linked && tenantOptionsByLinked) {
                    const linkedId = op.linked.toString();
                    const linkedOptions = tenantOptionsByLinked.get(linkedId);

                    if (linkedOptions && linkedOptions.length > 0) {
                        // Found option(s) with same linked field in same tenant
                        const replacementOption = linkedOptions[0]; // All are from same tenant

                        console.log(`   ✓ Found replacement option via linked field (tenant: ${tenantId}): ${optionId} → ${replacementOption._id}`);
                        console.log(`     Linked ID: ${linkedId}`);
                        console.log(`     Old option name: ${op.name}`);
                        console.log(`     New option name: ${replacementOption.name}`);

                        op.id = replacementOption._id;
                        docModified = true;
                        totalOptionsFixed++;

                        optionUpdates.push({
                            boopId: boopDoc._id,
                            tenantId: tenantId,
                            boxId: boopItem.id,
                            oldOptionId: optionId,
                            newOptionId: replacementOption._id.toString(),
                            linkedId: linkedId,
                            method: 'linked_match_tenant_specific'
                        });
                    } else {
                        // No option found even with linked field in this tenant
                        console.log(`   ❌ No option found with linked field ${linkedId} for tenant ${tenantId}`);
                        optionNotFound.push({
                            boopId: boopDoc._id,
                            boopName: boopDoc.name,
                            boxId: boopItem.id,
                            boxName: boopItem.name,
                            optionId: optionId,
                            optionName: op.name,
                            linkedId: linkedId,
                            tenantId: tenantId
                        });
                    }
                } else {
                    // No linked field, cannot find replacement
                    console.log(`   ❌ Option has no linked field, cannot find replacement`);
                    optionNotFound.push({
                        boopId: boopDoc._id,
                        boopName: boopDoc.name,
                        boxId: boopItem.id,
                        boxName: boopItem.name,
                        optionId: optionId,
                        optionName: op.name,
                        linkedId: null,
                        tenantId: tenantId
                    });
                }
            }
        }

        // Update the document if modified
        if (docModified) {
            await SupplierBoop.updateOne(
                { _id: boopDoc._id },
                { $set: { boops: boopDoc.boops } }
            );
        }
    }

    console.log(`\n   📊 Option Validation Results:`);
    console.log(`      Total options checked: ${totalOptionsChecked}`);
    console.log(`      Options fixed via linked field: ${totalOptionsFixed}`);
    console.log(`      Options not found (manual review needed): ${optionNotFound.length}`);

    return { optionUpdates, optionNotFound, totalOptionsChecked, totalOptionsFixed };
}

/**
 * Validate and fix excludes references in supplier_boops ops (tenant-specific)
 */
async function validateAndFixExcludesReferences() {
    console.log('\n🔗 Validating Excludes References (Tenant-Specific)...');

    const allBoops = await SupplierBoop.find({}).lean();
    const allOptions = await SupplierOption.find({}).lean();

    // Create tenant-specific lookup maps for options
    const optionsByIdAndTenant = new Map(); // Map<tenantId, Map<optionId, option>>
    const optionsByLinkedAndTenant = new Map(); // Map<tenantId, Map<linkedId, option[]>>

    for (const option of allOptions) {
        const tenantId = option.tenant_id || 'no-tenant';

        // Index by ID and tenant
        if (!optionsByIdAndTenant.has(tenantId)) {
            optionsByIdAndTenant.set(tenantId, new Map());
        }
        optionsByIdAndTenant.get(tenantId).set(option._id.toString(), option);

        // Index by linked and tenant
        if (option.linked) {
            if (!optionsByLinkedAndTenant.has(tenantId)) {
                optionsByLinkedAndTenant.set(tenantId, new Map());
            }

            const linkedStr = option.linked.toString();
            const tenantLinkedMap = optionsByLinkedAndTenant.get(tenantId);

            if (!tenantLinkedMap.has(linkedStr)) {
                tenantLinkedMap.set(linkedStr, []);
            }
            tenantLinkedMap.get(linkedStr).push(option);
        }
    }

    const excludesUpdates = [];
    const excludesNotFound = [];
    let totalExcludesChecked = 0;
    let totalExcludesFixed = 0;

    for (const boopDoc of allBoops) {
        if (!boopDoc.boops || !Array.isArray(boopDoc.boops)) {
            continue;
        }

        const tenantId = boopDoc.tenant_id || 'no-tenant';
        const tenantOptionsById = optionsByIdAndTenant.get(tenantId);
        const tenantOptionsByLinked = optionsByLinkedAndTenant.get(tenantId);

        if (!tenantOptionsById) {
            console.log(`   ⚠️  No options found for tenant ${tenantId}`);
            continue;
        }

        let docModified = false;

        for (let boopItem of boopDoc.boops) {
            if (!boopItem.ops || !Array.isArray(boopItem.ops)) {
                continue;
            }

            for (let op of boopItem.ops) {
                if (!op.excludes || !Array.isArray(op.excludes)) {
                    continue;
                }

                for (let i = 0; i < op.excludes.length; i++) {
                    const excludeGroup = op.excludes[i];

                    if (!Array.isArray(excludeGroup)) {
                        continue;
                    }

                    for (let j = 0; j < excludeGroup.length; j++) {
                        const excludeId = excludeGroup[j];
                        if (!excludeId) continue;

                        totalExcludesChecked++;
                        const excludeIdStr = excludeId.toString();

                        // Check if excluded option exists for this tenant
                        if (tenantOptionsById.has(excludeIdStr)) {
                            // Exclude exists, all good
                            continue;
                        }

                        console.log(`   ⚠️  Excluded option ${excludeIdStr} not found for tenant ${tenantId} in op ${op.id}`);

                        // Try to find by linked field within same tenant
                        // First, check if the missing option was part of a linked group
                        let foundReplacement = false;

                        if (tenantOptionsByLinked) {
                            for (const [linkedId, linkedOptions] of tenantOptionsByLinked.entries()) {
                                // Check if any option in this linked group has the old ID
                                // We need to find the linked field of the missing option
                                // Since we don't have it directly, we'll check all linked groups

                                // Get the current op's linked field to find related options
                                if (op.linked) {
                                    const opLinkedId = op.linked.toString();
                                    const relatedOptions = tenantOptionsByLinked.get(opLinkedId);

                                    if (relatedOptions && relatedOptions.length > 0) {
                                        // Find a replacement from the same box (same linked as op)
                                        const replacementOption = relatedOptions.find(opt =>
                                            opt._id.toString() !== excludeIdStr
                                        );

                                        if (replacementOption) {
                                            console.log(`   ✓ Found replacement for exclude via op linked field (tenant: ${tenantId}): ${excludeIdStr} → ${replacementOption._id}`);

                                            op.excludes[i][j] = replacementOption._id;
                                            docModified = true;
                                            totalExcludesFixed++;
                                            foundReplacement = true;

                                            excludesUpdates.push({
                                                boopId: boopDoc._id,
                                                tenantId: tenantId,
                                                opId: op.id,
                                                oldExcludeId: excludeIdStr,
                                                newExcludeId: replacementOption._id.toString(),
                                                linkedId: opLinkedId
                                            });
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if (!foundReplacement) {
                            console.log(`   ❌ No replacement found for exclude ${excludeIdStr} in tenant ${tenantId}`);
                            excludesNotFound.push({
                                boopId: boopDoc._id,
                                boopName: boopDoc.name,
                                opId: op.id,
                                opName: op.name,
                                excludeId: excludeIdStr,
                                tenantId: tenantId
                            });
                        }
                    }
                }
            }
        }

        // Update the document if modified
        if (docModified) {
            await SupplierBoop.updateOne(
                { _id: boopDoc._id },
                { $set: { boops: boopDoc.boops } }
            );
        }
    }

    console.log(`\n   📊 Excludes Validation Results:`);
    console.log(`      Total excludes checked: ${totalExcludesChecked}`);
    console.log(`      Excludes fixed via linked field: ${totalExcludesFixed}`);
    console.log(`      Excludes not found (manual review needed): ${excludesNotFound.length}`);

    return { excludesUpdates, excludesNotFound, totalExcludesChecked, totalExcludesFixed };
}

/**
 * Generate report files for manual review
 */
async function generateReports(boxNotFound, optionNotFound, excludesNotFound) {
    console.log('\n📄 Generating Reports for Manual Review...');

    const fs = require('fs');
    const path = require('path');

    const reportsDir = path.join(__dirname, 'validation-reports');
    if (!fs.existsSync(reportsDir)) {
        fs.mkdirSync(reportsDir);
    }

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

    // Box report
    if (boxNotFound.length > 0) {
        const boxReportPath = path.join(reportsDir, `boxes-not-found-${timestamp}.json`);
        fs.writeFileSync(boxReportPath, JSON.stringify(boxNotFound, null, 2));
        console.log(`   ✓ Box report saved: ${boxReportPath}`);
        console.log(`     Contains ${boxNotFound.length} boxes that need manual review`);
    }

    // Option report
    if (optionNotFound.length > 0) {
        const optionReportPath = path.join(reportsDir, `options-not-found-${timestamp}.json`);
        fs.writeFileSync(optionReportPath, JSON.stringify(optionNotFound, null, 2));
        console.log(`   ✓ Option report saved: ${optionReportPath}`);
        console.log(`     Contains ${optionNotFound.length} options that need manual review`);
    }

    // Excludes report
    if (excludesNotFound.length > 0) {
        const excludesReportPath = path.join(reportsDir, `excludes-not-found-${timestamp}.json`);
        fs.writeFileSync(excludesReportPath, JSON.stringify(excludesNotFound, null, 2));
        console.log(`   ✓ Excludes report saved: ${excludesReportPath}`);
        console.log(`     Contains ${excludesNotFound.length} excludes that need manual review`);
    }

    // Group by tenant for better analysis
    const tenantSummary = {};

    [...boxNotFound, ...optionNotFound, ...excludesNotFound].forEach(item => {
        if (!tenantSummary[item.tenantId]) {
            tenantSummary[item.tenantId] = {
                boxes: 0,
                options: 0,
                excludes: 0
            };
        }
    });

    boxNotFound.forEach(item => tenantSummary[item.tenantId].boxes++);
    optionNotFound.forEach(item => tenantSummary[item.tenantId].options++);
    excludesNotFound.forEach(item => tenantSummary[item.tenantId].excludes++);

    // Summary report
    const summary = {
        timestamp: new Date().toISOString(),
        boxesNotFound: boxNotFound.length,
        optionsNotFound: optionNotFound.length,
        excludesNotFound: excludesNotFound.length,
        total: boxNotFound.length + optionNotFound.length + excludesNotFound.length,
        byTenant: tenantSummary
    };

    const summaryPath = path.join(reportsDir, `validation-summary-${timestamp}.json`);
    fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2));
    console.log(`   ✓ Summary report saved: ${summaryPath}`);

    // Print tenant summary
    console.log(`\n   📊 Issues by Tenant:`);
    for (const [tenantId, counts] of Object.entries(tenantSummary)) {
        const total = counts.boxes + counts.options + counts.excludes;
        if (total > 0) {
            console.log(`      ${tenantId}:`);
            console.log(`         Boxes: ${counts.boxes}`);
            console.log(`         Options: ${counts.options}`);
            console.log(`         Excludes: ${counts.excludes}`);
        }
    }
}

/**
 * Main validation function
 */
async function runValidation() {
    console.log('========================================');
    console.log('Supplier Boops Reference Validation');
    console.log('(Tenant-Specific)');
    console.log('========================================\n');

    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI, {});
        console.log('✅ Connected to MongoDB\n');

        // Step 1: Validate and fix box references
        const boxResults = await validateAndFixBoxReferences();

        // Step 2: Validate and fix option references
        const optionResults = await validateAndFixOptionReferences();

        // Step 3: Validate and fix excludes references
        const excludesResults = await validateAndFixExcludesReferences();

        // Generate reports for manual review
        if (boxResults.boxNotFound.length > 0 ||
            optionResults.optionNotFound.length > 0 ||
            excludesResults.excludesNotFound.length > 0) {
            await generateReports(
                boxResults.boxNotFound,
                optionResults.optionNotFound,
                excludesResults.excludesNotFound
            );
        } else {
            console.log('\n✅ No issues found - all references are valid!');
        }

        console.log('\n========================================');
        console.log('Validation Completed! 🎉');
        console.log('========================================');
        console.log('Summary:');
        console.log(`\n📦 BOXES:`);
        console.log(`   - Checked: ${boxResults.totalBoxesChecked}`);
        console.log(`   - Fixed: ${boxResults.totalBoxesFixed}`);
        console.log(`   - Not Found: ${boxResults.boxNotFound.length}`);

        console.log(`\n🔧 OPTIONS:`);
        console.log(`   - Checked: ${optionResults.totalOptionsChecked}`);
        console.log(`   - Fixed: ${optionResults.totalOptionsFixed}`);
        console.log(`   - Not Found: ${optionResults.optionNotFound.length}`);

        console.log(`\n🔗 EXCLUDES:`);
        console.log(`   - Checked: ${excludesResults.totalExcludesChecked}`);
        console.log(`   - Fixed: ${excludesResults.totalExcludesFixed}`);
        console.log(`   - Not Found: ${excludesResults.excludesNotFound.length}`);

        console.log(`\n📊 TOTAL:`);
        console.log(`   - Total Fixed: ${boxResults.totalBoxesFixed + optionResults.totalOptionsFixed + excludesResults.totalExcludesFixed}`);
        console.log(`   - Needs Manual Review: ${boxResults.boxNotFound.length + optionResults.optionNotFound.length + excludesResults.excludesNotFound.length}`);
        console.log('========================================');

        // Close connection
        await mongoose.connection.close();
        console.log('\n✅ MongoDB connection closed');

        process.exit(0);
    } catch (error) {
        console.error('\n========================================');
        console.error('❌ Validation Failed!');
        console.error('========================================');
        console.error(error);

        // Close connection
        if (mongoose.connection.readyState === 1) {
            await mongoose.connection.close();
        }

        process.exit(1);
    }
}

// Run the validation
runValidation();