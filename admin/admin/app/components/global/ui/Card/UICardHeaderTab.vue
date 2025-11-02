<template>
  <div class="tab-container" :class="{ active: active, disabled: disabled }">
    <div class="tab-cover">&nbsp;</div>
    <button class="tab-button" @click="handleTabClick">
      <font-awesome-icon v-if="icon.length > 0" :icon="icon" :class="{ 'mr-1': $slots.default }" />
      {{ label }}
    </button>
  </div>
</template>

<script>
export default {
  name: "UICardHeaderTab",
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    label: {
      type: String,
      default: "English",
    },
    icon: {
      type: Array,
      default: () => [],
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["click"],
  methods: {
    handleTabClick() {
      if (!this.disabled) {
        this.$emit("click");
      }
    },
  },
};
</script>

<style lang="scss">
.tab-container.disabled {
  @apply opacity-50 cursor-not-allowed;
}

.tab-button {
  @apply py-2 px-3 mx-1 font-bold rounded-md hover:bg-theme-200 relative bg-theme-400 dark:bg-theme-400 dark:hover:bg-theme-700  text-themecontrast-400;
}

.tab-container.active .tab-button {
  @apply bg-gray-100 dark:bg-gray-800 dark:text-gray-100 text-gray-900 md:rounded-none md:rounded-t-md;
}

.tab-container {
  position: relative;
  z-index: 10;
  cursor: pointer;
}

.tab-button {
  cursor: pointer;
  @apply w-full md:w-auto;
}

.tab-container.active {
  z-index: 0;
}

.tab-cover {
  display: none;
}

.tab-container.active .tab-cover {
  --height: 0.75rem;
  // display: block;
  position: absolute;
  transform: translateX(-50%);
  left: 50%;
  width: calc(100% + 9px);
  height: var(--height);
  bottom: -5px;
  @apply bg-gray-100 dark:bg-gray-800 hidden md:block;
}

.tab-container.active .tab-cover:before,
.tab-container.active .tab-cover:after {
  content: "";
  position: absolute;
  bottom: 0;
  width: 10px;
  height: var(--height);
  @apply bg-theme-400 dark:bg-theme-400;
}

.tab-container.active .tab-cover:before {
  left: -1px;
  border-bottom-right-radius: 9999px;
}

.tab-container.active .tab-cover:after {
  right: -1px;
  border-bottom-left-radius: 9999px;
}

.tab-container.disabled .tab-button {
  @apply hover:bg-theme-400 hover:dark:bg-theme-800 hover:cursor-not-allowed;
}
</style>
