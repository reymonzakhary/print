<template>
    <div class="flex h-full">
        <transition name="fade">
            <component
                :is="component"
                :item="activeCategory"
                :unmatchedItem="unmatchedCat"
                :items="categories"

                :per_page="per_page"
                :current_page="pagination.current_page"
                :filter="filter"

                :view="view"
                :multiselectArray="multiselectArray"

                type="category"
                class="z-50"
            ></component>
        </transition>

        <div class="z-40 w-64 h-full">
            <div
                class="relative flex items-center justify-between pr-2 mb-2 border-gray-400"
            >
				<span class="flex items-center ml-2">
					<img
                        src="images/Prindustry-box.png"
                        alt="Prindustry Logo"
                        id="prindustry-logo"
                        class="h-6 mr-1"
                    />
					<p class="text-sm font-bold tracking-wide uppercase">
						Categories
					</p>
				</span>

                <!-- neumorphic style -->
                <!-- px-2 py-1 border border-white rounded-full nm-flat-white hover:nm-concave-gray-100 focus:nm-inset-gray-100 focus:outline-none -->

                <button
                    class="text-blue-500"
                    @click="component = 'NewPrindustryItem'"
                >
                    <font-awesome-icon :icon="['fal', 'plus']"/>
                    new
                </button>

                <ItemMenu
                    v-if="mainMenuItems"
                    class="z-10 text-sm text-blue-500 bg-blue-100 rounded-full dark:bg-blue-800 dark:text-blue-300"
                    :menuItems="mainMenuItems"
                    menuIcon="ellipsis-h"
                    menuClass="z-20"
                    @item-clicked="menuItemClicked($event)"
                />
            </div>

            <section class="h-full px-2 pb-2 overflow-auto text-sm">
                <!-- errored cats => unmatched categories during standardisation -->
                <ul
                    v-if="unmatched && unmatched.length > 0"
                    class="mb-3 bg-white border border-red-500 rounded-md shadow-md group dark:bg-gray-800"
                >
                    <li
                        class="relative"
                        v-for="(category, i) in unmatched"
                        :key="`match_${i}`"
                    >
                        <Button
                            @click.native="matchButtonClicked(category)"
                            :item="category"
                            classes="text-red-500 hover:bg-red-100"
                        ></Button>
                    </li>
                </ul>

                <div class="h-full overflow-y-auto bg-white rounded-md shadow-md dark:bg-gray-800"
                     style="max-height: calc(100vh - 10rem)">
                    <div
                        class="sticky top-0 z-10 flex flex-col w-full py-3"
                        :class="{ 'bg-white shadow-md rounded-t pb-0': multiselect }"
                    >
                        <div class="flex">
                            <input
                                class="w-full px-2 py-1 mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                                type="text"
                                placeholder="Search all categories"
                                v-model="filter"
                            />
                            <font-awesome-icon
                                :icon="['fal', 'filter']"
                                class="absolute right-0 mt-2 mr-4 text-gray-600"
                            />
                        </div>

                        <transition name="slide">
                            <div class="pt-2" v-if="multiselect">
                                <small
                                    class="flex justify-between w-full ml-2 text-gray-500"
                                >
									<span
                                        class="flex items-center font-bold tracking-tight uppercase"
                                    >
										<font-awesome-icon
                                            :icon="['fal', 'ballot']"
                                            class="mr-1 fa-sm"
                                        />
										multiselect
									</span>
                                    <span
                                        class="flex items-center justify-center px-1 py-0 mx-1 font-bold text-blue-500 "
                                    >
										{{ multiselectArray.length }}
										selected
									</span>
                                    <button
                                        class="mr-4 text-blue-900"
                                        @click="
											(multiselectArray = []), (multiselect = false)
										"
                                    >
                                        <font-awesome-icon
                                            :icon="['fad', 'times-circle']"
                                        />
                                        close
                                    </button>
                                </small>

                                <transition name="slide">
                                    <div
                                        class="flex items-center justify-between w-full px-2 py-1"
                                        v-if="multiselectArray.length > 1"
                                    >
                                        <button
                                            class="p-1 text-blue-500 rounded hover:bg-blue-100"
                                            @click="component = 'mergeItems'"
                                        >
                                            <font-awesome-icon
                                                :icon="['fal', 'code-merge']"
                                            />
                                            merge
                                        </button>
                                        <button
                                            class="p-1 text-red-500 rounded hover:bg-red-100"
                                            v-tooltip="
												'WARNING force deletes all categories and UNLINKS all boxes & options'
											"
                                            @click="multiForceDelete()"
                                        >
                                            <font-awesome-icon
                                                :icon="['fal', 'layer-minus']"
                                            />
                                            delete
                                        </button>
                                    </div>
                                </transition>
                            </div>
                        </transition>
                    </div>

                    <ul>
                        <li
                            class="relative bg-yellow-100 group"
                            :class="{
								'last:border-b-2 border-yellow-400 border-dashed':
									category.matches && category.matches.length > 0
							}"
                            v-for="(category, i) in categories.data"
                            :key="`match_${i}`"
                        >
                            <Button
                                @click.native="selectCategory(category, category.slug)"
                                v-if="category.matches && category.matches.length > 0"
                                :item="category"
                            ></Button>
                        </li>
                    </ul>

                    <ul>
                        <li
                            class="relative flex items-center group"
                            :class="{
								'bg-green-100 text-green-500 hover:bg-green-200':
									category.checked === true,
								'bg-pink-100 text-pink-500 hover:bg-pink-200':
									category.published === false
							}"
                            v-for="(category, i) in categories.data"
                            :key="i"
                        >
                            <input
                                type="checkbox"
                                :name="category.slug"
                                :id="category.slug"
                                v-if="multiselect"
                                @click="
									selectItem($event.target.checked, category.slug)
								"
                                :checked="multiselectArray.includes(category.slug)"
                                class="ml-2"
                            />
                            <Button
                                v-if="category.matches && category.matches.length === 0"
                                :class="{
									'bg-blue-100 text-blue-500 hover:bg-blue-200 dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-blue-300':
										category.checked === false &&
										activeCategory.name === category.name,
									'bg-green-200 text-green-500 hover:bg-green-200':
										category.checked === true &&
										activeCategory.name === category.name,
									'bg-pink-200 text-pink-500 hover:bg-pink-200':
										category.published === false &&
										activeCategory.name === category.name
								}"
                                @click.native="selectCategory(category, category.slug)"
                                :item="category"
                                :menuItems="menuItems"
                                :multiselect="multiselect"
                            ></Button>
                        </li>
                        <li
                            v-if="categories.data && categories.data.length === 0"
                            class="italic text-center text-gray-500"
                        >
                            no categories found
                            <button
                                class="text-blue-500"
                                @click="
									obtain_categories({
										page: pagination.current_page,
										per_page: per_page
									})
								"
                            >
                                refresh
                            </button>
                        </li>
                        <Pagination
                            v-if="categories.last_page > 1"
                            :pagination="pagination"
                            @paginate="
								(loading = true),
									obtain_categories({
										per_page: per_page,
										page: pagination.current_page
									}).then(() => {
										loading = false;
									})
							"
                        ></Pagination>
                        <div
                            v-if="loading === true"
                            class="absolute py-2 text-center text-white bg-blue-500 rounded w-60 bottom-5"
                        >
                            loading...
                        </div>
                    </ul>
                </div>
            </section>
        </div>

        <Boxes class="h-full"/>
    </div>
</template>

<script>
import {mapActions, mapMutations, mapState} from "vuex";
import selectParent from "../../mixins/selectParent";
import axios from "axios";
import EditPanel from "./EditPanel.vue";
import Button from "./Button.vue";
import ItemMenu from "../global/ItemMenu.vue";
import Pagination from "../global/Pagination.vue";

export default {
    components: {EditPanel, Button, ItemMenu, Pagination},
    mixins: [selectParent],
    data() {
        return {
            pagination: {},
            filter: "",
            loading: false,
            edit: false,
            mainMenuItems: [
                {
                    action: "multi-select",
                    icon: "ballot",
                    title: "Multi select",
                    classes: "text-blue-900 dark:text-blue-100",
                    show: true
                },
                {
                    action: "show-unlinked",
                    icon: "unlink",
                    title: "Unlinked categoires",
                    classes: "text-blue-900 dark:text-blue-100",
                    show: true
                }
            ],
            menuItems: [
                {
                    action: "edit",
                    icon: "pencil",
                    title: "Edit",
                    classes: "text-blue-900 dark:text-blue-100",
                    show: true
                },
                // {
                // 	action: "get_all_products",
                // 	icon: "sitemap",
                // 	title: "Show all products",
                // 	classes: "text-blue-900 dark:text-blue-100",
                // 	show: true
                // },
                {
                    action: "set_flag",
                    icon: "clipboard-check",
                    title: "Toggle verified",
                    classes: "text-green-500",
                    show: true
                },
                {
                    action: "delete",
                    icon: "trash",
                    title: "Delete",
                    classes: "text-red-500",
                    show: true
                }
            ],
            component: "",
            unmatchedCat: {},
            multiselect: false,
            multiselectArray: []
        };
    },
    computed: {
        ...mapState({
            unmatched: state => state.standardization.unmatchedCats,
            categories: state => state.standardization.categories,
            activeCategory: state => state.standardization.activeCategory,
            categoryEdit: state => state.standardization.categoryEdit,
            categoryUnmatched: state => state.standardization.categoryUnmatched,
            boxes: state => state.standardization.boxes,
            view: state => state.standardization.view,
            per_page: state => state.standardization.per_page
        })
    },
    watch: {
        categories(v) {
            this.pagination = v;
        },
        filter: _.debounce(function (val) {
            this.obtain_categories({page: null, filter: val});
        }, 300),
        categoryEdit(v) {
            if (v === true) {
                this.component = "EditPanel";
            } else {
                this.component = "";
            }
        },
        categoryUnmatched(v) {
            if (v === true) {
                this.component = "MatchPanel";
            } else {
                this.component = "";
            }
        }
    },
    mounted() {
        this.pagination = this.categories;
    },
    methods: {
        ...mapMutations({
            set_active_category: "standardization/set_active_category",
            set_active_box: "standardization/set_active_box",
            set_active_option: "standardization/set_active_option",
            populate_categories: "standardization/populate_categories",
            populate_boxes: "standardization/populate_boxes",
            populate_options: "standardization/populate_options"
        }),
        ...mapActions({
            obtain_categories: "standardization/obtain_categories"
        }),

        matchButtonClicked(category) {
            this.$store.commit("standardization/set_category_unmatched", true),
                (this.unmatchedCat = category);
        },
        getCategoryProducts(category, slug) {
            this.activeCategory = category;
            axios
                .get(`/categories/${slug}/products`)
                .then(response => {
                    this.products = response.data;
                })
                .catch(err => {
                    console.log(err.response);
                });
        },
        getUnlinkedCategories() {
            axios
                .get(`unlinked/categories`)
                .then(response => {
                    this.populate_categories(response.data);
                })
                .catch(err => {
                    console.log(err.response);
                });
        },
        selectItem(state, item) {
            // if checkbox is checked
            if (state == true) {
                this.multiselectArray.push(item);
            } else {
                // we unchecked & if the key: value exists in array
                if (this.multiselectArray.includes(item)) {
                    let position = this.multiselectArray.findIndex(
                        slug => slug === item
                    );
                    // remove the selected option
                    this.multiselectArray.splice(position, 1);
                }
            }
        },
        multiForceDelete() {
            for (let index = 0; index < this.multiselectArray.length; index++) {
                const element = this.multiselectArray[index];
                axios.delete(`categories/${element}?force=true`).then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                });
            }
            setTimeout(() => {
                this.multiselectArray = [];
                this.multiselect = false;
                this.obtain_categories({
                    page: this.pagination.current_page,
                    per_page: this.per_page,
                    filter: this.filter
                });
            }, 500);
        },
        closeModal() {
            this.component = "";
        },
        close() {
            this.$store.commit("standardization/set_category_unmatched", false);
        },

        /**
         * This processes the menu clicks
         * @param {String} event menu-item clicked
         * @return {Function}  respective funtion gets executed
         * @todo create functions for the current console.log's
         */
        menuItemClicked(event, category) {
            switch (event) {
                case "edit":
                    this.$store.commit("standardization/set_category_edit", true);
                    break;

                case "get_all_products":
                    this.getCategoryProducts(category, category.slug);
                    break;

                case "set_flag":
                    let bool;
                    if (this.activeCategory.checked === false) {
                        bool = true;
                    } else {
                        bool = false;
                    }
                    axios
                        .put(`/categories/${this.activeCategory.slug}`, {
                            name: this.activeCategory.name,
                            checked: bool,
                            description: this.activeCategory.description,
                            published: this.activeCategory.published
                        })
                        .then(response => {
                            this.obtain_categories({
                                page: this.pagination.current_page,
                                per_page: this.per_page
                            });
                        });
                    break;

                case "delete":
                    this.component = "DeleteItem";
                    break;

                case "multi-select":
                    this.multiselect = true;
                    break;

                case "show-unlinked":
                    this.getUnlinkedCategories();
                    break;

                default:
                    this.$store.commit("standardization/set_category_edit", true);
                    break;
            }
        }
    }
};
</script>
