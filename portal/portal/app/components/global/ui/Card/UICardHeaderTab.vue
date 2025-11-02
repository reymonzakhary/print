<template>
  <div
    class="tab-container relative z-10"
    :class="{ 'active z-0': active, 'cursor-not-allowed opacity-50': disabled }"
    role="tab"
    :aria-selected="active"
    :tabindex="active ? 0 : -1"
  >
    <div class="tab-cover hidden">&nbsp;</div>
    <button
      class="tab-button group relative mx-1 w-full rounded-md bg-theme-400 px-3 py-2 font-bold text-themecontrast-400 dark:bg-theme-400 md:w-auto"
      :class="{
        'cursor-not-allowed': disabled,
        'hover:bg-theme-200 dark:hover:bg-theme-700': !disabled,
      }"
      @click="handleTabClick"
    >
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
        this.$emit("click", this.label);
      }
    },
  },
};
</script>

<style lang="scss">
.tab-container.active .tab-button {
  @apply bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100 md:rounded-none md:rounded-t-md;
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
  @apply hidden bg-gray-100 dark:bg-gray-800 md:block;
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
  @apply hover:cursor-not-allowed hover:bg-theme-400 hover:dark:bg-theme-800;
}
</style>
