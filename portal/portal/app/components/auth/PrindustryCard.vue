<template>
  <div class="prindustry-card flex w-full rounded-lg">
    <div
      class="w-full rounded-lg bg-gradient-to-t from-transparent to-theme-500 p-8 shadow-lg shadow-theme-300"
    >
      <div class="flex h-full flex-col items-center justify-between">
        <header class="mb-4 flex w-80 items-end justify-center text-black">
          <img
            src="~/assets/images/Prindustry_logo_wit.png"
            alt="Prindustry Logo"
            aria-hidden="true"
            class="w-1/2 drop-shadow-lg"
          />
          <span class="text ml-2 text-lg font-bold uppercase italic">
            {{ $t("manager") }}
          </span>
        </header>

        <div class="w-full text-sm">
          <!-- <h3 // not very handy as the backend releases are not in sync with the frontend, so we cannot get this number synced without retreiving it from github
            class="flex w-full items-center justify-between gap-2 border-b border-theme-100 font-mono text-theme-50"
          >
            <span class="">{{ $t("system") }}</span>
            <span class="">v3.7.2</span>
          </h3> -->
          <h3 class="flex w-full items-center justify-between gap-2 font-mono text-theme-50">
            <span class="">{{ $t("interface") }}</span>
            <span class="">v{{ $config.public.version }}</span>
          </h3>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup></script>

<style lang="scss" scoped>
.prindustry-card {
  opacity: 0;
  animation: slideIn 0.4s 0.3s forwards;

  background: url("~/assets/images/mountain2.webp") center top no-repeat;
  // filter: grayscale(100%);
  background-size: cover;
  transition: opacity 200ms ease-in;
}

@keyframes slideIn {
  0% {
    opacity: 0;
    border-radius: 0;
    width: 0;
    // height: 0;
  }
  100% {
    opacity: 1;
  }
}
</style>
