import vSelect from "vue-select";

export default defineNuxtPlugin({
  name: "vue-select",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.vueApp.component("v-select", vSelect);
  },
});
