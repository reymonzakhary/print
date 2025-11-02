const fs = require("fs/promises");
const path = require("path");
const fastGlob = require("fast-glob");
const { parse: vueParse } = require("@vue/compiler-dom");
const { parse: babelParse } = require("@babel/parser");
const traverse = require("@babel/traverse").default;
const generate = require("@babel/generator").default;

async function main() {
  const files = await fastGlob([
    "./app/app.vue",
    "./app/components/**/*.{js,vue,ts}",
    "./app/layouts/**/*.vue",
    "./app/pages/**/*.{js,vue,ts}",
  ]);

  const icons = new Set();

  for (const file of files) {
    const content = await fs.readFile(file, "utf8");
    if (path.extname(file) === ".vue") {
      await processVueFile(content, icons, file);
    } else {
      await processJsFile(content, icons, file);
    }
  }

  // Merge in manually specified icons
  try {
    const manualIconsPath = path.join(process.cwd(), "icons.json");
    const manualIconsContent = await fs.readFile(manualIconsPath, "utf8");
    const manualIcons = JSON.parse(manualIconsContent);
    manualIcons.forEach(({ prefix, icon }) => addIcon(prefix, icon, icons, "manual-icons", 0));
  } catch {
    // No manual icons found just continue
  }

  // Remove 'fa-' prefix from icon names if present and check for prefix collisions
  const cleanedIcons = new Set();
  const prefixes = ["fas", "far", "fal", "fat", "fad", "fab", "fa"];

  for (const iconStr of icons) {
    const icon = JSON.parse(iconStr);

    // Check if icon name starts with 'fa-' and remove it
    if (icon.icon.startsWith("fa-")) {
      icon.icon = icon.icon.substring(3); // Remove first 3 chars ('fa-')
    }

    // Check if icon name matches any prefix
    if (prefixes.includes(icon.icon)) {
      console.warn(
        `Warning: Icon name "${icon.icon}" matches a Font Awesome prefix. This may cause conflicts. Found in ${icon.file}:${icon.line}. Removing icon...`,
      );
      continue; // Skip this icon since it matches a prefix
    }

    const cleanedIcon = { prefix: icon.prefix, icon: icon.icon };
    cleanedIcons.add(JSON.stringify(cleanedIcon));
  }
  icons.clear();
  cleanedIcons.forEach((icon) => icons.add(icon));

  const iconsArray = Array.from(icons).map((icon) => JSON.parse(icon));
  const importStatements = iconsArray
    .map(({ prefix, icon }) => {
      const packType =
        prefix === "fa"
          ? "free-solid"
          : prefix === "fab"
            ? "free-brands"
            : prefix === "fas"
              ? "pro-solid"
              : prefix === "far"
                ? "pro-regular"
                : prefix === "fal"
                  ? "pro-light"
                  : prefix === "fat"
                    ? "pro-thin"
                    : prefix === "fad"
                      ? "pro-duotone"
                      : null;

      if (!packType) return null;

      const transformedIconName = transformIconName(icon);
      return `import { fa${transformedIconName} as ${prefix}${transformedIconName} } from "@fortawesome/${packType}-svg-icons";`;
    })
    .filter(Boolean)
    .join("\n");

  const fileContent = `
// This file is auto-generated. Do not edit manually.
import { library } from "@fortawesome/fontawesome-svg-core";
${importStatements}

export function registerIcons() {
  library.add(
    ${iconsArray.map(({ prefix, icon }) => `${prefix}${transformIconName(icon)}`).join(",\n    ")}
  );
}
`;

  const outputPath = path.join(process.cwd(), ".prindustry", "fa-icons.js");
  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await fs.writeFile(outputPath, fileContent);

  const iconsJsonPath = path.join(process.cwd(), ".prindustry", "icons.json");
  let oldIconsArray = [];
  try {
    const oldIconsContent = await fs.readFile(iconsJsonPath, "utf8");
    oldIconsArray = JSON.parse(oldIconsContent);
  } catch {
    // If icons.json doesn't exist, assume no previous icons.
    console.log("No previous icons found");
  }

  // Create a simplified version of the icons array for diff and output
  const simplifiedIcons = iconsArray.map(({ prefix, icon }) => ({ prefix, icon }));
  const locationMap = Object.fromEntries(
    iconsArray.map(({ prefix, icon, file, line }) => [
      JSON.stringify({ prefix, icon }),
      { file, line },
    ]),
  );

  // Create sets for easier diffing by using JSON stringification.
  const newIconsSet = new Set(simplifiedIcons.map((icon) => JSON.stringify(icon)));
  const oldIconsSet = new Set(oldIconsArray.map((icon) => JSON.stringify(icon)));

  const addedIcons = simplifiedIcons.filter((icon) => !oldIconsSet.has(JSON.stringify(icon)));
  const removedIcons = oldIconsArray.filter((icon) => !newIconsSet.has(JSON.stringify(icon)));

  // Log differences with color: green for added, red for removed.
  console.log(`\x1b[32mAdded icons: ${addedIcons.length}\x1b[0m`);
  console.log(`\x1b[31mRemoved icons: ${removedIcons.length}\x1b[0m`);

  // Log locations of added icons for debugging
  if (addedIcons.length > 0) {
    console.log("\nAdded icons locations:");
    addedIcons.forEach((icon) => {
      const location = locationMap[JSON.stringify(icon)];
      if (location) {
        console.log(`  - ${icon.prefix} ${icon.icon}: ${location.file}:${location.line}`);
      }
    });
  }

  // Create an output file with the full location information
  const iconsWithLocationsPath = path.join(
    process.cwd(),
    ".prindustry",
    "icons-with-locations.json",
  );
  await fs.writeFile(iconsWithLocationsPath, JSON.stringify(iconsArray, null, 2));

  // Write simplified version for backwards compatibility
  await fs.mkdir(path.join(process.cwd(), ".prindustry"), { recursive: true });
  await fs.writeFile(iconsJsonPath, JSON.stringify(simplifiedIcons, null, 2));
  console.log(`Found ${iconsArray.length} icons and saved to icons.json`);
  console.log(`Detailed icon locations saved to icons-with-locations.json`);
}

function transformIconName(iconName) {
  // Convert 'arrow-right' to 'ArrowRight'
  return iconName
    .split("-")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join("");
}

async function processVueFile(content, icons, filePath) {
  const templateMatch = content.match(/<template>([\s\S]*)<\/template>/);
  if (!templateMatch) return;

  const template = templateMatch[1];
  const ast = vueParse(template, { comments: true });

  traverseVueAst(ast, icons, filePath);
}

function traverseVueAst(ast, icons, filePath) {
  const walk = (node) => {
    if (node.props && node.props.length) {
      const iconProp = node.props.find((prop) => {
        // Dynamic binding (e.g. :icon or v-bind:icon)
        if (prop.type === 7 && prop.name === "bind" && prop.arg && prop.arg.content === "icon") {
          return true;
        }
        // Static attribute (e.g. icon="...")
        if (prop.type === 6 && prop.name === "icon") {
          return true;
        }
        return false;
      });
      if (iconProp) {
        let code;
        let line = node.loc?.start?.line || 0;
        if (iconProp.type === 7 && iconProp.exp) {
          code = iconProp.exp.content;
          line = iconProp.loc?.start?.line || line;
        } else if (iconProp.type === 6 && iconProp.value) {
          // Wrap the static value in quotes so it's parsed as a string literal.
          code = JSON.stringify(iconProp.value.content);
          line = iconProp.loc?.start?.line || line;
        }
        if (code) {
          processIconExpression(code, icons, filePath, line);
        }
      }
    }
    if (node.children) {
      node.children.forEach(walk);
    }
  };

  ast.children.forEach(walk);
}

async function processJsFile(content, icons, filePath) {
  try {
    const ast = babelParse(content, {
      sourceType: "module",
      plugins: ["jsx", "typescript"],
    });

    traverse(ast, {
      JSXElement(path) {
        const attributes = path.node.openingElement.attributes;
        const iconAttr = attributes.find((attr) => attr.name && attr.name.name === "icon");
        if (iconAttr) {
          let code;
          let line = path.node.loc?.start?.line || 0;
          if (iconAttr.value?.expression) {
            code = generate(iconAttr.value.expression).code;
            line = iconAttr.value.loc?.start?.line || line;
          } else if (iconAttr.value?.value) {
            // Wrap the static value in quotes so it becomes a string literal.
            code = JSON.stringify(iconAttr.value.value);
            line = iconAttr.value.loc?.start?.line || line;
          }
          if (code) {
            processIconExpression(code, icons, filePath, line);
          }
        }
      },
    });
  } catch (error) {
    console.error(`Error processing JS file ${filePath}:`, error.message);
  }
}

function processIconExpression(expression, icons, filePath, line) {
  try {
    const ast = babelParse(`(${expression})`, {
      sourceType: "module",
      plugins: ["jsx"],
    });
    const rootExpression = ast.program.body[0].expression;

    // Create a function to process array expressions that represent icons
    const processArrayExpression = (arrayNode) => {
      if (!arrayNode || arrayNode.type !== "ArrayExpression" || arrayNode.elements.length < 2)
        return;
      const prefixNode = arrayNode.elements[0];
      const iconNode = arrayNode.elements[1];
      const prefixes = extractStrings(prefixNode);
      const theIcons = extractStrings(iconNode);
      for (const prefix of prefixes) {
        for (const iconName of theIcons) {
          addIcon(prefix, iconName, icons, filePath, line);
        }
      }
    };

    // Process conditional expressions by recursively inspecting their branches
    const processConditionalRecursively = (node) => {
      if (!node) return;

      if (node.type === "ArrayExpression") {
        processArrayExpression(node);
      } else if (node.type === "ConditionalExpression") {
        processConditionalRecursively(node.consequent);
        processConditionalRecursively(node.alternate);
      } else if (node.type === "LogicalExpression") {
        processConditionalRecursively(node.left);
        processConditionalRecursively(node.right);
      }
    };

    if (rootExpression.type === "ArrayExpression") {
      processArrayExpression(rootExpression);
    } else if (
      rootExpression.type === "ConditionalExpression" ||
      rootExpression.type === "LogicalExpression"
    ) {
      processConditionalRecursively(rootExpression);
    } else {
      // For a single string, use the default prefix
      const strings = extractStrings(rootExpression);
      const defaultPrefix = "fas"; // <-- adjust this if you want a different default
      for (const iconName of strings) {
        addIcon(defaultPrefix, iconName, icons, filePath, line);
      }
    }
  } catch (error) {
    console.error(`Error processing icon expression in ${filePath}:${line}:`, expression, error);
  }
}

function extractStrings(node) {
  const strings = new Set();

  const processNode = (currentNode) => {
    if (!currentNode) return;

    if (currentNode.type === "StringLiteral") {
      strings.add(currentNode.value);
    } else if (currentNode.type === "ConditionalExpression") {
      processNode(currentNode.consequent);
      processNode(currentNode.alternate);
    } else if (currentNode.type === "LogicalExpression") {
      processNode(currentNode.left);
      processNode(currentNode.right);
    } else if (currentNode.type === "ArrayExpression") {
      currentNode.elements.forEach(processNode);
    }
  };

  processNode(node);
  return Array.from(strings);
}

function addIcon(prefix, iconName, iconsSet, filePath, line) {
  if (prefix && iconName) {
    const key = JSON.stringify({ prefix, icon: iconName, file: filePath, line });
    iconsSet.add(key);
  }
}

main().catch(console.error);
