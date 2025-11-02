import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("suppliers", {
  submodules: {
    settings: ["access", "list", "read", "update"],
    categories: ["access", "list", "read", "create"],
    discounts: ["access", "list"],
  },

  groups: {},
});
