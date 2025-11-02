<template>
  <div
    v-if="!endofresults && pagination.per_page > 10"
    class="flex flex-wrap items-end h-72 bg-gradient-to-b from-white to-theme-500 via-theme-300 text-themecontrast-400"
  >
    <!-- {{ endofresults }} -->
    <p v-if="endofresults" class="mt-4 font-bold text-center">
      <font-awesome-icon :icon="['fad', 'plane-arrival']" />
      {{ $t("products loaded") }}
    </p>

    <div
      class="flex items-center justify-center w-full mt-2 mb-auto text-theme-500"
    >
      <font-awesome-icon :icon="['fal', 'long-arrow-down']" class="mr-2" />
      scroll down to load previous page
    </div>

    <Waypoint
      :active="true"
      :options="intersection_options_end"
      class="flex items-center justify-center w-full p-2 mx-auto mb-10 text-center transition-all"
      @change="scrolledToBottom"
    >
      loading page {{ pagination.current_page + 1 }}
      <font-awesome-icon
        v-show="loading === true"
        id="truck"
        :icon="['fad', 'truck']"
      />
    </Waypoint>
  </div>
</template>

<script>
import { Waypoint } from "vue-waypoint";
import { mapState, mapMutations } from "vuex";

export default {
  components: {
    Waypoint,
  },
  emits: ["paginationChangedBottom"],
  data() {
    return {
      active: true,
    };
  },
  computed: {
    ...mapState({
      endofresults: (state) => state.pagination.endofresults,
      intersection_options_end: (state) =>
        state.pagination.intersection_options_end,
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
      toggle_endofresult: "pagination/toggle_endofresults",
      toggle_waypoint_active: "pagination/toggle_waypoint_active",
    }),
    // @paginate methods
    scrolledToBottom({ going, direction }) {
      let curpage = 0;
      curpage = parseInt(this.pagination.current_page) + 1;
      // check if not already fired
      if (!this.fired) {
        // if scrolled to bottom
        if (going === "IN" && direction === "UP") {
          this.toggle_fired(true); // keep from firing multiple times
          this.set_loader(true); // show the loader

          if (curpage <= this.pagination.last_page) {
            if (this.pagination.current_page === this.pagination.last_page) {
              this.toggle_endofresults(true);
              this.set_loader(false);
              return;
            }

            setTimeout(() => {
              this.$emit("paginationChangedBottom", {
                page: curpage,
                type: "addEnd",
              });
            }, 100);
          } else if (
            this.pagination.current_page === this.pagination.last_page
          ) {
            this.toggle_endofresults(true);
            this.set_loader(false);
          }
        }
      }
    },
  },
};
</script>

<style scoped>
@keyframes truckMove {
  0% {
    transform-origin: 0%;
    opacity: 0;
    left: 0;
    transform: scale(1) translate(-20vw);
  }
  100% {
    opacity: 0;
    transform: translate(20vw) scale(1);
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
