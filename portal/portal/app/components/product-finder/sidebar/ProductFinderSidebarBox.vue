<template>
  <article
    :class="
      cn(
        'rounded-lg bg-gray-50 outline outline-1 outline-transparent transition-all hover:outline-theme-200 dark:bg-gray-700',
        !isCollapsible && 'py-2 pl-4 pr-2',
        !isCollapsible && size === 'xs' && 'p-3',
        disabled && 'opacity-50',
        (selectedOption || selected) && 'outline-theme-200',
      )
    "
  >
    <header>
      <component
        :is="isCollapsible ? 'button' : 'div'"
        :class="
          cn(
            'flex w-full items-center justify-between text-xs font-medium 2xl:text-xs',
            isCollapsible && 'p-2 px-4 transition-all',
            isCollapsible && !disabled && 'cursor-pointer',
          )
        "
        :disabled="disabled"
        @click="toggleExpanded"
      >
        <h4>{{ name }}</h4>
        <div class="flex items-center gap-2">
          <!-- <ProductFinderSearchChip
            v-if="selectedOption"
            :label="selectedOption"
            read-only
            :size="size"
          /> -->
          <div
            v-if="selectedOption"
            class="rounded-full bg-gradient-to-r from-theme-500 to-purple-500 px-2 py-1 text-white"
            @click="handleDeselectClick({ boop: box, option: option })"
          >
            {{ selectedOption }}
            <font-awesome-icon
              :icon="['fas', 'xmark']"
              class="size-3 text-theme-100 transition-all"
            />
          </div>
          <font-awesome-icon
            v-if="isCollapsible"
            :icon="['fas', 'chevron-right']"
            :class="cn('size-3 text-gray-500 transition-all', isExpanded && 'rotate-90')"
          />
        </div>
      </component>
    </header>
    <main
      :class="
        cn(
          'w-full overflow-hidden transition-all',
          !isExpanded && isCollapsible && 'max-h-[0px]',
          isExpanded && isCollapsible && 'max-h-[1000px]',
        )
      "
    >
      <div v-if="isCollapsible" class="px-4 pb-4">
        <slot />
      </div>
      <slot v-else />
    </main>
  </article>
</template>

<script setup>
const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  isCollapsible: {
    type: Boolean,
    default: false,
  },
  selectedOption: {
    type: String,
    default: null,
  },
  selected: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  producers: {
    type: [Number, String],
    default: 0,
  },
  size: {
    type: String,
    default: "sm",
    validator(value) {
      return ["xs", "sm"].includes(value);
    },
  },
});

const { cn } = useUtilities();

const manuallyExpanded = ref(false);
const _isExpanded = ref(!props.selectedOption);
const isExpanded = computed(() => _isExpanded.value);

const toggleExpanded = () => {
  manuallyExpanded.value = true;
  _isExpanded.value = !_isExpanded.value;
};

const handleDeselectClick = (option) => {
  emit("closeOption", option);
};

const emit = defineEmits(["closeOption"]);

watchEffect(() => {
  if (props.selectedOption && !manuallyExpanded.value) {
    _isExpanded.value = false;
  }
});
</script>
