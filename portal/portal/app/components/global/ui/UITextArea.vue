<template>
  <div ref="container" class="all grow-wrap relative w-full overflow-hidden">
    <textarea
      :class="[
        { 'cursor-not-allowed bg-gray-100 hover:bg-gray-100': disabled },
        `input max-w-full ${backgroundColor}`,
      ]"
      :value="theValue"
      :maxlength="maxLength"
      :disabled="disabled"
      :rows="rows"
      @input="handleInput"
      @blur="$emit('blur', $event.target.value)"
      @focus="$emit('focus', $event.target.value)"
    />
    <span
      v-if="!disabled"
      class="absolute bottom-1 right-2 text-sm font-medium"
      :class="{
        'text-orange-500':
          maxLength && theValue.length > maxLength - maxLength / 10 && theValue.length < maxLength,
        'text-red-500': maxLength && theValue.length >= maxLength,
      }"
      >{{ theValue.length }} / {{ maxLength || "∞" }}</span
    >
  </div>
</template>

<script>
export default {
  name: "UITextArea",
  props: {
    modelValue: {
      type: String,
      default: "",
    },
    maxLength: {
      type: [Number, Boolean, String],
      default: false,
    },
    rows: {
      type: [Number, String],
      default: 3,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    backgroundColor: {
      type: String,
      default: "",
    },
  },
  emits: ["input", "update:modelValue", "blur", "focus"],
  computed: {
    theValue() {
      return this.modelValue ?? "";
    },
  },
  watch: {
    modelValue(value) {
      this.$refs.container.dataset.replicatedValue = value;
    },
  },
  async mounted() {
    await nextTick();
    if (this.modelValue) {
      this.$refs.container.dataset.replicatedValue = this.modelValue;
    }
  },
  methods: {
    handleInput(event) {
      const value = event.target.value;
      this.$refs.container.dataset.replicatedValue = this.modelValue;
      if (this.maxLength && value.length > this.maxLength) return;
      this.$emit("input", value);
      this.$emit("update:modelValue", value);
    },
  },
};
</script>

<style lang="scss" scoped>
.grow-wrap {
  /* easy way to plop the elements on top of each other and have them both sized based on the tallest one's height */
  display: grid;
}

.grow-wrap::after {
  /* Note the weird space! Needed to preventy jumpy behavior */

  content: attr(data-replicated-value) " ";

  /* This is how textarea text behaves */
  white-space: pre-wrap;

  /* Hidden from view, clicks, and screen readers */
  visibility: hidden;
}

.grow-wrap > textarea {
  /* You could leave this, but after a user resizes, then it ruins the auto sizing */
  resize: none;

  /* Firefox shows scrollbar on growth, you can hide like this. */
  overflow: hidden;
}

.grow-wrap > textarea,
.grow-wrap::after {
  /* Identical styling required!! */
  padding: 0.5rem;
  font: inherit;

  /* Place on top of each other */
  grid-area: 1 / 1 / 2 / 2;
}
</style>
