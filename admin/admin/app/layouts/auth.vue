<template>
  <div class="flex flex-col h-screen bg-gradient-to-br from-green-400 via-blue-500 to-indigo-500">
    <nav class="flex items-center h-12 px-2">
      <img
        id="prindustry-logo"
        src="/images/Prindustry_logo_wit.png"
        alt="Prindustry Logo"
        class="h-8 mr-1"
      />
      <span class="mt-2 text-sm text-white uppercase">Manager</span>
    </nav>
    <div class="grid flex-grow place-items-center">
      <!-- The -mt-12 class offsets the h-12 navbar height to visually center the container. -->
      <div class="p-4 -mt-12 bg-white rounded-lg shadow-lg w-96">
        <slot />
      </div>
    </div>
  </div>
</template>
