<template>
  <article class="flex aspect-square w-56 flex-col rounded bg-white p-3 shadow-md dark:bg-gray-700">
    <section>
      <div class="flex items-center justify-between">
        <label for="infinite" class="text-xs font-bold uppercase tracking-wide">
          {{ $t("range:") }}
          {{ _discount.from }}
          -
          <template v-if="_discount.to === -1">
            <font-awesome-icon :icon="['fas', 'infinity']" />
          </template>
          <template v-else>
            {{ _discount.to }}
          </template>
        </label>
        <div class="flex items-center gap-2">
          <UISwitch
            :disabled="!isInfinitable"
            :name="`${type}-${discount.id}`"
            :value="_discount.to === -1"
            @input="handleInfiniteToggle"
          />
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </div>
      </div>
      <div class="mt-2 flex gap-2">
        <UIInputText
          v-model="_discount.from"
          :disabled="!isFromEditable"
          name="from"
          placeholder="From"
          type="number"
          class="text-sm"
          :icon="['fal', 'square-sliders-vertical']"
        />
        <template v-if="_discount.to === -1">
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
            :model-value="_discount.to"
            :min="_discount.from + 1"
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
              _discount.type === 'percentage',
          }"
          @click="_discount.type = 'percentage'"
        >
          {{ $t("Percentage") }}
        </button>
        <button
          class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
          :class="{
            'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
              _discount.type === 'fixed',
          }"
          @click="_discount.type = 'fixed'"
        >
          {{ $t("Fixed") }}
        </button>
      </div>
      <UIInputText
        v-if="_discount.type === 'percentage'"
        v-model="_discount.value"
        name="value"
        placeholder="33"
        type="number"
        class="text-sm"
        :icon="_discount.type === 'percentage' ? ['fal', 'percentage'] : ['fal', 'euro-sign']"
      />
      <UICurrencyInput
        v-else
        :model-value="_discount.value / 1000"
        input-class="w-full !p-1 border-green-500 ring-green-200 focus:border-green-500 text-sm"
        name="value"
        @update:model-value="_discount.value = $event * 1000"
      />
    </section>
    <footer class="flex flex-grow items-end justify-end">
      <UIButton
        variant="inverted-danger"
        :icon="['fal', 'trash-can']"
        :disabled="!isDeletable"
        @click="handleRemoveDiscount"
      >
        {{ $t("Delete Discount") }}
      </UIButton>
    </footer>
  </article>
</template>

<script setup>
import debounce from "debounce-promise";

const props = defineProps({
  discount: {
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
  type: {
    type: String,
    default: "general",
  },
});
const emit = defineEmits(["update:discount", "on-remove-discount"]);

const _discount = ref({
  from: 0,
  to: 0,
  type: "percentage",
  value: 0,
});
watch(
  () => props.discount,
  (value) => {
    _discount.value.from = value.from;
    _discount.value.to = value.to;
    _discount.value.type = value.type;
    _discount.value.value = value.value;
  },
  { immediate: true, deep: true },
);
watch(
  () => _discount.value.from,
  (value) => {
    if (_discount.value.to !== -1 && _discount.value.to < value) {
      _discount.value.to = _discount.value.from + 1;
    }
  },
);
watch(_discount.value, (value) => emit("update:discount", value));

const toInputRef = useTemplateRef("toInputRef");
const debouncedCheckValidity = debounce(handleCheckValidity, 800);
function handleCheckValidity(value) {
  if (value === -1) return (_discount.value.to = value);
  if (value > _discount.value.from) return (_discount.value.to = value);

  toInputRef.value.$el.classList.add("shake");
  setTimeout(() => {
    toInputRef.value.$el.classList.remove("shake");
  }, 250);

  const oldValue = _discount.value.to;
  _discount.value.to = 0;
  nextTick(() => {
    _discount.value.to = oldValue;
  });
}

const handleInfiniteToggle = (checked) => {
  checked ? (_discount.value.to = -1) : (_discount.value.to = _discount.value.from + 1);
};

const handleRemoveDiscount = () => {
  emit("on-remove-discount");
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
