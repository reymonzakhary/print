<template>
  <div class="w-full overflow-hidden">
    <div
      class="flex mb-1 overflow-hidden font-bold uppercase text-nowrap text-ellipsis nowrap"
    >
      <button
        class="px-2 -ml-2 rounded-full text-theme-500 hover:bg-theme-100"
        @click="$emit('edit-click')"
      >
        <font-awesome-icon :icon="['fal', 'pencil']" class="fa-fw" />
      </button>
      {{ title }}
    </div>
    <ul>
      <SkeletonLine v-if="isLoading" />
      <SkeletonLine v-if="isLoading" />
      <SkeletonLine v-if="isLoading" />
      <li
        v-for="item in items"
        v-else
        :key="`${item.name}_${item.id}`"
        class="flex text-nowrap text-ellipsis"
      >
        <font-awesome-icon :icon="itemsIcon" class="mr-1 fa-fw" />
        {{ item.display_name ?? item.name }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "UserAccessList",
  props: ["title", "items", "itemsIcon", "isLoading"],
  emits: ["edit-click"],
};
</script>
