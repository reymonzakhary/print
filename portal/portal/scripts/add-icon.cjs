const fs = require("fs");
const path = require("path");

// Get command line arguments
const args = process.argv.slice(2);
let prefix = "";
let icon = "";

// Parse arguments
for (let i = 0; i < args.length; i++) {
  if (args[i] === "-p" && i + 1 < args.length) {
    prefix = args[i + 1];
  } else if (args[i] === "-i" && i + 1 < args.length) {
    icon = args[i + 1];
  }
}

// Validate arguments
if (!prefix || !icon) {
  console.error("Error: Both prefix (-p) and icon (-i) are required");
  process.exit(1);
}

// Read existing icons
const iconsPath = path.join(__dirname, "..", "icons.json");
let icons = [];
try {
  const iconsContent = fs.readFileSync(iconsPath, "utf8");
  icons = JSON.parse(iconsContent);
} catch (error) {
  console.error("Error reading icons.json:", error);
  process.exit(1);
}

// Check if icon already exists
const iconExists = icons.some((item) => item.prefix === prefix && item.icon === icon);
if (iconExists) {
  console.log(`Icon "${icon}" with prefix "${prefix}" already exists in icons.json`);
  process.exit(0);
}

// Add new icon
icons.push({ prefix, icon });

// Write back to file
try {
  fs.writeFileSync(iconsPath, JSON.stringify(icons, null, 2));
  console.log(`Successfully added icon "${icon}" with prefix "${prefix}" to icons.json`);
} catch (error) {
  console.error("Error writing to icons.json:", error);
  process.exit(1);
}
