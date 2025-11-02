<template>
  <div class="grid justify-center gap-1 sm:flex sm:justify-between sm:items-center">
    <nav class="flex items-center -space-x-px" aria-label="Pagination">
      <!-- Previous page button -->
      <button
        :disabled="!hasPreviousPage"
        type="button"
        class="bg-white min-h-8 min-w-8 py-1 px-1.5 inline-flex justify-center items-center gap-x-1.5 text-sm first:rounded-s-lg last:rounded-e-lg border border-gray-200 text-gray-800 enabled:hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10 dark:bg-neutral-600"
        aria-label="Previous"
        @click="handleGoToPage(pageIndex - 1)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="shrink-0 size-3.5" />
        <span class="sr-only">Previous</span>
      </button>

      <!-- First page + start ellipsis -->
      <template v-if="showStartEllipsis">
        <button
          type="button"
          class="flex items-center justify-center px-2 py-1 text-sm text-gray-800 bg-white border border-gray-200 min-h-8 min-w-8 hover:bg-gray-100"
          @click="handleGoToPage(1)"
        >
          1
        </button>
        <div class="inline-block border border-gray-200 hs-tooltip dark:border-neutral-700">
          <button
            type="button"
            class="flex items-center bg-white justify-center text-sm text-gray-400 min-h-[30px] min-w-8"
          >
            <font-awesome-icon :icon="['fal', 'ellipsis']" class="shrink-0 size-3.5" />
          </button>
        </div>
      </template>

      <!-- Visible page numbers -->
      <template v-for="pageNum in visiblePages" :key="pageNum">
        <button
          type="button"
          class="flex items-center justify-center px-2 py-1 text-sm text-gray-800 bg-white border hover:bg-gray-200 min-h-8 min-w-8 first:rounded-s-lg last:rounded-e-lg disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-600 dark:border-neutral-700 dark:text-white dark:focus:bg-neutral-500"
          :disabled="pageIndex === pageNum"
          :class="{
            'border-gray-200 !bg-theme-400 text-white hover:bg-theme-100 !opacity-100':
              pageIndex === pageNum,
          }"
          aria-current="page"
          @click="handleGoToPage(pageNum)"
        >
          {{ pageNum }}
        </button>
      </template>

      <!-- Ellipsis and last page number -->
      <template v-if="showEndEllipsis">
        <div class="inline-block border border-gray-200 hs-tooltip dark:border-neutral-700">
          <button
            type="button"
            class="flex items-center bg-white justify-center text-sm text-gray-400 min-h-[30px] min-w-8 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-500"
          >
            <font-awesome-icon :icon="['fal', 'ellipsis']" class="shrink-0 size-3.5" />
          </button>
        </div>
        <button
          type="button"
          class="flex items-center justify-center px-2 py-1 text-sm text-gray-800 bg-white border border-gray-200 min-h-8 min-w-8 hover:bg-gray-100 first:rounded-s-lg last:rounded-e-lg focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
          @click="handleGoToPage(pageCount)"
        >
          {{ pageCount }}
        </button>
      </template>

      <!-- Next page button -->
      <button
        :disabled="!hasNextPage"
        type="button"
        class="bg-white min-h-8 min-w-8 py-1 px-1.5 inline-flex justify-center items-center gap-x-1.5 text-sm first:rounded-s-lg last:rounded-e-lg border border-gray-200 text-gray-800 enabled:hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-white dark:bg-neutral-600 dark:hover:bg-white/10 dark:focus:bg-white/10"
        aria-label="Next"
        @click="handleGoToPage(pageIndex + 1)"
      >
        <span class="sr-only">Next</span>
        <font-awesome-icon :icon="['fal', 'chevron-right']" class="shrink-0 size-3.5" />
      </button>
    </nav>

    <!-- Go to page input -->
    <div class="flex items-center justify-center sm:justify-start gap-x-2">
      <span class="text-sm text-gray-800 whitespace-nowrap dark:text-white"> Go to </span>
      <UIInputText
        type="number"
        name="page"
        class="w-16"
        placeholder=""
        :limit="pageCount"
        @keyup.enter="handleGoToPage($event.target.value)"
      />
      <span class="text-sm text-gray-800 whitespace-nowrap dark:text-white"> page </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  manualPagination: {
    type: Boolean,
    default: false,
  },
  totalPages: {
    type: Number,
    default: 10,
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  visiblePagesCount: {
    type: Number,
    default: 3,
  },
});

const emit = defineEmits(["update:page"]);

const pageIndex = ref(props.currentPage);
const pageCount = computed(() => props.totalPages);

watch(
  () => props.currentPage,
  (value) => {
    pageIndex.value = value;
  },
);

const hasPreviousPage = computed(() => pageIndex.value > 1);
const hasNextPage = computed(() => pageIndex.value < pageCount.value);

const visiblePages = computed(() => {
  const pages = [];

  // If total pages is less than or equal to visiblePagesCount, show all pages
  if (pageCount.value <= props.visiblePagesCount) {
    for (let i = 1; i <= pageCount.value; i++) {
      pages.push(i);
    }
    return pages;
  }

  // Special case: if we have just one more page than visiblePagesCount,
  // show all pages without ellipsis
  if (pageCount.value === props.visiblePagesCount + 1) {
    for (let i = 1; i <= pageCount.value; i++) {
      pages.push(i);
    }
    return pages;
  }

  // For pages exceeding visiblePagesCount, show a window based on visiblePagesCount
  const halfVisible = Math.floor(props.visiblePagesCount / 2);
  let start = Math.max(1, pageIndex.value - halfVisible);

  // Adjust start if we're near the beginning
  if (start <= halfVisible) {
    start = 1;
  }

  // Adjust start if we're near the end
  if (pageIndex.value > pageCount.value - halfVisible) {
    start = Math.max(1, pageCount.value - props.visiblePagesCount + 1);
  }

  // Generate the array of visible page numbers
  const end = Math.min(start + props.visiblePagesCount - 1, pageCount.value);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

const showStartEllipsis = computed(() => {
  // Show start ellipsis when we're not at the beginning and have enough pages
  return pageCount.value > props.visiblePagesCount + 1 && visiblePages.value[0] > 1;
});

const showEndEllipsis = computed(() => {
  const lastVisiblePage = visiblePages.value[visiblePages.value.length - 1];
  return lastVisiblePage < pageCount.value - 1;
});

const handleGoToPage = (value) => {
  const newPage = typeof value === "string" ? parseInt(value, 10) : value;

  if (newPage >= 1 && newPage <= pageCount.value) {
    if (!props.manualPagination) {
      pageIndex.value = newPage;
      emit("update:page", newPage);
    } else {
      emit("update:page", newPage);
    }
  }
};
</script>
