import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("members", {
  // A permission schema always has access, list, read, create, update, delete
  submodules: {
    addresses: true,
  },

  groups: {
    // A group can either reference its parent or submodules with an @ symbol.
    // e.g. "boxes": ["@boxes-read"]
    // Or it can just reference permissions as '@list'
    /**
     * @title Accessing the members module
     * @description Minimally required permissions to access the members module
     */
    moduleAccess: ["@access", "@list", "@read"],
    /**
     * @title Creating a member
     * @description Minimally required permissions to succesfully create a member
     */
    memberCreate: ["@create", "@addresses-create", "acl-list", "acl-roles-list", "teams-list"],
    /**
     * @title Creating an address
     * @description Minimally required permissions to succesfully create an address
     */
    addressCreate: ["@addresses-create"],
  },
});
