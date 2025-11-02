// @ts-check
import withNuxt from "./.nuxt/eslint.config.mjs";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";

/**
 * See the full list of options here:
 * https://eslint.nuxt.com/
 * BEFORE YOU ADD RULES HERE, consider using the nuxt.config.ts file!
 */
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
