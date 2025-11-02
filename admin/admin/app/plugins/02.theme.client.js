export default defineNuxtPlugin({
  name: "theme.client",
  client: true,
  async setup() {
    useTheme();
  },
});
