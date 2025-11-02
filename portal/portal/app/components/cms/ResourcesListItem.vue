<template>
  <draggable
    item-key="resourcesList"
    :remove-clone-on-hide="false"
    :swap-threshold="0.5"
    :empty-insert-threshold="20"
    tag="ul"
    :group="{ name: 'g1' }"
    :list="resources"
    :class="{
      'list-group': draggable,
      'pl-3 ml-3 border-l border-gray-300 dark:border-gray-900': !root,
    }"
    :disabled="!draggable"
    ghost-class="ghost"
    drag-class="drag"
    chosen-class="chosen"
    :animation="50"
    easing="linear"
  >
    <template #item="{ element: resource }">
      <li :draggable="draggable">
        <div
          class="flex items-center h-10 p-1 rounded group hover:bg-gray-200 dark:hover:bg-gray-700"
          :class="{
            'bg-theme-100 dark:bg-theme-900 text-theme-500':
              `${selectedResource}` === `${resource.id}`,
            'hover:cursor-pointer': !draggable,
            'hover:cursor-grab': draggable,
            'active:opacity-50': draggable,
          }"
          @click="$emit('onItemSelect', resource.id)"
        >
          <UIButton
            v-if="resource.resources && resource.resources.length > 0"
            :icon="openStates[resource.id] ? ['fas', 'caret-down'] : ['fas', 'caret-right']"
            variant="outline"
            class="-ml-2"
            @click.stop="handleToggleOpen(resource.id)"
          />

          <span
            v-if="showResourceIDs"
            class="mr-1 text-sm"
            :class="{ 'text-theme-100': !isBinItem, 'text-red-100': isBinItem }"
          >
            #{{ resource.id }}
          </span>

          <font-awesome-icon
            v-else
            :icon="icon(resource)"
            class="mr-2"
            :class="{ 'text-theme-500': !isBinItem, 'text-red-100': isBinItem }"
          />

          <span class="truncate">{{ resource.title }}</span>

          <UIButton
            v-if="!draggable && !isBinItem"
            :icon="['fas', 'trash']"
            variant="outline"
            class="ml-auto text-transparent group-hover:text-gray-500 group-hover:outline-gray-500 hover:!text-red-500 hover:!outline-red-500"
            @click.stop="$emit('onItemDelete', resource.id)"
          />

          <UIButton
            v-if="isBinItem"
            :icon="['fas', 'trash-undo']"
            variant="outline"
            class="ml-auto text-transparent group-hover:text-gray-500 group-hover:outline-gray-500 hover:!text-green-500 hover:!outline-green-500"
            @click.stop="$emit('onItemRestore', resource.id)"
          >
            {{ $t("Restore") }}
          </UIButton>

          <font-awesome-icon
            v-if="draggable"
            :icon="['fal', 'grip-vertical']"
            class="px-2 ml-auto text-gray-500"
          />
        </div>
        <ResourcesListItem
          v-show="openStates[resource.id] ?? true"
          :resources="resource.resources"
          :selected-resource="selectedResource"
          :is-bin-item="isBinItem"
          :draggable="draggable"
          :show-resource-i-ds="showResourceIDs"
          @on-item-select="(id) => $emit('onItemSelect', id)"
          @on-item-delete="(id) => $emit('onItemDelete', id)"
          @on-item-restore="(id) => $emit('onItemRestore', id)"
        />
      </li>
    </template>
  </draggable>
</template>

<script>
export default {
  props: {
    selectedResource: {
      type: [Number, String],
      default: null,
    },
    draggable: {
      type: Boolean,
      default: false,
    },
    isBinItem: {
      type: Boolean,
      default: false,
    },
    resources: {
      type: Array,
      default: () => [],
    },
    root: {
      type: Boolean,
      default: false,
    },
    showResourceIDs: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onItemRestore", "onItemSelect", "onItemDelete"],
  data() {
    return {
      openStates: {},
    };
  },
  beforeMount() {
    const savedOpenStates = localStorage.getItem("openStates");
    if (savedOpenStates) {
      this.openStates = JSON.parse(savedOpenStates);
    } else {
      if (!this.resources) return;
      this.resources.forEach((resource) => {
        this.openStates[resource.id] = true;
      });
    }
  },
  methods: {
    icon(resource) {
      if (!resource.resources || resource.resources.length === 0) {
        return ["fal", "file"];
      } else {
        return ["fad", "folder-open"];
      }
    },
    handleToggleOpen(id) {
      this.openStates[id] = !this.openStates[id];
      localStorage.setItem("openStates", JSON.stringify(this.openStates));
    },
  },
};
</script>

<style lang="scss" scoped>
.ghost {
  @apply outline-1 outline-dashed outline-theme-500 rounded;
}
</style>
