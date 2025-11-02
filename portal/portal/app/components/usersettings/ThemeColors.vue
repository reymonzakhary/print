<template>
  <div>
    <p>{{ $t("pick themecolor") }}</p>
    <!-- preset colors -->
    <div>
      <button
        style="background-color: #46afcf"
        class="rounded p-2 text-white dark:text-white"
        @click="setTheme('#46afcf')"
      >
        {{ $t("Prindustry") }} {{ $t("blue") }}
      </button>

      <button
        class="rounded bg-blue-500 p-2 text-white dark:text-white"
        @click="setTheme('#1e4cf1')"
      >
        {{ $t("sharp") }} {{ $t("blue") }}
      </button>

      <button
        class="rounded bg-red-400 p-2 text-white dark:text-black"
        @click="setTheme('#f87171')"
      >
        {{ $t("red") }}
      </button>

      <button
        class="rounded bg-orange-400 p-2 text-white dark:text-black"
        @click="setTheme('#fb923c')"
      >
        {{ $t("orange") }}
      </button>

      <button
        class="rounded bg-amber-400 p-2 text-white dark:text-black"
        @click="setTheme('#fbbf24')"
      >
        {{ $t("amber") }}
      </button>

      <button
        class="rounded bg-yellow-400 p-2 text-white dark:text-black"
        @click="setTheme('#facc15')"
      >
        {{ $t("yellow") }}
      </button>

      <button
        class="rounded bg-lime-400 p-2 text-white dark:text-black"
        @click="setTheme('#a3e635')"
      >
        {{ $t("lime") }}
      </button>

      <button
        class="rounded bg-green-400 p-2 text-white dark:text-black"
        @click="setTheme('#4ade80')"
      >
        {{ $t("green") }}
      </button>

      <button
        class="rounded bg-emerald-400 p-2 text-white dark:text-black"
        @click="setTheme('#34d399')"
      >
        {{ $t("emerald") }}
      </button>

      <button
        class="rounded bg-teal-400 p-2 text-white dark:text-black"
        @click="setTheme('#2dd4bf')"
      >
        {{ $t("teal") }}
      </button>

      <button
        class="rounded bg-teal-400 p-2 text-white dark:text-black"
        @click="setTheme('#22d3ee')"
      >
        {{ $t("cyan") }}
      </button>

      <button
        class="rounded bg-sky-400 p-2 text-white dark:text-black"
        @click="setTheme('#38bdf8')"
      >
        {{ $t("sky") }}
      </button>

      <button
        class="rounded bg-blue-400 p-2 text-white dark:text-black"
        @click="setTheme('#2e89f8')"
      >
        {{ $t("blue") }}
      </button>

      <button
        class="rounded bg-indigo-400 p-2 text-white dark:text-black"
        @click="setTheme('#818cf8')"
      >
        {{ $t("indigo") }}
      </button>

      <button
        class="rounded bg-violet-400 p-2 text-white dark:text-black"
        @click="setTheme('#a78bfa')"
      >
        {{ $t("violet") }}
      </button>

      <button
        class="rounded bg-purple-400 p-2 text-white dark:text-black"
        @click="setTheme('#c084fc')"
      >
        {{ $t("purple") }}
      </button>

      <button
        class="rounded bg-fuchsia-400 p-2 text-white dark:text-black"
        @click="setTheme('#e879f9')"
      >
        {{ $t("fuchsia") }}
      </button>

      <button
        class="rounded bg-pink-400 p-2 text-white dark:text-black"
        @click="setTheme('#f472b6')"
      >
        {{ $t("pink") }}
      </button>

      <button
        class="rounded bg-rose-400 p-2 text-white dark:text-black"
        @click="setTheme('#fb7185')"
      >
        {{ $t("rose") }}
      </button>
    </div>

    <!-- custom colors -->
    <div class="mt-4">
      <p>{{ $t("pick colorshades") }}</p>
      <div class="flex items-center">
        <div class="w-1/3 text-center">
          <div class="my-8 flex items-center justify-evenly">
            <label
              for="theme-color"
              class="cursor-pointer rounded border border-gray-400 bg-gray-200 p-2 transition hover:bg-gray-300 dark:bg-gray-700"
            >
              {{ $t("pick a custom color") }}
              <input id="theme-color" v-model="hex" type="color" name="theme-color" />
            </label>

            <font-awesome-icon :icon="['fal', 'arrow-right']" />
          </div>
        </div>
        <div class="mx-auto w-full rounded bg-white p-2 shadow-lg dark:bg-gray-900 md:w-1/3">
          <p>{{ $t("shades") }}</p>
          <div
            v-for="(shade, i) in result.shades"
            :key="'shade_' + shade.hex + '_' + i"
            :style="`background: ${shade.hex}; color:${shade.textColor}`"
            class="p-2 text-center first:rounded-t last:rounded-b"
          >
            {{ shade.hex }}
          </div>
        </div>

        <div class="flex w-1/3 items-center justify-evenly">
          <font-awesome-icon :icon="['fal', 'arrow-right']" />
          <button
            class="flex items-center rounded bg-theme-400 p-2 text-themecontrast-400 transition hover:bg-theme-500 dark:text-black"
            @click="setTheme(hex)"
          >
            {{ $t("use colors") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useThemeStore } from "@/stores/theme";
export default {
  data() {
    return {
      hex: this.$store.state.theme.hex,
      hsl: [227, 100, 60],
      groupOptions: {
        stepUp: 8,
        stepDown: 8,
      },
      ...useThemeStore,
    };
  },
  computed: {
    result() {
      const hsl = this.hsl;
      const stepUp = this.groupOptions.stepUp;
      const stepDown = this.groupOptions.stepDown;
      const shades = [];
      for (let i = 4; i >= 1; i--) {
        const lightness = hsl[2] + i * stepUp;
        const hex = this.HSLToHex(hsl[0], hsl[1], lightness > 100 ? 100 : lightness);
        shades.push({
          hex,
          textColor: this.textColorFromBrightness(hex),
        });
      }
      {
        const hex = this.HSLToHex(hsl[0], hsl[1], hsl[2]);
        shades.push({
          hex,
          textColor: this.textColorFromBrightness(hex),
        });
      }
      for (let i = 1; i < 6; i++) {
        const lightness = hsl[2] - i * stepDown;
        const hex = this.HSLToHex(hsl[0], hsl[1], lightness < 0 ? 0 : lightness);
        shades.push({
          hex,
          textColor: this.textColorFromBrightness(hex),
        });
      }
      return {
        hsl,
        shades,
      };
    },
  },
  watch: {
    hex(v) {
      this.hexToHSL(v);
    },
  },
  mounted() {
    this.hexToHSL(this.hex);
    this.themeStore = useThemeStore();
  },
  methods: {
    textColorFromBrightness(hex) {
      const rgb = this.hexToRGB(hex);
      // https://www.w3.org/TR/AERT/#color-contrast
      const brightness = (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
      return brightness > 125 ? "black" : "white";
    },
    hexToHSL(H) {
      // Convert hex to RGB first
      let r = 0;
      let g = 0;
      let b = 0;

      if (H.length == 4) {
        r = "0x" + H[1] + H[1];
        g = "0x" + H[2] + H[2];
        b = "0x" + H[3] + H[3];
      } else if (H.length == 7) {
        r = "0x" + H[1] + H[2];
        g = "0x" + H[3] + H[4];
        b = "0x" + H[5] + H[6];
      }
      // Then to HSL
      r /= 255;
      g /= 255;
      b /= 255;
      const cmin = Math.min(r, g, b);
      const cmax = Math.max(r, g, b);
      const delta = cmax - cmin;
      let h = 0;
      let s = 0;
      let l = 0;

      if (delta == 0) h = 0;
      else if (cmax == r) h = ((g - b) / delta) % 6;
      else if (cmax == g) h = (b - r) / delta + 2;
      else h = (r - g) / delta + 4;

      h = Math.round(h * 60);

      if (h < 0) h += 360;

      l = (cmax + cmin) / 2;
      s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
      s = +(s * 100).toFixed(1);
      l = +(l * 100).toFixed(1);

      this.hsl = [h, s, l];
    },
    hexToRGB(h) {
      let r = 0,
        g = 0,
        b = 0;

      // 3 digits
      if (h.length == 4) {
        r = "0x" + h[1] + h[1];
        g = "0x" + h[2] + h[2];
        b = "0x" + h[3] + h[3];

        // 6 digits
      } else if (h.length == 7) {
        r = "0x" + h[1] + h[2];
        g = "0x" + h[3] + h[4];
        b = "0x" + h[5] + h[6];
      }

      return [r, g, b];
    },
    HSLToHex(h, s, l) {
      s /= 100;
      l /= 100;

      const c = (1 - Math.abs(2 * l - 1)) * s;
      const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
      const m = l - c / 2;
      let r = 0;
      let g = 0;
      let b = 0;

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
      // Having obtained RGB, convert channels to hex
      r = Math.round((r + m) * 255).toString(16);
      g = Math.round((g + m) * 255).toString(16);
      b = Math.round((b + m) * 255).toString(16);

      // Prepend 0s, if necessary
      if (r.length == 1) r = "0" + r;
      if (g.length == 1) g = "0" + g;
      if (b.length == 1) b = "0" + b;

      return "#" + r + g + b;
    },
    setTheme(color) {
      this.hex = color;
      this.themeStore.setHexColor(color);
    },
  },
};
</script>
