<template>
  <div class="group relative">
    <button
      v-if="showLeftScrollButton"
      class="absolute left-0 top-1/2 z-10 -translate-y-1/2 transform rounded-full bg-white/80 p-2 text-gray-600 opacity-0 shadow-md transition hover:bg-white hover:text-gray-900 group-hover:opacity-100 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100"
      @click="scrollMenu('left')"
    >
      <font-awesome-icon :icon="['fas', 'chevron-left']" class="size-4" />
    </button>

    <div
      ref="menuRef"
      class="scrollbar-hide flex space-x-4 overflow-x-auto p-1"
      style="scroll-behavior: smooth"
    >
      <TransitionGroup name="list">
        <div
          v-for="producer in sortedProducers"
          :key="producer.id"
          v-tooltip.bottom-start="producer.name"
          :class="[
            'flex flex-shrink-0 flex-col items-center justify-start text-center',
            disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
          ]"
          @mouseenter="!disabled && (hoveringProducer = producer.id)"
          @mouseleave="hoveringProducer = null"
          @click="!disabled && emit('toggle-producer', producer)"
        >
          <div
            :class="
              cn(
                'relative mb-2 size-14 rounded-full bg-gray-50 outline outline-1 outline-gray-300 transition-all dark:bg-gray-700 dark:outline-gray-600',
                !unSelectedProducers.includes(producer.id) &&
                  'outline-theme-300 dark:outline-theme-600',
                hoveringProducer === producer.id && 'bg-gray-100 dark:bg-gray-800',
                !unSelectedProducers.includes(producer.id) &&
                  hoveringProducer === producer.id &&
                  'outline-theme-500 dark:outline-theme-800',
              )
            "
          >
            <div
              v-if="!unSelectedProducers.includes(producer.id)"
              class="absolute -bottom-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full bg-theme-100 text-theme-600 dark:bg-theme-700 dark:text-theme-100 2xl:size-5"
            >
              <font-awesome-icon :icon="['fas', 'check']" class="size-2 2xl:size-3" />
            </div>
            <div class="flex size-full items-center justify-center overflow-hidden rounded-full">
              <img
                v-if="producer.logo"
                :src="producer.logo"
                :alt="producer.name"
                class="h-full w-full object-contain p-1"
              />
              <font-awesome-icon
                v-else
                :icon="['fas', 'building']"
                class="text-3xl text-gray-400 dark:text-gray-500"
              />
            </div>
          </div>
          <span
            class="text-xs text-gray-700 dark:text-gray-300"
            :class="{
              'font-medium text-theme-700 dark:text-gray-100': !unSelectedProducers.includes(
                producer.id,
              ),
            }"
          >
            {{ producer.variants }} {{ $t("variants") }}
          </span>
        </div>
      </TransitionGroup>
    </div>

    <button
      v-if="showRightScrollButton"
      class="absolute right-0 top-1/2 z-10 -translate-y-1/2 transform rounded-full bg-white/80 p-2 text-gray-600 opacity-0 shadow-md transition hover:bg-white hover:text-gray-900 group-hover:opacity-100 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100"
      @click="scrollMenu('right')"
    >
      <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-4 w-4" />
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  producers: {
    type: Array,
    required: true,
  },
  unSelectedProducers: {
    type: Array,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-producer"]);

const { cn } = useUtilities();
const { menuRef, showLeftScrollButton, showRightScrollButton, scrollMenu } = useScrollableMenu({
  scrollThreshold: 10,
});

const hoveringProducer = ref(null);

const sortedProducers = computed(() => {
  const unselected = props.unSelectedProducers;
  return [...props.producers].sort((a, b) => {
    // Sort by selection status first (unselected producers at the end)
    if (!unselected.includes(a.id) && unselected.includes(b.id)) return -1;
    if (unselected.includes(a.id) && !unselected.includes(b.id)) return 1;
    // Then sort by number of variants
    return b.variants - a.variants;
  });
});
</script>
