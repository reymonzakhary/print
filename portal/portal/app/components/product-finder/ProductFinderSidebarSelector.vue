<template>
  <div>
    <ProductFinderSidebarDivider
      v-for="(collection, key) in groupedByDivider"
      :key="key"
      :name="key"
    >
      <ProductFinderSidebarBox
        v-for="box in collection"
        :key="box?.linked"
        :disabled="(!quantityChosen && box?.linked !== 'quantity') || disabled"
        :class="{ 'flex items-center gap-4': box.type === 'number' }"
        :name="$display_name(box?.display_name)"
        :is-collapsible="box?.type !== 'number'"
        :selected-option="$display_name(selectedOptions[box?.linked]?.display_name)"
        :selected="box?.linked === 'quantity' && !!quantityChosen"
        :size="$screen.isXxl ? 'sm' : 'xs'"
        @close-option="handleDeselectOption(box)"
      >
        <ProductFinderUIBoxInput
          :box="box"
          :disabled="(!quantityChosen && box?.linked !== 'quantity') || disabled"
          :supported-options="supportedOptions"
          :selected-options="selectedOptions"
          :manifest="manifest"
          :size="$screen.isXxl ? 'sm' : 'xs'"
          @select-option="handleOptionSelect"
        />
      </ProductFinderSidebarBox>
    </ProductFinderSidebarDivider>
  </div>
</template>

<script setup>
const props = defineProps({
  filterData: {
    type: Array,
    default: () => [],
  },
  selectedOptions: {
    type: Object,
    default: () => ({}),
  },
  supportedOptions: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  manifest: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const groupedByDivider = computed(() => {
  return props.filterData.reduce((acc, item) => {
    const dividerName = item.dividerName || "";
    if (!acc[dividerName]) acc[dividerName] = [];
    acc[dividerName].push(item);
    return acc;
  }, {});
});

const emit = defineEmits(["select-option"]);
const showModal = ref(false);

const quantityChosen = computed(() => props.selectedOptions["quantity"]);

const handleOptionSelect = ({ boop, option }) => {
  emit("select-option", { boop, option });
};

const handleDeselectOption = (boop) => {
  emit("select-option", { boop, option: null });
};
</script>
