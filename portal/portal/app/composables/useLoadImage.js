export function useLoadImage(disk = "assets") {
  const api = useAPI();
  const { handleError } = useMessageHandler();
  const { getMimeTypeFromArrayBuffer } = useUtilities();

  // Cache for raw image data (ArrayBuffer)
  // Key: resolved image path (string), Value: Promise<ArrayBuffer>
  const rawDataCache = new Map();

  /**
   * Creates a reactive image preview from a path or ref
   *
   * @param {string|Ref<string>} pathSource - Path to the image or ref containing the path
   * @param {string} fallbackUrl - Optional fallback image URL to use on errors
   * @returns {Ref<string|null>} - Reactive ref containing the blob URL
   */
  function getPreview(pathSource, fallbackUrl = null) {
    // Create a reactive ref to hold the blob URL
    const imageBlob = ref(null);
    // Track if we're loading the image
    const loading = ref(false);
    // Store current cleanup function
    let currentCleanup = null;

    // Determine if pathSource is already a ref or a plain string
    const imagePath = isRef(pathSource) ? pathSource : ref(pathSource);

    // Watch for changes to the path
    watch(
      imagePath,
      async (newPathSourceValue, oldPathSourceValue) => {
        // Resolve actual path string from potential object or string
        let newPath =
          typeof newPathSourceValue === "object" && newPathSourceValue !== null
            ? newPathSourceValue.path
            : newPathSourceValue;
        let oldPath =
          typeof oldPathSourceValue === "object" && oldPathSourceValue !== null
            ? oldPathSourceValue.path
            : oldPathSourceValue;

        // Skip if path is effectively empty or (if already loaded) unchanged.
        // This basic check can prevent some unnecessary processing.
        // The more robust cache check later handles the main optimization.
        if (newPath === oldPath && imageBlob.value && imageBlob.value !== fallbackUrl) return;

        loading.value = true;

        // Clean up previous blob URL if it exists
        if (currentCleanup) {
          currentCleanup();
          currentCleanup = null;
        }
        imageBlob.value = null;

        if (!newPath) {
          imageBlob.value = fallbackUrl;
          loading.value = false;
          return;
        }

        let dataPromise;

        if (rawDataCache.has(newPath)) {
          dataPromise = rawDataCache.get(newPath);
        } else {
          const params = new URLSearchParams({ disk, path: newPath });
          dataPromise = api
            .get(`/media-manager/file-manager/preview?${params}`, {
              responseType: "arrayBuffer",
            })
            .catch((error) => {
              // Important: If the fetch fails, remove from cache so retry is possible
              rawDataCache.delete(newPath);
              throw error; // re-throw to be caught by the main try/catch block
            });
          rawDataCache.set(newPath, dataPromise);
        }

        try {
          const responseData = await dataPromise; // responseData is ArrayBuffer

          const mimeType = getMimeTypeFromArrayBuffer(responseData);
          const blob = new Blob([responseData], { type: mimeType });
          const url = URL.createObjectURL(blob);

          // Store the cleanup function
          currentCleanup = () => URL.revokeObjectURL(url);

          // Set the new blob URL
          imageBlob.value = url;
        } catch (error) {
          handleError(error);
          imageBlob.value = fallbackUrl;
        } finally {
          loading.value = false;
        }
      },
      { immediate: true },
    );

    // Clean up when component is unmounted
    onScopeDispose(() => currentCleanup && currentCleanup());

    return {
      blob: readonly(imageBlob),
      loading: readonly(loading),
      // Method to manually reload the image
      reload: () => {
        const currentPathValue = imagePath.value; // imagePath is the ref local to this getPreview call
        let resolvedCurrentPath =
          typeof currentPathValue === "object" && currentPathValue !== null
            ? currentPathValue.path
            : currentPathValue;

        if (resolvedCurrentPath) {
          rawDataCache.delete(resolvedCurrentPath); // Clear cache for this specific path
        }

        // Trigger the watcher by creating a "change"
        // Store the original path to set it back
        const originalPath = imagePath.value;
        imagePath.value = null; // Or any unique value that's different
        nextTick(() => {
          imagePath.value = originalPath;
        });
      },
    };
  }

  return {
    getPreview,
  };
}
