require('dotenv').config();
const mongoose = require('mongoose');
const SupplierOption = require('../Models/SupplierOption');

const ObjectId = mongoose.Types.ObjectId;

/**
 * Normalize slug to a consistent format (remove hyphen between single digits)
 * Converts: "4-4-full-color" -> "44-full-color"
 *           "4-0-full-color" -> "40-full-color"
 *           "135-grs" -> "135-grs" (no change needed)
 */
function normalizeSlug(slug, name) {
    if (!slug) return slug;

    // Pattern to detect slugs like "4-4-something" that should be "44-something"
    // This handles cases like: 4-4, 4-0, 1-4, 1-0, etc. that represent fractions
    const fractionPattern = /^(\d)-(\d)(-|$)/;

    // Check if the slug starts with two single digits separated by a hyphen
    // We use the name to determine if it's a fraction format (like "4/4", "4/0")
    if (name && name.includes('/')) {
        const match = slug.match(fractionPattern);
        if (match) {
            // Remove hyphen between the two digits: "4-4" -> "44"
            const normalized = slug.replace(fractionPattern, `${match[1]}${match[2]}${match[3]}`);
            return normalized;
        }
    }

    return slug;
}

/**
 * Normalize system_key to a consistent format
 */
function normalizeSystemKey(systemKey, name) {
    if (!systemKey) return systemKey;

    // Apply same normalization as slug
    return normalizeSlug(systemKey, name);
}

/**
 * Detect and fix slug inconsistencies
 */
async function normalizeAllSlugs(dryRun = true) {
    console.log('========================================');
    console.log('Slug Normalization Script');
    console.log('Format: X-X-name → XX-name (compact)');
    console.log('========================================\n');
    console.log(`Mode: ${dryRun ? 'DRY RUN (no changes)' : 'LIVE (will update database)'}\n`);

    try {
        // Get all supplier options
        const options = await SupplierOption.find({}).lean();
        console.log(`Found ${options.length} total options\n`);

        const updates = [];
        const slugChanges = new Map(); // Track all slug changes

        for (const option of options) {
            const changes = {};
            let hasChanges = false;

            // Normalize slug
            const normalizedSlug = normalizeSlug(option.slug, option.name);
            if (normalizedSlug !== option.slug) {
                changes.slug = {
                    old: option.slug,
                    new: normalizedSlug
                };
                hasChanges = true;

                // Track this change
                if (!slugChanges.has(option.slug)) {
                    slugChanges.set(option.slug, []);
                }
                slugChanges.get(option.slug).push({
                    _id: option._id,
                    name: option.name,
                    newSlug: normalizedSlug
                });
            }

            // Normalize system_key
            const normalizedSystemKey = normalizeSystemKey(option.system_key, option.name);
            if (normalizedSystemKey !== option.system_key) {
                changes.system_key = {
                    old: option.system_key,
                    new: normalizedSystemKey
                };
                hasChanges = true;
            }

            if (hasChanges) {
                updates.push({
                    _id: option._id,
                    name: option.name,
                    tenant_id: option.tenant_id,
                    changes
                });
            }
        }

        // Display findings
        console.log('========================================');
        console.log('Slug Normalization Report');
        console.log('========================================\n');

        if (updates.length === 0) {
            console.log('✅ No slug inconsistencies found! All slugs are already normalized.');
            return { updated: 0, changes: [] };
        }

        console.log(`Found ${updates.length} options with slug inconsistencies:\n`);

        // Group changes by old slug pattern
        console.log('Slug Patterns Found:');
        console.log('--------------------');
        for (const [oldSlug, items] of slugChanges.entries()) {
            const newSlug = items[0].newSlug;
            console.log(`\n"${oldSlug}" → "${newSlug}" (${items.length} options)`);
            items.forEach(item => {
                console.log(`  - ${item._id}: "${item.name}"`);
            });
        }

        // Show detailed changes
        console.log('\n\nDetailed Changes:');
        console.log('-----------------');
        for (const update of updates) {
            console.log(`\n📝 ${update._id} - "${update.name}"`);
            console.log(`   Tenant: ${update.tenant_id}`);

            if (update.changes.slug) {
                console.log(`   slug: "${update.changes.slug.old}" → "${update.changes.slug.new}"`);
            }
            if (update.changes.system_key) {
                console.log(`   system_key: "${update.changes.system_key.old}" → "${update.changes.system_key.new}"`);
            }
        }

        // Apply updates if not dry run
        if (!dryRun) {
            console.log('\n========================================');
            console.log('Applying Updates...');
            console.log('========================================\n');

            let updateCount = 0;
            for (const update of updates) {
                const updateFields = {};

                if (update.changes.slug) {
                    updateFields.slug = update.changes.slug.new;
                }
                if (update.changes.system_key) {
                    updateFields.system_key = update.changes.system_key.new;
                }

                await SupplierOption.updateOne(
                    { _id: update._id },
                    { $set: updateFields }
                );

                updateCount++;
                console.log(`✓ Updated: ${update._id} - "${update.name}"`);
            }

            console.log(`\n✅ Successfully updated ${updateCount} options`);
        } else {
            console.log('\n========================================');
            console.log('DRY RUN - No changes applied');
            console.log('========================================');
            console.log(`\nTo apply these changes, run with --apply flag`);
            console.log(`${updates.length} options would be updated.`);
        }

        return { updated: dryRun ? 0 : updates.length, changes: updates };

    } catch (error) {
        console.error('❌ Error during normalization:', error);
        throw error;
    }
}

/**
 * Main function
 */
async function run() {
    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI, {});
        console.log('✅ Connected to MongoDB\n');

        // Check command line arguments
        const dryRun = process.argv.includes('--apply') ? false : true;

        // Run normalization
        await normalizeAllSlugs(dryRun);

        // Close connection
        await mongoose.connection.close();
        console.log('\n✅ MongoDB connection closed');

        process.exit(0);
    } catch (error) {
        console.error('\n========================================');
        console.error('❌ Script Failed!');
        console.error('========================================');
        console.error(error);

        if (mongoose.connection.readyState === 1) {
            await mongoose.connection.close();
        }

        process.exit(1);
    }
}

// Run the script
run();
