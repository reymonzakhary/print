<template>
  <header
    id="topbar"
    class="fixed top-0 z-40 items-center w-screen px-4 py-1 bg-gray-100 md:h-full md:relative dark:bg-gray-900 md:bg-transparent md:flex"
    role="navigation"
    aria-label="main navigation"
  >
    <nav
      class="flex items-center justify-between w-screen h-full px-2 md:w-auto"
    >
      <div class="flex items-center">
        <img
          id="prindustry-logo"
          src="~/assets/images/Prindustry_logo.png"
          alt="Prindustry Logo"
          class="h-8 max-w-none"
        />
        <span class="mt-2 text-sm italic font-semibold uppercase">
          {{ $t("manager") }}
        </span>
        <small class="mt-2 ml-2 font-mono text-gray-500"> v3.2.1 </small>
      </div>
    </nav>
    <div class="flex flex-col w-full">
      <TopBarMenuPlaceholder class="w-auto" />
    </div>
  </header>
</template>
