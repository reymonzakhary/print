export default {
  state: () => ({
    active_theme: "light",
    auto_theme: false,
    active_menu_item: "dashboard",
    themeColors: {
      "--text-color-50": "#000000",
      "--text-color-100": "#000000",
      "--text-color-200": "#000000",
      "--text-color-300": "#000000",
      "--text-color-400": "#ffffff",
      "--text-color-500": "#ffffff",
      "--text-color-600": "#ffffff",
      "--text-color-700": "#ffffff",
      "--text-color-800": "#ffffff",
      "--text-color-900": "#ffffff",
      "--theme-50": "#c0e3ee",
      "--theme-100": "#97d2e4",
      "--theme-200": "#6fc0d9",
      "--theme-300": "#46afcf",
      "--theme-400": "#2f94b3",
      "--theme-500": "#24738b",
      "--theme-600": "#1a5162",
      "--theme-700": "#0f303a",
      "--theme-800": "#050e11",
      "--theme-900": "#000000",
    },
    theme_colors: {
      "--theme-50": "#c0e3ee",
      "--theme-100": "#97d2e4",
      "--theme-200": "#6fc0d9",
      "--theme-300": "#46afcf",
      "--theme-400": "#2f94b3",
      "--theme-500": "#24738b",
      "--theme-600": "#1a5162",
      "--theme-700": "#0f303a",
      "--theme-800": "#050e11",
      "--theme-900": "#000000",
    },
    text_colors: {
      "--text-color-50": "#000000",
      "--text-color-100": "#000000",
      "--text-color-200": "#000000",
      "--text-color-300": "#000000",
      "--text-color-400": "#ffffff",
      "--text-color-500": "#ffffff",
      "--text-color-600": "#ffffff",
      "--text-color-700": "#ffffff",
      "--text-color-800": "#ffffff",
      "--text-color-900": "#ffffff",
    },
    hex: "#46afcf",
  }),
  mutations: {
    set_active_theme(state, theme) {
      state.active_theme = theme;
    },
    set_auto_theme(state, boolean) {
      state.auto_theme = boolean;
    },
    set_active_menu_item(state, value) {
      state.active_menu_item = value;
    },
    set_theme_colors(state, colors) {
      state.theme_colors["--theme-50"] = colors[0].hex;
      state.theme_colors["--theme-100"] = colors[1].hex;
      state.theme_colors["--theme-200"] = colors[2].hex;
      state.theme_colors["--theme-300"] = colors[3].hex;
      state.theme_colors["--theme-400"] = colors[4].hex;
      state.theme_colors["--theme-500"] = colors[5].hex;
      state.theme_colors["--theme-600"] = colors[6].hex;
      state.theme_colors["--theme-700"] = colors[7].hex;
      state.theme_colors["--theme-800"] = colors[8].hex;
      state.theme_colors["--theme-900"] = colors[9].hex;
      state.text_colors["--text-color-50"] = colors[0].textColor;
      state.text_colors["--text-color-100"] = colors[1].textColor;
      state.text_colors["--text-color-200"] = colors[2].textColor;
      state.text_colors["--text-color-300"] = colors[3].textColor;
      state.text_colors["--text-color-400"] = colors[4].textColor;
      state.text_colors["--text-color-500"] = colors[5].textColor;
      state.text_colors["--text-color-600"] = colors[6].textColor;
      state.text_colors["--text-color-700"] = colors[7].textColor;
      state.text_colors["--text-color-800"] = colors[8].textColor;
      state.text_colors["--text-color-900"] = colors[9].textColor;
      state.themeColors["--theme-50"] = colors[0].hex;
      state.themeColors["--theme-100"] = colors[1].hex;
      state.themeColors["--theme-200"] = colors[2].hex;
      state.themeColors["--theme-300"] = colors[3].hex;
      state.themeColors["--theme-400"] = colors[4].hex;
      state.themeColors["--theme-500"] = colors[5].hex;
      state.themeColors["--theme-600"] = colors[6].hex;
      state.themeColors["--theme-700"] = colors[7].hex;
      state.themeColors["--theme-800"] = colors[8].hex;
      state.themeColors["--theme-900"] = colors[9].hex;
      state.themeColors["--text-color-50"] = colors[0].textColor;
      state.themeColors["--text-color-100"] = colors[1].textColor;
      state.themeColors["--text-color-200"] = colors[2].textColor;
      state.themeColors["--text-color-300"] = colors[3].textColor;
      state.themeColors["--text-color-400"] = colors[4].textColor;
      state.themeColors["--text-color-500"] = colors[5].textColor;
      state.themeColors["--text-color-600"] = colors[6].textColor;
      state.themeColors["--text-color-700"] = colors[7].textColor;
      state.themeColors["--text-color-800"] = colors[8].textColor;
      state.themeColors["--text-color-900"] = colors[9].textColor;
      state.hex = colors[4].hex;
      return state.themeColors;
    },
    set_theme_colors_from_cookie(state, colors) {
      state.themeColors = colors;
    },
  },
  actions: {
    save_theme_colors({ commit }, colors) {
      return commit("set_theme_colors", colors);
    },
    get_theme_colors({ commit, rootState }) {
      let theme_colors = rootState.usersettings.settings.data.filter(
        (setting) => setting.key === "theme_colors",
      );
      commit("set_theme_colors", JSON.parse(theme_colors[0].value));
    },
  },
};
