require('dotenv').config();
const mongoose = require('mongoose');
const SupplierMachine = require('../Models/SupplierMachine');

/**
 * Clear all colors data from supplier_machines and set to empty array
 */
async function clearMachineColors() {
    console.log('========================================');
    console.log('Clear Machine Colors Migration');
    console.log('========================================\n');

    try {
        // Connect to MongoDB
        await mongoose.connect(process.env.mongoURI, {});
        console.log('✅ Connected to MongoDB\n');

        // Count machines with colors data before clearing
        const machinesWithColors = await SupplierMachine.countDocuments({
            colors: { $exists: true, $ne: [] }
        });

        console.log(`📊 Found ${machinesWithColors} machines with colors data\n`);

        if (machinesWithColors === 0) {
            console.log('✅ No machines with colors data found. Nothing to clear.');
            await mongoose.connection.close();
            process.exit(0);
        }

        // Get all machines for logging purposes
        const allMachines = await SupplierMachine.find({
            colors: { $exists: true, $ne: [] }
        }).lean();

        console.log('🔄 Clearing colors from the following machines:\n');
        for (const machine of allMachines) {
            const colorsCount = Array.isArray(machine.colors) ? machine.colors.length : 0;
            console.log(`   📦 Machine: ${machine.name} (${machine._id})`);
            console.log(`      Tenant: ${machine.tenant_name || machine.tenant_id}`);
            console.log(`      Colors entries: ${colorsCount}`);
        }

        console.log('\n🧹 Clearing colors data...\n');

        // Update all supplier_machines to set colors to empty array
        const result = await SupplierMachine.updateMany(
            {}, // Update all documents
            { $set: { colors: [] } }
        );

        console.log('========================================');
        console.log('Migration Completed Successfully! 🎉');
        console.log('========================================');
        console.log(`✅ Matched: ${result.matchedCount} machines`);
        console.log(`✅ Modified: ${result.modifiedCount} machines`);
        console.log(`✅ All colors data has been cleared`);
        console.log('========================================\n');

        // Verify the results
        const remainingWithColors = await SupplierMachine.countDocuments({
            colors: { $exists: true, $ne: [] }
        });

        if (remainingWithColors > 0) {
            console.log(`⚠️  Warning: ${remainingWithColors} machines still have colors data`);
        } else {
            console.log('✅ Verification passed: All colors arrays are now empty\n');
        }

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
clearMachineColors();
