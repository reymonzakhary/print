const fs = require("fs");
const path = require("path");

if (process.argv.length < 3) {
  console.error("Please provide a JSON filename as an argument.");
  process.exit(1);
}

const filename = process.argv[2];
const filePath = path.resolve(__dirname, filename);

fs.readFile(filePath, "utf8", (err, data) => {
  if (err) {
    console.error(`Error reading file: ${err.message}`);
    process.exit(1);
  }

  let jsonData;
  try {
    jsonData = JSON.parse(data);
  } catch (parseErr) {
    console.error(`Error parsing JSON: ${parseErr.message}`);
    process.exit(1);
  }

  const capitalizedData = {};
  for (const [key, value] of Object.entries(jsonData)) {
    if (typeof value === "string") {
      capitalizedData[key] = value.charAt(0).toUpperCase() + value.slice(1);
    } else {
      capitalizedData[key] = value;
    }
  }

  const outputFilename = `capitalized-${filename}`;
  const outputPath = path.resolve(__dirname, outputFilename);

  fs.writeFile(outputPath, JSON.stringify(capitalizedData, null, 2), "utf8", (writeErr) => {
    if (writeErr) {
      console.error(`Error writing file: ${writeErr.message}`);
      process.exit(1);
    }
    console.log(`File saved as ${outputFilename}`);
  });
});
