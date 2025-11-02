// Method to Create Form Data
const createFormData = (data) => {
  const formData = new FormData();

  // Helper function to append data to FormData
  const appendData = (value, parentKey = "") => {
    if (value instanceof File) {
      // Handle single file
      if (value) {
        formData.append(parentKey, value);
      }
    } else if (Array.isArray(value)) {
      // Handle arrays (e.g., files, strings, numbers)
      value.forEach((item, index) => {
        const key = `${parentKey}[${index}]`;

        appendData(item, key);
      });
    } else if (typeof value === "object" && value !== null) {
      // Handle nested objects
      Object.keys(value).forEach((key) => {
        const fullKey = parentKey ? `${parentKey}[${key}]` : key;
        appendData(value[key], fullKey);
      });
    } else {
      // Handle primitive values
      if (typeof value == "boolean") {
        formData.append(parentKey, value ? 1 : 0); // Convert `null` or `undefined` to an empty string
      } else {
        formData.append(parentKey, value != null ? value : ""); // Convert `null` or `undefined` to an empty string
      }
    }
  };

  appendData(data);
  return formData;
};

export { createFormData };
