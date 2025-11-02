import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import moment from "moment";

/**
 * Utility functions for the application
 * @returns {Object} The utility functions
 */
export default function useUtilities() {
  /**
   * Merges class names
   * @param {String} input - The class names to merge
   * @returns {String} The merged class names
   */
  function cn(...inputs) {
    return twMerge(clsx(inputs));
  }

  /**
   * Gets the mime type from an array buffer
   * @param {ArrayBuffer} arrayBuffer - The array buffer to get the mime type from
   * @returns {String} The mime type
   */
  function getMimeTypeFromArrayBuffer(arrayBuffer) {
    const uint8arr = new Uint8Array(arrayBuffer);

    const len = 4;
    if (uint8arr.length >= len) {
      let signatureArr = new Array(len);
      for (let i = 0; i < len; i++) signatureArr[i] = new Uint8Array(arrayBuffer)[i].toString(16);
      const signature = signatureArr.join("").toUpperCase();
      switch (signature) {
        case "89504E47":
          return "image/png";
        case "47494638":
          return "image/gif";
        case "25504446":
          return "application/pdf";
        case "FFD8FFDB":
        case "FFD8FFE0":
          return "image/jpeg";
        case "504B0304":
          return "application/zip";
        case "52494646":
          return "image/webp";
        default:
          return null;
      }
    }
    return null;
  }

  /**
   * Internal function to build a FormData object from an object
   * DO NOT USE THIS FUNCTION DIRECTLY
   * @param {FormData} formData - The FormData object to build
   * @param {Object} data - The object to build the FormData from
   * @param {String} parentKey - The parent key for nested objects
   * @returns {FormData} The built FormData object
   */
  function _buildFormData(formData, data, parentKey) {
    if (
      typeof data === "object" &&
      Object.keys(data).length > 0 &&
      !(data instanceof Date) &&
      !(data instanceof File) &&
      !(data instanceof Blob)
    ) {
      Object.keys(data).forEach((key) => {
        _buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
      });
    } else if (typeof data === "object" && Object.keys(data).length === 0){
      formData.append(parentKey, '');
    } else {
      const value = data == null ? "" : data;
      formData.append(parentKey, value);
    }
  }

  /**
   * Builds a FormData object from an object
   * @param {Object} data - The object to build the FormData from
   * @returns {FormData} The built FormData object
   */
  function objectToFormData(data) {
    const formData = new FormData();
    _buildFormData(formData, data);
    return formData;
  }

  /**
   * Formats a date string to a readable format
   * @param {String} datestr - The date string to format
   * @returns {String} The formatted date string
   */
  function formatDateString(datestr) {
    datestr = datestr ? datestr : moment().format("YYYY-MM-DD LTS");
    return moment(datestr, "YYYY-MM-DD LTS").calendar(null, {
      sameDay: "HH:mm",
      lastDay: "[Yesterday]",
      lastWeek: "DD-MM-YYYY",
      sameElse: "DD-MM-YYYY",
    });
  }

  /**
   * Truncates a string to a given length
   * @param {String} str - The string to truncate
   * @param {Number} len - The length to truncate the string to
   * @returns {String} The truncated string
   */
  const truncate = (str, len) => (str.length > len ? str.substring(0, len) : str);

  /**
   * Capitalizes the first letter of a string
   * @param {String} string - The string to capitalize
   * @returns {String} The capitalized string
   */
  const capitalizeFirstLetter = (string) => string.charAt(0).toUpperCase() + string.slice(1);

  /**
   * Converts a string to a slug
   * @param {String} text - The string to convert to a slug
   * @param {Boolean} removePunctation - Whether to remove punctuation from the slug
   * @returns {String} The slug
   */
  const convertToSlug = (text, removePunctation = true) => {
    let slug = text.toLowerCase().replace(/ /g, "-");
    if (removePunctation) {
      slug = slug.replace(/[^\w-]+/g, "-");
    }
    return slug;
  };

  /**
   * Converts an array buffer to a base64 string
   * @param {ArrayBuffer} buffer - The array buffer to convert to a base64 string
   * @returns {String} The base64 string
   */
  function arrayBufferToBase64(buffer) {
    var binary = "";
    var bytes = new Uint8Array(buffer);
    var len = bytes.byteLength;
    for (var i = 0; i < len; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
  }

  return {
    getMimeTypeFromArrayBuffer,
    objectToFormData,
    formatDateString,
    truncate,
    capitalizeFirstLetter,
    convertToSlug,
    arrayBufferToBase64,
    cn,
  };
}
