import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("custom-assortments", {
  submodules: {
    boxes: true,
    options: true,
    brands: true,
    categories: true,
    products: true,
  },

  groups: {
    categorySelect: ["@products-read"],
  },
});
