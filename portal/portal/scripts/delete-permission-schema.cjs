#!/usr/bin/env node

const fs = require("fs");
const path = require("path");

// Get the schema name from command line arguments
const schemaName = process.argv[2];

if (!schemaName) {
  console.error("Error: Please provide a schema name");
  console.error("Usage: node delete-permission-schema.cjs <schema-name>");
  process.exit(1);
}

// Transform dashed names to camelCase
const camelCaseName = schemaName.replace(/-([a-z])/g, (match, letter) => letter.toUpperCase());

// Define the path where the schema file is located
const schemaPath = path.join(process.cwd(), "app", "permissions", "schemas", `${camelCaseName}.js`);

// Check if the file exists
if (!fs.existsSync(schemaPath)) {
  console.error(`Error: Schema file ${schemaPath} does not exist`);
  process.exit(1);
}

// Delete the schema file
try {
  fs.unlinkSync(schemaPath);
  console.log(`Successfully deleted schema file: ${schemaPath}`);

  // Update usePermissions.js to remove the schema
  updateUsePermissions(schemaName, camelCaseName);
} catch (error) {
  console.error(`Error deleting schema file: ${error.message}`);
  process.exit(1);
}

// Function to update usePermissions.js
function updateUsePermissions(schemaName, camelCaseName) {
  const usePermissionsPath = path.join(process.cwd(), "app", "permissions", "usePermissions.js");

  try {
    // Read the current content
    let content = fs.readFileSync(usePermissionsPath, "utf8");

    // Remove import statement
    const importRegex = new RegExp(`import\\s+${camelCaseName}\\s+from\\s+["']\\./schemas/${camelCaseName}\\.js["'];\\n?`);
    content = content.replace(importRegex, "");

    // Remove from permissionRegistry.resolve()
    const resolveRegex = /permissionRegistry\.resolve\(([\s\S]*?)\)/;
    const resolveMatch = content.match(resolveRegex);

    if (resolveMatch) {
      const resolveParams = resolveMatch[1].trim();

      // Clean up the parameter list
      let paramList = resolveParams
        .split(",")
        .map((param) => param.trim())
        .filter((param) => param !== "" && param !== camelCaseName); // Remove the schema and empty parameters

      // Format the parameter list with proper indentation
      const formattedParams = paramList.map((param) => `  ${param}`).join(",\n");
      const newResolveCall = `permissionRegistry.resolve(\n${formattedParams},\n)`;

      content = content.replace(resolveRegex, newResolveCall);
    }

    // Write the updated content back to the file
    fs.writeFileSync(usePermissionsPath, content);
    console.log(`Successfully updated usePermissions.js to remove ${schemaName} schema`);
  } catch (error) {
    console.error(`Error updating usePermissions.js: ${error.message}`);
  }
} 