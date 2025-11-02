<template>
  <div class="relative inline-block">
    <button
      class="inline-flex items-center rounded-lg border border-transparent bg-gray-700 pr-4 text-sm font-medium text-gray-100 shadow-lg outline outline-1 transition-colors duration-150 ease-in-out hover:bg-gray-600"
      :style="{ outlineColor: displayedColor }"
      v-bind="$attrs"
      :aria-expanded="showPicker"
      aria-controls="color-picker-popover-content"
      type="button"
      @click="togglePicker"
    >
      <span
        class="relative mr-1 size-7 overflow-hidden rounded-md border-2 border-gray-700 shadow-inner"
      >
        <span class="block h-full w-full rounded-md" :style="{ backgroundColor: displayedColor }" />
      </span>
      <span>{{ $t("Choose Color") }}</span>
    </button>
    <transition name="fade-popover">
      <div
        v-if="showPicker"
        id="color-picker-popover-content"
        ref="popoverRef"
        class="absolute left-0 top-[calc(100%+10px)] z-[99999] rounded-lg border p-3 shadow-2xl"
        :class="{
          'scheme-studio border-gray-700 bg-gradient-to-b from-gray-800 to-gray-700':
            colorScheme === 'studio',
          'border-gray-200 bg-white': colorScheme === 'default',
        }"
        role="dialog"
        aria-label="Color picker"
      >
        <SketchPicker
          v-if="variant === 'sketch'"
          v-model="colorData"
          :formats="['hex']"
          :disable-alpha="disableAlpha"
        />
        <ChromePicker v-else v-model="colorData" :formats="['hex']" :disable-alpha="disableAlpha" />
      </div>
    </transition>
  </div>
</template>

<script setup>
// https://github.com/linx4200/vue-color?tab=readme-ov-file
import { ChromePicker, SketchPicker } from "vue-color";

defineProps({
  variant: {
    type: String,
    default: "sketch",
  },
  colorScheme: {
    type: String,
    default: "default",
    validator(value) {
      return ["default", "studio"].includes(value);
    },
  },
  disableAlpha: {
    type: Boolean,
    default: true,
  },
});

const colorData = defineModel({ type: String, required: true });

const showPicker = ref(false);
const togglePicker = () => (showPicker.value = !showPicker.value);

// Helper function to convert color to hex
const toHex = (color) => {
  if (!color) return "#000000";
  if (typeof color === "string") {
    // If it's already a hex string, return it
    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
      return color;
    }
    // If it's a named color or other format, convert it
    const tempDiv = document.createElement("div");
    tempDiv.style.color = color;
    document.body.appendChild(tempDiv);
    const computedColor = window.getComputedStyle(tempDiv).color;
    document.body.removeChild(tempDiv);

    // Convert RGB to Hex
    const rgb = computedColor.match(/\d+/g);
    if (rgb) {
      return (
        "#" +
        rgb
          .map((x) => {
            const hex = parseInt(x).toString(16);
            return hex.length === 1 ? "0" + hex : hex;
          })
          .join("")
      );
    }
  }
  if (color && typeof color === "object") {
    if (color.hex) return color.hex;
    if (color.rgba) {
      const { r, g, b } = color.rgba;
      return (
        "#" +
        [r, g, b]
          .map((x) => {
            const hex = Math.round(x).toString(16);
            return hex.length === 1 ? "0" + hex : hex;
          })
          .join("")
      );
    }
  }
  return "#000000";
};

// Watch for changes in colorData and ensure it's always hex
watch(
  colorData,
  (newValue) => {
    const hexValue = toHex(newValue);
    if (hexValue !== newValue) {
      colorData.value = hexValue;
    }
  },
  { immediate: true },
);

const displayedColor = computed(() => toHex(colorData.value));

const popoverRef = ref(null);
onClickOutside(popoverRef, () => (showPicker.value = false));
</script>

<style lang="scss" scoped>
/* Transition for the popover */
.fade-popover-enter-active,
.fade-popover-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}

.fade-popover-enter-from,
.fade-popover-leave-to {
  opacity: 0;
  transform: translateY(-5px); /* Slight upward movement on appear/disappear */
}

.scheme-studio :deep(.vc-chrome-picker .body) {
  @apply rounded-lg border border-gray-700 bg-gray-900 text-gray-900 shadow-xl;
}

:deep(.vc-sketch-picker) {
  @apply border-transparent bg-transparent p-0 shadow-none;
}

:deep(.vc-sketch-picker .vc-input-input) {
  @apply text-gray-900;
}

.scheme-studio :deep(.vc-sketch-picker .vc-input-label) {
  @apply w-[90%] text-gray-400;
}

.scheme-studio :deep(.vc-sketch-picker .presets) {
  @apply border-gray-500;
}

.scheme-studio :deep(.vc-sketch-picker .saturation) {
  @apply rounded-md;
}

.scheme-studio :deep(.vc-sketch-picker .field .vc-input-input) {
  @apply rounded bg-gray-700 text-gray-100 shadow-sm outline outline-1 outline-gray-500;
}

.scheme-studio :deep(.vc-sketch-picker .picker-wrap) {
  @apply rounded shadow-md;
}

.scheme-studio :deep(.vc-chrome-picker .vc-chrome-fields .vc-input-input) {
  @apply rounded border-gray-600 bg-gray-700 text-gray-100 shadow-sm;
}

.scheme-studio :deep(.vc-chrome-picker .vc-chrome-toggle-btn) {
  @apply text-gray-400 hover:text-gray-200;
}

.scheme-studio :deep(.vc-chrome-picker .vc-chrome-active-color) {
  @apply rounded shadow-inner;
}
</style>
