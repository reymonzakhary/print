<template>
  <section class="w-full h-full px-2 py-4 lg:w-2/6 xl:w-1/6">
    <!-- TODO: switch based on contexts (storefronts) -->
    <!-- <div class="flex items-center mb-4">
			<font-awesome-icon class="mx-1" :icon="['far', 'window-maximize']" />
        
			<select class="px-2 py-1 text-sm input">
				<option value="" :selected="true">
					webshop name
				</option>
				<option value="one">
					one
				</option>
			</select>
		</div> -->

    <div>
      <div class="flex flex-wrap items-baseline justify-between">
        <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'folder-tree']" />
          {{ $t("pages") }}
        </h2>

        <ItemMenu
          :menu-items="resourceItems"
          menu-icon="ellipsis-h"
          menu-class="w-6 h-6 text-center transition-colors rounded-full text-theme-500 hover:bg-gray-200 dark:hover:bg-gray-800"
          dropdown-class="w-52"
          @item-clicked="menuItemClicked($event)"
        ></ItemMenu>

        <div>
          <button
            v-if="!drag"
            class="mx-1 text-sm capitalize text-theme-500 hover:text-theme-600"
            @click="(drag = !drag), sortArray(reorder_tree)"
          >
            <font-awesome-icon :icon="['fal', 'shuffle']" />
            {{ $t("reorder") }}
          </button>
          <button
            v-if="drag"
            class="mx-1 text-sm text-green-500 capitalize"
            @click="saveReorder"
          >
            <font-awesome-icon :icon="['fal', 'shuffle']" />
            {{ $t("save") }}
          </button>
          <button
            v-if="drag"
            class="mx-1 text-sm text-gray-500 capitalize"
            @click="drag = !drag"
          >
            <font-awesome-icon :icon="['fal', 'shuffle']" />
            {{ $t("cancel") }}
          </button>
          <button
            v-if="!drag"
            class="text-sm capitalize text-theme-500 hover:text-theme-600"
            @click="newResource = true"
          >
            <font-awesome-icon :icon="['fal', 'plus']" />
            {{ $t("new") }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="recycle_bin"
      class="flex items-center justify-between my-4 ml-2 text-sm"
    >
      <h3 class="text-xs font-bold tracking-wide uppercase">
        {{ $t("recycle bin") }}
      </h3>
      <button class="text-red-500" @click="showEmptyBin = true">
        <font-awesome-icon :icon="['fal', 'dumpster-fire']" />
        {{ $t("empty bin") }}
      </button>
      <button
        class="text-theme-500"
        @click="set_recycle_bin(false), get_tree()"
      >
        {{ $t("close") }}
      </button>
    </div>

    <TreeMenu
      v-if="!drag"
      :children="nested_tree"
      :depth="0"
      :selected="selected_item.id"
      :class="{
        'border border-red-500 rounded bg-red-100 dark:bg-red-900': recycle_bin,
      }"
      :recycle_bin="recycle_bin"
    ></TreeMenu>

    <vue-nestable v-if="drag" v-model="reorder_tree" class="ml-4">
      <template #default="{ item }">
        <vue-nestable-handle :item="item">
          <font-awesome-icon :icon="['fal', 'file']" class="text-theme-500" />
          {{ item.title }}
        </vue-nestable-handle>
      </template>
    </vue-nestable>

    <transition name="fade">
      <ResourceNewItem v-if="newResource" :tree="tree" :templates="templates">
      </ResourceNewItem>
    </transition>

    <transition name="fade">
      <ResourceEmptyBin v-if="showEmptyBin" :tree="tree"> </ResourceEmptyBin>
    </transition>
    <!-- <ResourceMenuTree :depth="0" :parentId="null"></ResourceMenuTree> -->
  </section>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import _ from "lodash";

export default {
  setup() {
    const eventStore = useEventStore();
    return { eventStore };
  },
  data() {
    return {
      nested_tree: [],
      reorder_tree: [],
      payload: [],
      drag: false,
      newResource: false,
      showEmptyBin: false,
      resourceItems: [
        {
          items: [
            {
              action: "recycle-bin",
              icon: "trash-can",
              title: "Recycle bin",
              classes: "",
              show: true,
            },
          ],
        },
      ],
    };
  },
  computed: {
    ...mapState({
      tree: (state) => state.resources.tree,
      recycle_bin: (state) => state.resources.recycle_bin,
      selected_item: (state) => state.resources.selected_item,
      templates: (state) => state.templates.templates,
    }),
  },
  watch: {
    tree() {
      this.nested_tree = this.unflatten(this.tree);
      this.reorder_tree = _.sortBy(this.nested_tree, "sort");
    },
  },
  created() {
    this.eventStore.on("item-open", (item) => {
      this.set_selected_item(item);
    });
  },
  mounted() {
    this.set_recycle_bin(false);
    setTimeout(() => {
      this.nested_tree = this.unflatten(this.tree);
      this.reorder_tree = _.sortBy(this.nested_tree, "sort");
    }, 200);
  },
  methods: {
    ...mapMutations({
      set_selected_item: "resources/set_selected_item",
      set_recycle_bin: "resources/set_recycle_bin",
      set_resource: "resources/set_resource",
    }),
    ...mapActions({
      get_tree: "resources/get_tree",
      update_tree: "resources/update_tree",
      get_recycle_bin: "resources/get_recycle_bin",
    }),
    unflatten(arr) {
      let tree = [],
        mappedArr = {},
        arrElem,
        mappedElem;

      // First map the nodes of the array to an object -> create a hash table.
      for (let i = 0, len = arr.length; i < len; i++) {
        arrElem = arr[i];
        mappedArr[arrElem.id] = arrElem;
        mappedArr[arrElem.id]["children"] = [];
      }

      for (const id in mappedArr) {
        if (mappedArr.hasOwnProperty(id)) {
          mappedElem = mappedArr[id];
          // If the element is not at the root level, add it to its parent array of children.
          if (
            mappedElem.parent_id &&
            mappedArr[mappedElem["parent_id"]] &&
            mappedArr[mappedElem["parent_id"]]["children"]
          ) {
            mappedArr[mappedElem["parent_id"]]["children"].push(mappedElem);
          }
          // If the element is at the root level, add it to first level elements array.
          else {
            tree.push(mappedElem);
          }
        }
      }
      return _.sortBy(tree, "sort");
    },
    flatten(array, parent_id) {
      array.forEach((element) => {
        this.payload.push({
          id: element["id"],
          parent_id: parent_id,
        });

        if (element["children"].length > 0) {
          this.flatten(element["children"], element["id"]);
        }
      });
    },
    sortArray(array) {
      array.sort((a, b) => a.sort - b.sort);
      array.forEach((a) => {
        if (a.children && a.children.length > 0) this.sortArray(a.children);
      });
      return array;
    },
    saveReorder() {
      this.flatten(this.reorder_tree, null);

      this.update_tree({ sort: this.payload });

      this.drag = false;
    },
    menuItemClicked(event) {
      switch (event) {
        case "recycle-bin":
          this.get_recycle_bin();
          this.set_recycle_bin(true);
          this.set_resource({});
          break;

        default:
          break;
      }
    },
  },
};
</script>

<style>
/*
																								* Style for nestable
																								*/
.nestable {
  position: relative;
}

.nestable-rtl {
  direction: rtl;
}

.nestable .nestable-list {
  margin: 0;
  padding: 0 0 0 30px;
  list-style-type: none;
}

.nestable-rtl .nestable-list {
  padding: 0 30px 0 0;
}

.nestable > .nestable-list {
  padding: 0;
}

.nestable-item,
.nestable-item-copy {
  margin: 10px 0 0;
}

.nestable-item:first-child,
.nestable-item-copy:first-child {
  margin-top: 0;
}

.nestable-item .nestable-list,
.nestable-item-copy .nestable-list {
  margin-top: 10px;
}

.nestable-item {
  position: relative;
}

.nestable-item.is-dragging .nestable-list {
  pointer-events: none;
  cursor: grabbing;
}

.nestable-item.is-dragging * {
  opacity: 0;
  filter: alpha(opacity=0);
  cursor: grabbing;
  border-radius: 5px;
}

.nestable-item.is-dragging:before {
  content: " ";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(106, 127, 233, 0.274);
  border: 1px dashed rgb(73, 100, 241);
  -webkit-border-radius: 5px;
  border-radius: 5px;
  cursor: grabbing;
}

.nestable-drag-layer {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 100;
  pointer-events: none;
}

.nestable-rtl .nestable-drag-layer {
  left: auto;
  right: 0;
}

.nestable-drag-layer > .nestable-list {
  position: absolute;
  top: 0;
  left: 0;
  padding: 0;
  background-color: rgba(106, 127, 233, 0.274);
  border-radius: 5px;
}

.nestable-rtl .nestable-drag-layer > .nestable-list {
  padding: 0;
}

.nestable [draggable="true"] {
  cursor: grab;
}

.nestable-handle {
  display: inline;
}
</style>
