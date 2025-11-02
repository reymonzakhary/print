<template>
    <div class="h-full p-4">
        <Categories
            v-if="view !== 'boxes & options'"
            class="z-30 h-full mt-6 xl:mt-0"
        ></Categories>

        <Boxes v-else class="z-30 h-full mt-6 xl:mt-0"></Boxes>

        <div class="absolute right-0 z-40 flex items-center mr-6 text-xs top-8">
            <div class="p-2 m-4 text-xs">
                Show
                <select
                    name="boxes_per_page"
                    id="boxes_per_page"
                    class="mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    @change="set_per_page($event.target.value)"
                >
                    <option value="10" :selected="per_page === '10'">10</option>
                    <option value="20" :selected="per_page === '20'">20</option>
                    <option value="50" :selected="per_page === '50'">50</option>
                    <option value="100" :selected="per_page === '100'">100</option>
                </select>
                items per column
            </div>
            <button
                class="px-2 py-1 transition-colors border border-blue-500 rounded-l outline-none hover:bg-blue-100"
                :class="
					view === 'relational'
						? 'bg-blue-500 text-white'
						: 'text-blue-500 '
				"
                @click="toggle_view('relational')"
            >
                <font-awesome-icon
                    :icon="['fal', 'network-wired']"
                    rotation="270"
                />
                Relational
            </button>
            <button
                class="px-2 py-1 transition-colors border border-blue-500 outline-none hover:bg-blue-100"
                :class="
					view === 'boxes & options'
						? 'bg-blue-500 text-white'
						: 'text-blue-500 '
				"
                @click="toggle_view('boxes & options')"
            >
                <font-awesome-icon :icon="['fal', 'box-full']"/>
                Boxes &amp; Options
            </button>
            <button
                class="px-2 py-1 transition-colors border border-blue-500 rounded-r outline-none hover:bg-blue-100"
                :class="
					view === 'list all' ? 'bg-blue-500 text-white' : 'text-blue-500 '
				"
                @click="toggle_view('list all')"
            >
                <font-awesome-icon :icon="['fal', 'list']"/>
                List all
            </button>
        </div>

        <transition name="fade" v-if="pageLoading">
            <PageLoader class="z-50"></PageLoader>
        </transition>
    </div>
</template>

<script>
import {mapActions, mapMutations, mapState} from "vuex";
import PageLoader from "../global/PageLoader.vue";
import Categories from "./Categories.vue";

export default {
    components: {Categories, PageLoader},
    computed: {
        ...mapState({
            pageLoading: state => state.standardization.pageLoading,
            view: state => state.standardization.view,
            per_page: state => state.standardization.per_page
        })
    },
    watch: {
        view(newVal) {
            if (newVal === "list all") {
                this.toggle_page_loading();
                this.set_active_category({});
                this.set_active_box({});
                this.set_active_option({});

                // setTimeout(() => {
                this.obtain_boxes({per_page: this.per_page, page: 1});
                this.obtain_options({per_page: this.per_page, page: 1}).then(
                    response => {
                        this.toggle_page_loading();
                    }
                );
                // }, 200);
            }
            if (newVal === "boxes & options") {
                // this.toggle_page_loading();
                this.set_active_category({});
                this.set_active_box({});
                this.set_active_option({});
                this.populate_boxes([]);
                this.populate_options([]);

                // setTimeout(() => {
                this.obtain_categories({per_page: this.per_page, page: 1});
                this.obtain_boxes({per_page: this.per_page, page: 1}).then(
                    response => {
                        // this.toggle_page_loading();
                    }
                );
                // }, 200);
            }
            if (newVal === "relational") {
                this.set_active_category({});
                this.set_active_box({});
                this.set_active_option({});
                this.populate_categories([]);
                this.populate_boxes([]);
                this.populate_options([]);
                this.obtain_categories({per_page: this.per_page, page: 1});
            }
        },
        pageLoading(newVal) {
            return newVal;
        },
        per_page(v) {
            this.obtainData();
            return v;
        }
    },
    mounted() {
        this.initialise_store();
        this.obtainData();
    },
    methods: {
        ...mapMutations({
            toggle_view: "standardization/toggle_view",
            toggle_page_loading: "standardization/toggle_page_loading",
            populate_categories: "standardization/populate_categories",
            populate_boxes: "standardization/populate_boxes",
            populate_options: "standardization/populate_options",
            set_active_category: "standardization/set_active_category",
            set_active_box: "standardization/set_active_box",
            set_active_option: "standardization/set_active_option",
            initialise_store: "standardization/initialise_store",
            set_per_page: "standardization/set_per_page"
        }),
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_unmatched_categories:
                "standardization/obtain_unmatched_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_unmatched_boxes: "standardization/obtain_unmatched_boxes",
            obtain_options: "standardization/obtain_options",
            obtain_unmatched_options: "standardization/obtain_unmatched_options"
        }),
        obtainData() {
            if (this.view === "relational") {
                this.obtain_categories({per_page: this.per_page, page: 1});
            }
            if (this.view === "boxes & options") {
                this.obtain_boxes({per_page: this.per_page, page: 1});
            }
            if (this.view === "list all") {
                this.obtain_categories({per_page: this.per_page, page: 1});
                this.obtain_boxes({per_page: this.per_page, page: 1});
                this.obtain_options({per_page: this.per_page, page: 1});
            }

            this.obtain_unmatched_categories();
            this.obtain_unmatched_boxes();
            this.obtain_unmatched_options();
        }
    }
};
</script>

<style>
</style>
