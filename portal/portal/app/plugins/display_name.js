// plugins/display-name.js
export default defineNuxtPlugin({
  name: "display_name",
  parallel: true,
  setup(nuxtApp) {
    nuxtApp.provide("display_name", (display_name) => {
      const { $i18n } = useNuxtApp(); // Access the i18n instance
      const lang = $i18n.locale.value;

      if (Array.isArray(display_name)) {
        let res = display_name.find((name) => name.iso === lang)?.display_name;
        return !res ? display_name[0].display_name : res;
      } else {
        return display_name;
      }
    });
  },
});
