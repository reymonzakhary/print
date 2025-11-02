<template>
  <div v-if="menuVisible" class="absolute bottom-0 left-0 z-50 right-0 top-0" @click="closeMenu">
    <div
      ref="contextMenu"
      :style="menuStyle"
      class="firefox:bg-opacity-100 absolute overflow-auto rounded bg-white bg-opacity-60 shadow-lg backdrop-blur-md dark:bg-gray-900 dark:bg-opacity-60"
      tabindex="-1"
    >
      <ul v-for="(group, index) in menu" :key="`g-${index}`">
        <template v-for="(item, index) in group">
          <li
            v-if="showMenuItem(item.name)"
            :key="`i-${index}`"
            class="w-full cursor-pointer border-b px-3 py-2 text-left text-sm capitalize transition-all duration-200 first:rounded-t last:rounded-b hover:text-theme-500 dark:border-black"
            @click="menuAction(item.name)"
          >
            <font-awesome-icon :icon="['fal', item.icon]" :class="item.class" />
            {{ item.name }}
          </li>
        </template>
      </ul>
    </div>
  </div>
</template>

<script>
import contextMenu from "~/components/filemanager/mixins/contextMenuMixin.js";
import contextMenuRules from "~/components/filemanager/mixins/contextMenuRules";
import contextMenuActions from "~/components/filemanager/mixins/contextMenuActions";

import { useStore } from "vuex";

export default {
  name: "ContextMenu",
  mixins: [contextMenu, contextMenuRules, contextMenuActions],
  setup() {
    const store = useStore();
    const eventStore = useEventStore();
    return { store, eventStore };
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
      return this.store.state.fm.settings.contextMenu;
    },
    selectedItems() {
      return this.store.getters["fm/content/selectedList"];
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.eventStore.on("contextMenu", (event) => this.showMenu(event));
    });
  },
  beforeUnmount() {
    this.eventStore.off("contextMenu");
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

    async setMenu(top, left) {
      // await this.$nextTick(() => {
      // get parent el (.fm-body)
      const el = this.$refs["contextMenu"]?.parentNode?.parentNode?.parentNode;
      if (!el) return;

      // get parent el size
      const elSize = el.getBoundingClientRect();

      // actual coordinates of the block
      const elY = window.scrollY + elSize.top;
      const elX = window.scrollX + elSize.left;

      // calculate the preliminary coordinates
      let menuY = top - elY;
      let menuX = left - elX;

      // calculate max X and Y coordinates
      const maxY = elY + (el.offsetHeight - this.$refs.contextMenu.offsetHeight - 25);
      const maxX = elX + (el.offsetWidth - this.$refs.contextMenu.offsetWidth - 25);
      if (top > maxY) menuY = maxY - elY;
      if (left > maxX) menuX = maxX - elX;

      // set coordinates
      this.menuStyle.top = `${menuY}px`;
      this.menuStyle.left = `${menuX}px`;
      // });
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
