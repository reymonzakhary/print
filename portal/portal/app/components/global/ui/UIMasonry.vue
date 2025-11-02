<template>
  <div :id="randomId">
    <slot />
  </div>
</template>

<script setup>
import Macy from "macy";
const randomId = ref(Math.random().toString(36).substring(7));

const props = defineProps({
  columns: {
    type: Number,
    default: 4,
  },
  trueOrder: {
    type: Boolean,
    default: false,
  },
  margin: {
    type: Number,
    default: 20,
  },
  waitForImages: {
    type: Boolean,
    default: false,
  },
  useOwnImageLoader: {
    type: Boolean,
    default: false,
  },
  mobileFirst: {
    type: Boolean,
    default: false,
  },
  breakAt: {
    type: Object,
    default: () => ({}),
  },
  cancelLegacy: {
    type: Boolean,
    default: false,
  },
});

const macyInstance = ref(null);
const observer = ref(null);
const recalculateTimeout = ref(null);

onMounted(() => {
  init();
});

watch(
  () => props,
  () => {
    nextTick(() => {
      update();
    });
  },
  { deep: true },
);

onBeforeUpdate(() => {
  update();
});

onBeforeUnmount(() => {
  remove();
});

const init = () => {
  macyInstance.value = Macy({
    container: `#${randomId.value}`,
    columns: props.columns,
    trueOrder: props.trueOrder,
    margin: props.margin,
    waitForImages: props.waitForImages,
    useOwnImageLoader: props.useOwnImageLoader,
    mobileFirst: props.mobileFirst,
    breakAt: props.breakAt,
    cancelLegacy: props.cancelLegacy,
  });

  observer.value = new MutationObserver(handleMutation);
  observer.value.observe(document.getElementById(`${randomId.value}`), {
    childList: true,
    subtree: true,
    attributes: true,
    characterData: true,
  });
};

const remove = () => {
  if (macyInstance.value && observer.value) {
    observer.value.disconnect();
    macyInstance.value.remove();
  }

  // Clear any pending timeouts
  if (recalculateTimeout.value) {
    clearTimeout(recalculateTimeout.value);
  }
};

// Handle mutations with debouncing
const handleMutation = (_mutations) => {
  // Clear previous timeout if it exists
  if (recalculateTimeout.value) {
    clearTimeout(recalculateTimeout.value);
  }

  // Set a new timeout to ensure all DOM updates have been painted
  recalculateTimeout.value = setTimeout(() => {
    // Use requestAnimationFrame to ensure we recalculate after the next paint
    window.requestAnimationFrame(() => {
      // Double RAF for extra safety to ensure browser has painted
      window.requestAnimationFrame(() => {
        if (macyInstance.value) {
          macyInstance.value.recalculate(true);
        }
      });
    });
  }, 50); // Small delay to allow for DOM updates
};

const update = () => {
  // Use the same robust approach for manual updates
  if (recalculateTimeout.value) {
    clearTimeout(recalculateTimeout.value);
  }

  recalculateTimeout.value = setTimeout(() => {
    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        if (macyInstance.value) {
          macyInstance.value.recalculate(true);
        }
      });
    });
  }, 50);
};
</script>
