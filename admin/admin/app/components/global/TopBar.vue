<template>
  <header
    id="topbar"
    class="flex items-center py-1 h-full text-white bg-theme-500 dark:bg-theme-700"
    role="navigation"
    aria-label="main navigation"
  >
    <nav class="flex justify-between items-center px-2 w-full h-full">
      <div class="flex items-center">
        <NuxtLink class="mr-1 w-auto" to="/">
          <img
            id="prindustry-logo"
            src="/images/Prindustry_logo_wit.png"
            alt="Prindustry Logo"
            class="max-w-none h-8"
          />
        </NuxtLink>
        <span class="hidden mt-2 text-sm italic font-semibold uppercase sm:inline">
          admin manager
        </span>
      </div>

      <div class="flex items-center h-full">
        <Notifications disabled />

        <!-- Seperator -->
        <div class="w-[0.025rem] h-5 mx-4 bg-theme-800" />

        <!-- Dark Mode Switch -->
        <div class="flex relative justify-center items-center">
          <font-awesome-icon
            :icon="['fad', 'sun']"
            class="fa-lg"
            :class="{ 'text-yellow-500': !isDark }"
          />

          <div
            class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
            :class="[isDark ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="toggle"
              class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
              :class="[isDark ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300']"
            />
            <input
              id="toggle"
              v-model="isDark"
              type="checkbox"
              name="toggle"
              class="w-full h-full appearance-none active:outline-none focus:outline-none"
              @click="toggleTheme"
            />
          </div>

          <font-awesome-icon
            :icon="['fad', 'moon-stars']"
            class="mb-1 fa-lg"
            :class="{ 'text-white': isDark }"
          />
        </div>

        <!-- Seperator -->
        <div class="w-[0.025rem] h-5 mx-4 bg-theme-800 hidden sm:block" />

        <!-- User -->
        <div class="hidden items-center sm:flex">
          <div class="flex justify-center items-center w-10 h-10 rounded-full bg-theme-400">
            <font-awesome-icon :icon="['fad', 'user-circle']" class="fa-lg" />
          </div>
          <span class="ml-2 text-sm font-semibold">{{ sessionStore.session?.username }}</span>
        </div>

        <!-- Seperator -->
        <div class="w-[0.025rem] h-5 ml-4 mr-2 bg-theme-800" />

        <!-- Sign Out -->
        <UIButton
          v-tooltip="'Sign out'"
          to="/auth/logout"
          variant="theme"
          :icon="['fad', 'sign-out-alt']"
        />
      </div>
    </nav>
  </header>
</template>

<script setup>
const sessionStore = useSessionStore();
const { isDark, toggleTheme } = useTheme();
</script>
