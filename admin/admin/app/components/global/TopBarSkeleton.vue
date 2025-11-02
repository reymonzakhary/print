<template>
  <header class="flex items-center h-full py-1 text-white bg-theme-500 dark:bg-theme-700">
    <nav class="flex items-center justify-between w-full h-full gap-2 px-2">
      <div class="flex items-center">
        <NuxtLink class="w-auto mr-1" to="/">
          <img
            id="prindustry-logo"
            src="/images/Prindustry_logo_wit.png"
            alt="Prindustry Logo"
            class="h-8 max-w-none"
          />
        </NuxtLink>
        <span class="mt-2 text-sm italic font-semibold uppercase"> admin manager </span>
      </div>
      <SkeletonLine
        class="flex-1 h-full bg-transparent rounded-full dark:bg-transparent"
        style="--animation-speed: 1500ms"
      />
    </nav>
  </header>
</template>
