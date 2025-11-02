<template>
  <div>
    <div
      id="default-wrapper"
      class="grid h-screen w-screen grid-flow-row grid-cols-12 grid-rows-18 bg-gray-200 text-black dark:bg-gray-950 dark:text-white lg:pb-4 print:bg-white"
    >
      <div id="topbar" class="col-span-full row-span-1 row-start-1 print:hidden">
        <TopBarPlaceholder />
      </div>

      <div
        id="sidebar"
        class="col-span-1 col-start-1 row-span-17 row-start-2 hidden overflow-y-auto text-center dark:bg-gray-950 lg:flex print:lg:hidden"
      >
        <SideBarPlaceholder class="w-full" />
      </div>
      <div
        id="content-area"
        class="col-span-full row-span-16 overflow-y-auto rounded-t-lg bg-gray-100 shadow-lg shadow-gray-300 dark:bg-gray-900 dark:shadow-black md:rounded-bl-lg md:rounded-tr-none lg:col-span-11 lg:row-span-17 print:h-screen print:w-screen print:bg-white"
      >
        <div
          class="flex h-full w-full flex-wrap items-center justify-center bg-gradient-to-tr from-gray-300 via-white to-gray-200 dark:from-gray-900 dark:via-black dark:to-gray-800"
          :class="{
            'from-gray-950 via-gray-800 to-gray-950': activeTheme === 'dark',
          }"
        >
          <font-awesome-layers class="pr-24">
            <font-awesome-icon
              class="fa-spin fa-5x text-theme-400 dark:text-theme-600"
              :icon="['fat', 'spinner-third']"
            />
            <font-awesome-icon class="fa-lg text-theme-400" :icon="['fad', 'key']" fade />
          </font-awesome-layers>

          <p class="mt-4 w-full text-center font-bold">authenticating...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const activeTheme = ref("light");
provide("activeTheme");

onMounted(() => {
  activeTheme.value = localStorage.getItem("active_theme") || "light";
});
</script>
