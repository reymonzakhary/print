<template>
  <div class="grid justify-center gap-1 sm:flex sm:items-center sm:justify-between">
    <nav
      class="flex items-center -space-x-px"
      :class="{ 'mx-auto': noPageJump }"
      aria-label="Pagination"
    >
      <!-- Previous page button -->
      <button
        :disabled="!hasPreviousPage"
        type="button"
        class="inline-flex min-h-8 min-w-8 items-center justify-center gap-x-1.5 border border-gray-200 bg-white px-1.5 py-1 text-sm text-gray-800 first:rounded-s-lg last:rounded-e-lg enabled:hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-50 dark:border-neutral-700 dark:bg-neutral-600 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
        aria-label="Previous"
        @click="handleGoToPage(pageIndex - 1)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="size-3.5 shrink-0" />
        <span class="sr-only">{{ $t("Previous") }}</span>
      </button>

      <!-- First page + start ellipsis -->
      <template v-if="showStartEllipsis">
        <button
          type="button"
          class="flex min-h-8 min-w-8 items-center justify-center border border-gray-200 bg-white px-2 py-1 text-sm text-gray-800 hover:bg-gray-100"
          @click="handleGoToPage(1)"
        >
          1
        </button>
        <div class="hs-tooltip inline-block border border-gray-200 dark:border-neutral-700">
          <button
            type="button"
            class="flex min-h-[30px] min-w-8 items-center justify-center bg-white text-sm text-gray-400"
          >
            <font-awesome-icon :icon="['fal', 'ellipsis']" class="size-3.5 shrink-0" />
          </button>
        </div>
      </template>

      <!-- Visible page numbers -->
      <template v-for="pageNum in visiblePages" :key="pageNum">
        <button
          type="button"
          class="flex min-h-8 min-w-8 items-center justify-center border bg-white px-2 py-1 text-sm text-gray-800 first:rounded-s-lg last:rounded-e-lg hover:bg-gray-200 disabled:pointer-events-none disabled:opacity-50 dark:border-neutral-700 dark:bg-neutral-600 dark:text-white dark:focus:bg-neutral-500"
          :disabled="pageIndex === pageNum"
          :class="{
            'border-gray-200 !bg-theme-400 text-white !opacity-100 hover:bg-theme-100':
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
        <div class="hs-tooltip inline-block border border-gray-200 dark:border-neutral-700">
          <button
            type="button"
            class="flex min-h-[30px] min-w-8 items-center justify-center bg-white text-sm text-gray-400 focus:outline-none disabled:pointer-events-none disabled:opacity-50 dark:text-neutral-500"
          >
            <font-awesome-icon :icon="['fal', 'ellipsis']" class="size-3.5 shrink-0" />
          </button>
        </div>
        <button
          type="button"
          class="flex min-h-8 min-w-8 items-center justify-center border border-gray-200 bg-white px-2 py-1 text-sm text-gray-800 first:rounded-s-lg last:rounded-e-lg hover:bg-gray-100 focus:bg-gray-100 focus:outline-none disabled:pointer-events-none disabled:opacity-50 dark:border-neutral-700 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
          @click="handleGoToPage(pageCount)"
        >
          {{ pageCount }}
        </button>
      </template>

      <!-- Next page button -->
      <button
        :disabled="!hasNextPage"
        type="button"
        class="inline-flex min-h-8 min-w-8 items-center justify-center gap-x-1.5 border border-gray-200 bg-white px-1.5 py-1 text-sm text-gray-800 first:rounded-s-lg last:rounded-e-lg enabled:hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-50 dark:border-neutral-700 dark:bg-neutral-600 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
        aria-label="Next"
        @click="handleGoToPage(pageIndex + 1)"
      >
        <span class="sr-only">{{ $t("Next") }}</span>
        <font-awesome-icon :icon="['fal', 'chevron-right']" class="size-3.5 shrink-0" />
      </button>
    </nav>

    <!-- Go to page input -->
    <div v-if="!noPageJump" class="flex items-center justify-center gap-x-2 sm:justify-start">
      <span class="whitespace-nowrap text-sm text-gray-800 dark:text-white">
        {{ $t("Go to") }}
      </span>
      <UIInputText
        type="number"
        name="page"
        class="w-16"
        placeholder=""
        :limit="pageCount"
        @keyup.enter="handleGoToPage($event.target.value)"
      />
      <span class="whitespace-nowrap text-sm text-gray-800 dark:text-white">
        {{ $t("page") }}
      </span>
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
    default: 10,
  },
  noPageJump: {
    type: Boolean,
    default: false,
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
