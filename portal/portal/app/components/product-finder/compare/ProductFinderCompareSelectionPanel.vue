<template>
  <Teleport to="body">
    <div
      v-if="!isExpanded"
      class="fixed bottom-8 right-6 z-[50] cursor-pointer select-none"
      @click="expandPanel"
    >
      <div
        class="relative flex size-16 items-center justify-center rounded-lg bg-white shadow-lg transition-all duration-300 ease-in-out hover:shadow-xl dark:bg-gray-800"
      >
        <font-awesome-icon :icon="['fas', 'tag']" class="size-6 text-gray-700 dark:text-gray-200" />
        <div
          v-if="amountOfSelectedOptions"
          class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-theme-500 text-xs font-medium text-white"
        >
          {{ amountOfSelectedOptions }}
        </div>
      </div>
    </div>

    <div
      v-if="isExpanded"
      ref="target"
      class="fixed z-[50] select-none"
      style="touch-action: none"
      :style="draggable.style"
    >
      <div
        class="w-80 rounded-lg bg-white p-4 shadow-lg transition-all duration-300 ease-in-out hover:shadow-xl dark:bg-gray-800"
      >
        <div
          class="mb-3 flex cursor-grab items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700"
        >
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $t("Your {category} selection", { category: category.name.toLowerCase() }) }}
          </h3>
          <button
            class="rounded-full p-1 px-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            @click="collapsePanel"
          >
            <font-awesome-icon :icon="['fal', 'times']" class="size-4" />
          </button>
        </div>

        <!-- Content -->
        <div class="max-h-[60vh] space-y-2 overflow-y-auto">
          <template v-if="Object.keys(selectedOptions).length">
            <div
              v-for="(optionValue, boopId) in selectedOptions"
              :key="boopId"
              class="flex items-center justify-between rounded-md bg-gray-50 p-2 dark:bg-gray-700/50"
            >
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                  {{ getBoopName(boopId) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ getOptionName(boopId, optionValue) }}
                </p>
              </div>
            </div>
          </template>
          <div
            v-else
            class="flex items-center justify-center py-4 text-sm text-gray-500 dark:text-gray-400"
          >
            {{ $t("No options selected") }}
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  selectedOptions: {
    type: Object,
    required: true,
  },
  category: {
    type: Object,
    required: true,
  },
});

defineEmits(["close", "remove-option"]);

const amountOfSelectedOptions = computed(() => Object.keys(props.selectedOptions).length);

// Draggable functionality
const target = ref(null);
const isExpanded = ref(false);

const windowSize = useWindowSize();
const draggable = ref({ x: 0, y: 0, style: {} });

// Initialize position
const initializePosition = () => {
  if (target.value) {
    const width = 320; // 80 * 4 = 320px for expanded
    const height = target.value.offsetHeight;

    draggable.value = useDraggable(target, {
      initialValue: {
        x: windowSize.width.value - width - 32,
        y: windowSize.height.value - height - 42,
      },
      preventDefault: true,
    });
  }
};

watch(target, initializePosition);

// Handle panel expansion/collapse
const expandPanel = () => {
  isExpanded.value = true;
  // nextTick(() => {
  //   initializePosition();
  // });
};

const collapsePanel = () => {
  isExpanded.value = false;
};

// Update position when window is resized
const previousWindowSize = ref({ width: windowSize.width.value, height: windowSize.height.value });

watch([() => windowSize.width.value, () => windowSize.height.value], () => {
  if (target.value && isExpanded.value) {
    const currentRight = previousWindowSize.value.width - draggable.value.x - 320;
    const currentBottom =
      previousWindowSize.value.height - draggable.value.y - target.value.offsetHeight;

    draggable.value.x = windowSize.width.value - currentRight - 320;
    draggable.value.y = windowSize.height.value - currentBottom - target.value.offsetHeight;

    // Ensure panel stays within viewport
    const minX = 0;
    const minY = 0;
    const maxX = windowSize.width.value - 320 - 32;
    const maxY = windowSize.height.value - target.value.offsetHeight - 42;

    draggable.value.x = Math.max(minX, Math.min(maxX, draggable.value.x));
    draggable.value.y = Math.max(minY, Math.min(maxY, draggable.value.y));

    previousWindowSize.value = { width: windowSize.width.value, height: windowSize.height.value };
  }
});

// Helper functions to get names
const getBoopName = (boopId) => {
  const boop = props.category.dividers
    .flatMap((divider) => divider.boops)
    .find((boop) => boop.id === boopId);
  return boop?.name || boopId;
};

const getOptionName = (boopId, optionId) => {
  const boop = props.category.dividers
    .flatMap((divider) => divider.boops)
    .find((boop) => boop.id === boopId);
  const option = boop?.options?.find((opt) => opt.id === optionId);
  return option?.name || optionId;
};
</script>
