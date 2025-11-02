<template>
  <article
    class="flex aspect-square w-56 flex-col rounded-md border-b border-r bg-white p-3 shadow shadow-gray-200 hover:shadow-md dark:border-gray-900 dark:bg-gray-700 dark:shadow-gray-900"
  >
    <section>
      <div class="flex items-center justify-between">
        <label for="infinite" class="text-xs font-bold uppercase tracking-wide">
          {{ $t("range:") }}
          {{ _margin.from }}
          -
          <template v-if="_margin.to === -1">
            <font-awesome-icon :icon="['fas', 'infinity']" />
          </template>
          <template v-else>
            {{ _margin.to }}
          </template>
        </label>
        <div class="flex items-center gap-2">
          <UISwitch
            :disabled="!isInfinitable"
            :name="`${margin.id}`"
            :value="_margin.to === -1"
            @input="handleInfiniteToggle"
          />
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </div>
      </div>
      <div class="mt-2 flex gap-2">
        <UIInputText
          v-model="_margin.from"
          :disabled="!isFromEditable"
          name="from"
          placeholder="From"
          type="number"
          class="text-sm"
          :icon="['fal', 'square-sliders-vertical']"
        />
        <template v-if="_margin.to === -1">
          <UIInputText
            model-value="∞"
            name="to"
            placeholder="To"
            class="text-sm"
            :icon="['fal', 'square-sliders-vertical']"
            disabled
          />
        </template>
        <template v-else>
          <UIInputText
            ref="toInputRef"
            :model-value="_margin.to"
            :min="_margin.from + 1"
            name="to"
            placeholder="To"
            type="number"
            class="text-sm"
            :icon="['fal', 'square-sliders-vertical']"
            @update:model-value="debouncedCheckValidity"
          />
        </template>
      </div>
    </section>
    <hr class="-mx-2 my-4" />
    <section>
      <div class="mb-2 flex">
        <button
          class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
          :class="{
            'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
              _margin.type === 'percentage',
          }"
          @click="_margin.type = 'percentage'"
        >
          {{ $t("Percentage") }}
        </button>
        <button
          class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
          :class="{
            'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
              _margin.type === 'fixed',
          }"
          @click="_margin.type = 'fixed'"
        >
          {{ $t("Fixed") }}
        </button>
      </div>
      <UIInputText
        v-if="_margin.type === 'percentage'"
        v-model="_margin.value"
        name="value"
        placeholder="33"
        type="number"
        class="text-sm"
        :icon="_margin.type === 'percentage' ? ['fal', 'percentage'] : ['fal', 'euro-sign']"
      />
      <UICurrencyInput
        v-else
        :model-value="_margin.value / 1000"
        input-class="w-full !p-1 border-green-500 ring-green-200 focus:border-green-500 text-sm"
        name="value"
        @update:model-value="_margin.value = $event * 1000"
      />
    </section>
    <footer class="flex flex-grow items-end justify-end">
      <UIButton
        variant="inverted-danger"
        :icon="['fal', 'trash-can']"
        :disabled="!isDeletable"
        @click="handleRemoveMargin"
      >
        {{ $t("Delete Margin") }}
      </UIButton>
    </footer>
  </article>
</template>

<script setup>
import debounce from "debounce-promise";

const props = defineProps({
  margin: {
    type: Object,
    required: true,
  },
  isDeletable: {
    type: Boolean,
    default: false,
  },
  isInfinitable: {
    type: Boolean,
    default: false,
  },
  isFromEditable: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["update:margin", "on-remove-margin"]);

const _margin = ref({
  from: 0,
  to: 0,
  type: "percentage",
  value: 0,
});
watch(
  () => props.margin,
  (value) => {
    _margin.value.from = value.from;
    _margin.value.to = value.to;
    _margin.value.type = value.type;
    _margin.value.value = value.value;
  },
  { immediate: true, deep: true },
);
watch(
  () => _margin.value.from,
  (value) => {
    if (_margin.value.to !== -1 && _margin.value.to < value) {
      _margin.value.to = _margin.value.from + 1;
    }
  },
);
watch(_margin.value, (value) => emit("update:margin", value));

const toInputRef = useTemplateRef("toInputRef");
const debouncedCheckValidity = debounce(handleCheckValidity, 800);
function handleCheckValidity(value) {
  if (value === -1) return (_margin.value.to = value);
  if (value > _margin.value.from) return (_margin.value.to = value);

  toInputRef.value.$el.classList.add("shake");
  setTimeout(() => {
    toInputRef.value.$el.classList.remove("shake");
  }, 250);

  const oldValue = _margin.value.to;
  _margin.value.to = 0;
  nextTick(() => {
    _margin.value.to = oldValue;
  });
}

const handleInfiniteToggle = (checked) => {
  checked ? (_margin.value.to = -1) : (_margin.value.to = _margin.value.from + 1);
};

const handleRemoveMargin = () => {
  emit("on-remove-margin");
};
</script>

<style lang="scss">
.shake {
  animation: horizontal-shaking 0.25s linear;
}

@keyframes horizontal-shaking {
  0% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(5px);
  }
  50% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
  100% {
    transform: translateX(0);
  }
}
</style>
