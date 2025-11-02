export function useDebounce(fn, ms = 0) {
  const lastTimer = ref(undefined);
  const cancelled = ref(false);
  const lastPromise = ref(undefined);
  const isPending = ref(false);
  const lastArgs = ref(undefined);

  const debounced = (...args) => {
    cancelled.value = false;
    lastArgs.value = args;
    if (lastTimer.value) {
      clearTimeout(lastTimer.value);
    }

    isPending.value = true;
    return new Promise((resolve, reject) => {
      const curTimer = setTimeout(async () => {
        try {
          let res;
          if (!cancelled.value && curTimer === lastTimer.value) {
            res = await fn(...args);
          }
          if (!cancelled.value && curTimer === lastTimer.value) {
            resolve(res);
            isPending.value = false;
          }
        } catch (e) {
          reject(e);
          isPending.value = false;
        }
      }, ms);
      lastTimer.value = curTimer;
    });
  };

  const cancel = () => {
    cancelled.value = true;
    isPending.value = false;
  };

  const execute = async () => {
    // If no lastTimer is set, return
    if (!lastTimer.value || !lastArgs.value) return;

    // Cancel any pending debounced calls
    if (lastTimer.value) {
      clearTimeout(lastTimer.value);
      lastTimer.value = undefined;
    }

    // Execute immediately
    try {
      isPending.value = true;
      lastPromise.value = fn(...lastArgs.value);
      const result = await lastPromise.value;
      isPending.value = false;
      return result;
    } catch (e) {
      isPending.value = false;
      throw e;
    }
  };

  return {
    debounced,
    cancel,
    execute,
    isPending: readonly(isPending),
  };
}
