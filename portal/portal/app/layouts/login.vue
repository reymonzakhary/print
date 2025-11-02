<template>
  <div
    class="background-image flex h-screen items-center bg-gradient-to-tl from-theme-500 to-theme-50 text-theme-900 backdrop-opacity-50 dark:from-theme-800 dark:to-gray-600"
  >
    <div class="mx-auto h-screen w-screen backdrop-blur-sm md:p-16">
      <div
        class="background-card mx-auto flex h-full w-full bg-white/80 p-4 shadow-2xl backdrop-blur-md dark:bg-gray-800 md:rounded-2xl lg:w-2/3"
      >
        <div class="hidden w-1/2 overflow-hidden rounded-lg md:flex xl:w-1/3">
          <PrindustryCard />
        </div>

        <div
          class="relative flex w-full items-center justify-center dark:text-white md:w-1/2 xl:w-2/3"
        >
          <nav class="absolute top-0 flex w-full justify-end">
            <UIButton
              v-if="!$route.path.includes('/login')"
              variant="link"
              to="/login"
              :icon="['fal', 'arrow-left']"
              class="!text-base hover:!bg-theme-100"
            >
              {{ $t("back") }}
            </UIButton>
          </nav>

          <div class="w-full p-8 sm:w-2/3 md:w-full xl:w-7/12">
            <header class="mx-auto mb-8 flex max-w-80 flex-wrap items-end justify-center md:hidden">
              <img src="~/assets/images/Prindustry_logo.png" alt="Prindustry Logo" class="w-1/2" />
              <span class="text text-lg font-bold uppercase italic">
                {{ $t("manager") }}
              </span>
              <h3 class="flex w-full items-center justify-end gap-2 font-mono text-theme-500">
                <span aria-label="Version number">v{{ $config.public.version }}</span>
              </h3>
            </header>

            <figure class="logo mb-8 flex h-full max-h-40 rounded-lg border bg-white p-4">
              <img
                v-if="tenantLogo"
                :src="tenantLogo"
                alt="Tenant logo"
                class="mx-auto object-contain"
              />
              <img
                v-else
                src="~/assets/images/Prindustry_logo.png"
                alt="Prindustry Logo"
                class="w-1/2"
              />
            </figure>

            <!-- slot for components -->
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  link: [
    {
      rel: "icon",
      type: "image/x-icon",
      href: "lock/favicon.ico",
    },
    { rel: "icon", type: "image/png", sizes: "32x32", href: "lock/favicon-32x32.png" },
    { rel: "icon", type: "image/png", sizes: "16x16", href: "lock/favicon-16x16.png" },
  ],
});

const tenantLogo = ref("");

useAsyncData("tenant", async () => {
  const { get } = useAPI();
  const result = await get("/info");
  tenantLogo.value = result.logo;
});
</script>

<style lang="scss" scoped>
.background-image:before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: url("~/assets/images/mountain2.webp") center top no-repeat;
  // filter: grayscale(100%);

  background-size: cover;
  transition: opacity 200ms ease-in;
  opacity: 0.5;
  z-index: -1;
}

@media (prefers-reduced-motion: reduce) {
  .logo {
    animation: none;
    opacity: 1;
  }
}

.logo {
  opacity: 0;
  animation: slideDown 0.4s 0.6s forwards cubic-bezier(0.075, 0.82, 0.165, 1);
}

.logo img {
  opacity: 1;
  transition: opacity 0.2s ease-in-out;
}

.logo img[src=""] {
  opacity: 0;
}

.background-card {
  opacity: 0;
  animation: fadeIn 0.4s 0s forwards;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    border-radius: 0;
    // width: 0;
    // height: 0;
  }
  100% {
    opacity: 1;
  }
}

@keyframes slideDown {
  0% {
    opacity: 0;
    border-radius: 0;
    // width: 0;
    height: 0;
  }
  100% {
    opacity: 1;
  }
}
</style>
