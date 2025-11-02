import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("finder", {
  // A permission schema always has access, list, read, create, update, delete
  submodules: {
    categories: true,
    boxes: true,
    options: true,
  },

  groups: {
    moduleAccess: ["@access", "@list", "@categories-list", "@categories-read", "@options-list"],
  },
});
