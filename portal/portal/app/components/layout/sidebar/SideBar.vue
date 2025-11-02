<template>
  <ul v-if="sidebarItems.length > 0" id="sidebarmenu" class="h-full w-full overflow-y-auto">
    <li v-for="item in sidebarItems" :key="item.name">
      <!-- Regular navigation item -->
      <SideBarButton
        v-if="!item.disabled"
        :to="item.path"
        :label="$t(getDisplayName(item))"
        :icon="item.name === 'marketplace' ? '' : [...item.icon]"
        :active-icon="item.name === 'marketplace' ? '' : [...item.activeIcon]"
        :active="isRouteActive(item)"
        @click="handleItemClick(item)"
      >
        <!-- Beta Badge -->
        <SideBarButtonBadge
          v-if="item.beta"
          :label="$t('beta')"
          :icon="['fal', 'sparkles']"
          class="absolute right-3 top-1.5"
        />

        <!-- Custom Icon (like UIPrindustryBox) -->
        <UIPrindustryBox
          v-if="item.name === 'marketplace'"
          class="mx-auto h-10 w-10"
          :line="{
            'text-theme-500': isRouteActive('marketplace'),
          }"
          :bg="{
            'text-theme-100': isRouteActive('marketplace'),
            'text-transparent': !isRouteActive('marketplace'),
          }"
        />
      </SideBarButton>

      <!-- Disabled item with tooltip -->
      <SideBarButton
        v-else
        v-tooltip="$t(item.tooltip || 'disabled')"
        :disabled="true"
        :icon="[...item.icon]"
        :label="$t(getDisplayName(item))"
      />
    </li>
  </ul>
</template>

<script setup>
import { computed } from "vue";
import { useStore } from "vuex";
import { useNavigation } from "~/composables/navigation/useNavigation.js";

const { getVisibleItems, isRouteActive, getDisplayName } = useNavigation();
const store = useStore();

const sidebarItems = computed(() => getVisibleItems("sidebar"));

const handleItemClick = (item) => {
  if (item.onClick && typeof item.onClick === "function") {
    item.onClick(store);
  }
};
</script>
