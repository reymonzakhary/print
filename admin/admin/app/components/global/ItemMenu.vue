<template>
  <VDropdown :placement="'bottom-end'" :shown="open">
    <button
      v-click-outside="close"
      :class="[menuClass, { '!cursor-not-allowed hover:!bg-transparent': disabled }]"
      :disabled="disabled"
    >
      <span v-if="menuTitle" class="mr-2" :class="[menuTitleClass]">
        {{ menuTitle }}
      </span>
      <font-awesome-icon :icon="['fal', menuIcon]" class="ml-auto" />
    </button>

    <!-- class="absolute z-30 block min-w-48 rounded bg-white shadow-lg dark:bg-gray-900" -->
    <!-- <transition name="slide"> -->
    <template #popper>
      <!-- <div v-if="open" class="relative z-20"> -->
      <div id="dropdown-menu" :class="dropdownClass" role="menu">
        <template v-for="(item, i) in menuItems">
          <button
            v-if="item.show"
            :key="item.title"
            v-close-popper
            :href="item.href"
            :class="[
              item.classes,
              'w-full text-left py-2 px-3 hover:bg-gray-100 dark:hover:bg-gray-900 border-b dark:border-black',
              { 'border-none rounded-b': i == menuItems.length - 1 },
            ]"
            @click="(click(item.action), close)"
          >
            <font-awesome-icon :icon="['fal', item.icon]" fixed-width class="mr-1" />
            {{ item.title }}
          </button>
        </template>
      </div>
      <!-- </div> -->
    </template>
    <!-- </transition> -->
  </VDropdown>
</template>

<script>
export default {
  directives: {
    clickOutside: {
      mounted(el, binding) {
        // Provided expression must evaluate to a function.
        if (typeof binding.value !== "function") {
          const warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`;
          console.warn(warn);
        }
        // Define Handler and cache it on the element
        const bubble = binding.modifiers.bubble;
        const handler = (e) => {
          if (bubble || (!el.contains(e.target) && el !== e.target)) {
            binding.value(e);
          }
        };
        el.__vueClickOutside__ = handler;

        // add Event Listeners
        document.addEventListener("click", handler);
      },

      unmounted(el) {
        // Remove Event Listeners
        document.removeEventListener("click", el.__vueClickOutside__);
        el.__vueClickOutside__ = null;
      },
    },
  },
  props: {
    menuTitle: {
      required: false,
      type: String,
      default: "",
    },
    menuClass: {
      type: String,
      default: "ellipsis",
    },
    menuTitleClass: {
      required: false,
      default: "",
      type: [String, Object],
    },
    dropdownClass: {
      required: false,
      default: "",
      type: [String, Object],
    },
    menuIcon: {
      type: String,
      default: "ellipsis",
    },
    menuItems: {
      type: Array,
      required: true,
    },
    index: {
      required: false,
      type: Number,
      default: 0,
    },
    showMenuButton: {
      required: false,
      default: true,
      type: Boolean,
    },
    disabled: {
      required: false,
      default: false,
      type: Boolean,
    },
  },
  emits: ["item-clicked"],
  data() {
    return {
      open: false,
    };
  },
  methods: {
    click(item) {
      // console.log(item);

      this.$emit("item-clicked", item);
      this.open = false;
    },
    close() {
      this.open = false;
    },
  },
};
</script>
