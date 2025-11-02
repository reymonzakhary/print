<template>
  <div
    v-if="pagination.current_page > 1 && pagination.per_page > 10"
    class="flex flex-wrap items-start h-72 bg-gradient-to-b to-white via-theme-300 from-theme-500 text-themecontrast-400"
  >
    <Waypoint
      :active="true"
      :options="intersection_options_start"
      class="flex items-center justify-center w-full p-2 mx-auto mt-10 text-center transition-all"
      @change="scrolledToTop"
    >
      <font-awesome-icon
        v-show="loading === true"
        id="truck"
        :icon="['fad', 'truck']"
      />
      loading page {{ pagination.current_page - 1 }}
    </Waypoint>
    <div
      class="flex items-center justify-center w-full mt-auto mb-2 text-theme-500"
    >
      <font-awesome-icon :icon="['fal', 'long-arrow-up']" class="mr-2" />
      scroll up to load previous page
    </div>
  </div>
</template>

<script>
import { Waypoint } from "vue-waypoint";
import { mapState, mapMutations } from "vuex";

export default {
  components: {
    Waypoint,
  },
  emits: ["paginationChangedStart"],
  data() {
    return {
      active: true,
    };
  },
  computed: {
    ...mapState({
      intersection_options_start: (state) =>
        state.pagination.intersection_options_start,
      pagination: (state) => state.pagination.pagination,
      loading: (state) => state.pagination.loading,
      fired: (state) => state.pagination.fired,
      fired_start: (state) => state.pagination.fired_start,
      endofresults: (state) => state.pagination.endofresults,
      waypoint_active: (state) => state.pagination.waypoint_active,
      intersection_options: (state) => state.pagination.intersection_options,
    }),
  },
  watch: {
    endofresults(v) {
      return v;
    },
    reached_bottom(v) {
      return v;
    },
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
    // @paginate methods
    scrolledToTop({ going, direction }) {
      let curpage = 0;
      curpage = parseInt(this.pagination.current_page) - 1;

      // check if not already fired
      if (!this.fired_start) {
        // if scrolled to bottom
        if (going === "IN" && direction === "DOWN") {
          this.toggle_fired_start(true); // keep from firing multiple times
          this.set_loader(true); // show the loader

          if (curpage >= 1) {
            if (this.pagination.current_page === 1) {
              this.toggle_startofresult(true);
              this.set_loader(false);
              return;
            }

            setTimeout(() => {
              // real in the next page of results
              this.$emit("paginationChangedStart", {
                page: curpage,
                type: "addStart",
              });
            }, 1000);
          } else if (
            this.pagination.current_page === this.pagination.last_page
          ) {
            this.toggle_startofresult(true);
            this.set_loader(false);
          }
        }
      }
    },
  },
};
</script>

<style scoped>
#truck {
  transform: scaleX(-1);
}
@keyframes truckMove {
  0% {
    transform-origin: 0%;
    opacity: 0;
    left: 0;
    transform: scale(1) translate(20vw);
  }
  100% {
    opacity: 0;
    transform: translate(-20vw) scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) translate(0vw);
  }
}

#truck {
  animation: truckMove 3s ease 0s infinite normal none;
}
</style>
