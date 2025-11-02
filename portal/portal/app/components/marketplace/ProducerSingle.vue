<template>
  <div>
    <div
      class="relative z-20 grid grid-cols-4 items-center gap-x-2 gap-y-4 overflow-hidden rounded bg-white p-4 pl-8 shadow-sm shadow-gray-200 transition hover:bg-gray-50 dark:bg-gray-700 dark:shadow-gray-900/50 dark:hover:bg-theme-900 md:grid-cols-8 lg:gap-x-4"
      :class="{
        '!bg-theme-50 !text-theme-500': selected,
        'cursor-wait': props.loadingCategories,
        'cursor-pointer': !props.loadingCategories,
        'shadow-gray-300': selected,
      }"
      @click="!props.loadingCategories ? $emit('select-producer', producer) : ''"
    >
      <!-- <div class="flex w-full items-center space-x-2"> -->
      <HandshakeStatus :producer="producer" />

      <figure class="aspect-video max-h-20">
        <img
          :src="producer.logo"
          :alt="`${producer.name} logo`"
          class="aspect-video object-contain"
        />
      </figure>

      <div class="block font-bold" :class="{ 'text-theme-500': selected }">
        <span
          v-for="(part, index) in highlightName(producer.name)"
          :key="index"
          class="truncate"
          :class="{ 'text-theme-500': part.highlight }"
        >
          {{ part.text }}
        </span>
      </div>

      <div
        class="text-sm font-normal text-gray-600 dark:text-gray-300 sm:w-auto sm:flex-1"
        :class="{ 'text-theme-500': selected }"
      >
        {{ producer.location }}
      </div>

      <!-- </div> -->

      <!-- stars -->
      <!-- <div v-if="producer.rating" class="flex items-center flex-shrink-0">
        <UIRatingStars
          :rating="producer.rating"
          :reviews="producer.reviews"
          class="text-theme-400"
        />
      </div> -->

      <!-- <div class="flex w-full flex-1 justify-between"> -->
      <!-- duplicate element to prevent v-tooltip from rendering when not necesary -->
      <div
        v-if="producer.producerInfo.page_title?.length > 35"
        v-tooltip="producer.producerInfo.page_title"
        class="!block !flex-1 !truncate text-sm font-normal text-gray-600 dark:text-gray-300"
        :class="{ 'text-theme-500': selected }"
        @click.prevent=""
      >
        {{ producer.producerInfo.page_title }}
      </div>
      <div
        v-else
        class="flex-1 text-sm font-normal text-gray-600 dark:text-gray-300"
        :class="{ 'text-theme-500': selected }"
      >
        {{ producer.producerInfo.page_title }}
      </div>

      <div
        class="text-sm font-normal text-gray-600 dark:text-gray-300"
        :class="{ 'text-theme-500': selected }"
      >
        {{ new Date(producer.activeSince).getFullYear() }}
      </div>
      <!-- </div> -->

      <!-- <section
        class="col-start-3 flex items-center justify-between text-right text-sm font-normal text-gray-600 dark:text-gray-300"
      > -->
      <div class="lg:text-right">
        <span class="rounded-full bg-theme-50 px-2 font-bold text-theme-500">
          <font-awesome-icon :icon="['fal', 'radar']" class="mr-1" />
          {{ producer.sharedCategories }}
        </span>
      </div>

      <HandshakeIcon :producer="producer" />

      <UIButton
        class="!text-base md:col-start-auto"
        @click.stop="$emit('clicked-producer-details', producer)"
      >
        {{ $t("details") }}
        <font-awesome-icon :icon="['fal', 'arrow-right']" class="ml-4" />
      </UIButton>
      <!-- </section> -->
    </div>

    <Transition name="slide" class="relative z-0" tag="section">
      <div v-if="selected" class="z-0 flex rounded-b bg-gray-200 p-4 shadow-lg dark:bg-gray-900">
        <div class="flex w-full">
          <section
            v-if="sharedCategories.length > 0 && !loadingCategories"
            class="grid w-full grid-cols-3 gap-4 sm:grid-cols-4 xl:grid-cols-6"
          >
            <div
              v-for="category in sharedCategories"
              :key="category"
              class="flex h-24 cursor-pointer flex-col justify-between rounded bg-white p-4 shadow transition-shadow hover:shadow-xl dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
              @click="navigateTo(`/marketplace/product-finder/${category.slug}`)"
            >
              <span class="self-start text-sm font-bold">
                {{ $display_name(category.display_name) }}
              </span>

              <section class="mt-auto flex justify-between">
                <div
                  class="w-auto -skew-x-12 space-x-2 self-start rounded bg-green-100 text-green-500"
                >
                  <span
                    v-if="bestSlot(category.id)?.type === 'percentage'"
                    class="skew-x-12 px-4 text-xs font-bold"
                  >
                    -
                    {{ bestSlot(category.id).value }}
                    <span class="font-mono">%</span>
                  </span>
                  <span
                    v-if="bestSlot(category.id)?.type === 'fixed'"
                    class="skew-x-12 px-4 text-xs font-bold"
                  >
                    -
                    {{ formatMinor(bestSlot(category.id).value) }}
                  </span>
                </div>
                <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end text-sm" />
              </section>
            </div>
          </section>

          <UIListSkeleton
            v-else-if="loadingCategories"
            :key="'skeleton3'"
            class="w-1/6"
            :skeleton-line-height="10"
            :skeleton-line-amount="1"
            :skeleton-tone="200"
          />

          <div v-else class="italic text-gray-500 dark:text-gray-300">
            {{ $t("This producer has no shared categories") }}
            <font-awesome-icon :icon="['fal', 'sad-tear']" class="ml-2" />
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
const props = defineProps({
  producer: {
    type: Object,
    default: () => ({
      logo: "",
      name: "",
      location: "",
      rating: 0,
      reviews: 0,
      verified: false,
      online: false,
    }),
  },
  selected: { type: Boolean, default: true },
  search: {
    type: String,
    default: "",
  },
  loadingCategories: { type: Boolean, default: false },
  sharedCategories: {
    type: Array,
    default: () => [],
  },
});

const { formatCurrency } = useMoney();

const emit = defineEmits([
  "select-producer",
  "clicked-producer-details",
  "cleanup-shared-categories",
]);

// data

onUnmounted(() => {
  // cleanup
  emit("cleanup-shared-categories", []);
});
// methods
const highlightName = (name) => {
  if (!props.search || props.search.length === 0) {
    return [{ text: name, highlight: false }];
  }

  const escapedSearch = props.search.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const check = new RegExp(`(${escapedSearch})`, "ig");
  const parts = name.split(check).map((part) => {
    if (check.test(part)) {
      return { text: part, highlight: true };
    } else {
      return { text: part, highlight: false };
    }
  });

  return parts;
};

/**
 * Determines the best slot for a given category ID based on the value of the slots.
 *
 * The function processes a nested data structure to locate the category specified by the
 * `categoryId`, and further identifies the slot within that category that has the highest
 * value. If the category or its slots are unavailable, the function returns `null`.
 *
 * @param {string | number} categoryId - The unique identifier of the category to search for.
 * @returns {Object | null} The slot object with the highest value in the specified category, or `null` if
 *                          no valid slots or category are found.
 */
const bestSlot = (categoryId) => {
  const cats = props.producer?.contract?.custom_fields?.contract?.discount?.categories;
  if (!Array.isArray(cats)) return null;
  const cat = cats.find((c) => c.id === categoryId);
  const slots = cat?.slots;
  if (!Array.isArray(slots) || slots.length === 0) return null;
  let best = slots[0];
  for (let i = 1; i < slots.length; i++) {
    const s = slots[i];
    if ((s?.value ?? -Infinity) > (best?.value ?? -Infinity)) best = s;
  }
  return best ?? null;
};

// Centralize minor-unit formatting (see verification note below)
const MINOR_UNIT_DIVISOR = 1000;
const formatMinor = (minor) => formatCurrency((minor ?? 0) / MINOR_UNIT_DIVISOR);
</script>

<style scoped>
/* Your styles go here */
</style>
