<template>
  <div class="relative">
    <div
      class="relative flex rounded border border-gray-200 bg-white transition dark:border-gray-500 dark:bg-gray-500"
      :class="{
        'border-red-500': errors.length > 0,
        'cursor-not-allowed opacity-50': disabled,
      }"
    >
      <!-- Country Code Selector -->
      <div class="relative">
        <button
          type="button"
          :disabled="disabled"
          class="flex h-full min-w-[80px] cursor-pointer appearance-none items-center justify-between border-none bg-transparent px-2 py-1 text-sm font-normal outline-none"
          :class="{
            '!py-1': scale === 'sm',
            '!py-2': scale === 'md',
            'cursor-not-allowed bg-gray-100': disabled,
            'text-red-500': errors.length > 0,
          }"
          @click="toggleDropdown"
        >
          <span v-if="selectedCountry"
            >{{ getFlagEmoji(selectedCountry?.iso2) }} ( {{ selectedCountry?.dial_code }} )</span
          >
          <span v-else>Select</span>
          <svg
            class="ml-1 h-4 w-4 text-gray-400 transition-transform duration-200"
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
        </button>

        <!-- Dropdown -->
        <div
          v-if="isOpen"
          ref="dropdown"
          class="absolute z-50 mt-1 max-h-80 w-80 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800"
        >
          <!-- Search Input -->
          <div class="border-b border-gray-200 p-3 dark:border-gray-600">
            <div class="relative">
              <svg
                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform text-gray-400"
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
              <input
                ref="searchInput"
                v-model="searchTerm"
                type="text"
                placeholder="Search countries..."
                class="w-full rounded-md border border-gray-200 py-2 pl-10 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                @keydown="handleKeyDown"
              />
              <button
                v-if="searchTerm"
                class="absolute right-2 top-1/2 -translate-y-1/2 transform text-gray-400 hover:text-gray-600"
                @click="clearSearch"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>

          <!-- Countries List -->
          <div class="max-h-60 overflow-y-auto">
            <template v-if="filteredCountries.length > 0">
              <div
                v-for="(country, index) in filteredCountries"
                :key="`${country.code}-${country.dial_code}-${index}`"
                class="flex cursor-pointer items-center gap-3 px-4 py-3 transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700"
                :class="{
                  'bg-blue-50 dark:bg-blue-900/20': highlightedIndex === index,
                  'bg-gray-50 dark:bg-gray-700': selectedCountry?.dial_code === country.dial_code,
                }"
                @click="selectCountry(country)"
              >
                <span class="flex-shrink-0 text-lg">{{ getFlagEmoji(country.iso2) }}</span>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <span class="truncate font-medium text-gray-900 dark:text-white">{{
                      country.name
                    }}</span>
                    <span class="flex-shrink-0 text-sm text-gray-500 dark:text-gray-400">{{
                      country.dial_code
                    }}</span>
                  </div>
                </div>
                <svg
                  v-if="selectedCountry?.dial_code === country.dial_code"
                  class="h-4 w-4 flex-shrink-0 text-blue-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
            </template>
            <div v-else class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
              No countries found for "{{ searchTerm }}"
            </div>
          </div>
        </div>
      </div>

      <!-- Separator -->
      <div class="w-px self-stretch bg-gray-300 dark:bg-gray-400" />

      <!-- Phone Number Input -->
      <input
        :id="name"
        ref="inputField"
        :name="name"
        type="tel"
        :value="phone"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        class="min-w-4 flex-1 border-none bg-transparent px-2 font-normal outline-none"
        :class="[
          {
            '!py-1': scale === 'sm',
            '!py-2': scale === 'md',
            'border-1 border-red-500 text-red-500': errors.length > 0,
            'cursor-not-allowed bg-gray-100 hover:bg-gray-100': disabled,
          },
          inputClass,
        ]"
        :disabled="disabled"
        @input="handlePhoneChange"
        @blur="handleTheBlur"
        @focus="$event.target.select()"
      />

      <!-- Icon -->
      <font-awesome-icon
        v-if="icon.length > 0"
        class="absolute right-0 top-0 mr-2 origin-center translate-y-1/2"
        :class="{ 'text-red-600': errors.length > 0 }"
        :icon="icon"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick, toRef } from "vue";

function getFlagEmoji(countryCode) {
  if (!countryCode) return "";
  return countryCode
    .toUpperCase()
    .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt()));
}

const props = defineProps({
  modelValue: {
    type: [String, Object],
    default: () => ({ dial_code: "+1", phone: "" }),
  },
  rules: {
    type: [String, Object, undefined],
    default: undefined,
  },
  scale: {
    type: [String, Number],
    default: "sm",
  },
  placeholder: {
    type: String,
    default: "Phone number...",
  },
  name: {
    type: String,
    required: true,
  },
  icon: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  inputClass: {
    type: String,
    default: "",
  },
  autocomplete: {
    type: String,
    default: "tel",
  },
  countries: {
    type: Array,
    default: () => [
      { code: "US", flag: "🇺🇸", dial_code: "+1", name: "United States", iso2: "us" },
      { code: "GB", flag: "🇬🇧", dial_code: "+44", name: "United Kingdom", iso2: "gb" },
      { code: "CA", flag: "🇨🇦", dial_code: "+1", name: "Canada", iso2: "ca" },
      { code: "AU", flag: "🇦🇺", dial_code: "+61", name: "Australia", iso2: "au" },
      { code: "DE", flag: "🇩🇪", dial_code: "+49", name: "Germany", iso2: "de" },
      { code: "FR", flag: "🇫🇷", dial_code: "+33", name: "France", iso2: "fr" },
      { code: "JP", flag: "🇯🇵", dial_code: "+81", name: "Japan", iso2: "jp" },
      { code: "CN", flag: "🇨🇳", dial_code: "+86", name: "China", iso2: "cn" },
      { code: "IN", flag: "🇮🇳", dial_code: "+91", name: "India", iso2: "in" },
      { code: "BR", flag: "🇧🇷", dial_code: "+55", name: "Brazil", iso2: "br" },
      { code: "EG", flag: "🇪🇬", dial_code: "+20", name: "Egypt", iso2: "eg" },
      { code: "SA", flag: "🇸🇦", dial_code: "+966", name: "Saudi Arabia", iso2: "sa" },
      { code: "AE", flag: "🇦🇪", dial_code: "+971", name: "United Arab Emirates", iso2: "ae" },
      { code: "TR", flag: "🇹🇷", dial_code: "+90", name: "Turkey", iso2: "tr" },
      { code: "IT", flag: "🇮🇹", dial_code: "+39", name: "Italy", iso2: "it" },
      { code: "ES", flag: "🇪🇸", dial_code: "+34", name: "Spain", iso2: "es" },
      { code: "NL", flag: "🇳🇱", dial_code: "+31", name: "Netherlands", iso2: "nl" },
      { code: "BE", flag: "🇧🇪", dial_code: "+32", name: "Belgium", iso2: "be" },
      { code: "CH", flag: "🇨🇭", dial_code: "+41", name: "Switzerland", iso2: "ch" },
      { code: "AT", flag: "🇦🇹", dial_code: "+43", name: "Austria", iso2: "at" },
    ],
  },
});

const emit = defineEmits(["blur", "focus", "update:modelValue"]);

const inputField = ref(null);

// Initialize values from modelValue
const selectedCountry = ref(null);
const phone = ref("");

// Initialize selectedCountry and phone based on modelValue
const initializeValues = () => {
  if (typeof props.modelValue === "object" && props.modelValue) {
    if (props.modelValue.dial_code) {
      const country = props.countries.find(
        (c) => c.dial_code === String(props.modelValue.dial_code),
      );
      selectedCountry.value = country || props.countries[0];
    }
    if (props.modelValue.phone !== undefined) {
      phone.value = props.modelValue.phone;
    }
  } else if (typeof props.modelValue === "string") {
    // Try to parse a full phone number
    const match = props.modelValue.match(/^(\+\d{1,4})(.*)$/);
    if (match) {
      const dial_code = match[1];
      const number = match[2];
      const country = props.countries.find((c) => c.dial_code === String(dial_code));
      if (country) {
        selectedCountry.value = country;
        phone.value = number;
      }
    } else {
      phone.value = props.modelValue;
      selectedCountry.value = props.countries[0]; // Default to first country
    }
  } else {
    // Default initialization
    selectedCountry.value = props.countries[0];
    phone.value = "";
  }
};

// Initialize on mount
onMounted(() => {
  initializeValues();
});

const firstTime = ref(true);
watch(
  [() => props.modelValue, () => props.countries],
  ([newModelValue, newCountries], [oldModelValue]) => {
    if (props.modelValue && props.countries.length > 0 && firstTime.value) {
      firstTime.value = false;
      initializeValues();
    }
  },
);

// Computed values
const currentDialCode = computed(() => {
  return selectedCountry.value ? selectedCountry.value.dial_code : "";
});

const fullphone = computed(() => {
  if (!phone.value) return "";
  return `${currentDialCode.value}${phone.value}`;
});

// Search functionality
const isOpen = ref(false);
const searchTerm = ref("");
const highlightedIndex = ref(-1);
const dropdown = ref(null);
const searchInput = ref(null);

const filteredCountries = computed(() => {
  if (!searchTerm.value || searchTerm.value.trim() === "") {
    return props.countries;
  }

  const term = searchTerm.value.toLowerCase().trim();
  const filtered = props.countries.filter((country) => {
    const nameMatch = country.name && country.name.toLowerCase().includes(term);
    const dialCodeMatch = country.dial_code && country.dial_code.includes(term);
    const codeMatch = country.code && country.code.toLowerCase().includes(term);
    const iso2Match = country.iso2 && country.iso2.toLowerCase().includes(term);

    return nameMatch || dialCodeMatch || codeMatch || iso2Match;
  });
  return filtered;
});

// Search methods
const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  } else {
    searchTerm.value = "";
    highlightedIndex.value = -1;
  }
};

const selectCountry = (country) => {
  selectedCountry.value = country;
  isOpen.value = false;
  searchTerm.value = "";
  highlightedIndex.value = -1;
  handleCountryChange();
};

const clearSearch = () => {
  searchTerm.value = "";
  highlightedIndex.value = -1;
  nextTick(() => {
    searchInput.value?.focus();
  });
};

const handleKeyDown = (event) => {
  const maxIndex = filteredCountries.value.length - 1;

  switch (event.key) {
    case "ArrowDown":
      event.preventDefault();
      highlightedIndex.value = highlightedIndex.value < maxIndex ? highlightedIndex.value + 1 : 0;
      break;
    case "ArrowUp":
      event.preventDefault();
      highlightedIndex.value = highlightedIndex.value > 0 ? highlightedIndex.value - 1 : maxIndex;
      break;
    case "Enter":
      event.preventDefault();
      if (highlightedIndex.value >= 0 && filteredCountries.value[highlightedIndex.value]) {
        selectCountry(filteredCountries.value[highlightedIndex.value]);
      }
      break;
    case "Escape":
      event.preventDefault();
      isOpen.value = false;
      searchTerm.value = "";
      highlightedIndex.value = -1;
      break;
  }
};

const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target) && !event.target.closest("button")) {
    isOpen.value = false;
    searchTerm.value = "";
    highlightedIndex.value = -1;
  }
};

// Handle country change
function handleCountryChange() {
  emitValue();
}

// Handle phone number change
function handlePhoneChange(event) {
  phone.value = event.target.value;
  emitValue();
}

function handleTheBlur(event) {
  emit("blur", event);
  return handleBlur(event);
}

// Emit the combined value
function emitValue() {
  const value = {
    dial_code: currentDialCode.value,
    phone: phone.value,
    fullNumber: fullphone.value,
    countryCode: selectedCountry.value?.code || "",
  };
  emit("update:modelValue", value);
}

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    if (typeof newValue === "object" && newValue) {
      if (newValue.dial_code) {
        const country = props.countries.find((c) => c.dial_code === newValue.dial_code);
        if (country && country.dial_code !== selectedCountry.value?.dial_code) {
          selectedCountry.value = country;
        }
      }
      if (newValue.phone !== undefined && newValue.phone !== phone.value) {
        phone.value = newValue.phone;
      }
    } else if (typeof newValue === "string") {
      // Try to parse a full phone number
      const match = newValue.match(/^(\+\d{1,4})(.*)$/);
      if (match) {
        const dial_code = match[1];
        const number = match[2];
        const country = props.countries.find((c) => c.dial_code === dial_code);
        if (country) {
          selectedCountry.value = country;
          phone.value = number;
        }
      } else {
        phone.value = newValue;
      }
    }
  },
  { deep: true },
);

// Reset highlighted index when search term changes
watch(searchTerm, () => {
  highlightedIndex.value = -1;
});

// Form validation
const name = toRef(props, "name");
const errors = ref([]);
const meta = ref({});
let handleBlur = () => {};

// Try to use useField if available
try {
  if (typeof useField !== "undefined") {
    const fieldResult = useField(name, props.rules, { syncVModel: false });
    errors.value = fieldResult.errors;
    meta.value = fieldResult.meta;
    handleBlur = fieldResult.handleBlur;
  }
} catch (e) {
  console.log("useField not available, using fallback validation");
}

// Lifecycle for dropdown
onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});

// Expose
defineExpose({
  meta,
  inputField,
  selectedCountry,
  phone,
  fullphone,
});
</script>

<style scoped>
.input {
  @apply w-full border-none bg-transparent px-2 py-1 text-sm outline-none;
}

select {
  background-image: none;
}

select::-ms-expand {
  display: none;
}

/* Custom scrollbar for the dropdown */
.max-h-60::-webkit-scrollbar {
  width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-60::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.dark .max-h-60::-webkit-scrollbar-thumb {
  background: #4b5563;
}

.dark .max-h-60::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>
