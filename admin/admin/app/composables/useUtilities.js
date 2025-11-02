export default function useUtilities() {
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
    arrayBufferToBase64,
  };
}
