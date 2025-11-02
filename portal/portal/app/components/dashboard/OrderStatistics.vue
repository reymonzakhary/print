<template>
  <section>
    <h2 class="mb-2 text-sm font-bold uppercase tracking-wide">
      {{ capitalizeFirstLetter($t("{orders}", { orders: $t(type) })) }}
    </h2>
    <div class="grid grid-cols-1 items-center justify-between gap-1 text-sm">
      <div class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-blue-50 text-center font-bold text-blue-500">
          {{ newO }}
        </p>
        {{ $t("new {orders}", { orders: $t(type) }) }}
      </div>
      <div class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-pink-50 text-center font-bold text-pink-500">
          {{ editingO }}
        </p>
        {{ $t("editing {orders}", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'quotations'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-gray-50 text-center font-bold text-gray-500">
          {{ pendingO }}
        </p>
        {{ $t("awaiting {orders}", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'quotations'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-green-50 text-center font-bold text-green-500">
          {{ acceptedO }}
        </p>
        {{ $t("accepted {orders}", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'quotations'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-red-50 text-center font-bold text-red-500">
          {{ rejectedO }}
        </p>
        {{ $t("rejected {orders}", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'orders'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-amber-50 text-center font-bold text-amber-400">
          {{ inProgressO }}
        </p>
        {{ $t("{orders} in progress", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'orders'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-orange-50 text-center font-bold text-orange-500">
          {{ inProductionO }}
        </p>
        {{ $t("{orders} in production", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'orders'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-lime-50 text-center font-bold text-lime-500">
          {{ readyO }}
        </p>
        {{ $t("{orders} ready", { orders: $t(type) }) }}
      </div>
      <div v-if="type === 'orders'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-green-50 text-center font-bold text-green-500">
          {{ beingShippedO }}
        </p>
        {{ $t("{orders} being shipped", { orders: $t(type) }) }}
      </div>

      <div v-if="type === 'orders'" class="flex truncate">
        <p class="mr-2 w-10 rounded-full bg-emerald-50 text-center font-bold text-emerald-500">
          {{ deliveredO }}
        </p>
        {{ $t("{orders} delivered", { orders: $t(type) }) }}
      </div>
      <!-- <div class="flex truncate">
        <p class="w-10 font-bold text-emerald-500 mr-">{{ doneO }}</p>
        {{ $t("{orders} done", { orders: $t(type) }) }}
      </div> -->
    </div>
  </section>
</template>

<script>
import moment from "moment";
import _ from "lodash";
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  props: {
    type: {
      type: String,
      default: "orders",
      required: true,
    },
  },
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const { statusMap } = useOrderStatus();
    const quotationRepository = useQuotationRepository();
    const orderRepository = useOrderRepository();
    const { permissions, theUser: me } = storeToRefs(useAuthStore());
    const API = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return {
      permissions,
      me,
      API,
      handleError,
      handleSuccess,
      statusMap,
      quotationRepository,
      orderRepository,
      capitalizeFirstLetter,
    };
  },
  data() {
    return {
      orders: [],
      quotations: [],
      neworders: [],
      newquotations: [],
      barChartData: {
        labels: [],
        datasets: [
          {
            label: "Orders",
            data: [],
            backgroundColor: null,
            borderColor: "#ffffff",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 6,
          },
          {
            label: "Pending quotations",
            data: [],
            backgroundColor: null,
            borderColor: "#ffffff",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 6,
          },
        ],
      },
      barChartOptions: {
        responsive: true,
        plugins: {
          legend: {
            display: false,
          },
          title: {
            display: true,
            text: "Order data",
            font: {
              size: 24,
            },
            color: "#6b7280",
          },
          tooltip: {
            backgroundColor: "#ffffffbb",
            titleColor: "#333",
            bodyColor: "#333",
            intersect: false,
            borderWidth: 1,
            borderColor: "#ddd",
          },
        },
        scales: {
          x: {
            grid: {
              display: true,
            },
          },
          y: {
            beginAtZero: true,
            grid: {
              display: false,
            },
          },
        },
        animation: {
          easing: "easeInOutSine",
        },
      },
      newO: 0,
      editingO: 0,
      pendingO: 0,
      inProductionO: 0,
      inProgressO: 0,
      beingShippedO: 0,
      readyO: 0,
      deliveredO: 0,
      doneO: 0,
      acceptedO: 0,
      rejectedO: 0,
    };
  },
  computed: {
    ...mapState({
      statuses: (state) => state.orders.statuses,
    }),
  },
  watch: {
    orders() {
      // this.makeOrderdata();
      // this.ordersByStatus();
    },
    quotations() {
      // this.makeQuotationdata();
      // this.ordersByStatus();
    },
    statuses(v) {
      return v;
    },
  },
  async mounted() {
    // full permission
    // if (
    //   this.permissions.includes("quotations-list") &&
    //   this.permissions.includes("orders-list") &&
    //   !this.ordertype && type === 'orders'
    // ) {
    //   this.set_ordertype("quotation");
    //   this.get_orders();
    //   this.set_ordertype("order");
    //   this.get_orders();
    // }

    // order permission & type
    if (this.permissions.includes("orders-list") && this.type === "orders") {
      await this.orderRepository
        .getAllOrders()
        .then((res) => (this.orders = res[0]))
        .catch((error) => {
          this.handleError(error);
        });

      this.makeOrderData();
      this.ordersByStatus();
    }

    // quotation permission & type
    if (this.permissions.includes("quotations-list") && this.type === "quotations") {
      await this.quotationRepository
        .getAllQuotations()
        .then((res) => (this.quotations = res[0]))
        .catch((error) => {
          this.handleError(error);
        });
      this.makeQuotationData();
      this.ordersByStatus();
    }

    if (this.statuses.length === 0) {
      this.getStatuses();
    }
  },
  methods: {
    ...mapMutations({
      set_ordertype: "orders/set_active_order_type",
    }),
    ...mapActions({
      get_orders: "orders/get_orders",
    }),
    ordersByStatus() {
      let loopData;
      if (this.type === "orders") {
        loopData = this.orders;
      } else {
        loopData = this.quotations;
      }

      loopData.forEach((item) => {
        if (item.status.is("NEW")) {
          this.newO++;
        } else if (item.status.is("WAITING_FOR_RESPONSE")) {
          this.pendingO++;
        } else if (item.status.is("EDITING")) {
          this.editingO++;
        } else if (item.status.is("IN_PROGRESS")) {
          this.inProgressO++;
        } else if (item.status.is("IN_PRODUCTION")) {
          this.inProductionO++;
        } else if (item.status.is("BEING_SHIPPED")) {
          this.beingShippedO++;
        } else if (item.status.is("READY")) {
          this.readyO++;
        } else if (item.status.is("DELIVERED")) {
          this.deliveredO++;
        } else if (item.status.is("DONE")) {
          this.doneO++;
        } else if (item.status.is("ACCEPTED")) {
          this.acceptedO++;
        } else if (item.status.is("REJECTED")) {
          this.rejectedO++;
        }
      });
    },
    async makeOrderData() {
      // loop trough orders to make a new array based on the day
      for (let i = 0; i < this.orders.length; i++) {
        const order = this.orders[i];
        const neworder = {};
        // convert created_at
        if (order.status.code !== 300) {
          neworder.created_at = moment(order.created_at).format("DD-MM");
          this.neworders.push(neworder);
        }
      }

      this.neworders = _.sortBy(this.neworders, "created_at");
      const data = _.map(this.neworders, "created_at");
      const counts = {};
      data.forEach(function (x) {
        counts[x] = (counts[x] || 0) + 1;
      });
      // for (const key in counts) {
      //   if (Object.prototype.hasOwnProperty.call(counts, key)) {
      //     const value = counts[key];
      //     this.barChartData.labels.push(key);
      //     this.barChartData.datasets[0].data.push(value);
      //   }
      // }
    },
    async makeQuotationData() {
      if (this.statuses.length === 0) {
        this.getStatuses();
      }
      // loop trough orders to make a new array based on the day
      for (let i = 0; i < this.quotations.length; i++) {
        const quotation = this.quotations[i];
        const newquotation = {};
        // convert created_at
        if (quotation.status.code !== 300) {
          newquotation.created_at = moment(quotation.created_at).format("DD-MM");
          this.newquotations.push(newquotation);
        }
      }

      this.newquotations = _.sortBy(this.newquotations, "created_at");

      const data = _.map(this.newquotations, "created_at");
      const counts = {};
      data.forEach(function (x) {
        counts[x] = (counts[x] || 0) + 1;
      });
      // for (const key in counts) {
      //   if (Object.prototype.hasOwnProperty.call(counts, key)) {
      //     const value = counts[key];
      //     if (!this.barChartData.labels.includes(key)) {
      //       this.barChartData.labels.push(key);
      //     }
      //     this.barChartData.datasets[1].data.push(value);
      //   }
      // }
    },
    async getStatuses() {
      const statuses = await this.API.get("statuses");
      statuses.data.forEach((element) => {
        this.statuses.push(element.name);
      });
    },
  },
};
</script>
