import { mapState, mapMutations } from "vuex";
export default {
  computed: {
    ...mapState({
      pagination: (state) => state.pagination.pagination,
      loading: (state) => state.pagination.loading,
      fired: (state) => state.pagination.fired,
      endofresults: (state) => state.pagination.endofresults,
      waypoint_active: (state) => state.pagination.waypoint_active,
      intersection_options: (state) => state.pagination.intersection_options,
    }),
  },
  methods: {
    ...mapMutations({
      set_pagination: "pagination/set_pagination",
      set_loader: "pagination/set_loader",
      toggle_fired: "pagination/toggle_fired",
      toggle_fired_start: "pagination/toggle_fired_start",
      toggle_endofresults: "pagination/toggle_endofresults",
      toggle_waypoint_active: "pagination/toggle_waypoint_active",
    }),
    climbTheCanion() {
      // scroll to top of the element
      let l = document.getElementById("resultContainer");
      // console.log(l);
      l.scrollTo(0, 250);

      setTimeout(() => {
        // reset and re go!
        this.toggle_fired(false);
        this.toggle_fired_start(false);
      }, 500);
    },
    downTheCanion() {
      // scroll to top of the element
      let l = document.getElementById("resultContainer");
      // console.log(l.scrollHeight - 600);
      // console.log(l);
      l.scrollTo(0, l.scrollHeight - 1111);

      setTimeout(() => {
        // reset and re go!
        this.toggle_fired(false);
        this.toggle_fired_start(false);
      }, 500);
    },
    // @paginate methods
    // scrolledToBottom(going, direction) {
    //    // WARNING: copy from finder and not orders
    //    // define the elemnt
    //    // var el = event.srcElement;

    //    // check if not already scrolled to bottom
    //    if (!this.reached_bottom) {
    //       // if scrolled to bottom
    //       // if (el.scrollTop >= el.scrollHeight - el.clientHeight) {
    //       if (
    //          going === this.$waypointMap.GOING_IN &&
    //          direction === this.$waypointMap.DIRECTION_TOP
    //       ) {
    //          this.toggle_reached_bottom(true); // you reached the bottom of the canion

    //          // timeout to prevent flywheel scrolling
    //          setTimeout(() => {
    //             if (
    //                this.pagination.current_page + 1 <=
    //                this.pagination.last_page
    //             ) {
    //                // real in the next page of results
    //                this.set_pagination({
    //                   current_page: this.pagination.current_page + 1,
    //                   per_page: this.pagination.per_page,
    //                   last_page: this.pagination.last_page,
    //                   total: this.pagination.total
    //                });

    //                if (this.pagination.current_page === this.pagination.last_page) {
    //                   this.toggle_endofresult(true);
    //                }
    //             } else if (
    //                this.pagination.current_page === this.pagination.last_page
    //             ) {
    //                this.toggle_endofresult(true);
    //             }
    //          }, 100);
    //       }
    //    }
    // },
    // toTop(event) {
    //    var el = event.srcElement;
    //    // scroll to top of the element
    //    el.scrollTo(0, 0);

    //    // reset and re go!
    //    this.toggle_reached_bottom(false);
    // },

    // toTop(el) {
    //    // reset and re go!
    //    this.reachedBottom = false;

    //    // var el = event.srcElement;
    //    // scroll to top of the element
    //    let l = document.getElementById("resultContainer");
    //    l.scrollTo(0, 0);
    // }
  },
};
