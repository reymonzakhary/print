// import vuex mappings
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

export default {
  data() {
    return {
      activeStateRunState: [],
      activeStatePriceState: [],
      iso: "nl-NL",
      currency: "EUR",
      mode: 1,
      change: 0,
    };
  },
  computed: {
    // collect discounts from vuex store
    ...mapState({
      discounts: (state) => state.discounts.discounts,
      runInfinityCheck: (state) => state.discounts.runCheck,
      priceInfinityCheck: (state) => state.discounts.priceCheck,
    }),
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update: "discounts/store",
      changeRunCheck: "discounts/changeRunCheck",
      changePriceCheck: "discounts/changePriceCheck",
    }),

    activeStateRun(i, checked) {
      this.changeRunCheck(checked);
      let data = _.cloneDeep(this.discounts);
      data.filter((item) => {
        if (item.mode === "run" && item.slots.length > 0) {
          item.slots[i].to = checked ? "-1" : "";
          item.slots.splice(i + 1);
        }
      });
      if (checked) {
        this.activeStateRunState = [];
      }
      this.update(data);
    },

    activeStatePrice(i, checked) {
      this.changePriceCheck(checked);
      let data = _.cloneDeep(this.discounts);
      if (!checked) {
        data.filter((item) => {
          if (item.mode === "price") {
            if (item.slots.length > 0) {
              item.slots[i].to = "";
              item.slots.splice(i + 1);
            }
          }
        });
      } else {
        data.filter((item) => {
          if (item.mode === "price") {
            if (item.slots.length > 0) {
              item.slots[i].to = "-1";
              item.slots.splice(i + 1);
            }
          }
        });
        this.activeStatePriceState = [];
      }
      this.update(data);
    },

    // update discounts mode
    updateMode(value) {
      this.change++;
      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.discounts);
      // change the status from desired mode to true
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        element.status = false;
        if (element.mode == value) {
          element.status = true;
        }
      }
      // update store
      this.update(data);
    },

    inputUpdate(discount, slot, input, value) {
      if (!value && input == "to") {
        this.checkInfinity = true;
      } else {
        this.checkInfinity = false;
      }

      this.change++;
      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.discounts);

      // manipulate data
      data[discount].slots[slot][input] = value;

      // update store with mutation
      this.update(data);
    },

    // add new discount
    addDiscount(discount) {
      this.change++;
      // set slot object
      let newDiscount = {
        from: "",
        to: "",
        type: "percentage",
        value: "",
      };

      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.discounts);

      // append new slot to slots
      data[discount].slots.push(newDiscount);

      // update store with mutation
      this.update(data);
    },

    // add new discount
    newDiscount() {
      this.change++;
      // set slot object
      let data = [
        {
          mode: "run",
          slots: [
            {
              from: 0,
              to: 100,
              type: "percentage",
              value: 10,
            },
          ],
          status: true,
        },
        {
          mode: "price",
          slots: [
            {
              from: 0,
              to: 100,
              type: "percentage",
              value: 10,
            },
          ],
          status: false,
        },
      ];

      // append new slot to slots

      // update store with mutation
      this.update(data);
      this.saveDiscounts();
    },

    async saveDiscounts(object) {
      this.$emit("save-discounts", object);
    },
  },
};
