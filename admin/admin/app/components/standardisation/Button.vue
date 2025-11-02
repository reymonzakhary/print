<template>
  <button
    class="flex items-center justify-between w-full px-2 py-1 text-left transition-colors duration-75 group dark:hover:bg-black"
    :class="classes"
    @click="$emit('button-clicked', item)"
  >
    <span
      v-tooltip.bottom="item.description ? 'description: ' + item.description : ''"
      class="flex items-center justify-between w-full relative"
    >
      <template v-for="media in item.media">
        <span
          v-if="media.path"
          :key="media.name"
          class="w-6 h-6 overflow-hidden bg-blue-500 rounded-full"
        >
          <img :src="media.path" :alt="media.name" />
        </span>
      </template>

      <!-- <span v-if="item.object" class="flex items-center">
        <font-awesome-icon :icon="['fal', 'exclamation-triangle']" class="mr-2 text-sm" />
        <p>{{ item.object.name }}</p>
      </span> -->
      <section
        v-tooltip.top="item.name?.length > 34 ? item.name : ''"
        class="truncate w-56 flex items-center"
        :class="{
          'bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 inline-block text-transparent bg-clip-text':
            item.percentage && item.percentage >= 85,
          'bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-500 inline-block text-transparent bg-clip-text':
            item.percentage && item.percentage <= 85,
        }"
      >
        <div v-if="type === 'categories'" class="w-5">
          <font-awesome-icon
            v-if="item.has_manifest"
            :icon="['fad', 'scroll']"
            class="text-green-500 text-xs"
            fixed-with
          />
        </div>
        {{ item.name }}
      </section>
      <span
        v-if="item.percentage"
        class="text-black absolute right-2 z-50 text-right font-bold"
        :class="{
          'bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 inline-block text-transparent bg-clip-text':
            item.percentage >= 85,
          'bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-500 inline-block text-transparent bg-clip-text':
            item.percentage <= 85,
        }"
      >
        {{ item.percentage }}% match
      </span>
    </span>

    <font-awesome-icon
      v-show="$parent.loading === item.slug"
      :icon="['fad', 'spinner-third']"
      class="text-blue-500 fa-spin"
    />
    <!-- <div
      v-if="item.matches && item.matches.length > 0"
      class="w-5 h-5 text-center absolute text-xs text-white bg-yellow-400 rounded-full hover:bg-yellow-500 right-2"
      @click="emit('match-button-clicked', item)"
    >
      <font-awesome-icon :icon="['fal', 'exclamation-triangle']" />
    </div> -->

    <font-awesome-layers v-if="item.published === false">
      <font-awesome-icon :icon="['fad', 'heart-rate']" class="text-pink-500" />
      <font-awesome-icon :icon="['fal', 'ban']" class="text-pink-500" transform="grow-10 right-3" />
    </font-awesome-layers>

    <font-awesome-icon
      v-if="item?.suppliers?.length"
      :icon="['fal', 'link']"
      class="absolute right-14 text-xs text-gray-500"
    />
    
    <font-awesome-icon
      v-if="item?.additional?.calc_ref"
      :icon="['fal', calcRef(item.additional?.calc_ref)]"
      class="absolute right-20 text-xs text-gray-500"
    />
    <button
      v-if="
        menuItems &&
        menuItems.length > 0 &&
        (!item.matches || item.matches?.length === 0) &&
        !unlinked
      "
      class="w-5 h-5 text-center absolute text-xs invisible group-hover:visible rounded-full hover:bg-gray-200 right-2"
      @click="emit('menu-item-clicked', 'edit', item, item.slug)"
    >
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </button>

    <ItemMenu
      v-if="menuItems && menuItems.length > 0 && (!item.matches || item.matches?.length === 0)"
      class="absolute right-8 z-10 invisible group-hover:visible hover:bg-gray-200 dark:hover:bg-gray-900 rounded-full px-1"
      :menu-items="menuItems"
      menu-icon="ellipsis-h"
      menu-class="z-20"
      dropdown-class=" w-44 dark:border-black text-theme-900"
      @item-clicked="$emit('menu-item-clicked', $event, item, item.slug)"
    />
  </button>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
  classes: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "",
  },
  menuItems: {
    type: Array,
    default: () => [],
  },
  unlinked: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["button-clicked", "menu-item-clicked", "match-button-clicked"]);

const tooltip = ref(false);

const calcRef = (calc_ref) => {
  switch (calc_ref) {
    case "format":
      return "ruler-combined";
    case "material":
      return "file";
    case "weight":
      return "weight-hanging";
    case "printing_colors":
      return "circles-overlap-3";
    default:
      return "check";
  }
};
</script>
