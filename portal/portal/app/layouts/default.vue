<template>
  <div
    id="layout"
    class="grid h-dvh w-dvw grid-flow-row grid-cols-12 grid-rows-[52px_repeat(17,_minmax(0,_1fr))] bg-gray-200 text-black dark:bg-gray-900 dark:text-gray-100 lg:pb-2 print:!overflow-visible print:bg-white"
  >
    <Html :lang="i18nHead.htmlAttrs.lang" :dir="i18nHead.htmlAttrs.dir">
      <!-- header -->
      <div id="topbar" class="col-span-full row-span-1 row-start-1 print:hidden">
        <TopBar />
      </div>

      <!-- sidebar -->
      <aside
        class="col-span-1 col-start-1 row-span-17 row-start-2 hidden h-full truncate text-center dark:bg-gray-900 lg:flex print:lg:hidden"
      >
        <!-- Tablet / desktop page navigation -->
        <SideBar />
      </aside>

      <div
        id="contentContainer"
        class="col-span-full row-span-16 flex h-full flex-col overflow-y-auto rounded-t-lg bg-gray-100 shadow-lg shadow-gray-300 dark:bg-gray-800 dark:shadow-black md:rounded-bl-lg md:rounded-tr-none lg:col-span-11 lg:row-span-17 print:w-screen print:!overflow-visible print:bg-white"
      >
        <slot />

        <!-- <NuxtErrorBoundary>
        <template #error="{ error, clearError }">
          <section
            class="grid relative items-center h-full text-center bg-gray-100 items-top dark:bg-gray-900 sm:items-center sm:pt-0"
          >
            <div>
              <div
                class="px-4 text-lg tracking-wider text-gray-500 border-r border-gray-400"
              >
                <h1>An Error Occurred</h1>
              </div>
              <div class="ml-4 text-lg tracking-wider text-gray-500 uppercase">
                {{ error }}
              </div>
              <br />
              <UIButton @click="clearError">{{
                $t("Reset and try again")
              }}</UIButton>
              <UIButton @click="clearError({ redirect: '/' })">{{
                $t("Go back to home")
              }}</UIButton>
            </div>
          </section>
        </template>
      </NuxtErrorBoundary> -->
      </div>

      <div class="row-span-1 w-screen lg:hidden print:hidden">
        <!-- Mobile page navigation -->
        <BottomBar />
      </div>
    </Html>
  </div>
</template>

<script setup>
const i18nHead = useLocaleHead({
  seo: {},
  addDirAttribute: true,
  identifierAttribute: "id",
  addSeoAttributes: true,
});
useHead({
  link: [
    {
      rel: "icon",
      type: "image/x-icon",
      href: "base/favicon.ico",
    },
    { rel: "icon", type: "image/png", sizes: "32x32", href: "base/favicon-32x32.png" },
    { rel: "icon", type: "image/png", sizes: "16x16", href: "base/favicon-16x16.png" },
  ],
  htmlAttrs: {
    lang: () => i18nHead.value.htmlAttrs?.lang, // somehow not working...
    dir: () => i18nHead.value.htmlAttrs?.dir,
  },
});
</script>
