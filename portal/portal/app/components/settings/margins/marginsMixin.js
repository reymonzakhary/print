// import vuex mappings
import { mapState, mapMutations } from "vuex";
import _ from "lodash";

export default {
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      activeStateRunState: [],
      activeStatePriceState: [],
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      mode: 1,
      change: 0,
    };
  },
  computed: {
    // collect margins from vuex store
    ...mapState({
      margins: (state) => state.margins.margins,
      runInfinityCheck: (state) => state.margins.runCheck,
      priceInfinityCheck: (state) => state.margins.priceCheck,
    }),
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update: "margins/store",
      changeRunCheck: "margins/changeRunCheck",
      changePriceCheck: "margins/changePriceCheck",
    }),

    activeStateRun(i, checked) {
      this.changeRunCheck(checked);
      let data = _.cloneDeep(this.margins);
      if (!checked) {
        data.filter((item) => {
          if (item.mode === "run") {
            if (item.slots.length > 0) {
              item.slots[i].to = "";
              item.slots.splice(i + 1);
            }
          }
        });
      } else {
        data.filter((item) => {
          if (item.mode === "run") {
            if (item.slots.length > 0) {
              item.slots[i].to = "-1";
              item.slots.splice(i + 1);
            }
          }
        });
        this.activeStateRunState = [];
      }
      this.update(data);
    },

    activeStatePrice(i, checked) {
      this.changePriceCheck(checked);
      let data = _.cloneDeep(this.margins);
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

    // update margins mode
    updateMode(value) {
      this.change++;
      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.margins);
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

    inputUpdate(margin, slot, input, value) {
      if (!value && input == "to") {
        this.checkInfinity = true;
      } else {
        this.checkInfinity = false;
      }

      this.change++;
      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.margins);

      // manipulate data
      data[margin].slots[slot][input] = value;

      // update store with mutation
      this.update(data);
    },

    // add new margin
    addMargin(margin) {
      this.change++;
      // set slot object
      let newMargin = {
        from: "",
        to: "",
        type: "percentage",
        value: "",
      };

      // clone store data to be allowed to manipulate
      let data = _.cloneDeep(this.margins);

      // append new slot to slots
      data[margin].slots.push(newMargin);

      // update store with mutation
      this.update(data);
    },

    // add new margin
    newMargin() {
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
      this.saveMargins();
    },

    async saveMargins(object) {
      this.$emit("save-margins", object);
    },
  },
};
