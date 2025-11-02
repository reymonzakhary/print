export default {
  state: () => ({
    statuses: {
      sequential_item_statuses: [
        {
          name: "Draft",
          code: 300,
          color: "cyan",
        },
        {
          name: "New",
          code: 302,
          color: "blue",
        },
        {
          name: "Editing",
          code: 324,
          color: "purple",
        },
        {
          name: "waiting_for_response",
          code: 322,
          color: "gray",
        },
        {
          name: "In progress",
          code: 318,
          color: "amber",
        },
        {
          name: "In production",
          code: 303,
          color: "yellow",
        },
        {
          name: "Ready",
          code: 306,
          color: "lime",
        },
        {
          name: "Being shipped",
          code: 304,
          color: "green",
        },
        {
          name: "Delivered",
          code: 307,
          color: "emerald",
        },
      ],
      sequential_order_statuses: [
        {
          name: "Draft",
          code: 300,
          color: "cyan",
        },
        {
          name: "Pending",
          code: 301,
          color: "amber",
        },
        {
          name: "New",
          code: 302,
          color: "blue",
        },
        {
          name: "In progress",
          code: 318,
          color: "amber",
        },
        {
          name: "In production",
          code: 303,
          color: "yellow",
        },
        {
          name: "Done",
          code: 308,
          color: "emerald",
        },
        {
          name: "Archived",
          code: 310,
          color: "gray",
        },
      ],
      deviant_statuses: [
        {
          name: "Editable",
          code: 317,
          color: "cyan",
        },
        {
          name: "Locked",
          code: 309,
          color: "gray",
        },
        {
          name: "Mailing",
          code: 312,
          color: "indigo",
        },
        {
          name: "Mailed",
          code: 313,
          color: "violet",
        },
        {
          name: "Processing",
          code: 314,
          color: "purple",
        },
        {
          name: "Expiring",
          code: 315,
          color: "fuchsia",
        },
        {
          name: "Expired",
          code: 316,
          color: "pink",
        },
        {
          name: "Rejected",
          code: 319,
          color: "rose",
        },
        {
          name: "Accepted",
          code: 320,
          color: "green",
        },
        {
          name: "Failed",
          code: 321,
          color: "red",
        },
        {
          name: "Canceled",
          code: 305,
          color: "red",
        },
        {
          name: "Blocked",
          code: 311,
          color: "orange",
        },
      ],
    },
  }),
  getters: {
    // WARNING: not working with the tailwind library if the colors are not
    //          defined statically elsewhere in a component or page...
    statusColor: (state) => (status, text, bg, border) => {
      let status_color = "";
      let status_bg_color = "";
      let status_border_color = "";
      let color = "";
      Object.values(state.statuses).forEach((element) => {
        element.find((st) => {
          if (st?.code === status?.code) {
            color = st.color;
          }
        });
      });
      if (status) {
        status_border_color = `border-${color}-300`;
        status_bg_color = `bg-${color}-100 dark:bg-${color}-500`;
        status_color = `text-${color}-500 dark:text-${color}-100`;
      } else {
        status_border_color = "border-blue-300";
        status_bg_color = "bg-blue-100 dark:bg-blue-500";
        status_color = "text-blue-500 dark:text-blue-100";
      }
      let returnValues = [];
      if (text) {
        returnValues.push(status_color);
      }
      if (bg) {
        returnValues.push(status_bg_color);
      }
      if (border) {
        returnValues.push(status_border_color);
      }
      return returnValues;
    },
  },
};
