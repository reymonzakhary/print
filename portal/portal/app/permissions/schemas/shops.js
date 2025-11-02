import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("shops", {
  // A permission schema always has access, list, read, create, update, delete

  submodules: {
    categories: ["access", "list", "read", "create"],
    categoryProducts: ["access", "list", "read", "create"],
  },

  groups: {
    // Required permissions for fetching prices via ShopPriceTable
    pricesFetch: ["@categories-read", "@categories-list", "@categoryProducts-read"],
  },
});
