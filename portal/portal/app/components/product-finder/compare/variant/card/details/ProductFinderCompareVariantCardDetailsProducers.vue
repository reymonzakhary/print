<template>
  <ul
    class="relative -z-10 -mt-2 rounded-b-md bg-gray-100 pt-2 shadow-md dark:bg-gray-800 dark:shadow-black/30"
  >
    <li
      v-for="producer in sortedProducers"
      :key="producer.id"
      class="grid grid-cols-[28px_35%_65%] items-center gap-2 px-4 py-3 text-xs transition-all duration-200 last:rounded-b-md hover:border-theme-100 hover:!bg-gray-100 dark:border-black dark:bg-gray-800 dark:hover:border-theme-200 dark:hover:!bg-gray-800 [&:nth-child(even)]:bg-gray-50 dark:[&:nth-child(even)]:bg-gray-750"
    >
      <NuxtImg
        v-if="producer.logo"
        :src="producer.logo"
        :alt="producer.name"
        class="size-7 rounded-full border border-gray-200 object-contain p-0.5 dark:border-gray-800"
      />
      <div
        v-else
        class="grid size-7 place-items-center rounded-full border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800"
      >
        <font-awesome-icon
          :icon="['fas', 'building']"
          class="text-[10px] text-gray-400 dark:text-gray-800"
        />
      </div>
      <div
        class="overflow-hidden hyphens-auto text-pretty break-words"
        style="hyphenate-character: &quot;—&quot;"
      >
        <span class="font-medium text-gray-800 dark:text-gray-100">{{ producer.name }}</span>
      </div>
      <div class="grid grid-cols-2 gap-1">
        <div class="overflow-hidden truncate">
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ money.getCurrencySymbol() }}
          </span>
          {{ money.removeCurrencySymbol(bestPrice(producer).display_selling_price_ex) }}
          <p class="text-xs text-gray-400 dark:text-gray-400">
            {{ $t("Price from") }}
          </p>
        </div>
        <div class="overflow-hidden truncate">
          <span>
            <font-awesome-icon
              :icon="['far', 'truck']"
              class="text-2xs text-gray-500 dark:text-gray-400"
            />
            {{ bestPrice(producer).dlv.actual_days }}
          </span>
          <p class="text-xs text-gray-400 dark:text-gray-400">
            {{ $t("days", bestPrice(producer).dlv.actual_days) }}
          </p>
        </div>
      </div>
    </li>
  </ul>
</template>

<script setup>
const props = defineProps({
  producers: {
    type: Array,
    required: true,
  },
});

const money = useMoney();

const sortedProducers = computed(() =>
  [...props.producers].sort((a, b) => a.prices[0].p - b.prices[0].p),
);

const bestPrice = (p) => p.prices.reduce((a, b) => (b.p < a.p ? b : a));
</script>
