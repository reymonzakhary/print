<template>
  <div class="flex items-center gap-1">
    <font-awesome-icon :icon="['fas', 'truck']" class="text-xs" />
    <span>{{ displayValue }}</span>
  </div>
</template>

<script setup>
const props = defineProps({
  value: {
    type: [Number, String],
    default: null,
  },
});

const { t: $t } = useI18n();

const displayValue = computed(() => {
  if (props.value === undefined || props.value === null) return "N/A";
  return `${props.value} ${$t("days")}`;
});
</script>
