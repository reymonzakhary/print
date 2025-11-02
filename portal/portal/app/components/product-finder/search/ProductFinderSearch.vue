<template>
  <div>
    <ProductFinderSearchInput
      ref="smartSearchElement"
      v-model="query"
      autofocus
      :placeholder="!query.length ? animatedPlaceholder : ''"
      input-class="bg-transparent !ps-10 text-transparent caret-black dark:text-transparent dark:bg-transparent"
      :disabled="!options.length || !categories.length"
      @click="query.length && (smartOpen = true)"
      @input="query.length && (smartOpen = true)"
    >
      <!-- Icons and Highlighting -->
      <ProductFinderSearchIcon class="absolute start-3 top-1/2 z-[2] -translate-y-1/2" />
      <div class="absolute inset-0 w-full py-3 pe-10 ps-10 text-sm text-gray-800 dark:text-white">
        <template v-for="token in tokens" :key="token.text">
          <span v-if="token.type === 'text'">{{ token.text }}</span>
          <ProductFinderSearchHighlight v-else :label="token.text" :type="token.type" animate />
        </template>
      </div>
    </ProductFinderSearchInput>

    <ProductFinderSearchContainer v-model:shown="smartOpen">
      <ProductFinderSearchSection v-if="chosenFilters.length">
        <ProductFinderSearchChipContainer>
          <ProductFinderSearchChip
            v-if="chosenFilters.some((filter) => filter.type === 'category')"
            :label="chosenFilters.find((filter) => filter.type === 'category').data.name"
            :variant="chosenFilters.find((filter) => filter.type === 'category').type"
            @click="removeFilter(chosenFilters.find((filter) => filter.type === 'category'))"
          />
          <ProductFinderSearchChip
            v-for="filter in chosenFilters.filter((filter) => filter.type === 'option')"
            :key="filter.data.name"
            :label="filter.data.name"
            :variant="filter.type"
            @click="removeFilter(filter)"
          />
        </ProductFinderSearchChipContainer>
        <!-- Prindustry standard category -->
        <ProductFinderSearchButton
          v-if="chosenFilters.some((filter) => filter.type === 'category')"
          variant="search"
          @click="
            navigateToCategory(
              chosenFilters.find((filter) => filter.type === 'category'),
              chosenFilters.filter((filter) => filter.type === 'option'),
            )
          "
        >
          {{ $t("Go to") }}
          {{ chosenFilters.find((filter) => filter.type === "category").data.name }}
          {{ $t("with these chosen options") }}
        </ProductFinderSearchButton>
      </ProductFinderSearchSection>

      <Separator v-if="chosenFilters.length && smartSuggestions.length" />

      <ProductFinderSearchSection v-if="smartSuggestions.length">
        <ProductFinderSearchSectionTitle>Smart Search</ProductFinderSearchSectionTitle>
        <ProductFinderSearchButton
          v-for="suggestion in smartSuggestions"
          :key="suggestion.text"
          variant="smart"
          @click="addFilter(suggestion)"
        >
          {{ $t("Do you mean") }}
          {{ suggestion.type === "category" ? $t("the category") : suggestion.data.box }}
          <ProductFinderSearchHighlight
            :label="suggestion.data[locale] ? suggestion.data[locale] : suggestion.data.name"
            :type="suggestion.type"
          />?
        </ProductFinderSearchButton>
      </ProductFinderSearchSection>

      <Separator v-if="smartSuggestions.length && categorySearchResults.length" />

      <ProductFinderSearchSection v-if="isSearching || categorySearchStatus === 'success'">
        <ProductFinderSearchSectionTitle>{{
          $t("Search results")
        }}</ProductFinderSearchSectionTitle>

        <ProductFinderSearchGrid v-if="isSearching">
          <SkeletonLine v-for="i in resultsToShow" :key="i" class="!h-12 w-full" />
        </ProductFinderSearchGrid>
        <ProductFinderSearchGrid v-else-if="categorySearchResults.length > 0">
          <ProductFinderSearchCategoryButton
            v-for="result in categorySearchResults.slice(0, resultsToShow)"
            :key="result.slug"
            :category="result"
            :image="categories.find((c) => c.id === result.id)?.image"
          />
          <ProductFinderSearchCategoryButton
            v-if="categorySearchResults.length > resultsToShow"
            variant="all"
            :count="categorySearchResults.length"
            :disabled="true"
          />
        </ProductFinderSearchGrid>
        <div v-else>
          <div
            class="flex flex-col items-center justify-center rounded-md bg-gray-50 py-4 text-center dark:bg-gray-800"
          >
            <div
              class="grid place-items-center rounded-full bg-gray-100 p-2 px-2.5 dark:bg-gray-900/30"
            >
              <font-awesome-icon
                :icon="['far', 'face-monocle']"
                class="size-10 text-gray-400 dark:text-gray-400"
              />
            </div>
            <h3
              class="mt-2 max-w-xl whitespace-pre-line text-pretty text-sm font-medium text-gray-600 dark:text-gray-300"
            >
              {{ $t("No results found. Try again with a different query.") }}
            </h3>
          </div>
        </div>
      </ProductFinderSearchSection>
    </ProductFinderSearchContainer>
  </div>
</template>

<script setup>
const emit = defineEmits(["search"]);

const productFinderRepository = useProductFinderRepository();
const productFinderStore = useProductFinderStore();
const { locale, t: $t } = useI18n();

const categories = computed(() => productFinderStore.categories);
const options = computed(() => productFinderStore.options);

/**
 * Back-end search
 */
const query = ref("");
const _query = ref("");
const debouncedSearchQuery = refDebounced(_query, 500);
const { data: categorySearchResults, status: categorySearchStatus } = await useLazyAsyncData(
  "categorySearch",
  () => productFinderRepository.searchCategories(debouncedSearchQuery.value),
  {
    immediate: !!_query.value.length,
    watch: [debouncedSearchQuery],
    transform: ({ data }) =>
      data.suggestions.map(productFinderRepository.transformCategorySearchResult),
    default: () => [],
  },
);
const isSearching = computed(
  () => categorySearchStatus.value === "pending" || _query.value !== debouncedSearchQuery.value,
);
watch(query, () => {
  emit("search", query.value);
  const smartCategory = smartSuggestions.value.find((s) => s.type === "category");
  if (smartCategory) {
    _query.value = smartCategory.data.name;
  } else {
    _query.value = query.value;
  }
});

const { $screen } = useNuxtApp();
const resultsToShow = ref(3);
watchEffect(() => {
  switch ($screen.breakpoint) {
    case "2xl":
      resultsToShow.value = 8;
      break;
    case "xl":
    case "lg":
      resultsToShow.value = 5;
      break;
    default:
      resultsToShow.value = 3;
  }
});

// Filter category and options
const chosenFilters = ref([]);
const addFilter = (filter) => chosenFilters.value.push(filter);
const removeFilter = (filter) => {
  chosenFilters.value = chosenFilters.value.filter((f) => f.data.name !== filter.data.name);
};
watch(query, () => {
  if (!query.value.length) smartOpen.value = false;
  chosenFilters.value.forEach((f) => {
    if (!tokens.value.some((t) => t.data?.name === f.data.name)) {
      removeFilter(f);
    }
  });
});

// Tokenize query into category, option and text tokens
const tokens = computed(() => {
  if (categories.value?.length && options.value?.length) {
    return useTokenizeQuery(
      query.value,
      categories.value,
      categorySearchResults.value, // also match on search results
      options.value,
      locale.value,
    );
  }
  return [];
});

// Smart Suggestions, exclude already chosen filters
const smartOpen = ref(false);
const smartSuggestions = computed(() =>
  tokens.value.filter(
    (token) =>
      (token.type === "category" &&
        !chosenFilters.value.some((f) => f.type === "category") &&
        !chosenFilters.value.some((f) => f.data.name === token.data.name)) ||
      (token.type === "option" &&
        !chosenFilters.value.some((f) => f.data.name === token.data.name)),
  ),
);

// Animated placeholder
const { animatedText: animatedPlaceholder } = useTypingAnimation({
  baseText: `${$t("Search")} `,
  texts: [
    $t("flyers with 4/4 full color"),
    $t("business cards with PVC-lamination"),
    $t("2025 calendars"),
  ],
});

// Focus on search input
const smartSearchElement = ref(null);
onMounted(() => smartSearchElement.value.$el.focus());

/**
 * (Smart) Navigation to Category
 */
const navigateToCategory = (category, options) => {
  return navigateTo({
    name: "marketplace-product-finder-slug",
    params: {
      slug: category.data.slug,
    },
    query: {
      smart: options.map((o) => `${o.data.slug}`).join(";"),
    },
  });
};
</script>
