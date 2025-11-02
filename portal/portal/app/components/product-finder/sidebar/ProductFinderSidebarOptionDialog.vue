<!-- app/components/product-finder/UI/OptionsModal.vue -->
<template>
  <Teleport to="body">
    <ConfirmationModal
      classes="w-11/12 sm:w-1/2 lg:w-1/3 xl:w-1/3 2xl:w-1/4"
      @on-close="emit('close')"
    >
      <template #modal-header>
        {{ $t("Select an option") }}
      </template>

      <template #modal-body>
        <ProductFinderSearchInput
          v-model="searchQuery"
          placeholder="Zoek optie"
          class="mb-4"
          input-class="py-2"
        />
        <div class="flex flex-wrap gap-2">
          <ProductFinderSidebarOption
            v-for="option in queriedOptions"
            :key="option?.id"
            :class="'max-w-fit'"
            :name="$display_name(option?.display_name)"
            :is-selected="selectedOptions[box?.linked]?.linked === option?.linked"
            @click="handleOptionSelect({ boop: box, option: option })"
          />
        </div>
      </template>

      <template #confirm-button>
        <ModalButton variant="success" :disabled="disabled" @click="emit('close')">
          {{ $t("Done") }}
        </ModalButton>
      </template>
    </ConfirmationModal>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  box: {
    type: Object,
    default: () => ({}),
  },
  selectedOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "select-option"]);

const { searchQuery, filteredResults: queriedOptions } = useFuzzySearch(props.box?.ops, {
  keys: ["name"],
});

function handleOptionSelect({ boop, option }) {
  emit("select-option", { boop, option });
}
</script>

<style scoped>
/* Scrollbar styling for modal options */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  @apply rounded-lg bg-gray-100;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  @apply rounded-lg bg-gray-400 hover:bg-gray-500;
}
</style>
