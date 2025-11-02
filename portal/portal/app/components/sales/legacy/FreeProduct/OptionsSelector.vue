<template>
  <v-select
    ref="optionSelector"
    v-model="selectedOption"
    placeholder="Select option"
    :loading="pending"
    :disabled="disabled"
    :options="reducedOptions"
    :filterable="!useApiSearch"
    class="has-icon rounded border border-gray-200 bg-white transition-all duration-100 dark:border-gray-900 dark:bg-gray-700 dark:text-white"
    @search="handleSearch"
  >
    <template #no-options="{ search, searching }">
      <div class="p-2">
        <template v-if="searching">
          {{ $t("No results found for") }}
          <em>{{ search }}</em>
          <div class="mt-2">
            <UIButton
              v-tooltip="!props.categoryId && $t('You need to create a category first')"
              :disabled="!props.categoryId"
              :icon="['fal', 'plus']"
              variant="success"
              class="block"
              @click="handleCreateOption(search)"
            >
              {{ $t("Create New Option") }}
            </UIButton>
            <UIButton
              variant="theme-light"
              class="mt-2 block"
              :icon="['fas', 'sync']"
              :loading="isRefreshing"
              @click="refreshData"
            >
              {{ $t("Or retrieve the latest options") }}
            </UIButton>
          </div>
        </template>
      </div>
    </template>
  </v-select>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      label: "",
      value: "",
      slug: "",
    }),
  },
  categoryId: {
    type: String,
    required: false,
    default: null,
  },
  useApiSearch: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
const emits = defineEmits(["update:modelValue"]);

const api = useAPI();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

const optionSelector = ref(null);
const selectedOption = ref(null);
const isRefreshing = ref(false);

const { allOptions } = storeToRefs(useSalesStore());

const searchQuery = ref("");
const debouncedSearch = refDebounced(searchQuery, 300);

const {
  data: _allOptions,
  status,
  refresh,
} = await useLazyAPI("/finder/options/search", {
  query: computed(() => ({
    search: props.useApiSearch ? debouncedSearch.value : null,
    per_page: props.useApiSearch ? "10" : "99999",
  })),
  default: () => ({ data: [] }),
  immediate: false,
  onResponse({ error, response }) {
    if (error) {
      handleError(error);
    } else {
      allOptions.value = response._data.data;
    }
  },
});

const pending = computed(() => status.value === "pending");

const reducedOptions = computed(() => {
  if (!props.useApiSearch && searchQuery.value) {
    return allOptions.value
      .filter((option) => option.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
      .map((option) => ({
        ...option,
        label: option.name,
        value: option.id,
        slug: option.slug,
      }));
  }
  if (allOptions.value?.suggestions) {
    return allOptions.value.suggestions.map((option) => ({
      ...option,
      label: option.name,
      value: option.linked,
      slug: option.slug,
    }));
  } else {
    return allOptions.value.map((option) => ({
      ...option,
      label: option.name,
      value: option.id,
      slug: option.slug,
    }));
  }
});

onMounted(async () => {
  if (allOptions.value.length === 0) await refresh();
  if (props.modelValue.value) {
    selectedOption.value = {
      ...props.modelValue.value,
      label: props.modelValue.value.label,
      value: props.modelValue.value.value,
      slug: props.modelValue.value.slug,
    };
  }
});

const refreshData = async () => {
  isRefreshing.value = true;
  try {
    await refresh();
    addToast({
      type: "success",
      message: "Options refreshed successfully",
    });
  } catch (error) {
    handleError(error);
  } finally {
    isRefreshing.value = false;
  }
};

const handleSearch = async (search, loading) => {
  searchQuery.value = search;
  if (props.useApiSearch) {
    if (loading) loading(true);
    try {
      searchQuery.value = search;
    } finally {
      if (loading) loading(false);
    }
  }
};

async function handleCreateOption(optionName) {
  const newOption = {
    name: optionName,
    published: true,
    system_key: optionName.toLowerCase().replace(/ /g, "-"),
    input_type: "checkbox",
    category_id: props.categoryId,
  };

  try {
    const response = await api.post("/options", newOption);
    addToast({
      type: "success",
      message: "Option created successfully",
    });
    selectedOption.value = {
      ...response.data,
      label: response.data.name,
      value: response.data.id,
      slug: response.data.slug,
    };
    allOptions.value = [...allOptions.value, response.data];
    optionSelector.value.select(selectedOption.value);
    emits("update:modelValue", selectedOption.value);
  } catch (err) {
    handleError(err);
  }
}

watch(selectedOption, (newValue) => {
  emits("update:modelValue", newValue);
});
</script>
