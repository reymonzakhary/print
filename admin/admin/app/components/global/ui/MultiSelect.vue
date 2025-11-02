<template>
  <div v-if="isLoading" class="p-1 input">
    <UILoader class="text-base" />
  </div>
  <div v-else class="relative">
    <div
      :id="name"
      class="px-2 py-1 w-full text-black rounded border dark:text-white dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300 cursor-pointer min-h-[2.25rem] flex items-center justify-between"
      :class="{
        'border-1 border-red-500 text-red-500': errors.length > 0,
        'cursor-not-allowed !bg-gray-100 !hover:bg-gray-100': disabled,
        'bg-red': !disabled,
      }"
      tabindex="0"
      @click="toggleDropdown"
      @keydown.enter="toggleDropdown"
      @keydown.escape="closeDropdown"
    >
      <div class="flex-1 flex flex-wrap gap-1">
        <span v-if="selectedOptions.length === 0" class="text-gray-500 dark:text-gray-400">
          {{ `Select ${name.charAt(0).toUpperCase() + name.slice(1)}` }}
        </span>
        <span
          v-for="selected in selectedOptions"
          :key="selected[optionValue]"
          class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-sm"
        >
          {{
            displayProperty === "display_name"
              ? $display_name(selected[displayProperty])
              : selected[displayProperty]
          }}
          <button
            v-if="!disabled"
            class="ml-1 hover:text-red-600 dark:hover:text-red-400"
            type="button"
            @click.stop="removeSelection(selected[optionValue])"
          >
            ×
          </button>
        </span>
      </div>
      <div class="flex items-center">
        <svg
          class="w-4 h-4 transition-transform duration-200"
          :class="{ 'rotate-180': isOpen }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </div>
    </div>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-lg max-h-60 overflow-hidden"
    >
      <!-- Search Input -->
      <div
        class="sticky top-0 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 p-2"
      >
        <div class="relative">
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            placeholder="Search options..."
            class="w-full px-3 py-2 pl-8 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
            @click.stop
            @keydown.escape="closeDropdown"
          />
          <svg
            class="absolute left-2 top-2.5 w-4 h-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
      </div>

      <!-- Options List -->
      <div class="max-h-48 overflow-y-auto">
        <div
          v-for="option in filteredOptions"
          :key="option[optionValue]"
          class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer flex items-center gap-2"
          :class="{
            'opacity-50 cursor-not-allowed': option.disabled,
            'bg-blue-50 dark:bg-blue-900/20': isSelected(option[optionValue]),
          }"
          @click.stop="!option.disabled && toggleSelection(option)"
        >
          <input
            type="checkbox"
            :checked="isSelected(option[optionValue])"
            :disabled="option.disabled"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            @click.stop
          />
          <span class="flex-1" v-html="highlightMatch(option)" />
        </div>

        <div
          v-if="filteredOptions.length === 0 && searchQuery"
          class="px-3 py-2 text-gray-500 dark:text-gray-400"
        >
          No options found for "{{ searchQuery }}"
        </div>

        <div v-if="options.length === 0" class="px-3 py-2 text-gray-500 dark:text-gray-400">
          No options available
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  value: {
    type: Array,
    default: () => [],
  },
  rules: {
    type: [String, Object, undefined],
    default: undefined,
  },
  invalid: {
    type: Boolean,
    default: false,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: "option",
  },
  options: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  displayProperty: {
    type: String,
    default: "label",
  },
  optionValue: {
    type: String,
    default: "value",
  },
  searchable: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["input", "update:modelValue"]);

const isOpen = ref(false);
const searchQuery = ref("");
const searchInput = ref(null);
const dropdownRef = ref(null);
const triggerRef = ref(null);
const dropdownPosition = ref("bottom");
const maxDropdownHeight = ref(240);

const selectedValues = computed(() => {
  return props.modelValue.length > 0 ? props.modelValue : props.value;
});

const selectedOptions = computed(() => {
  return props.options.filter((option) => selectedValues.value.includes(option[props.optionValue]));
});

const filteredOptions = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.options;
  }

  const query = searchQuery.value.toLowerCase();
  return props.options.filter((option) => {
    const displayText =
      props.displayProperty === "display_name"
        ? option[props.displayProperty]
        : option[props.displayProperty];
    return displayText.toLowerCase().includes(query);
  });
});

const toggleDropdown = async () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
      await nextTick();
      calculateDropdownPosition();
      // Focus search input when dropdown opens
      if (searchInput.value) {
        searchInput.value.focus();
      }
    } else {
      // Clear search when dropdown closes
      searchQuery.value = "";
    }
  }
};

const calculateDropdownPosition = () => {
  if (!triggerRef.value || !dropdownRef.value) return;

  const triggerRect = triggerRef.value.getBoundingClientRect();
  const dropdownHeight = dropdownRef.value.scrollHeight;
  const viewportHeight = window.innerHeight;
  const spaceBelow = viewportHeight - triggerRect.bottom - 8;
  const spaceAbove = triggerRect.top - 8;

  if (spaceBelow >= Math.min(dropdownHeight, 240)) {
    dropdownPosition.value = "bottom";
    maxDropdownHeight.value = Math.min(spaceBelow, 240);
  } else if (spaceAbove >= Math.min(dropdownHeight, 240)) {
    dropdownPosition.value = "top";
    maxDropdownHeight.value = Math.min(spaceAbove, 240);
  } else {
    if (spaceBelow > spaceAbove) {
      dropdownPosition.value = "bottom";
      maxDropdownHeight.value = spaceBelow;
    } else {
      dropdownPosition.value = "top";
      maxDropdownHeight.value = spaceAbove;
    }
  }
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = "";
};

const isSelected = (value) => {
  return selectedValues.value.includes(value);
};

const toggleSelection = (option) => {
  if (props.disabled) return;

  const value = option[props.optionValue];
  let newSelection = [...selectedValues.value];

  if (isSelected(value)) {
    newSelection = newSelection.filter((v) => v !== value);
  } else {
    newSelection.push(value);
  }
  searchQuery.value = "";
  emit("input", newSelection);
  emit("update:modelValue", newSelection);
  handleChange({ target: { value: newSelection } });
};

const removeSelection = (value) => {
  if (props.disabled) return;

  const newSelection = selectedValues.value.filter((v) => v !== value);
  emit("input", newSelection);
  emit("update:modelValue", newSelection);
  handleChange({ target: { value: newSelection } });
};

const highlightMatch = (option) => {
  if (!searchQuery.value.trim()) {
    const displayText =
      props.displayProperty === "display_name"
        ? option[props.displayProperty]
        : option[props.displayProperty];
    return displayText;
  }

  const displayText =
    props.displayProperty === "display_name"
      ? option[props.displayProperty]
      : option[props.displayProperty];

  const query = searchQuery.value.toLowerCase();
  const text = displayText.toLowerCase();
  const index = text.indexOf(query);

  if (index === -1) return displayText;

  return displayText;
};

// Close dropdown when clicking outside or on window resize
const handleClickOutside = (event) => {
  if (!event.target.closest(`#${props.name}`)) {
    closeDropdown();
  }
};

const handleResize = () => {
  if (isOpen.value) {
    calculateDropdownPosition();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("resize", handleResize);
  window.addEventListener("scroll", handleResize);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("resize", handleResize);
  window.removeEventListener("scroll", handleResize);
});

const name = toRef(props, "name");
const {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  value: inputValue,
  handleChange,
  errors,
} = useField(name, props.rules, {
  initialValue: props.value.length > 0 ? props.value : props.modelValue,
});
</script>
