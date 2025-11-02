<template>
  <label
    :for="name"
    class="relative flex w-10 h-4 transition-all bg-gray-300 rounded-full cursor-pointer"
    :class="{
      'bg-theme-400': value && variant === 'default',
      'bg-green-400': value && variant === 'success',
      'opacity-40 !cursor-not-allowed': disabled,
    }"
  >
    <div
      class="absolute h-full transition-all bg-white border-2 rounded-full aspect-square"
      :class="{
        '-translate-x-full left-full': value,
        'border-theme-400': value && variant === 'default',
        'border-green-400': value && variant === 'success',
        'translate-x-0 left-0 border-gray-300': !value,
      }"
    ></div>
    <input
      :id="name"
      type="checkbox"
      :name="name"
      :checked="!!value"
      :disabled="disabled"
      class="absolute w-0 h-0 appearance-none active:outline-none focus:outline-none"
      @change="handleCheckboxChange"
    />
  </label>
</template>

<script>
export default {
  name: "UISwitch",
  props: {
    value: {
      type: Boolean,
      required: true,
      default: false,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    variant: {
      type: String,
      required: false,
      default: "default",
      validator: function (value) {
        return ["default", "success"].indexOf(value) !== -1;
      },
    },
    name: {
      type: String,
      required: false,
      default: "checkythecheckbox",
    },
  },
  emits: ["input"],
  methods: {
    handleCheckboxChange() {
      if (this.disabled) return;
      this.$emit("input", !this.value);
    },
  },
};
</script>
