<template>
  <Line
    :chart-data="chartData"
    :options="options"
    @chart:render="handleChartRender"
  />
</template>

<script>
import "chart.js/auto";
import { Line } from "vue-chart-3";

export default {
  name: "LineChart",
  components: {
    Line,
  },
  props: ["chartData", "options"],
  data() {
    return {
      gradient: null,
    };
  },
  mounted() {
    // this.gradient = this.$refs.canvas
    //   .getContext("2d")
    //   .createLinearGradient(500, 0, 100, 0);
    // this.gradient.addColorStop(0, "rgba(16, 185, 129, 1)");
    // this.gradient.addColorStop(0.5, "rgba(16, 185, 129, 0.5)");
    // this.gradient.addColorStop(1, "rgba(16, 185, 129, 0.25)");
    // this.gradient2 = this.$refs.canvas
    //   .getContext("2d")
    //   .createLinearGradient(500, 0, 100, 0);
    // this.gradient2.addColorStop(0, "rgba(237, 137, 54, 1)");
    // this.gradient2.addColorStop(0.5, "rgba(237, 137, 54, 0.5)");
    // this.gradient2.addColorStop(1, "rgba(237, 137, 54, .25)");
    // this.chartData.datasets[0].backgroundColor = this.gradient;
    // this.chartData.datasets[1].backgroundColor = this.gradient2;
    // this.renderChart(this.chartData, this.options);
  },
  methods: {
    handleChartRender(chart) {
    },
  },
};
</script>
