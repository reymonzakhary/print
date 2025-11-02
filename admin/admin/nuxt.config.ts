import pkg from "./package.json";

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
    pageTransition: {
      name: "page",
      mode: "out-in",
    },
    head: {
      htmlAttrs: {
        style: "background: black",
      },
    },
  },

  runtimeConfig: {
    public: {
      version: pkg.version,
      baseURL: process.env.BASE_URL,
      auth: {
        excludedPaths: ["/auth/login", "/auth/forgot-credentials", "/auth/verify", "/auth/logout"],
        refreshTokenPath: "/auth/refresh-token",
        tokenPath: "/auth/login",
      },
      toast: {
        dissapearanceTime: 5000,
      },
    },
  },

  modules: [
    "@nuxt/eslint",
    "@nuxtjs/tailwindcss",
    "@pinia/nuxt",
    "floating-vue/nuxt",
    "@vee-validate/nuxt",
  ],

  imports: {
    dirs: ["repositories"],
  },

  css: [
    "@fortawesome/fontawesome-svg-core/styles.css",
    "vue-select/dist/vue-select.css",
    "~/assets/scss/master.scss",
  ],

  build: { transpile: ["@fortawesome/vue-fontawesome"] },

  compatibilityDate: "2024-07-03",
});
