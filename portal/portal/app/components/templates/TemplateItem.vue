<template>
  <div>
    <TemplateEditItem
      v-if="showEditItem === item.id"
      :item="item"
      :type="type"
      :folders="folders"
      class="fixed top-0 right-0 z-50 w-screen h-screen"
      @on-close="showEditItem = false"
    />

    <div
      :key="item.id"
      class="flex items-center justify-between px-2 py-1 transition-colors duration-100 rounded cursor-pointer focus:outline-none hover:text-theme-500 group"
      :class="{
        'bg-theme-100 dark:bg-theme-900 text-theme-500': selected_item.name === item.name,
      }"
      @click="getItem(item.id), set_selected_item_type(type)"
    >
      <p class="truncate">
        {{ item.name }}
      </p>
      <span class="flex">
        <button
          class="flex invisible px-2 py-1 mr-1 text-red-500 rounded-full group-hover:visible hover:bg-red-100"
          @click="showRemoveItem = item.id"
        >
          <font-awesome-icon :icon="['fal', 'trash-can']" />
        </button>
        <button
          class="flex invisible px-2 py-1 mr-1 rounded-full text-theme-500 group-hover:visible hover:bg-theme-100"
          @click="showEditItem = item.id"
        >
          <font-awesome-icon :icon="['fal', 'pencil']" />
        </button>
      </span>

      <transition name="fade">
        <TemplateRemoveItem
          v-if="showRemoveItem === item.id"
          :item="item"
          :item-type="type"
          @on-close="showRemoveItem = false"
        />
      </transition>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  props: {
    item: Object,
    type: String,
    folders: Array,
  },
  data() {
    return {
      showRemoveItem: false,
      showEditItem: false,
    };
  },
  computed: {
    ...mapState({
      selected_item: (state) => state.templates.selected_item,
      selected_item_type: (state) => state.templates.selected_item_type,
    }),
  },
  methods: {
    ...mapMutations({
      set_selected_item_type: "templates/set_selected_item_type",
    }),
    ...mapActions({
      get_single_template: "templates/get_single_template",
      get_single_chunk: "templates/get_single_chunk",
    }),
    getItem(id) {
      switch (this.type) {
        case "template":
          this.get_single_template(id);
          break;

        case "chunk":
          this.get_single_chunk(id);
          break;

        default:
          break;
      }
    },
  },
};
</script>
