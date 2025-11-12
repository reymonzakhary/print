require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');

/**
 * Extract the actual display name text from various formats
 */
function extractDisplayNameText(displayName) {
    // If it's already a string, return it
    if (typeof displayName === 'string') {
        return displayName;
    }

    // If it's an object with nested display_name
    if (typeof displayName === 'object' && displayName !== null) {
        // Check for nested display_name object
        if (displayName.display_name) {
            // Recursively extract if it's still an object
            if (typeof displayName.display_name === 'object' && displayName.display_name.display_name) {
                return displayName.display_name.display_name;
            }
            return displayName.display_name;
        }
    }

    // Default fallback
    return 'Unnamed';
}

/**
 * Check if display_name needs fixing
 */
function needsDisplayNameFix(displayName) {
    // If it's not an array, it needs fixing
    if (!Array.isArray(displayName)) {
        return true;
    }

    // Check if any item in the array has wrong structure
    // (display_name should be a string, not an object)
    for (const item of displayName) {
        if (item.display_name && typeof item.display_name === 'object') {
            return true;
        }
        // Check if required fields are missing
        if (!item.iso || !item.display_name) {
            return true;
        }
    }

    // Check if it has all required locales
    const requiredLocales = ['en', 'fr', 'de', 'nl', 'ar'];
    const existingLocales = displayName.map(item => item.iso);
    const hasAllLocales = requiredLocales.every(locale => existingLocales.includes(locale));

    return !hasAllLocales;
}

/**
 * Fix display_name - ensure it's always an array with proper structure
 */
function fixDisplayName(displayName, fallbackName = 'Unnamed') {
    let nameText = fallbackName;

    // Case 1: It's an array but has malformed items
    if (Array.isArray(displayName) && displayName.length > 0) {
        // Try to extract a valid name from the array
        for (const item of displayName) {
            const extracted = extractDisplayNameText(item);
            if (extracted && extracted !== 'Unnamed') {
                nameText = extracted;
                break;
            }
        }

        // Check if we need to rebuild the array
        const hasWrongStructure = displayName.some(item =>
            typeof item.display_name === 'object' || !item.iso || !item.display_name
        );

        if (!hasWrongStructure) {
            // Array is good, but check if all locales are present
            const existingLocales = displayName.map(item => item.iso);
            const requiredLocales = ['en', 'fr', 'de', 'nl', 'ar'];
            const missingLocales = requiredLocales.filter(locale => !existingLocales.includes(locale));

            if (missingLocales.length === 0) {
                return displayName; // No changes needed
            }

            // Add missing locales
            const result = [...displayName];
            missingLocales.forEach(locale => {
                result.push({
                    iso: locale,
                    display_name: nameText
                });
            });
            return result;
        }
    }
    // Case 2: It's a single object (wrong structure)
    else if (typeof displayName === 'object' && displayName !== null && !Array.isArray(displayName)) {
        nameText = extractDisplayNameText(displayName);
    }
    // Case 3: It's a string
    else if (typeof displayName === 'string') {
        nameText = displayName;
    }

    // Create proper array with all required locales
    return [
        { iso: 'en', display_name: nameText },
        { iso: 'fr', display_name: nameText },
        { iso: 'de', display_name: nameText },
        { iso: 'nl', display_name: nameText },
        { iso: 'ar', display_name: nameText }
    ];
}

/**
 * Fix SupplierOption display_name fields
 */
async function fixSupplierOptionDisplayNames() {
    console.log('Starting SupplierOption display_name migration...');

    try {
        const options = await SupplierOption.find({}).lean();
        let updatedCount = 0;
        let errorCount = 0;
        let skippedCount = 0;

        console.log(`Found ${options.length} SupplierOption documents to process`);

        for (const optionData of options) {
            try {
                // Check if display_name needs fixing
                if (!needsDisplayNameFix(optionData.display_name)) {
                    skippedCount++;
                    continue;
                }

                // Fix the display_name
                const fixedDisplayName = fixDisplayName(
                    optionData.display_name,
                    optionData.name || 'Unnamed'
                );

                // Log the change for debugging
                console.log(`\n📝 Fixing document ${optionData._id}:`);
                console.log(`   Before: ${JSON.stringify(optionData.display_name)}`);
                console.log(`   After:  ${JSON.stringify(fixedDisplayName)}`);

                // Update the document
                await SupplierOption.updateOne(
                    { _id: optionData._id },
                    { $set: { display_name: fixedDisplayName } }
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
        if (errorCount > 0) {
            console.log(`⚠️  Errors: ${errorCount} documents`);
        }
        console.log('========================================');
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
    console.log('Starting Display Name Migration');
    console.log('========================================\n');

    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI);
        console.log('✅ Connected to MongoDB\n');

        // Run migration
        await fixSupplierOptionDisplayNames();

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