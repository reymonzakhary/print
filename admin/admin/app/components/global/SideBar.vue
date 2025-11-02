<template>
  <ul
    class="flex h-full text-sm text-center text-white sm:px-2 bg-theme-500 dark:bg-theme-700 sm:block space-y-2"
    role="menu"
    aria-label="Sidebar Navigation"
  >
    <li v-for="menuItem in menuItems" :key="menuItem.path" class="w-full">
      <NuxtLink
        v-tooltip="menuItem.disabled ? 'Coming Soon' : null"
        :to="menuItem.disabled ? null : menuItem.path"
        class="grid h-full gap-1 px-1 py-4 capitalize truncate transition-all duration-150 sm:rounded-md hover:bg-theme-200 hover:shadow-md hover:shadow-theme-300 shadow-gray-300 place-items-center"
        :class="{
          'bg-theme-100 shadow-md shadow-theme-300 text-theme-500 font-bold hover:bg-theme-100':
            isActive(menuItem.path),
          'cursor-not-allowed dark:bg-theme-700 bg-theme-500 text-theme-300 dark:text-theme-400 hover:bg-theme-500 hover:shadow-none':
            menuItem.disabled,
        }"
      >
        <font-awesome-icon
          :icon="[isActive(menuItem.path) ? 'fad' : 'fal', menuItem.icon]"
          class="text-base sm:text-3xl"
        />
        <span class="hidden sm:inline">{{ menuItem.title }}</span>
      </NuxtLink>
    </li>
  </ul>
</template>

<script setup>
const route = useRoute();

const menuItems = ref([
  {
    icon: "tachometer-fastest",
    title: "Dashboard",
    path: "/",
  },
  {
    icon: "users-crown",
    title: "Tenants",
    path: "/tenants",
  },
  {
    icon: "users",
    title: "Users",
    path: "/users",
  },
  {
    icon: "messages",
    title: "Messages.",
    path: "/messages",
  },
  {
    icon: "file-contract",
    title: "Contracts",
    path: "/contracts",
  },
  {
    icon: "digging",
    title: "Standardization",
    path: "/standardization",
  },
]);

const isActive = (path) => route.path === path || route.path.startsWith(`${path}/`);
</script>
