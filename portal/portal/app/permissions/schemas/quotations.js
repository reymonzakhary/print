import definePermissionsSchema from "../helpers/definePermissionsSchema.js";

export default definePermissionsSchema("quotations", {
  submodules: {
    trashed: ["access", "list", "read", "update"],

    // Quotation Discount
    discount: ["access", "create"],

    // Quotation Items
    items: ["access", "list", "create", "update", "delete"],
    itemsMedia: ["list", "create", "update", "delete"],
    itemsServices: true,
    itemsServicesMedia: ["list", "create", "delete"],
    itemsNote: ["update"],
    itemsReference: ["update"],
    itemsProduct: ["update"],
    itemsDeliverySeparated: ["update"],
    itemsDeliveryPickup: ["update"],
    itemsProducer: ["update"],

    // Quotation Services
    services: true,

    // Quotation Details
    media: ["access", "list", "create", "delete"],
    history: ["access", "list", "read"],
    note: ["update"],
    reference: ["update"],
    user: ["update"],
    address: ["update"],
    invoiceAddress: ["update"],
    deliveryMultiple: ["update"],
    deliveryPickup: ["update"],
  },

  groups: {
    /**
     * @title Quotation Access
     * @description Access to the quotation module.
     */
    moduleAccess: ["@access", "@list"],

    /**
     * @title Quotation Details
     * @description Access to the quotation details page. Contains items-list to prevent quotation price summary bugs.
     */
    moduleDetails: ["@quotations.moduleAccess", "@read", "@items-list"],

    /**
     * @title Quotation User Update
     * @description All necessary permissions to succesfully fetch and update a quotation user.
     */
    userUpdate: [
      "@quotations.moduleAccess",
      "@quotations.moduleDetails",
      "@update",
      "@user-update",
      "members-list",
    ],

    /**
     * @title Address Update
     * @description All necessary permissions to succesfully update the delivery address.
     */
    deliveryAddressUpdate: [
      "@quotations.moduleAccess",
      "@quotations.moduleDetails",
      "@address-update",
      "members-list",
    ],

    /**
     * @title Invoice Address Update
     * @description All necessary permissions to succesfully update the invoice address.
     */
    invoiceAddressUpdate: [
      "@quotations.moduleAccess",
      "@quotations.moduleDetails",
      "@invoiceAddress-update",
      "members-list",
    ],

    /**
     * @title Delivery Pickup Address Select
     * @description All necessary permissions to succesfully select a delivery pickup address.
     */
    pickupAddressSelect: [
      "@quotations.deliveryAddressUpdate",
      "@deliveryPickup-update",
      "contexts-addresses-list",
    ],

    /**
     * @title Item Address Update
     * @description All necessary permissions to succesfully update the item address.
     */
    itemAddressUpdate: [
      "@quotations.deliveryAddressUpdate",
      "@deliveryMultiple-update",
      "@itemsDeliverySeparated-update",
      "@itemsDeliveryPickup-update",
    ],

    /**
     * @title Add Open Product
     * @description All necessary permissions to succesfully add an open product. Needs either one of the two groups.
     */
    addOpenProduct: [
      // Or print assortments categories, boxes, options
      [
        "@items-create",
        "print-assortments-categories-list",
        "print-assortments-boxes-list",
        "print-assortments-options-list",
      ],
      // Or custom assortments categories, boxes, options
      [
        "@items-create",
        "custom-assortments-categories-list",
        "custom-assortments-boxes-list",
        "custom-assortments-options-list",
      ],
    ],

    /**
     * @title Add Assortment Product
     * @description All necessary permissions to succesfully add an assortment product.
     */
    addAssortmentProduct: [
      "@items-create",
      "@printAssortments.moduleAccess",
      "@printAssortments.categorySelect",
    ],
    /**
     * @title Add Finder Product
     * @description All necessary permissions to succesfully add a finder product.
     */
    addFinderProduct: ["@items-create", "@finder.moduleAccess"],

    /**
     * @title Add Discount
     * @description All necessary permissions to succesfully add a discount.
     */
    addDiscount: ["@discount-access", "@discount-create"],
  },
});
