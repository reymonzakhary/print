<template>
  <div class="relative">
    <v-select
      ref="categorySelector"
      v-model="selectedCategory"
      placeholder="Select category"
      :loading="pending"
      :disabled="allCategories.length === 0"
      :options="reducedCategories"
      class="has-icon rounded border border-gray-200 bg-white transition-all duration-100 dark:border-gray-900 dark:bg-gray-700 dark:text-white"
    >
      <template #no-options="{ search, searching }">
        <template v-if="searching">
          <div class="p-2">
            {{ $t("No results found for") }}
            <em>{{ search }}</em>
            <UIButton
              variant="theme-light"
              class="mt-2 block"
              :icon="['fas', 'sync']"
              @click="refreshData"
            >
              {{ $t("Or retrieve the latest category updates") }}
            </UIButton>
          </div>
        </template>
      </template>
    </v-select>
  </div>
</template>

<script setup>
const api = useAPI();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const categorySelector = ref(null);
const selectedCategory = ref(null);
const isRefreshing = ref(false);

const emits = defineEmits(["update:modelValue"]);
const { modelValue } = defineProps({
  modelValue: {
    type: Object,
    required: true,
    validator: (prop) => {
      return typeof prop === "object" && "label" in prop && "value" in prop;
    },
  },
});

const { allCategories } = storeToRefs(useSalesStore());

// Use lazy API with caching
const { status, refresh } = await useLazyAsyncData(
  "sales-categories",
  async () => {
    const [ownCategories] = await Promise.allSettled([api.get("/categories?per_page=99999")]);
    allCategories.value = ownCategories.status === "fulfilled" ? ownCategories.value.data : [];
    return allCategories.value;
  },
  { default: () => [], immediate: false },
);
const pending = computed(() => status.value === "pending");

const reducedCategories = computed(() => {
  return allCategories.value.map((category) => ({
    ...category,
    label: category.name,
    value: category.id,
    slug: category.slug ?? category.name.toLowerCase().replace(/ /g, "-"),
  }));
});

onMounted(async () => {
  if (allCategories.value.length === 0) await refresh();
  if (selectedCategory.value) selectedCategory.value = modelValue.value;
});

const refreshData = async () => {
  isRefreshing.value = true;
  try {
    await refresh();
    addToast({
      type: "success",
      message: "Categories refreshed successfully",
    });
  } catch (error) {
    handleError(error);
  } finally {
    isRefreshing.value = false;
  }
};

watch(selectedCategory, (newValue) => {
  emits("update:modelValue", newValue);
});
</script>
