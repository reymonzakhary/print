<template>
  <nav
    class="flex w-full justify-between bg-gray-100 text-center shadow-md dark:bg-gray-900"
    role="navigation"
  >
    <ul
      v-if="bottomItems.length > 0"
      class="flex h-full w-full content-between items-center justify-around overflow-x-auto"
    >
      <li v-for="item in bottomItems" :key="item.name" :class="listClass">
        <nuxt-link
          :to="item.path"
          :class="[
            linkClass,
            {
              'bg-white font-bold text-theme-500 shadow-md shadow-gray-300 dark:bg-gray-700 dark:shadow-sm dark:shadow-black':
                isRouteActive(item),
            },
          ]"
          @click="handleItemClick(item)"
        >
          <!-- Custom Icon (like UIPrindustryBox) -->
          <UIPrindustryBox
            v-if="item.name === 'marketplace'"
            class="mx-auto h-4 w-4"
            :line="{
              'text-theme-500': isRouteActive('marketplace'),
            }"
            :bg="{
              'text-theme-100': isRouteActive('marketplace'),
              'text-transparent': !isRouteActive('marketplace'),
            }"
          />

          <!-- FontAwesome Icon -->
          <font-awesome-icon
            v-else
            :class="{ 'text-theme-500': isRouteActive(item) }"
            :icon="isRouteActive(item) ? [...item.activeIcon] : [...item.icon]"
            size="lg"
          />

          <p class="hidden md:block">{{ $t(getDisplayName(item)) }}</p>
        </nuxt-link>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { useStore } from "vuex";
import { useNavigation } from "~/composables/navigation/useNavigation.js";

const { getVisibleItems, isRouteActive, getDisplayName } = useNavigation();
const store = useStore();

const bottomItems = computed(() => getVisibleItems("bottom"));

const listClass = "";
const linkClass =
  "block hover:bg-gray-100 dark:hover:bg-gray-900 transition-all duration-150 p-2 sm:text-lg md:text-xs";

const handleItemClick = (item) => {
  if (item.onClick && typeof item.onClick === "function") {
    item.onClick(store);
  }
};
</script>
