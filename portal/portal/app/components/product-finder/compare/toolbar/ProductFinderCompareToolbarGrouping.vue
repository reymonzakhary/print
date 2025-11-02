<template>
  <div>
    <span class="mb-1.5 ml-1 block text-xs font-medium">
      <font-awesome-icon
        :icon="['fas', 'bolt-lightning']"
        class="mr-1.5 inline-block size-3 text-yellow-500"
      />
      {{ $t("Group by difference:") }}
    </span>
    <div
      ref="container"
      :class="cn('relative flex min-h-[30px] flex-wrap', shouldShowButtons && 'rounded-lg')"
    >
      <template v-if="shouldShowButtons">
        <button
          v-for="(box, index) in groupingOptions"
          :key="box.linked"
          :class="
            cn(
              'relative z-10 flex items-center truncate border-2 bg-gray-200 px-3 py-1 text-sm transition-all dark:border-gray-800 dark:bg-gray-800',
              groupBy?.linked === box.linked
                ? '!bg-white font-medium text-theme-700 shadow-sm dark:!bg-gray-700 dark:!text-theme-500'
                : 'text-gray-600 hover:bg-gray-50 hover:text-theme-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-theme-500',
              index === 0 && 'rounded-l-lg',
              index === groupingOptions.length - 1 && 'rounded-r-lg',
              disabled &&
                'cursor-not-allowed opacity-50 hover:bg-white hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-theme-500',
            )
          "
          :disabled="disabled"
          @click="groupBy && groupBy.linked === box?.linked ? (groupBy = null) : (groupBy = box)"
        >
          {{ getDisplayName(box.display_name) }}
          <font-awesome-icon
            v-if="groupBy?.linked === box.linked"
            :icon="['fas', 'xmark']"
            class="ml-2 size-4 text-theme-800 dark:text-theme-200"
          />
        </button>
      </template>
      <UIVSelect
        v-else
        v-model="groupBy"
        :get-option-key="(option) => option.linked"
        :get-option-label="(option) => option.name"
        :options="groupingOptions"
        :searchable="false"
        :disabled="disabled"
        class="w-full min-w-24"
        :placeholder="$t('Select grouping')"
        deselect-from-dropdown
        @option:deselected="groupBy = null"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  groupingOptions: {
    type: Array,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const { cn } = useUtilities();
const { getDisplayName } = useDisplayName();

const groupBy = defineModel("groupBy", { type: [Object, null], required: true });

const container = ref(null);
const shouldShowButtons = ref(true);

const updateLayout = () => {
  if (!container.value) return;

  const containerWidth = container.value.offsetWidth;
  const totalButtonWidth = props.groupingOptions.reduce((acc, option) => {
    // Approximate width based on text length and padding
    return acc + (option.name.length * 8 + 24); // 8px per character + 24px padding
  }, 0);

  shouldShowButtons.value = totalButtonWidth <= containerWidth;
};

watch(
  () => props.groupingOptions,
  () => updateLayout(),
  { immediate: true },
);

onMounted(() => {
  const resizeObserver = new ResizeObserver(updateLayout);
  if (container.value) {
    resizeObserver.observe(container.value);
  }

  onUnmounted(() => {
    resizeObserver.disconnect();
  });
});
</script>
