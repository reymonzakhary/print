<template>
  <div class="flex flex-col">
    <ul v-if="diffBoops.length > 0" class="flex flex-wrap divide-y p-2 pt-4 dark:divide-gray-900">
      <li
        v-for="(boop, index) in diffBoops"
        :key="boop.linked_key"
        class="w-full px-2 py-1"
        :class="{
          'flex-grow-0':
            diffBoops.length === 1 || (diffBoops.length > 2 && index === diffBoops.length - 1),
        }"
      >
        <ProductFinderCompareVariantCardOption :boop="boop" />
      </li>
    </ul>
    <ul
      v-if="commonBoops.length > 0"
      class="h-full divide-y bg-gray-50 p-2 shadow-inner dark:divide-gray-900 dark:bg-gray-800"
    >
      <li v-for="(boop, index) in commonBoops" :key="boop.linked_key" class="w-full px-2 py-1">
        <ProductFinderCompareVariantCardOption :boop="boop" />
      </li>
    </ul>
<!--    <ul-->
<!--      v-if="sameBoops.length > 0"-->
<!--      class="flex flex-wrap items-center gap-x-2 gap-y-1 bg-gray-50 px-4 pb-2 pt-3 shadow-inner dark:bg-gray-800"-->
<!--    >-->
<!--      <li v-for="boop in sameBoops" :key="boop.linked_key">-->
<!--        <ProductFinderCompareVariantCardOptionChip :boop="boop" />-->
<!--      </li>-->
<!--    </ul>-->
  </div>
</template>

<script setup>
const props = defineProps({
  boops: {
    type: Array,
    required: true,
  },
});

const { getDisplayName } = useDisplayName();

// const sameBoops = computed(() =>
//   props.boops
//     .filter((boop) => boop.isSelected || boop.isCommon)
//     .sort(
//       (a, b) =>
//         (getDisplayName(a.value_display_name) ?? a.value).length -
//         (getDisplayName(b.value_display_name) ?? b.value).length,
//     )
//     .sort((a, b) => a.isManual - b.isManual),
// );
const diffBoops = computed(() =>
  props.boops.filter((boop) => !boop.isSelected && (!boop.isCommon || boop.isCommon)),
);
const commonBoops = computed(() => props.boops.filter((boop) => boop.isSelected));
</script>
