import { MaskInput } from "vue-3-mask";

export default defineNuxtPlugin({
  name: "mask",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.vueApp.component("VueTheMask", MaskInput);
  },
});
