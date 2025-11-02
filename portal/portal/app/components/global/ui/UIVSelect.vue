<template>
  <div class="relative">
    <v-select
      v-bind="$attrs"
      v-model="modelValue"
      :loading="loading"
      class="ui-v-select input rounded border border-gray-200 bg-white p-0 text-sm transition-all duration-100 dark:border-gray-900 dark:bg-gray-700 dark:text-white"
      :class="{ 'pl-5': icon.length > 0, '!bg-gray-50 dark:!bg-gray-800': isDisabled }"
      :options="options"
      :disabled="isDisabled"
      :get-option-label="getOptionLabel"
      :get-option-key="getOptionKey"
      :deselect-from-dropdown="deselectFromDropdown"
    >
      <template #spinner="{ loading: isLoading }">
        <font-awesome-icon
          v-if="isLoading"
          icon="spinner-third"
          class="fa-spin absolute text-theme-500"
        />
      </template>
      <template v-if="!$slots.option && typeof options[0] === 'object'" #option="option">
        <div :class="cn(option.icon && 'grid grid-cols-[0.75rem_auto] items-center')">
          <div v-if="option.icon" class="-ml-3 text-gray-400">
            <font-awesome-icon :icon="option.icon" />
          </div>
          <span>{{ option.label ?? getOptionLabel(option) }}</span>
        </div>
      </template>
      <template v-else-if="$slots.option" #option="optionProps">
        <slot name="option" v-bind="optionProps" />
      </template>
    </v-select>
    <font-awesome-icon
      v-if="icon.length > 0"
      class="absolute left-2 top-[.54rem] aspect-square text-sm text-gray-400"
      :icon="icon"
    />
  </div>
</template>

<script setup>
const props = defineProps({
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
  getOptionLabel: {
    type: Function,
    required: false,
    default: undefined,
  },
  getOptionKey: {
    type: Function,
    required: false,
    default: undefined,
  },
  deselectFromDropdown: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const { cn } = useUtilities();

const isDisabled = computed(() => props.disabled);
const modelValue = defineModel({ type: [Number, String, Object, null] });
</script>

<style lang="scss">
.ui-v-select .vs__search::placeholder,
.ui-v-select .vs__dropdown-toggle,
.ui-v-select .vs__dropdown-menu {
  @apply text-sm text-gray-800 dark:text-white;
}

.ui-v-select .vs__dropdown-menu,
.ui-v-select .vs__selected {
  @apply text-sm dark:bg-gray-700 dark:text-white;
}

.vs--disabled .vs__dropdown-toggle,
.vs--disabled .vs__clear,
.vs--disabled .vs__search,
.vs--disabled .vs__selected,
.vs--disabled .vs__open-indicator {
  @apply dark:!bg-gray-800 dark:text-white;
}
</style>
