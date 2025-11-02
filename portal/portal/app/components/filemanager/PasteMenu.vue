<template>
  <div v-if="menuVisible" @click="closeMenu">
    <div
      :style="menuStyle"
      class="absolute rounded shadow-lg backdrop-blur-md bg-white/80 dark:bg-gray-900/80"
      tabindex="-1"
    >
      <ul>
        <li
          class="w-full px-3 py-2 text-sm text-left capitalize transition-all duration-200 border-b cursor-pointer hover:bg-gray-200 dark:border-black hover:text-theme-500 first:rounded-t last:rounded-b"
          @click="menuAction('paste')"
        >
          <font-awesome-icon :icon="['fal', 'clipboard']" />
          Paste
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import contextMenu from "~/components/filemanager/mixins/contextMenuMixin.js";
import contextMenuRules from "~/components/filemanager/mixins/contextMenuRules";
import contextMenuActions from "~/components/filemanager/mixins/contextMenuActions";

export default {
  mixins: [contextMenu, contextMenuRules, contextMenuActions],
  setup() {
    const eventStore = useEventStore();
    return { eventStore };
  },
  data() {
    return {
      menuVisible: false,
      menuStyle: {
        top: 0,
        left: 0,
      },
    };
  },
  computed: {
    menu() {
      return this.$store.state.fm.settings.contextMenu;
    },
    selectedItems() {
      return this.$store.getters["fm/content/selectedList"];
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.eventStore.on("pasteMenu", (event) => this.showMenu(event));
    });
  },
  beforeUnmount() {
    this.eventStore.off("pasteMenu");
  },
  methods: {
    showMenu(event) {
      if (this.selectedItems) {
        this.menuVisible = true;
        // focus on menu
        this.$nextTick(() => {
          // set menu params
          this.setMenu(event.y, event.x);
        });
      }
    },

    async setMenu(x, y) {
      // set the position
      const { innerHeight, innerWidth } = window;
      const dY = innerHeight - y;
      const dX = innerWidth - x;

      this.menuStyle = { left: y - 190 + "px", top: x - 50 + "px" };
    },

    closeMenu() {
      this.menuVisible = false;
    },

    showMenuItem(name) {
      if (Object.prototype.hasOwnProperty.call(this, `${name}Rule`)) {
        return this[`${name}Rule`]();
      }
      return false;
    },

    menuAction(name) {
      if (Object.prototype.hasOwnProperty.call(this, `${name}Action`)) {
        this[`${name}Action`]();
      }
      // close context menu
      this.closeMenu();
    },
  },
};
</script>
