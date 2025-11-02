<template>
  <div>
    <div v-if="box.type === 'select'">
      <div class="flex flex-wrap gap-2">
        <ProductFinderSidebarOption
          v-for="option in box?.ops.slice(0, showAll ? box?.ops.length : 4)"
          :key="option?.id"
          :class="cn(showAll && 'max-w-fit', size === 'xs' && 'text-xs')"
          :name="$display_name(option?.display_name)"
          :disabled="
            checkIfHasGeneralExclude(option?.linked, selectedOptions, manifest, box?.linked)
          "
          :is-selected="selectedOptions[box?.linked]?.linked === option?.linked"
          @click="handleOptionSelect({ boop: props.box, option: option })"
        />
      </div>
      <ProductFinderSidebarOption
        v-if="box?.ops.length > 4 && !showAll"
        :disabled="
          disabled ||
          checkIfHasGeneralExclude(option?.linked, selectedOptions, manifest, box?.linked)
        "
        :class="cn('mt-2 flex w-full items-center justify-between', size === 'xs' && 'text-xs')"
        :is-selected="
          box?.ops
            .slice(4)
            .some((option) => selectedOptions[box?.linked]?.linked === option?.linked)
        "
        @click="showModal = true"
      >
        <span :class="size === 'xs' && 'text-xs'">
          +{{ box?.ops.length - 4 }} {{ $t("more") }}
        </span>
        <font-awesome-icon :icon="['fas', 'chevron-down']" />
      </ProductFinderSidebarOption>
      <ProductFinderSidebarOptionDialog
        v-if="showModal"
        :box="box"
        :selected-options="selectedOptions"
        @close="showModal = false"
        @select-option="handleOptionSelect"
      />
    </div>
    <UIInputText
      v-if="box.type === 'number'"
      ref="boxInput"
      :model-value="selectedOptions[box?.linked] ?? ''"
      type="number"
      :name="box?.name"
      placeholder="0"
      :class="cn('w-full', size === 'xs' && 'text-sm')"
      :disabled="disabled"
      @input="debouncedSelect({ boop: box, option: $event.target.value })"
    />
  </div>
</template>

<script setup>
import { checkIfHasGeneralExclude } from "~/composables/market-place/helper.js";

const props = defineProps({
  box: {
    type: Object,
    required: true,
  },
  selectedOptions: {
    type: Object,
    required: true,
  },
  showAll: {
    type: Boolean,
    required: false,
    default: false,
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  supportedOptions: {
    type: Array,
    required: false,
    default: () => [],
  },
  focusOnMount: {
    type: Boolean,
    required: false,
    default: false,
  },
  size: {
    type: String,
    required: false,
    default: "sm",
    validator(value) {
      return ["xs", "sm"].includes(value);
    },
  },
  manifest: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const emit = defineEmits(["select-option"]);

const { cn } = useUtilities();

const boxInput = ref(null);
watch(boxInput, () => {
  if (boxInput.value && props.focusOnMount) {
    boxInput.value.$el.querySelector("input").focus();
  }
});

const showModal = ref(false);

const handleOptionSelect = ({ boop, option }) => {
  emit("select-option", { boop, option });
};
const debouncedSelect = useDebounceFn((payload) => handleOptionSelect(payload), 500);
</script>
