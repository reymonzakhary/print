import { RecycleScroller } from "vue-virtual-scroller";
import "vue-virtual-scroller/dist/vue-virtual-scroller.css";

export default defineNuxtPlugin({
  name: "virtual-scroller",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.vueApp.component("RecycleScroller", RecycleScroller);
  },
});
