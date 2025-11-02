<script>
import {Bar} from "vue-chartjs";
import {mapActions, mapState} from "vuex";

export default {
    extends: Bar,
    data: () => ({
        gradient: null,
        gradient2: null,
        chartdata: {
            labels: ["Assortment"],

            // barchart
            datasets: [
                {
                    label: "Categories",
                    backgroundColor: "#10B98140",
                    borderColor: "#10B981",
                    borderWidth: 1,
                    barPercentage: 0.6,
                    data: []
                },
                {
                    label: "Boxes",
                    backgroundColor: "#3B82F640",
                    borderColor: "#3B82F6",
                    borderWidth: 1,
                    barPercentage: 0.6,
                    data: []
                },
                {
                    label: "Options",
                    backgroundColor: "#f8797940",
                    borderColor: "#f87979",
                    borderWidth: 1,
                    barPercentage: 0.6,
                    data: []
                }
            ]

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
            scales: {
                xAxes: [
                    {
                        stacked: false,
                        ticks: {
                            min: 0
                        }
                    }
                ],
                yAxes: [
                    {
                        stacked: false
                    }
                ]
            },
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
            categories: state => state.standardization.categories,
            boxes: state => state.standardization.boxes,
            prodoptions: state => state.standardization.options
        }),
        // ...mapGetters({
        // 	suppliers: "clients/suppliers",
        // 	resellers: "clients/resellers"
        // })
    },
    mounted() {
        this.fetchData()

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

    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_options: "standardization/obtain_options"
        }),
        fetchData() {
            this.obtain_categories({per_page: 9999999, page: 1});
            this.obtain_boxes({per_page: 9999999, page: 1});
            this.obtain_options({per_page: 9999999, page: 1}).then(() => {
                this.chartdata.datasets[0].data.push(this.categories.data.length);
                this.chartdata.datasets[1].data.push(this.boxes.data.length);
                this.chartdata.datasets[2].data.push(this.prodoptions.data.length);

                this.chartdata.datasets[2].backgroundColor = this.gradient;

                this.renderChart(this.chartdata, this.options);

                this.$parent.loading = false
            })
        }
    }
};
</script>

<style>
</style>
