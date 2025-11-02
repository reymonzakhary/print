const blue = {
  50: "rgb(230, 235, 255)",
  100: "rgb(204, 215, 255)",
  200: "rgb(153, 175, 255)",
  300: "rgb(102, 136, 255)",
  400: "rgb(51, 96, 255)",
  500: "rgb(0, 56, 255)",
  600: "rgb(0, 7, 197)",
  700: "rgb(0, 0, 141)",
  800: "rgb(0, 13, 88)",
  900: "rgb(0, 3, 41)",
};

/**
 * Considerable color palette instead of the royal blue one
 */
// const blue = {
//   50: "rgb(249, 252, 252)", // Background, hover states
//   100: "rgb(184, 217, 227)", // Backgrounds, disabled states
//   200: "rgb(114, 184, 205)", // Borders, dividers
//   300: "rgb(74, 170, 199)", // Secondary text, icons
//   400: "rgb(53, 164, 198)", // Primary interactive elements
//   500: "rgb(48, 149, 180)", // Your brand color
//   600: "rgb(36, 129, 158)", // Hover states for primary elements
//   700: "rgb(26, 109, 134)", // Active states
//   800: "rgb(15, 79, 99)", // Text on light backgrounds
//   900: "rgb(8, 48, 61)", // Emphasis, headlines
// };

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: [],
  theme: {
    extend: {
      spacing: {
        "1/24": "4.166666%",
        "2/24": "8.333333%",
        "3/24": "12.5%",
        "4/24": "16.666667%",
        "5/24": "20.833333%",
        "6/24": "25%",
        "7/24": "29.166667%",
        "8/24": "33.333333%",
        "9/24": "37.5%",
        "10/24": "41.666667%",
        "11/24": "45.833333%",
        "12/24": "50%",
        "13/24": "54.166667%",
        "14/24": "58.333333%",
        "15/24": "62.5%",
        "16/24": "66.666667%",
        "17/24": "70.833333%",
        "18/24": "75%",
        "19/24": "79.166667%",
        "20/24": "83.333333%",
        "21/24": "87.5%",
        "22/24": "91.666667%",
        "23/24": "95.833333%",
        "24/24": "100%",
      },
      colors: {
        blue: {
          50: blue[50],
          100: blue[100],
          200: blue[200],
          300: blue[300],
          400: blue[400],
          500: blue[500],
          600: blue[600],
          700: blue[700],
          800: blue[800],
          900: blue[900],
        },
        theme: {
          50: blue[50],
          100: blue[100],
          200: blue[200],
          300: blue[300],
          400: blue[400],
          500: blue[500],
          600: blue[600],
          700: blue[700],
          800: blue[800],
          900: blue[900],
        },
        themecontrast: {
          50: "#000000",
          100: "#000000",
          200: "#000000",
          300: "#ffffff",
          400: "#ffffff",
          500: "#ffffff",
          600: "#ffffff",
          700: "#ffffff",
          800: "#ffffff",
          900: "#ffffff",
        },
      },
      animation: {
        shake: "shake 0.82s cubic-bezier(.36,.07,.19,.97) both",
      },
      keyframes: {
        shake: {
          "10%, 90%": { transform: "translate3d(-1px, 0, 0)" },
          "20%, 80%": { transform: "translate3d(2px, 0, 0)" },
          "30%, 50%, 70%": { transform: "translate3d(-4px, 0, 0)" },
          "40%, 60%": { transform: "translate3d(4px, 0, 0)" },
        },
      },
    },
  },
  plugins: [],
};
