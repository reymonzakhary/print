<template>
  <div
    class="flex overflow-hidden flex-col w-full h-screen bg-theme-500 dark:bg-theme-700 styled-scrollbars dark:text-white print:bg-white"
  >
    <div id="topbar" class="flex-none h-12">
      <TopBar v-if="authStore.isAuthenticated" />
      <TopBarSkeleton v-else />
    </div>
    <div class="flex flex-col flex-1 min-h-0 sm:flex-row">
      <aside id="sidebar" class="flex-none order-last w-full h-12 sm:w-40 sm:order-first sm:h-full">
        <SideBar v-if="authStore.isAuthenticated" />
        <SideBarSkeleton v-else />
      </aside>
      <div class="overflow-y-auto flex-1 min-w-0 h-full bg-gray-100 rounded-tl-lg dark:bg-gray-900">
        <div v-if="authStore.isAuthenticated" id="content" class="p-4">
          <slot />
        </div>
        <ContentSkeleton v-else />
      </div>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore();
</script>
