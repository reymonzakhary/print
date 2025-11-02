#!/usr/bin/env node

const fs = require("fs");
const path = require("path");

// Get the schema name and modules from command line arguments
const args = process.argv.slice(2);
const schemaName = args[0];

// Find the --submodules flag and get the modules after it
const submodulesIndex = args.indexOf("--submodules");
const modules = submodulesIndex !== -1 ? args.slice(submodulesIndex + 1) : [];

if (!schemaName) {
  console.error("Error: Please provide a schema name");
  console.error(
    "Usage: node scaffold-permission-schema.cjs <schema-name> [--submodules module1 module2 ...]",
  );
  process.exit(1);
}

// Transform dashed names to camelCase
const camelCaseName = schemaName.replace(/-([a-z])/g, (match, letter) => letter.toUpperCase());

// Create submodules object if modules are provided
const submodules =
  modules.length > 0
    ? modules.reduce((acc, module) => {
        acc[module] = true;
        return acc;
      }, {})
    : {};

// Define the schema template
const schemaTemplate = `import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("${schemaName}", {
  // A permission schema always has access, list, read, create, update, delete
  submodules: ${JSON.stringify(submodules, null, 2)},

  groups: {
    // A group can either reference its parent or submodules with an @ symbol.
    // e.g. "boxes": ["@boxes-read"]
    // Or it can just reference permissions as '@list'
  },
});
`;

// Define the path where the schema will be created
const schemaPath = path.join(process.cwd(), "app", "permissions", "schemas", `${camelCaseName}.js`);

// Check if the file already exists
if (fs.existsSync(schemaPath)) {
  console.error(`Error: Schema file ${schemaPath} already exists`);
  process.exit(1);
}

// Create the schema file
try {
  fs.writeFileSync(schemaPath, schemaTemplate);
  console.log(`Successfully created schema file: ${schemaPath}`);

  // Update usePermissions.js to include the new schema
  updateUsePermissions(schemaName, camelCaseName);
} catch (error) {
  console.error(`Error creating schema file: ${error.message}`);
  process.exit(1);
}

// Function to update usePermissions.js
function updateUsePermissions(schemaName, camelCaseName) {
  const usePermissionsPath = path.join(process.cwd(), "app", "permissions", "usePermissions.js");

  try {
    // Read the current content
    let content = fs.readFileSync(usePermissionsPath, "utf8");

    // Add import statement if it doesn't exist
    const importStatement = `import ${camelCaseName} from "./schemas/${camelCaseName}.js";`;
    if (!content.includes(importStatement)) {
      // Find the last import statement
      const importRegex = /import\s+[\w-]+\s+from\s+["'][^"']+["'];/g;
      const lastImportMatch = [...content.matchAll(importRegex)].pop();

      if (lastImportMatch) {
        const insertPosition = lastImportMatch.index + lastImportMatch[0].length;
        content =
          content.slice(0, insertPosition) + "\n" + importStatement + content.slice(insertPosition);
      } else {
        // If no imports found, add after the first line
        content =
          content.split("\n")[0] +
          "\n" +
          importStatement +
          "\n" +
          content.split("\n").slice(1).join("\n");
      }
    }

    // Add to permissionRegistry.resolve() if not already there
    const resolveRegex = /permissionRegistry\.resolve\(([\s\S]*?)\)/;
    const resolveMatch = content.match(resolveRegex);

    if (resolveMatch) {
      const resolveParams = resolveMatch[1].trim();

      // Clean up any existing issues with extra commas
      let paramList = resolveParams
        .split(",")
        .map((param) => param.trim())
        .filter((param) => param !== ""); // Remove empty parameters

      // Check if the schema is already in the list
      if (!paramList.includes(camelCaseName)) {
        // Add the new schema to the list
        paramList.push(camelCaseName);

        // Format the parameter list with proper indentation
        const formattedParams = paramList.map((param) => `  ${param}`).join(",\n");
        const newResolveCall = `permissionRegistry.resolve(\n${formattedParams},\n)`;

        content = content.replace(resolveRegex, newResolveCall);
      }
    }

    // Write the updated content back to the file
    fs.writeFileSync(usePermissionsPath, content);
    console.log(`Successfully updated usePermissions.js to include ${schemaName} schema`);
  } catch (error) {
    console.error(`Error updating usePermissions.js: ${error.message}`);
  }
}
