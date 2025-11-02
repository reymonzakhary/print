import { library, config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon, FontAwesomeLayers } from "@fortawesome/vue-fontawesome";
import { fas } from "@fortawesome/pro-solid-svg-icons";
import { far } from "@fortawesome/pro-regular-svg-icons";
import { fal } from "@fortawesome/pro-light-svg-icons";
import { fat } from "@fortawesome/pro-thin-svg-icons";
import { fad } from "@fortawesome/pro-duotone-svg-icons";
import { fab } from "@fortawesome/free-brands-svg-icons";

export default defineNuxtPlugin({
  name: "fontawesome",
  parallel: true,
  setup(nuxtApp) {
    config.autoAddCss = false;

    library.add(fas);
    library.add(far);
    library.add(fal);
    library.add(fat);
    library.add(fad);
    library.add(fab);

    nuxtApp.vueApp.component("font-awesome-icon", FontAwesomeIcon, {});
    nuxtApp.vueApp.component("font-awesome-layers", FontAwesomeLayers, {});
  },
});
