import FloatingVue from "floating-vue";

export default defineNuxtPlugin({
  name: "floating-vue",
  parallel: true,
  setup() {
    FloatingVue.options.themes["smart-search"] = {
      $extend: "dropdown",
      $resetCss: true,
      triggers: [],
    };
  },
});
