import pkg from "./package.json";
import { existsSync } from "fs";
import { execSync } from "child_process";
import { resolve } from "path";

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  future: {
    compatibilityVersion: 4,
  },

  ssr: false,

  devtools: {
    enabled: true,
    timeline: {
      enabled: true,
    },
  },

  app: {
    baseURL: "/manager",
    pageTransition: { name: "page", mode: "out-in" },
  },

  runtimeConfig: {
    public: {
      version: pkg.version,
      frontendUrl: process.env.FRONTEND_URL,
      baseURL: process.env.BASE_URL,
      googleMapApiKey: process.env.GOOGLE_MAP_API,
      auth: {
        excludedPaths: ["/auth/login", "/auth/forgot-credentials", "/auth/verify", "/auth/logout"],
      },
      toast: {
        dissapearanceTime: 5000,
      },
      formSaveDebounceTime: 10000,
    },
  },

  routeRules: {
    "/login": { redirect: "/manager/auth/login" },
    "/forgot-credentials": { redirect: "/manager/auth/forgot-credentials" },
    "/logout": { redirect: "/manager/auth/logout" },
  },

  // Modules
  modules: [
    "@nuxtjs/tailwindcss",
    "@nuxtjs/i18n",
    "nuxt3-vuex-module",
    "@pinia/nuxt",
    "@vee-validate/nuxt",
    "nuxt-tour",
    "floating-vue/nuxt",
    "@nuxt/test-utils/module",
    "@nuxt/eslint",
    "@vueuse/nuxt",
    "@nuxt/image",
  ],

  // Imports
  imports: {
    dirs: ["repositories", "permissions"],
  },

  // CSS
  css: [
    "@fortawesome/fontawesome-svg-core/styles.css",
    "~/assets/scss/master.scss",
    "vue-select/dist/vue-select.css",
    "vue-color/style.css",
  ],

  build: { transpile: ["@fortawesome/vue-fontawesome"] },

  // Components
  // This is for backwards compatibility. We should consider renaming the components to follow the Nuxt 3 naming convention.
  // Please refer to this page on Nuxt documentation for proper component naming: https://nuxt.com/docs/guide/directory-structure/components
  components: [
    {
      path: "~/components",
      pathPrefix: false,
    },
  ],

  i18n: {
    bundle: {
      optimizeTranslationDirective: false,
    },
    detectBrowserLanguage: false,
    strategy: "no_prefix",
    locales: [
      { code: "en", name: "English", file: "en.json" },
      { code: "nl", name: "Dutch", file: "nl.json" },
    ],
    lazy: true,
    defaultLocale: "en",
  },

  /**
   * Creates a JSON file with all the icons used in the project.
   * This is used to generate the icons.json file for the fontawesome plugin.
   * This is only needed for the fontawesome plugin to work without a huge performance hit.
   */
  watch: [".prindustry/icons.json", "app/permissions/schemas/**/*.js"],
  hooks: {
    "build:before": () => {
      const iconsPath = resolve(__dirname, ".prindustry/icons.json");
      if (!existsSync(iconsPath)) {
        console.log("Generating icons.json...");
        execSync("node scripts/extract-icons.cjs", { stdio: "inherit" });
      }
    },
    // For the dev server using Vite:
    "vite:serverCreated": () => {
      const iconsPath = resolve(__dirname, ".prindustry/icons.json");
      if (!existsSync(iconsPath)) {
        console.log("Auto-generated icons.json not found. Generating...");
        execSync("node scripts/extract-icons.cjs", { stdio: "inherit" });
      }
    },
  },

  compatibilityDate: "2024-07-03",
});
