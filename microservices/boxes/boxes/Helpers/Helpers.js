const SupplierBox = require("../Models/SupplierBox");
const Box = require("../Models/Box");
const StoreSupplierBoxRequest = require("../Requests/StoreSupplierBoxRequest");
const axios = require('axios');

/**
 * Handle linked box creation
 */
async function handleLinkedBox(supplierId, body, boxDisplayName) {
    const box = await Box.findById(body.linked);

    if (box) {
        return await createLinkedSupplierBox(
            supplierId, 
            body, 
            box, 
            boxDisplayName
        );
    } else {
        return await createStandaloneSupplierBox(
            supplierId, 
            body, 
            boxDisplayName,
            true
        );
    }
}

/**
 * Handle standalone box creation
 */
async function handleStandaloneBox(supplierId, body, boxDisplayName) {
    return await createStandaloneSupplierBox(
        supplierId, 
        body, 
        boxDisplayName,
        true
    );
}

/**
 * Create linked supplier box
 */
async function createLinkedSupplierBox(supplierId, body, box, boxDisplayName) {
    const storeRequest = new StoreSupplierBoxRequest();
    
    // Override name with linked box name and handle additional field
    const excludesVisible = body.additional?.excludes_visible !== undefined 
        ? Boolean(body.additional.excludes_visible) 
        : true;
    
    const dataToStore = storeRequest.prepare(
        { ...body, name: box.name },
        supplierId,
        boxDisplayName,
        box._id
    );
    
    // Set additional field for linked boxes
    dataToStore.additional = { excludes_visible: excludesVisible };

    return await SupplierBox.create(dataToStore);
}

/**
 * Create standalone supplier box
 */
async function createStandaloneSupplierBox(supplierId, body, boxDisplayName, runSimilarity = false) {
    const storeRequest = new StoreSupplierBoxRequest();
    
    const dataToStore = storeRequest.prepare(
        body,
        supplierId,
        boxDisplayName,
        null
    );

    const newSupplierBox = await SupplierBox.create(dataToStore);

    if (runSimilarity) {
        await runSimilarityCheck(supplierId, body);
    }

    return newSupplierBox;
}

/**
 * Run similarity check for boxes
 */
async function runSimilarityCheck(supplierId, body) {
    try {
        const url = 'http://assortments:5000/similarity/boxes';
        const payload = { 
            tenant: supplierId, 
            tenant_name: body.tenant_name, 
            boxes: [{ name: body.name, sku: "" }]
        };

        await axios.post(url, payload, {
            headers: { "Content-Type": "application/json" }
        });
    } catch (error) {
        console.error('Error running similarity check:', error.message);
        // Don't throw error, similarity check is non-critical
    }
}

module.exports = {
    handleLinkedBox,
    handleStandaloneBox,
    createLinkedSupplierBox,
    createStandaloneSupplierBox,
    runSimilarityCheck
};