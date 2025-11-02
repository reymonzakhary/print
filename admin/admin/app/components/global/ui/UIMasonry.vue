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

  observer.value = new MutationObserver(update);
  observer.value.observe(document.getElementById(`${randomId.value}`), {
    childList: true,
    subtree: true,
  });
};

const remove = () => {
  if (macyInstance.value && observer.value) {
    observer.value.disconnect();
    macyInstance.value.remove();
  }
};

const update = () => {
  macyInstance.value.recalculate(true);
};
</script>
