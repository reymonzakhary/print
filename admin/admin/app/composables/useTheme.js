export const useTheme = () => {
  const activeTheme = useCookie("prindustry:theme", {
    default: () => (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"),
  });
  const isDark = ref(activeTheme.value === "dark");

  useHead(() => ({
    htmlAttrs: {
      class: activeTheme,
    },
  }));

  watch(isDark, (newVal) => {
    activeTheme.value = newVal ? "dark" : "light";
  });

  const toggleTheme = () => {
    isDark.value = !isDark.value;
  };

  return {
    isDark,
    toggleTheme,
  };
};
