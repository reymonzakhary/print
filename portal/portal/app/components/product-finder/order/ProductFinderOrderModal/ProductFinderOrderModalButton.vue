<template>
  <button
    class="flex-1 rounded-md bg-theme-500 px-4 py-2 text-sm text-white"
    :class="[
      {
        'cursor-not-allowed opacity-50': disabled,
        'hover:bg-theme-600': !disabled,
      },
    ]"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <font-awesome-icon v-if="loading" :icon="['fas', 'spinner']" class="mr-1.5 animate-spin" />
    <font-awesome-icon v-else-if="icon" :icon="icon" class="mr-1.5" />
    <template v-if="activeId">
      {{ $t("Add all to {type}", { type: displayType }) }} #{{ activeId }}
    </template>
    <template v-else>
      {{ $t("Place {type}", { type: displayType }) }}
    </template>
  </button>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: "order",
  },
  icon: {
    type: Array,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  activeId: {
    type: String,
    default: null,
  },
});

defineEmits(["click"]);

const { t: $t } = useI18n();
const displayType = computed(() => {
  if (props.type === "order") return $t("Order");
  if (props.type === "quotation") return $t("Quotation");
  return props.type;
});
</script>
