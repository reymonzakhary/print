export const useThemeStore = defineStore("theme", () => {
  const activeTheme = ref("light");
  const autoTheme = ref(false);
  const hex = ref("#46afcf");
  const { addToast } = useToastStore();

  // Options for color shade generation
  const shadeOptions = ref({ stepUp: 8, stepDown: 8 });

  // Computed property to get color shades
  const colorShades = computed(() => generateShadesFromHex(hex.value));

  // Generate theme colors CSS string
  const colorsCssString = computed(() => generateColorsCssString(colorShades.value.shades));

  function generateColorsCssString(shades) {
    const themeColors = generateThemeColorsFromShades(shades);
    return Object.entries(themeColors)
      .map(([key, value]) => `${key}: ${value};`)
      .join(" ");
  }

  // Initialize theme from cookies
  async function initTheme() {
    const activeThemeCookie = useCookie("prindustry:active_theme");
    const autoThemeCookie = useCookie("prindustry:auto_theme");
    const themeCookie = useCookie("prindustry:theme");

    if (activeThemeCookie.value) {
      activeTheme.value = activeThemeCookie.value;
    } else {
      activeThemeCookie.value = activeTheme.value;
    }

    if (autoThemeCookie.value !== undefined) {
      autoTheme.value = autoThemeCookie.value === "true";
    }

    if (themeCookie.value) {
      hex.value = themeCookie.value;
    } else {
      const api = useAPI();
      const { handleError } = useMessageHandler();
      try {
        const { data } = await api.get(`account/settings?namespace=themes&area=colors`);
        themeCookie.value = data[0]?.value;
      } catch (error) {
        handleError(error);
        throw new Error("Failed to get settings");
      }
    }

    applyTheme();
  }

  // Apply theme to document
  function applyTheme() {
    useHead(() => ({
      htmlAttrs: {
        "data-theme": activeTheme.value,
        class: activeTheme.value,
      },
      link: [
        {
          rel: "icon",
          type: "image/x-icon",
          href: `/base/favicon.ico`,
        },
      ],
      style: `
        :root {
          ${colorsCssString.value}}
        }
        `,
    }));
  }

  // Generate color shades from a hex color
  function generateShadesFromHex(hexColor) {
    // Convert hex to HSL
    const hslValues = hexToHSL(hexColor);
    const stepUp = shadeOptions.value.stepUp;
    const stepDown = shadeOptions.value.stepDown;
    const shades = [];

    // Generate lighter shades
    for (let i = 4; i >= 1; i--) {
      const lightness = hslValues[2] + i * stepUp;
      const adjustedLightness = lightness > 100 ? 100 : lightness;
      const shadeHex = HSLToHex(hslValues[0], hslValues[1], adjustedLightness);
      shades.push({
        hex: shadeHex,
        textColor: textColorFromBrightness(shadeHex),
      });
    }

    // Add base color
    shades.push({
      hex: HSLToHex(hslValues[0], hslValues[1], hslValues[2]),
      textColor: textColorFromBrightness(HSLToHex(hslValues[0], hslValues[1], hslValues[2])),
    });

    // Generate darker shades
    for (let i = 1; i < 6; i++) {
      const lightness = hslValues[2] - i * stepDown;
      const adjustedLightness = lightness < 0 ? 0 : lightness;
      const shadeHex = HSLToHex(hslValues[0], hslValues[1], adjustedLightness);
      shades.push({
        hex: shadeHex,
        textColor: textColorFromBrightness(shadeHex),
      });
    }

    return {
      hsl: hslValues,
      shades,
    };
  }

  // Generate theme colors object from shades
  function generateThemeColorsFromShades(shades) {
    const themeColors = {};

    // Map shades to CSS variables
    for (let i = 0; i <= 9; i++) {
      const shade = shades[i];
      const index = i === 0 ? 50 : i * 100;
      themeColors[`--theme-${index}`] = shade.hex;
      themeColors[`--text-color-${index}`] = shade.textColor;
    }

    return themeColors;
  }

  function previewHexColor(color) {
    hex.value = color;
    applyTheme();
  }

  // Set hex color
  async function setHexColor(color) {
    hex.value = color;
    useCookie("prindustry:theme").value = color;
    const api = useAPI();
    const { handleError } = useMessageHandler();
    try {
      const { data } = await api.put(`/account/settings/theme_colors`, {
        value: color,
      });
      addToast({
        type: "success",
        message: data.message || "Theme color updated successfully",
      });
    } catch (error) {
      handleError(error);
      throw new Error("Failed to get settings");
    }
    applyTheme();
  }

  // Set active theme
  function setActiveTheme(theme) {
    activeTheme.value = theme;
    useCookie("prindustry:active_theme").value = theme;
  }

  // Set auto theme
  function setAutoTheme(value) {
    autoTheme.value = value;
    if (value) detectColorScheme();
    useCookie("prindustry:auto_theme").value = String(value);
  }

  // Detect system color scheme preference
  function detectColorScheme() {
    if (autoTheme.value) {
      const isDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
      const isLightMode = window.matchMedia("(prefers-color-scheme: light)").matches;

      if (isDarkMode) {
        setActiveTheme("dark");
      } else if (isLightMode) {
        setActiveTheme("light");
      } else {
        // Default to light if no preference
        setActiveTheme("light");
      }
    }
  }

  // COLOR UTILITY FUNCTIONS

  // Utility function to determine text color based on background brightness
  function textColorFromBrightness(hexColor) {
    const rgb = hexToRGB(hexColor);
    // Calculate brightness using the perceived brightness formula
    const brightness = (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
    return brightness > 125 ? "black" : "white";
  }

  // Get brightness for UI purposes
  function getBrightness(hexColor) {
    const rgb = hexToRGB(hexColor);
    return (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
  }

  // Convert hex color to HSL
  function hexToHSL(hexColor) {
    // Convert hex to RGB first
    let r = 0;
    let g = 0;
    let b = 0;

    if (hexColor.length === 4) {
      r = parseInt("0x" + hexColor[1] + hexColor[1], 16);
      g = parseInt("0x" + hexColor[2] + hexColor[2], 16);
      b = parseInt("0x" + hexColor[3] + hexColor[3], 16);
    } else if (hexColor.length === 7) {
      r = parseInt("0x" + hexColor[1] + hexColor[2], 16);
      g = parseInt("0x" + hexColor[3] + hexColor[4], 16);
      b = parseInt("0x" + hexColor[5] + hexColor[6], 16);
    }

    // Convert RGB to HSL
    r /= 255;
    g /= 255;
    b /= 255;

    const cmin = Math.min(r, g, b);
    const cmax = Math.max(r, g, b);
    const delta = cmax - cmin;

    let h = 0;
    let s = 0;
    let l = 0;

    if (delta === 0) {
      h = 0;
    } else if (cmax === r) {
      h = ((g - b) / delta) % 6;
    } else if (cmax === g) {
      h = (b - r) / delta + 2;
    } else {
      h = (r - g) / delta + 4;
    }

    h = Math.round(h * 60);
    if (h < 0) h += 360;

    l = (cmax + cmin) / 2;
    s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));

    s = +(s * 100).toFixed(1);
    l = +(l * 100).toFixed(1);

    return [h, s, l];
  }

  // Convert hex color to RGB
  function hexToRGB(hexColor) {
    let r = 0,
      g = 0,
      b = 0;

    // 3 digits
    if (hexColor.length === 4) {
      r = parseInt("0x" + hexColor[1] + hexColor[1], 16);
      g = parseInt("0x" + hexColor[2] + hexColor[2], 16);
      b = parseInt("0x" + hexColor[3] + hexColor[3], 16);
    }
    // 6 digits
    else if (hexColor.length === 7) {
      r = parseInt("0x" + hexColor[1] + hexColor[2], 16);
      g = parseInt("0x" + hexColor[3] + hexColor[4], 16);
      b = parseInt("0x" + hexColor[5] + hexColor[6], 16);
    }

    return [r, g, b];
  }

  // Convert HSL to hex color
  function HSLToHex(h, s, l) {
    s /= 100;
    l /= 100;

    const c = (1 - Math.abs(2 * l - 1)) * s;
    const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
    const m = l - c / 2;

    let r = 0,
      g = 0,
      b = 0;

    if (0 <= h && h < 60) {
      r = c;
      g = x;
      b = 0;
    } else if (60 <= h && h < 120) {
      r = x;
      g = c;
      b = 0;
    } else if (120 <= h && h < 180) {
      r = 0;
      g = c;
      b = x;
    } else if (180 <= h && h < 240) {
      r = 0;
      g = x;
      b = c;
    } else if (240 <= h && h < 300) {
      r = x;
      g = 0;
      b = c;
    } else if (300 <= h && h < 360) {
      r = c;
      g = 0;
      b = x;
    }

    r = Math.round((r + m) * 255).toString(16);
    g = Math.round((g + m) * 255).toString(16);
    b = Math.round((b + m) * 255).toString(16);

    // Ensure 2 digits for each component
    if (r.length === 1) r = "0" + r;
    if (g.length === 1) g = "0" + g;
    if (b.length === 1) b = "0" + b;

    return "#" + r + g + b;
  }

  // Watch for theme changes and update document
  watch([hex, activeTheme], () => applyTheme());

  return {
    activeTheme,
    autoTheme,
    hex,
    shadeOptions,
    colorShades,
    colorsCssString,
    generateColorsCssString,
    initTheme,
    applyTheme,
    setActiveTheme,
    setAutoTheme,
    setHexColor,
    previewHexColor,
    detectColorScheme,
    generateShadesFromHex,
    textColorFromBrightness,
    getBrightness,
    hexToHSL,
    hexToRGB,
    HSLToHex,
  };
});
