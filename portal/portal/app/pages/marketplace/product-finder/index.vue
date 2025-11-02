<template>
  <main class="container space-y-8 py-10">
    <ProductFinderHeader>
      <section class="mx-auto w-full xl:w-7/12">
        <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-gray-100 md:text-3xl">
          {{ $t("Product Finder") }}
        </h1>
        <p class="mb-4 hidden text-gray-500 dark:text-gray-400 md:block">
          {{ $t("Search for products quick and simple and discover customized smart suggestions") }}
        </p>
      </section>
      <ProductFinderSearch class="mx-auto w-full xl:w-7/12" @search="searchQuery = $event" />
    </ProductFinderHeader>

    <ProductFinderLists v-if="categories.length" />
    <div v-else class="flex gap-4">
      <SkeletonLine v-for="i in 10" :key="i" class="!h-8 w-48 rounded-full" />
    </div>
    <!-- <Separator /> -->

    <ProductFinderSection
      class="background-image relative rounded-lg border border-theme-100 bg-gradient-to-l from-theme-500 to-theme-50 p-4 backdrop-opacity-50 dark:border-theme-900 dark:to-theme-900"
      :title="$t('Recent choices')"
      :description="
        $t('Simply find your most recent products and discover smart suggestions tailored to you')
      "
    >
      <div
        class="relative grid auto-rows-[0] grid-cols-2 grid-rows-1 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7"
      >
        <template v-if="categories.length">
          <template v-if="quickChoiceCategories.length">
            <ProductFinderCategoryCard
              v-for="category in quickChoiceCategories.slice(0, quickChoiceAmount)"
              :key="category.name"
              :category="category"
            />
          </template>
          <template v-else>
            <p
              class="aspect-square rounded-lg border border-dashed border-gray-300 bg-gray-100 p-4 text-center font-medium text-gray-700"
            >
              ⭐
              {{ $t("Check out some categories and see them added here for quick access!") }}
              ⭐
            </p>
          </template>
          <!-- <PrindustryChoiceCard class="col-span-2" /> -->
        </template>
        <template v-else>
          <ProductFinderCategoryCardSkeleton v-for="i in 10" :key="i" />
        </template>
      </div>
    </ProductFinderSection>

    <!-- <Separator /> -->

    <ProductFinderSection>
      <ProductFinderSectionHeader class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <ProductFinderSectionTitle class="flex items-center gap-2">
            <span>{{ $t("All categories") }}</span>
            <button
              v-tooltip.right="'Fetch the latest category data'"
              :disabled="isRefreshing"
              class="mt-1 grid place-items-center rounded-full p-2 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-900"
              aria-label="Refresh product data"
              @click="refreshData"
            >
              <font-awesome-icon
                :icon="['fas', 'sync']"
                class="size-5"
                :class="{ 'animate-spin': isRefreshing }"
              />
            </button>
          </ProductFinderSectionTitle>
          <ProductFinderSectionDescription>
            {{
              //prettier-ignore
              $t("Business cards to large format print - everything you need for your print projects.")
            }}
          </ProductFinderSectionDescription>
        </div>
        <div class="flex items-center justify-end gap-4">
          <ProductFinderSearchInput
            v-model="searchQuery"
            :placeholder="$t('Search in categories')"
            class="flex-1"
          />
          <MarketPlaceUIRadioGroup
            v-model="activeView"
            :options="[
              { value: 'grid', icon: ['fal', 'grid-2'] },
              { value: 'list', icon: ['fal', 'list'], disabled: true },
            ]"
          />
        </div>
      </ProductFinderSectionHeader>

      <div
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7"
      >
        <TransitionGroup v-if="visibleItems.length" name="fade">
          <ProductFinderCategoryCard
            v-for="category in visibleItems"
            :key="category.name"
            :category="category"
          />
          <div v-if="visibleItems.length < filteredCategories.length" class="col-span-full mt-4">
            <button
              class="group mx-auto flex w-full max-w-xs items-center justify-center gap-3 rounded-md bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-750"
              @click="currentPage++"
            >
              <span>Load More Items</span>
              <font-awesome-icon
                :icon="['fas', 'arrow-down']"
                class="size-4 transition group-hover:translate-y-0.5"
              />
            </button>
          </div>
        </TransitionGroup>
        <ProductFinderZeroState
          v-else
          :show-clear-button="!!searchQuery"
          class="col-span-full flex flex-col items-center justify-center py-16 text-center"
          @clear="searchQuery = ''"
        />
      </div>
    </ProductFinderSection>
  </main>
</template>

<script setup>
import { addImageToCategory } from "~/composables/market-place/helper.js";

const { t: $t, locale } = useI18n();
const productFinderRepository = useProductFinderRepository();
const productFinderStore = useProductFinderStore();

/**
 * Fetching Categories for Product Finder & Smart Search
 */
const { data: categories } = await useLazyAsyncData(
  "categories",
  () => productFinderRepository.getAllCategories(),
  {
    transform: ({ data }) => data.map(addImageToCategory),
    default: () => [],
  },
);
watch(categories, () => (productFinderStore.categories = categories.value));

/**
 * Fetching Options for Smart Search
 */
const { data: options } = await useLazyAsyncData(
  "options",
  () => productFinderRepository.getAllOptions(),
  {
    default: () => [],
  },
);
watch(options, () => (productFinderStore.options = options.value.data));

/**
 * Quick Choice Logic
 */
const quickChoiceRepository = useQuickChoiceRepository();
const favorites = await quickChoiceRepository.index();
const quickChoiceCategories = computed(() => {
  const favs = favorites.slice(0, 5);
  return favs.map((fav) => {
    const cat = categories.value.find((c) => c.slug === fav.slug);
    if (!cat) quickChoiceRepository.remove(fav);
    return cat;
  });
});

/**
 * Logic for the Dumb Search
 */
const { searchQuery, filteredResults: filteredCategories } = useFuzzySearch(categories, {
  keys: ["name", "slug"],
});

const activeView = ref("grid");

// Quick Choice Amount calculated based on screen size
const { $screen } = useNuxtApp();
const quickChoiceAmount = computed(() => {
  switch ($screen.breakpoint) {
    case "2xl":
      return 5;
    case "xl":
    case "lg":
      return 4;
    case "md":
      return 4;
    case "sm":
      return 3;
    default:
      return 2;
  }
});

// Infinite Scroll Logic
const itemsPerPage = 20;
const currentPage = ref(1);
const visibleItems = computed(() => {
  const total = currentPage.value * itemsPerPage;
  return filteredCategories.value.slice(0, total);
});

// Refreshing Logic
const isRefreshing = ref(false);
const refreshData = async () => {
  isRefreshing.value = true;
  try {
    await refreshNuxtData("categories");
    await refreshNuxtData("options");
    currentPage.value = 1;
  } catch (error) {
    console.error("Error refreshing data:", error);
  } finally {
    isRefreshing.value = false;
  }
};

watch(
  () => locale.value,
  async () => {
    await refreshData();
  },
);
</script>

<style scoped>
.background-image:before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: url("~/assets/images/mountain2.webp") center left no-repeat;
  /* filter: grayscale(100%); */
  transform: scaleX(-1);
  background-size: 100%;
  transition: opacity 200ms ease-in;
  opacity: 0.1;
  z-index: -1;
}
</style>
