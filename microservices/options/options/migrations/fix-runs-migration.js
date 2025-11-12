require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');
const ObjectId = mongoose.Types.ObjectId;

/**
 * Check if a run item is in the wrong format (has from/to/price directly)
 */
function isWrongRunFormat(runItem) {
    // Wrong format has from, to, price directly without category_id wrapper
    return (
        runItem &&
        typeof runItem === 'object' &&
        (runItem.from !== undefined || runItem.to !== undefined || runItem.price !== undefined) &&
        !runItem.category_id &&
        !runItem.runs
    );
}

/**
 * Check if runs array needs fixing
 */
function needsRunsFix(runs) {
    if (!Array.isArray(runs) || runs.length === 0) {
        return false;
    }

    // Check if any item in the array has wrong structure
    return runs.some(item => isWrongRunFormat(item));
}

/**
 * Fix runs structure - wrap direct run objects in proper category structure
 */
function fixRunsStructure(runs, defaultCategoryId = null) {
    if (!Array.isArray(runs) || runs.length === 0) {
        return runs;
    }

    // Check if any runs are in wrong format
    const hasWrongFormat = runs.some(item => isWrongRunFormat(item));

    if (!hasWrongFormat) {
        return runs; // Already in correct format
    }

    // Separate wrong format items from correct format items
    const wrongFormatItems = [];
    const correctFormatItems = [];

    runs.forEach(item => {
        if (isWrongRunFormat(item)) {
            wrongFormatItems.push(item);
        } else {
            correctFormatItems.push(item);
        }
    });

    // If we have wrong format items, wrap them in the proper structure
    if (wrongFormatItems.length > 0) {
        const wrappedRuns = {
            category_id: defaultCategoryId,
            start_cost: 0,
            runs: wrongFormatItems
        };

        // Return correct items + newly wrapped items
        return [...correctFormatItems, wrappedRuns];
    }

    return runs;
}

/**
 * Try to find a default category_id from the document
 */
function findDefaultCategoryId(optionData) {
    // Try to find category_id from existing correct runs
    if (Array.isArray(optionData.runs)) {
        for (const run of optionData.runs) {
            if (run.category_id) {
                return run.category_id;
            }
        }
    }

    // Try to get from linked field
    if (optionData.linked && ObjectId.isValid(optionData.linked)) {
        return optionData.linked;
    }

    // Return null if no default found (we'll set it in the migration)
    return null;
}

/**
 * Fix SupplierOption runs fields
 */
async function fixSupplierOptionRuns() {
    console.log('Starting SupplierOption runs migration...');

    try {
        const options = await SupplierOption.find({}).lean();
        let updatedCount = 0;
        let errorCount = 0;
        let skippedCount = 0;
        let needsManualReviewCount = 0;

        console.log(`Found ${options.length} SupplierOption documents to process\n`);

        for (const optionData of options) {
            try {
                // Check if runs needs fixing
                if (!needsRunsFix(optionData.runs)) {
                    skippedCount++;
                    continue;
                }

                // Find a default category_id
                const defaultCategoryId = findDefaultCategoryId(optionData);

                // Fix the runs structure
                const fixedRuns = fixRunsStructure(optionData.runs, defaultCategoryId);

                // Log the change for debugging
                console.log(`\n📝 Fixing document ${optionData._id}:`);
                console.log(`   Name: ${optionData.name || 'Unnamed'}`);
                console.log(`   Default Category ID: ${defaultCategoryId || 'NULL (needs manual review)'}`);
                console.log(`   Before: ${JSON.stringify(optionData.runs, null, 2)}`);
                console.log(`   After:  ${JSON.stringify(fixedRuns, null, 2)}`);

                if (!defaultCategoryId) {
                    console.log(`   ⚠️  WARNING: No category_id found - set to null, may need manual review`);
                    needsManualReviewCount++;
                }

                // Update the document
                await SupplierOption.updateOne(
                    { _id: optionData._id },
                    { $set: { runs: fixedRuns } }
                );

                updatedCount++;
            } catch (error) {
                console.error(`\n❌ Error updating option ${optionData._id}:`, error.message);
                errorCount++;
            }
        }

        console.log('\n========================================');
        console.log('Migration Summary:');
        console.log(`✅ Updated: ${updatedCount} documents`);
        console.log(`⏭️  Skipped: ${skippedCount} documents (already correct)`);
        if (needsManualReviewCount > 0) {
            console.log(`⚠️  Needs Review: ${needsManualReviewCount} documents (category_id set to null)`);
        }
        if (errorCount > 0) {
            console.log(`❌ Errors: ${errorCount} documents`);
        }
        console.log('========================================');

        if (needsManualReviewCount > 0) {
            console.log('\n⚠️  IMPORTANT: Some documents have null category_id and may need manual review.');
            console.log('   You can find them with: db.supplieroptions.find({ "runs.category_id": null })');
        }
    } catch (error) {
        console.error('❌ Migration error:', error);
        throw error;
    }
}

/**
 * Main migration function
 */
async function runMigration() {
    console.log('\n========================================');
    console.log('Starting Runs Structure Migration');
    console.log('========================================\n');

    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI);
        console.log('✅ Connected to MongoDB\n');

        // Run migration
        await fixSupplierOptionRuns();

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