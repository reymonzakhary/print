import { ref, computed } from "vue";
import { defineNuxtPlugin } from "#app";

// Define Tailwind breakpoints in pixels
// These match Tailwind's default breakpoints
const BREAKPOINTS = {
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  "2xl": 1536,
};

export default defineNuxtPlugin((nuxtApp) => {
  // Create reactive width and height refs
  const width = ref(typeof window !== "undefined" ? window.innerWidth : 0);
  const height = ref(typeof window !== "undefined" ? window.innerHeight : 0);

  const breakpoint = ref("xs");
  watch(
    width,
    () => {
      // Default to xs if width is smaller than sm breakpoint
      if (width.value < BREAKPOINTS.sm) return "xs";

      // Find the largest breakpoint that the current width exceeds
      const currentBreakpoint = Object.entries(BREAKPOINTS)
        .reverse()
        .find(([, minWidth]) => width.value >= minWidth);

      breakpoint.value = currentBreakpoint ? currentBreakpoint[0] : "xs";
    },
    { immediate: true },
  );

  // Computed properties for easy breakpoint checks
  const isXs = computed(() => breakpoint.value === "xs");
  const isSm = computed(() => breakpoint.value === "sm");
  const isMd = computed(() => breakpoint.value === "md");
  const isLg = computed(() => breakpoint.value === "lg");
  const isXl = computed(() => breakpoint.value === "xl");
  const isXxl = computed(() => breakpoint.value === "2xl");

  // Computed properties for comparison checks
  const smAndUp = computed(() => width.value >= BREAKPOINTS.sm);
  const mdAndUp = computed(() => width.value >= BREAKPOINTS.md);
  const lgAndUp = computed(() => width.value >= BREAKPOINTS.lg);
  const xlAndUp = computed(() => width.value >= BREAKPOINTS.xl);
  const xxlAndUp = computed(() => width.value >= BREAKPOINTS["2xl"]);

  // Update dimensions on resize
  if (typeof window !== "undefined") {
    const updateDimensions = () => {
      width.value = window.innerWidth;
      height.value = window.innerHeight;
    };

    window.addEventListener("resize", updateDimensions);
    nuxtApp.hook("app:beforeMount", () => {
      updateDimensions();
    });
  }

  // Create the screen object with all our reactive properties
  const screen = {
    width,
    height,
    breakpoint,
    isXs,
    isSm,
    isMd,
    isLg,
    isXl,
    isXxl,
    smAndUp,
    mdAndUp,
    lgAndUp,
    xlAndUp,
    xxlAndUp,
  };

  return {
    provide: {
      screen: reactive(screen),
    },
  };
});
