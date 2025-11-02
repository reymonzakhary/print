<template>
  <div>
    <UIModalSlideIn
      class="w-1/2"
      :icon="['fas', 'pencil']"
      :title="`Unmatched ${type}: ${unmatchedItem.name}`"
      :show="props.show"
      @on-close="emit('close', $event)"
      @on-backdrop-click="emit('close', $event)"
    >
      <MatchingTool
        :type="type"
        :unmatched-item="unmatchedItem"
        :items="items"
        :item="item"
        :box="box"
        :unlinked="unlinked"
        @close="emit('close', $event)"
      />
    </UIModalSlideIn>
  </div>
</template>
<script setup>
import MatchingTool from "./MatchingTool.vue";

const props = defineProps({
  items: {
    type: [Array, Object],
    default: () => [],
  },
  show: {
    type: Boolean,
    default: false,
  },
  unmatchedItem: {
    type: Object,
    default: () => ({}),
  },
  type: {
    type: String,
    default: "",
  },
  item: {
    type: Object,
    default: () => ({}),
  },
  box: {
    type: Object,
    default: () => ({}),
  },
  unlinked: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close"]);
</script>

<style></style>
