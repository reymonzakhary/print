export const useAPI = (endpoint, _options = {}) => {
  if (endpoint) {
    const { $api } = useNuxtApp();
    return useFetch(endpoint, {
      $fetch: $api,
      ..._options,
    });
  }

  const handleRequest = async (method, url, options = {}, data) => {
    const { $api } = useNuxtApp();
    return await $api(url, {
      method,
      body: options.isFormData ? data : JSON.stringify(data),
      ...options,
    });
  };

  /**
   * File upload support as ofetch does not support it
   */
  const config = useRuntimeConfig();
  const token = useCookie("prindustry:auth");
  const defaultHeaders = {
    Accept: "application/json, text/plain, */*",
  };
  function handleFileUpload(url, file, onProgress = () => {}) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      const fullUrl = `${config.public.baseURL}${url}`;

      xhr.open("POST", fullUrl, true);

      // Set headers
      if (token.value) {
        xhr.setRequestHeader("Authorization", `Bearer ${token.value}`);
      }
      Object.entries(defaultHeaders).forEach(([key, value]) => {
        xhr.setRequestHeader(key, value);
      });

      xhr.onload = function () {
        if (this.status >= 200 && this.status < 300) {
          resolve(JSON.parse(xhr.response));
        } else {
          if (this.status === 401) {
            token.value = null;
            if (import.meta.client) {
              window.location.href = `${config.app.baseURL}/auth/login`;
            }
            reject(new Error("Authentication failed. Redirecting to login."));
          } else {
            reject(new Error(`HTTP error! status: ${xhr.status}`));
          }
        }
      };

      xhr.onerror = function () {
        reject(new Error("Network error occurred"));
      };

      xhr.upload.onprogress = function (progressEvent) {
        if (progressEvent.lengthComputable && onProgress) {
          const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          onProgress(progress);
        }
      };

      xhr.send(file);
    });
  }

  return {
    get: (url, options) => handleRequest("GET", url, options),
    post: (url, data, options) => handleRequest("POST", url, options, data),
    put: (url, data, options) => handleRequest("PUT", url, options, data),
    patch: (url, data, options) => handleRequest("PATCH", url, options, data),
    delete: (url, options, data) => handleRequest("DELETE", url, options, data),
    uploadFile: (url, file, onProgress) => handleFileUpload(url, file, onProgress),
  };
};
