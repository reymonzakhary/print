<template>
  <nav
    class="flex items-center w-full text-sm bg-white rounded-t dark:bg-gray-700"
  >
    <template v-for="(tab, i) in tabs" :key="`tab_${tab.name}_${i}`">
      <button
        class="flex items-center justify-center flex-1 px-6 py-2 text-gray-600 capitalize border-b-2 rounded-t hover:text-theme-500 focus:outline-none"
        :class="{
          'text-theme-500 font-semibold border-theme-500 bg-theme-100':
            activeTab === tab.name,
        }"
        @click="(activeTab = tab.name), $emit('tabClicked', tab)"
      >
        <font-awesome-icon
          :icon="['fal', tab.icon]"
          class="mr-1 text-theme-500"
        />
        {{ tab.name }}
      </button>
    </template>
  </nav>
</template>

<script>
export default {
  props: {
    tabs: {
      type: Array,
      required: true,
    },
  },
  emits: ["tabClicked"],
  data() {
    return {
      activeTab: {},
    };
  },
  created() {
    this.activeTab = this.tabs[0];
  },
};
</script>
