import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("teams", {
  // A permission schema always has access, list, read, create, update, delete
  submodules: {
  "addresses": true,
  "accessibility": true,
  "users": true,
  "members": true
},

  groups: {
    // A group can either reference its parent or submodules with an @ symbol.
    // e.g. "boxes": ["@boxes-read"]
    // Or it can just reference permissions as '@list'
  },
});
