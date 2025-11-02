import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("print-assortments", {
  submodules: {
    categories: true,
    products: true,
    margins: true,
    boxes: true,
    options: true,
    boops: true,
    combinations: true,
    machines: true,
    catalogues: true,
    printingMethods: true,
    delivery: true,
  },

  groups: {
    moduleAccess: ["@categories-list", "@categories-read", "suppliers-list"],
    categorySelect: ["@products-read", "@catalogues-list", "@machines-list", "@shops.pricesFetch"],
  },
});
