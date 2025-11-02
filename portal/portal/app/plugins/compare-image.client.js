import VueCompareImages from "vue3-compare-image";

export default defineNuxtPlugin({
  name: "compare-image",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.vueApp.use(VueCompareImages);
  },
});
