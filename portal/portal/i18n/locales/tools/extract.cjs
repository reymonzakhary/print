const fs = require("fs");
const path = require("path");

if (process.argv.length < 3) {
  console.error("Please provide a JSON filename as an argument.");
  process.exit(1);
}

// Paths to the input and output files
const filename = process.argv[2];
const inputFilePath = path.resolve(__dirname, filename);
const baseFilename = path.basename(filename, ".json");

const untranslatedOutputFilePath = path.join(__dirname, `${baseFilename}-untranslated.json`);
const translatedOutputFilePath = path.join(__dirname, `${baseFilename}-translated.json`);

// Read the nl.json file
fs.readFile(inputFilePath, "utf8", (err, data) => {
  if (err) {
    console.error("Error reading nl.json:", err);
    return;
  }

  // Parse the JSON data
  const jsonData = JSON.parse(data);
  const untranslated = {};
  const translated = {};

  // Find key-value pairs where the key and value are equal or not equal
  for (const key in jsonData) {
    if (jsonData[key] === key) {
      untranslated[key] = jsonData[key];
    } else {
      translated[key] = jsonData[key];
    }
  }

  // Write the untranslated key-value pairs to nl-untranslated.json
  fs.writeFile(untranslatedOutputFilePath, JSON.stringify(untranslated, null, 2), "utf8", (err) => {
    if (err) {
      console.error("Error writing nl-untranslated.json:", err);
      return;
    }
    console.log("Untranslated key-value pairs have been exported to nl-untranslated.json");
  });

  // Write the translated key-value pairs to nl-translated.json
  fs.writeFile(translatedOutputFilePath, JSON.stringify(translated, null, 2), "utf8", (err) => {
    if (err) {
      console.error("Error writing nl-translated.json:", err);
      return;
    }
    console.log("Translated key-value pairs have been exported to nl-translated.json");
  });
});
