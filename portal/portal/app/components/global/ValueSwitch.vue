<template>
  <section class="flex items-center" :class="classes">
    <p
      class="text-xs font-bold text-gray-700 uppercase dark:text-white"
      :class="{ 'opacity-50': disabled }"
    >
      <font-awesome-icon v-if="icon" :icon="['fal', icon]" fixed-width class="mr-1 text-base" />
      <template v-if="displayName">{{ name }}</template>
    </p>
    <div
      class="relative flex items-center justify-center"
      :class="[disabled ? 'opacity-40' : '', displayName ? 'md:ml-8' : '']"
    >
      <span class="text-sm">{{ leftValue }}</span>
      <div
        class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
        :class="[checked ? 'bg-theme-400' : 'bg-gray-300']"
      >
        <label
          :for="name"
          class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full"
          :class="[
            !disabled ? 'cursor-pointer' : '',
            checked ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
          ]"
        ></label>
        <input
          :id="name"
          v-model="checked"
          type="checkbox"
          :name="name"
          class="w-full h-full appearance-none active:outline-none focus:outline-none"
          :disabled="disabled"
          @change="check"
        />
      </div>
      <span class="text-sm">{{ rightValue }}</span>
      <div class="w-10 cursor-pointer">
        <font-awesome-icon
          v-if="info"
          v-tooltip="info"
          :icon="['fal', 'circle-info']"
          fixed-width
          class="text-base"
        />
      </div>
    </div>
  </section>
</template>

<script>
/**
 * @deprecated since 07 dec 2023 - will eventually be removed. Use UISwitch instead.
 */
export default {
  name: "ValueSwitch",
  props: {
    name: {
      type: String,
      required: true,
    },
    leftValue: {
      required: false,
      default: "",
      type: String,
    },
    rightValue: {
      required: false,
      default: "",
      type: String,
    },
    setChecked: {
      type: Boolean,
      required: false,
      default: false,
    },
    icon: {
      type: String,
      required: false,
      default: "",
    },
    disabled: {
      type: Boolean,
      required: false,
    },
    displayName: {
      type: Boolean,
      required: false,
      default: true,
    },
    info: {
      default: "",
      type: String,
      required: false,
    },
    classes: {
      default: "",
      type: String,
      required: false,
    },
  },
  data() {
    return {
      checked: false,
    };
  },
  watch: {
    setChecked(v) {
      this.checked = v;
    },
  },
  created() {
    if (this.setChecked) {
      this.checked = true;
    }
    if (process.env.NODE_ENV === "development") {
      console.warn(
        "ValueSwitch is deprecated and will be removed in future releases. Please use UISwitch instead.",
      );
    }
  },
  methods: {
    check(e) {
      this.$emit("checked-value", {
        name: this.name,
        value: e.target.checked,
      });
    },
  },
};
</script>

<style></style>
