// @ts-check
import withNuxt from "./.nuxt/eslint.config.mjs";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";

export default withNuxt(
  // Should be last in the chain
  eslintPluginPrettierRecommended,
).overrideRules({
  "vue/html-self-closing": [
    "error",
    {
      html: {
        void: "always",
        normal: "always",
        component: "always",
      },
      svg: "always",
      math: "always",
    },
  ],
});
