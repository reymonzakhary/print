<script>
import {Doughnut} from "vue-chartjs";
import {mapActions, mapGetters, mapState} from "vuex";

export default {
    extends: Doughnut,
    data: () => ({
        gradient: null,
        gradient2: null,
        chartdata: {
            labels: ["producers", "resellers"],

            // barchart
            // datasets: [
            // 	{
            // 		label: "Producers",
            // 		backgroundColor: "#10B98140",
            // 		borderColor: "#10B981",
            // 		borderWidth: 1,
            // 		barPercentage: 0.6,
            // 		data: []
            // 	},
            // 	{
            // 		label: "Resellers",
            // 		backgroundColor: "#3B82F640",
            // 		borderColor: "#3B82F6",
            // 		borderWidth: 1,
            // 		barPercentage: 0.6,
            // 		data: []
            // 	},
            // 	{
            // 		label: "Brandowners",
            // 		backgroundColor: "#f8797940",
            // 		borderColor: "#f87979",
            // 		borderWidth: 1,
            // 		barPercentage: 0.6,
            // 		data: [60]
            // 	}
            // ],

            // piechart
            // datasets: [
            // 	{
            // 		// backgroundColor: ["#10B98140", "#3B82F640", "#f8797940"],
            // 		backgroundColor: [this.gradient, this.gradient2, "#00D8FF"],
            // 		// borderColor: ["#10B981", "#3B82F6", "#f87979"],
            // 		data: [10, 20]
            // 	}
            // ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // scales: {
            // 	xAxes: [
            // 		{
            // 			stacked: false,
            // 			ticks: {
            // 				min: 0
            // 			}
            // 		}
            // 	],
            // 	yAxes: [
            // 		{
            // 			stacked: false
            // 		}
            // 	]
            // },
            tooltips: {
                backgroundColor: "#ffffffbb",
                titleFontColor: "#333",
                bodyFontColor: "#333",
                intersect: false,
                borderWidth: 1,
                borderColor: "#ddd"
            },
            animations: {
                easing: "easeInOutExpo"
            }
        }
    }),
    computed: {
        ...mapState({
            tenants: state => state.tenants.tenants,
            activeTenant: state => state.tenants.activeTenant
        }),
        ...mapGetters({
            suppliers: "tenants/suppliers",
            resellers: "tenants/resellers"
        })
    },
    mounted() {
        this.obtain_tenants();

        this.gradient = this.$refs.canvas
            .getContext("2d")
            .createLinearGradient(0, 0, 0, 450);
        this.gradient2 = this.$refs.canvas
            .getContext("2d")
            .createLinearGradient(0, 0, 0, 450);

        this.gradient.addColorStop(0, "rgba(255, 0,0, 0.5)");
        this.gradient.addColorStop(0.5, "rgba(255, 0, 0, 0.25)");
        this.gradient.addColorStop(1, "rgba(255, 0, 0, 0)");

        this.gradient2.addColorStop(0, "rgba(0, 231, 255, 0.9)");
        this.gradient2.addColorStop(0.5, "rgba(0, 231, 255, 0.25)");
        this.gradient2.addColorStop(1, "rgba(0, 231, 255, 0)");

        setTimeout(() => {
            // this.chartdata.datasets[0].data = [this.suppliers.length];
            // this.chartdata.datasets[1].data = [this.resellers.length];
            // this.chartdata.datasets[0].data.push(this.suppliers.length);
            // this.chartdata.datasets[0].data.push(this.resellers.length);

            // this.renderChart(this.datasets, this.options);

            this.renderChart({
                labels: ["producers", "resellers"],
                datasets: [
                    {
                        // backgroundColor: ["#10B98140", "#3B82F640", "#f8797940"],
                        backgroundColor: [this.gradient, this.gradient2, "#00D8FF"],
                        borderColor: ["#f87979", "#3B82F6", "#10B981"],
                        // borderColor: ["#10B981", "#3B82F6", "#f87979"],
                        data: [this.suppliers.length, this.resellers.length]
                    }
                ]
            }, this.options);
        }, 500);
    },
    methods: {
        ...mapActions({
            obtain_tenants: "tenants/obtain_tenants"
        })
    }
};
</script>

<style>
</style>
