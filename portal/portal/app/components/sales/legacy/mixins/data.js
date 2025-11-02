export default {
  /**
   *
   * @returns array of fields
   */
  data() {
    return {
      fields: [
        {
          id: 1,
          key: "Ordernumber",
          label: "nr.",
          class: "hidden sm:flex pl-2",
        },
        {
          id: 10,
          key: "external_id",
          label: "external id",
          class: "",
        },
        {
          id: 2,
          key: "created_from",
          label: "Type",
          class: "hidden sm:flex",
        },
        {
          id: 3,
          key: "Date",
          label: "date",
          class: "",
        },
        {
          id: 4,
          key: "Customer",
          label: "customer",
          class: "",
        },
        {
          id: 5,
          key: "Company",
          label: "company",
          class: "hidden md:flex",
        },
        {
          id: 6,
          key: "Payment",
          label: "payment",
          class: "",
        },
        {
          id: 7,
          key: "status",
          label: "status",
          class: "",
        },
        // {
        // id: 8,
        // 	key: "Price",
        // 	label: "Price",
        // 	class: ""
        // },
        {
          id: 9,
          key: "actions",
          label: "actions",
          class: "justify-end items-center",
        },
      ],
      detailfields: [
        {
          key: "nr",
          label: "Nr.",
          class: "",
        },
        {
          key: "Product",
          label: "product",
          class: "",
        },
        {
          key: "status",
          label: "status",
          class: "",
        },
        {
          key: "delivery_date",
          label: "delivery",
          class: "",
        },
        {
          key: "producer",
          label: "producer",
          class: "",
        },
        {
          key: "file",
          label: "file",
          class: "",
        },
        {
          key: "actions",
          label: "actions",
          class: "flex justify-end items-center",
        },
      ],
    };
  },
};
