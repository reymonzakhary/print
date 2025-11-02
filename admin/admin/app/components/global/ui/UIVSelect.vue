<template>
  <div class="relative">
    <v-select
      v-bind="$attrs"
      :model-value="props.modelValue"
      :loading="props.loading"
      class="text-sm transition-all duration-100 bg-white border border-gray-200 rounded dark:bg-gray-700 dark:border-gray-900 dar:text-white ui-v-select"
      :class="{ 'pl-5': props.icon.length > 0, '!bg-gray-50 dark:!bg-gray-800': isDisabled }"
      :options="props.options"
      :disabled="isDisabled"
      @update:model-value="emit('update:modelValue', $event)"
    >
      <template #spinner="{ loading: isLoading }">
        <font-awesome-icon
          v-if="isLoading"
          icon="spinner-third"
          class="absolute fa-spin text-theme-500"
        />
      </template>
      <template v-if="$slots.option" #option="optionProps">
        <slot name="option" v-bind="optionProps" />
      </template>
    </v-select>
    <font-awesome-icon
      v-if="props.icon.length > 0"
      class="absolute text-sm text-gray-400 top-[.54rem] left-2 aspect-square"
      :icon="props.icon"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [Number, String, Object, null],
    required: true,
  },
  options: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    required: false,
    default: false,
  },
  icon: {
    type: Array,
    required: false,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const isDisabled = computed(() => props.disabled);
</script>

<style lang="scss">
.ui-v-select .vs__search::placeholder,
.ui-v-select .vs__dropdown-toggle,
.ui-v-select .vs__dropdown-menu {
  @apply text-sm dark:text-white text-gray-800;
}

.ui-v-select .vs__dropdown-menu,
.ui-v-select .vs__selected {
  @apply dark:bg-gray-700 dark:text-white text-sm;
}

.vs--disabled .vs__dropdown-toggle,
.vs--disabled .vs__clear,
.vs--disabled .vs__search,
.vs--disabled .vs__selected,
.vs--disabled .vs__open-indicator {
  @apply dark:!bg-gray-800 dark:text-white;
}
</style>
