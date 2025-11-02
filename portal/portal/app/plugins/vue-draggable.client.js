import Draggable from "vuedraggable";

export default defineNuxtPlugin({
  name: "vue-draggable",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.vueApp.component("draggable", Draggable);
  },
});
