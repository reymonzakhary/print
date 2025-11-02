const fs = require("fs");
const path = require("path");

const categoriesDir = path.join(__dirname, "..", "public", "img", "categories");
const indexFilePath = path.join(__dirname, "..", ".prindustry", "category-images-index.json");
const imageExtensions = [".png", ".jpg", ".jpeg", ".gif", ".webp", ".svg"];

try {
  console.log(`Indexing images from '${categoriesDir}'...`);

  const allFiles = fs.readdirSync(categoriesDir);

  const imageFiles = allFiles.filter((file) => {
    const ext = path.extname(file).toLowerCase();
    // Check if it's an image
    return imageExtensions.includes(ext);
  });

  // Sort the image file names alphabetically
  imageFiles.sort();

  const jsonData = JSON.stringify(imageFiles, null, 2); // Pretty print JSON

  fs.writeFileSync(indexFilePath, jsonData, "utf8");
  console.log(
    `Successfully created/updated '${indexFilePath}' with ${imageFiles.length} image entries.`,
  );
} catch (error) {
  console.error("Error processing category images:", error);
  process.exit(1); // Exit with error code
}
