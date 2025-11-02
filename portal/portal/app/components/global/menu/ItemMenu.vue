<template>
  <div class="relative">
    <button
      :class="[menuClass, { '!cursor-not-allowed hover:!bg-transparent': disabled }]"
      :disabled="disabled"
      @click.stop="showMenu(index)"
    >
      <font-awesome-icon
        v-if="buttonIcon"
        :icon="['fal', buttonIcon]"
        class="mr-2 text-theme-500"
      />
      <span v-if="menuTitle" class="mr-2" :class="[menuTitleClass]">
        {{ menuTitle }}
      </span>
      <font-awesome-icon :icon="['fal', menuIcon]" class="ml-auto" />
    </button>

    <transition name="slide">
      <div v-if="open" class="relative z-20">
        <div
          id="dropdown-menu"
          class="firefox:bg-opacity-100 absolute z-30 block rounded bg-white bg-opacity-60 shadow-lg backdrop-blur-md dark:bg-gray-900 dark:bg-opacity-60"
          :class="dropdownClass"
          role="menu"
        >
          <div
            v-for="menu in visibleMenuItems"
            :key="menu.heading || Math.random()"
            class="dark:border-black [&:not(:first-child)]:pt-2 [&:not(:last-child)]:border-b [&:not(:last-child)]:pb-2"
          >
            <div
              v-if="menu.heading && menu.heading.show"
              class="px-3 pt-2 text-xs font-semibold tracking-wide text-gray-400"
            >
              {{ menu.heading.title }}
            </div>

            <template v-for="(item, i) in menu.items">
              <button
                v-if="item.show"
                :key="item.title"
                :href="item.href"
                :class="[
                  'flex w-full px-3 py-2 text-left transition-all duration-200 hover:text-theme-500 dark:text-theme-200 dark:hover:text-theme-500',
                  item.classes,
                  { 'rounded-b border-0': i === menu.items.length - 1 },
                  {
                    'relative rounded-b border-b-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-theme-100 via-theme-200 to-theme-300 shadow-md before:absolute before:inset-0 before:bg-[linear-gradient(45deg,transparent_25%,theme(colors.white/.5)_50%,transparent_75%,transparent_100%)] before:bg-[length:250%_250%,100%_100%] before:bg-[position:200%_0,0_0] before:bg-no-repeat before:[transition:background-position_0s_ease] hover:before:bg-[position:-100%_0,0_0] hover:before:duration-[1500ms] dark:from-gray-900 dark:via-gray-800 dark:to-gray-700 dark:text-gray-100 dark:[text-shadow:_0_1px_0_rgb(0_0_0_/_40%)] dark:before:bg-[linear-gradient(45deg,transparent_25%,theme(colors.white)_50%,transparent_75%,transparent_100%)]':
                      item.variant === 'studio',
                  },
                ]"
                @click.stop="click(item.action)"
              >
                <div class="w-6">
                  <font-awesome-icon
                    :icon="[item.variant === 'studio' ? 'far' : 'fal', item.icon]"
                    class="fa-sm fa-fw"
                    :class="{
                      '[filter:drop-shadow(0_1px_0_rgb(0_0_0_/_40%))]': item.variant === 'studio',
                    }"
                  />
                </div>

                {{ item.title }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  props: {
    menuTitle: {
      required: false,
      type: String,
      default: "",
    },
    menuClass: {
      required: false,
      default: "",
      type: [String, Object],
    },
    menuTitleClass: {
      required: false,
      default: "",
      type: [String, Object],
    },
    buttonIcon: {
      required: false,
      default: "",
      type: String,
    },
    menuIcon: {
      required: false,
      default: "",
      type: String,
    },
    menuItems: {
      required: false,
      type: Array,
      default: [],
    },
    dropdownClass: {
      required: false,
      default: "",
      type: [String, Object],
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
  computed: {
    visibleMenuItems() {
      return this.menuItems.filter((item) => {
        if (item.heading) {
          return item.heading.show !== false;
        }
        return true;
      });
    },
  },
  mounted() {
    window.addEventListener("click", this.close);
  },
  beforeUnmount() {
    window.removeEventListener("click", this.close);
  },
  methods: {
    click(item) {
      this.close();
      this.$emit("item-clicked", item);
    },
    close() {
      this.open = false;
    },
    showMenu(i) {
      this.open = !this.open;
      if (this.$parent.permissionsFlag) {
        this.$parent.showSettings(i);
      }
    },
  },
};
</script>
