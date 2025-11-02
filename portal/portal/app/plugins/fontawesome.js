import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon, FontAwesomeLayers } from "@fortawesome/vue-fontawesome";
import { registerIcons } from "~~/.prindustry/fa-icons";

export default defineNuxtPlugin({
  name: "fontawesome",
  parallel: true,
  async setup(nuxtApp) {
    config.autoAddCss = false;

    registerIcons();

    nuxtApp.vueApp.component("font-awesome-icon", FontAwesomeIcon, {});
    nuxtApp.vueApp.component("font-awesome-layers", FontAwesomeLayers, {});
  },
});
