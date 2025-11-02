<template>
  <div>
    <section class="mb-8">
      <UISectionTitle is="h2">{{ $t("mode") }}</UISectionTitle>
      <div class="mb-2 flex w-1/3">
        <template v-if="loading">
          <SkeletonLine class="h-8 w-96" />
        </template>
        <template v-else>
          <button
            class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
            :class="{
              'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                mode === 'runs',
            }"
            @click="mode = 'runs'"
          >
            {{ $t("Runs") }}
          </button>
          <button
            class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
            :class="{
              'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                mode === 'price',
            }"
            @click="mode = 'price'"
          >
            {{ $t("Price") }}
          </button>
        </template>
      </div>
    </section>
    <section>
      <UISectionTitle is="h2">{{ $t("slots") }}</UISectionTitle>
      <div v-if="loading" class="flex flex-wrap gap-4">
        <SkeletonLine key="skel1" class="aspect-square h-56" />
        <SkeletonLine key="skel3" class="aspect-square h-56" />
        <SkeletonLine key="skel2" class="aspect-square h-56" />
      </div>
      <TransitionGroup v-else name="list" tag="div" class="flex flex-wrap gap-4">
        <MarginTile
          v-for="(margin, index) in margins"
          :key="margin.id"
          :margin="margin"
          :is-deletable="index === margins.length - 1"
          :is-infinitable="index === margins.length - 1"
          :is-from-editable="index === 0"
          @update:margin="handleMarginUpdate($event, margin.id)"
          @on-remove-margin="handleMarginDelete(margin.id)"
        />
        <MarginSlot
          v-if="!margins.length || Number(margins[margins.length - 1].to) !== -1"
          key="MarginSlot"
          @add-margin="handleMarginAdd"
        />
      </TransitionGroup>
    </section>
    <footer v-if="!wizardMode" class="mt-4 flex items-end justify-end px-24">
      <UIButton variant="success" class="!px-4 !text-base" @click="handleSaveMargins">
        <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
        {{ $t("Save Margins") }}
      </UIButton>
    </footer>
  </div>
</template>

<script setup>
const props = defineProps({
  category: {
    type: String,
    required: false,
    default: "general",
  },
  wizardMode: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['margins-saved']);

const { t: $t } = useI18n();
const marginRepository = useMarginRepository();
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();

const mode = ref(null);
const loading = ref(true);
const margins = ref(null);
const isChanged = ref(false);
watch(
  () => margins.value,
  () => {
    if (loading.value || isChanged.value) return;
    addToast({
      type: "info",
      message: $t("You made changes, be sure to save them"),
    });
    isChanged.value = true;
  },
  { deep: true },
);

watch(
  mode,
  (value, oldValue) => {
    if (value === oldValue || (loading.value && mode.value !== null)) return;
    handleFetchMargins();
  },
  { immediate: true },
);

async function handleFetchMargins() {
  try {
    loading.value = true;
    const [response, activeMode] = await marginRepository.getMargins(mode.value, props.category);
    margins.value = response;
    if (!mode.value) mode.value = activeMode;
    await nextTick();
    loading.value = false;
  } catch (err) {
    handleError(err);
  }
}

function handleMarginAdd() {
  if (margins.value.length >= 1) {
    margins.value.push({
      id: margins.value.length + 1,
      from: margins.value[margins.value.length - 1].to + 1,
      to: margins.value[margins.value.length - 1].to + 2,
      type: "percentage",
      value: margins.value[margins.value.length - 1].value,
    });
    return;
  }
  margins.value.push({
    id: Math.random().toString(36).substring(2, 9),
    from: 0,
    to: 1,
    type: "percentage",
    value: 0,
  });
}

function handleMarginUpdate(updatedMargin, id) {
  const updatedMarginIndex = margins.value.findIndex((margin) => margin.id === id);
  const margin = {
    id,
    ...updatedMargin,
  };

  margins.value[updatedMarginIndex] = margin;
  const nextMarginIndex = updatedMarginIndex + 1;
  if (nextMarginIndex >= margins.value.length) return;

  const nextMargin = margins.value[nextMarginIndex];

  const theNextMargin = {
    ...nextMargin,
    from: updatedMargin.to + 1,
  };

  margins.value[nextMarginIndex] = theNextMargin;
}

function handleMarginDelete(id) {
  margins.value = margins.value.filter((margin) => margin.id !== id);
}

async function handleSaveMargins() {
  try {
    await marginRepository.saveMargins(margins.value, mode.value, props.category);

    if (props.wizardMode) {
      // In wizard mode, emit event instead of showing toast
      emit('margins-saved', { margins: margins.value, mode: mode.value });
    } else {
      addToast({
        type: "success",
        message: $t("Margins saved successfully"),
      });
    }
  } catch (error) {
    handleError(error);
    throw error; // Re-throw in wizard mode so parent can handle
  }
}

// Expose the save function for wizard mode
defineExpose({ handleSaveMargins });
</script>
