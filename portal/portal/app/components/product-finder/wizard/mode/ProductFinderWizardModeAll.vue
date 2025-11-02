<template>
  <div>
    <div v-for="(collection, key) in groupedByDivider" :key="key">
      <div
        v-for="box in collection.filter((boop) => boop.id === 'quantity')"
        :key="box.id"
        class="mb-4"
      >
        <h4 class="mb-2 font-semibold">
          {{ $display_name(box.display_name) }}
        </h4>
        <ProductFinderUIBoxInput
          focus-on-mount
          :box="box"
          :manifest="manifests"
          :selected-options="selectedOptions"
          @select-option="handleOptionSelect"
        />
      </div>
      <h3 v-if="key" class="mb-4 text-xl font-semibold">
        {{ key }}
      </h3>
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div v-for="box in collection.filter((boop) => boop.id !== 'quantity')" :key="box.id">
          <h4 class="mb-2 font-semibold">{{ $display_name(box.display_name) }}</h4>
          <ProductFinderUIBoxInput
            :box="box"
            :disabled="!selectedOptions['quantity'] && box?.id !== 'quantity'"
            :selected-options="selectedOptions"
            :manifest="manifests"
            @select-option="handleOptionSelect"
          />
        </div>
      </div>
    </div>
    <ProductFinderWizardNavButtons
      mode="all"
      :disabled="!selectedOptions['quantity']"
      :enable-calculation="enableCalculation"
      @start-calculation="emit('start-calculation')"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  selectedOptions: {
    type: Object,
    required: true,
  },
  enableCalculation: {
    type: Boolean,
    required: true,
  },
  manifests: {
    type: Array,
    default: () => [],
  },
});

const groupedByDivider = computed(() => {
  return props.category.boops.reduce((acc, item) => {
    const dividerName = item.dividerName || "";
    if (!acc[dividerName]) acc[dividerName] = [];
    acc[dividerName].push(item);
    return acc;
  }, {});
});

const emit = defineEmits(["select-option", "start-calculation"]);

const handleOptionSelect = (option) => {
  emit("select-option", option);
};
</script>
