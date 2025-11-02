<template>
  <header class="flex flex-col">
    <h2
      class="w-full py-4 text-center text-base font-bold uppercase text-gray-600 dark:text-gray-400"
    >
      <font-awesome-icon class :icon="['fal', 'calculator']" />
      {{ $t("How is this price calculated?") }}
    </h2>

    <!-- Filter Input -->
    <div class="mx-auto mb-4 w-full max-w-md">
      <div class="relative">
        <input
          v-model="searchFilter"
          type="text"
          :placeholder="$t('Search calculation details... (e.g., binding method, machine type)')"
          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pl-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
        />
        <font-awesome-icon
          :icon="['fal', 'search']"
          class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
        />
        <button
          v-if="searchFilter"
          @click="searchFilter = ''"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
          <font-awesome-icon :icon="['fal', 'times']" />
        </button>
      </div>
    </div>
  </header>

  <UIMasonry :columns="2">
    <!-- <section class="grid items-start grid-cols-2 gap-4"> -->
    <section
      class="relative overflow-hidden rounded bg-white p-4 shadow transition-all dark:bg-gray-700"
    >
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("machine details") }}
      </h2>

      <div
        v-if="Object.keys(filteredMachineDetails).length === 0 && searchFilter"
        class="py-4 text-center italic text-gray-500"
      >
        {{ $t("No machine details found matching your filter") }}
      </div>
      <div
        v-for="(value, key, index) in filteredMachineDetails"
        :key="index"
        class="grid grid-cols-2 gap-2 border-b py-2 last:border-b-0"
      >
        <span class="font-bold">
          {{ key.toString().replace(/_/g, " ") }}
        </span>
        <span class="text-end">{{ value }}</span>
      </div>
    </section>

    <section class="rounded bg-white p-4 shadow dark:bg-gray-700">
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("calculation") }}
      </h2>

      <div
        v-if="Object.keys(filteredCalculationDetails).length === 0 && searchFilter"
        class="py-4 text-center italic text-gray-500"
      >
        {{ $t("No calculation details found matching your filter") }}
      </div>
      <div
        v-for="(value, key, index) in filteredCalculationDetails"
        :key="index"
        class="grid grid-cols-2 gap-2 border-b py-2 last:border-b-0"
        :class="{ 'text-gray-400 dark:text-gray-600': !value }"
      >
        <span
          v-if="key !== 'position' && key !== 'color' && key !== 'price_list'"
          class="whitespace-nowrap font-bold"
        >
          <font-awesome-icon
            :icon="[
              'fal',
              key.toString().startsWith('machine')
                ? 'print'
                : key.toString().startsWith('catalog') || key.toString().startsWith('material')
                  ? 'layer-group'
                  : 'circle',
            ]"
            fixed-width
            class="mr-1 text-gray-400"
          />
          {{ key.toString().replace(/_/g, " ") }}
        </span>
        <span v-if="key !== 'position' && key !== 'color' && key !== 'price_list'" class="text-end">
          {{ value }}
        </span>
      </div>
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("color") }}
      </h2>
      <div class="mb-4">
        <div
          v-for="(value, key, index) in filteredColorDetails"
          :key="index"
          class="grid grid-cols-2 py-1"
        >
          <span v-if="key !== 'run' && key !== 'dlv' && key !== 'price_list'" class="font-bold">{{
            key.toString().replace(/_/g, " ")
          }}</span>
          <template v-if="key !== 'run' && key !== 'dlv' && key !== 'price_list'">
            {{ value }}
          </template>
        </div>

        <div v-if="filteredColorRunDetails && Object.keys(filteredColorRunDetails).length > 0">
          <h3 class="pt-2 text-xs font-bold uppercase text-gray-400">
            {{ $t("chosen run") }}
          </h3>
          <div v-for="(v, k, i) in filteredColorRunDetails" :key="i" class="grid grid-cols-2 py-1">
            {{ k.toString().replace(/_/g, " ") }}
            {{ v }}
          </div>
        </div>

        <div v-if="filteredColorDlvDetails && Object.keys(filteredColorDlvDetails).length > 0">
          <h3 class="pt-2 text-xs font-bold uppercase text-gray-400">
            {{ $t("delivery cost") }}
          </h3>
          <div v-for="(v, k, i) in filteredColorDlvDetails" :key="i">
            {{ k.toString().replace(/_/g, " ") }} {{ $t("days") }} =
            {{ v.value }}
            <span v-if="v.mode === 'percentage'">%</span>
            <span v-else>{{ $t("fixed price") }}</span>
          </div>
        </div>
      </div>
    </section>

    <section class="rounded bg-white p-4 shadow dark:bg-gray-700">
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("approximate production duration") }}
      </h2>

      <div
        v-if="Object.keys(filteredDurationDetails).length === 0 && searchFilter"
        class="py-4 text-center italic text-gray-500"
      >
        {{ $t("No duration details found matching your filter") }}
      </div>
      <div
        v-for="(value, key, index) in filteredDurationDetails"
        :key="index"
        class="grid grid-cols-2 gap-2 py-2"
        :class="{ 'text-gray-400 dark:text-gray-600': !value }"
      >
        <span class="font-bold">
          <font-awesome-icon
            :icon="[
              'fal',
              key.toString().startsWith('machine')
                ? 'print'
                : key.toString().startsWith('catalog')
                  ? 'layer-group'
                  : 'circle',
            ]"
            fixed-width
            class="mr-1 text-gray-400"
          />
          {{ key.toString().replace(/_/g, " ") }}
        </span>
        {{ value }}
      </div>
    </section>

    <section class="rounded bg-white p-4 shadow dark:bg-gray-700">
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("possible positionings") }}
      </h2>
      <div class="mb-4 divide-y-2 divide-gray-300 dark:divide-gray-800">
        <div v-for="(position, index) in filteredPositionDetails" :key="index" class="py-4">
          {{ index }}
          <div
            v-for="(value, key, idx) in position"
            :key="`position_${idx}`"
            class="grid grid-cols-2 py-2"
            :class="{ 'text-gray-400 dark:text-gray-600': !value }"
          >
            <span class="font-bold">{{ key.toString().replace(/_/g, " ") }}</span>
            <span class="text-right">{{ value }}</span>
          </div>
        </div>
      </div>
    </section>

    <section class="rounded bg-white p-2 shadow dark:bg-gray-700">
      <h2 class="pb-2 text-sm font-bold uppercase text-gray-400">
        {{ $t("pricelist for comparison") }}
      </h2>
      <div class="mb-4 divide-y-2 divide-gray-300 dark:divide-gray-800">
        <div v-for="(position, index) in filteredPriceListDetails" :key="index" class="py-4">
          <!-- {{ index }} -->
          <div
            v-for="(value, key, idx) in position"
            :key="`position_${idx}`"
            class="grid grid-cols-2 py-1"
            :class="{ 'text-gray-400 dark:text-gray-600': !value }"
          >
            <span class="font-bold">{{ key.toString().replace(/_/g, " ") }}</span>
            {{ value }}
          </div>
        </div>
      </div>
    </section>
    <!-- </section> -->
  </UIMasonry>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  calculation: {
    type: Object,
    required: true,
  },
});

// Filter state
const searchFilter = ref("");

// Helper function to normalize text for fuzzy matching
const normalizeText = (text) => {
  return String(text)
    .toLowerCase()
    .replace(/[_\s-]/g, "") // Remove underscores, spaces, and hyphens
    .replace(/[^\w]/g, ""); // Remove non-word characters
};

// Helper function to check if search term matches target with fuzzy logic
const fuzzyMatch = (target, searchTerm) => {
  const normalizedTarget = normalizeText(target);
  const normalizedSearch = normalizeText(searchTerm);

  // Direct substring match
  if (normalizedTarget.includes(normalizedSearch)) {
    return true;
  }

  // Fuzzy character sequence match
  let searchIndex = 0;
  for (let i = 0; i < normalizedTarget.length && searchIndex < normalizedSearch.length; i++) {
    if (normalizedTarget[i] === normalizedSearch[searchIndex]) {
      searchIndex++;
    }
  }

  return searchIndex === normalizedSearch.length;
};

// Helper function to filter object entries with fuzzy search
const filterObjectEntries = (obj, filter) => {
  if (!obj || !filter) return obj;

  const filteredEntries = Object.entries(obj).filter(([key, value]) => {
    const keyMatch = fuzzyMatch(key, filter);
    const valueMatch = fuzzyMatch(String(value), filter);
    return keyMatch || valueMatch;
  });

  return Object.fromEntries(filteredEntries);
};

// Computed properties for filtered data
const filteredMachineDetails = computed(() => {
  return filterObjectEntries(props.calculation.machine, searchFilter.value);
});

const filteredCalculationDetails = computed(() => {
  return filterObjectEntries(props.calculation.details, searchFilter.value);
});

const filteredDurationDetails = computed(() => {
  return filterObjectEntries(props.calculation.duration, searchFilter.value);
});

const filteredColorDetails = computed(() => {
  if (!props.calculation.details?.color) return {};
  return filterObjectEntries(props.calculation.details.color, searchFilter.value);
});

const filteredColorRunDetails = computed(() => {
  if (!props.calculation.details?.color?.run?.[0]) return {};
  return filterObjectEntries(props.calculation.details.color.run[0], searchFilter.value);
});

const filteredColorDlvDetails = computed(() => {
  if (!props.calculation.details?.color?.dlv) return {};
  return filterObjectEntries(props.calculation.details.color.dlv, searchFilter.value);
});

// Helper function to filter array of objects with fuzzy search
const filterArrayOfObjects = (arr, filter) => {
  if (!arr || !filter) return arr;

  return arr.filter((item) => {
    return Object.entries(item).some(([key, value]) => {
      const keyMatch = fuzzyMatch(key, filter);
      const valueMatch = fuzzyMatch(String(value), filter);
      return keyMatch || valueMatch;
    });
  });
};

const filteredPositionDetails = computed(() => {
  if (!props.calculation.details?.position) return [];
  return filterArrayOfObjects(props.calculation.details.position, searchFilter.value);
});

const filteredPriceListDetails = computed(() => {
  if (!props.calculation.details?.price_list) return [];
  return filterArrayOfObjects(props.calculation.details.price_list, searchFilter.value);
});
</script>
