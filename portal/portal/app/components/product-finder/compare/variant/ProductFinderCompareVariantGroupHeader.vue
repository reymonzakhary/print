<template>
  <div class="flex items-end justify-between gap-8">
    <div class="relative flex flex-nowrap items-center rounded-full bg-white p-1 dark:bg-gray-700">
      <button
        v-if="showLeftScrollButton"
        class="flex h-full flex-shrink-0 items-center justify-center px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100"
        aria-label="Scroll left"
        @click="scrollMenu('left')"
      >
        <font-awesome-icon :icon="['fas', 'chevron-left']" />
      </button>
      <menu ref="menuRef" class="scrollbar-hide flex w-full flex-nowrap overflow-x-auto">
        <li
          v-for="(group, groupName, index) in productGroups"
          :key="groupName"
          class="flex-shrink-0"
        >
          <button
            :disabled="group.filter((item) => item.producers.length > 0).length === 0"
            class="relative flex items-center gap-1 rounded-full px-4 py-1 text-sm font-medium transition-all"
            :class="[
              chosenGroupName === groupName
                ? 'border border-gray-100 bg-gray-50 text-theme-600 shadow-inner dark:border-gray-900 dark:bg-gray-800 dark:text-theme-400 dark:shadow-black/50'
                : 'text-gray-600 hover:text-theme-500 disabled:text-gray-400 dark:text-gray-300 dark:hover:text-theme-500',
            ]"
            @click="chosenGroupName = groupName"
          >
            <span
              v-if="index === 0"
              v-tooltip.bottom-start="$t('Best group based on your global sorting criteria.')"
              class="grid aspect-square h-full place-items-center"
            >
              <font-awesome-icon :icon="['fas', 'star']" class="text-xs" />
            </span>
            {{ groupName }}
            <span
              class="text-xs text-gray-400 transition-colors group-hover:text-gray-500"
              :class="{ '!text-theme-600': chosenGroupName === groupName }"
            >
              ({{ group.filter((item) => item.producers.length > 0).length }})
            </span>
          </button>
        </li>
      </menu>
      <button
        v-if="showRightScrollButton"
        class="flex h-full flex-shrink-0 items-center justify-center px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100"
        aria-label="Scroll right"
        @click="scrollMenu('right')"
      >
        <font-awesome-icon :icon="['fas', 'chevron-right']" />
      </button>
    </div>
    <ProductFinderCompareToolbarSortMenu v-model="sortGroupBy" />
  </div>
</template>

<script setup>
defineProps({
  productGroups: {
    type: Object,
    required: true,
  },
});

const chosenGroupName = defineModel("chosenGroupName", { type: String, required: true });
const sortGroupBy = defineModel("sortGroupBy", { type: String, required: true });

const { menuRef, showLeftScrollButton, showRightScrollButton, scrollMenu } = useScrollableMenu();
</script>
