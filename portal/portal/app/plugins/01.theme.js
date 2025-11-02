export default defineNuxtPlugin({
  name: "theme",
  parallel: true,
  async setup() {
    const themeStore = useThemeStore();

    // Initialize theme from cookies
    themeStore.initTheme();

    // Apply theme to document
    themeStore.applyTheme();
  },
});
