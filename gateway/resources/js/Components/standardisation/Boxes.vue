<template>
    <div class="flex h-full" style="max-height: calc(100vh - 10rem)">
        <transition name="fade">
            <component
                :is="component"
                :category="activeCategory"
                :item="activeBox"
                :items="boxes"
                :unmatchedItem="unmatchedBox"
                :per_page="per_page"
                :current_page="pagination.current_page"
                :filter="filter"
                :view="view"
                :multiselectArray="multiselectArray"
                type="box"
                class="z-50"
            ></component>
        </transition>

        <div class="z-30 w-64 h-full ml-2">
            <div
                class="relative flex items-center justify-between pr-2 mb-2 border-gray-400"
            >
                <div class="flex items-center">
                    <p class="ml-2 text-sm font-bold tracking-wide uppercase">
                        Boxes
                    </p>
                    <div
                        class="relative ml-2 group"
                        v-if="
							Object.keys(activeCategory).length > 0 ||
								view === 'boxes & options' ||
								view === 'list all'
						"
                    >
                        <button class="text-blue-500">
                            new
                            <font-awesome-icon :icon="['fal', 'angle-down']"/>
                        </button>
                        <ul
                            class="absolute top-0 left-0 z-50 invisible mt-6 text-sm bg-white border divide-y rounded-md shadow-md w-52 dark:bg-gray-800 group-hover:visible"
                        >
                            <li v-if="view === 'relational'">
                                <button
                                    class="w-full p-2 text-left text-blue-500 hover:bg-blue-100"
                                    @click="component = 'NewPrindustryItem'"
                                >
                                    <font-awesome-icon
                                        :icon="['fal', 'network-wired']"
                                        rotation="270"
                                    />
                                    Relational box
                                </button>
                            </li>
                            <li>
                                <button
                                    class="w-full p-2 text-left text-blue-500 hover:bg-blue-100"
                                    @click="component = 'NewPrindustryItemStandalone'"
                                >
                                    <font-awesome-icon :icon="['fal', 'dice-one']"/>
                                    Standalone box
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <ItemMenu
                    v-if="mainMenuItems"
                    class="z-40 text-sm text-blue-500 bg-blue-100 rounded-full dark:bg-blue-800 dark:text-blue-300"
                    :menuItems="mainMenuItems"
                    menuIcon="ellipsis-h"
                    menuClass="z-50"
                    @item-clicked="menuItemClicked($event)"
                />
            </div>

            <transition name="list" tag="nav">
                <section
                    v-if="boxes.data"
                    class="h-full px-2 pb-2 overflow-auto text-sm"
                >
                    <ul
                        v-if="unmatchedBoxes.data && unmatchedBoxes.data.length > 0"
                        class="mb-3 bg-white border border-red-500 rounded-md shadow-md group dark:bg-gray-800"
                    >
                        <li class="relative" v-for="(box, i) in unmatched" :key="i">
                            <Button
                                @click.native="matchButtonClicked(box)"
                                :item="box"
                                classes="text-red-500 hover:bg-red-100"
                            ></Button>
                        </li>
                    </ul>

                    <div class="h-full overflow-y-auto bg-white rounded-md shadow-md dark:bg-gray-800"
                         style="max-height: calc(100vh - 10rem)">
                        <div
                            class="sticky top-0 z-10 flex flex-col w-full"
                            :class="{
								'bg-white shadow-md rounded-t pb-0': multiselect,
								'py-3': view !== 'relational'
							}"
                        >
                            <div class="flex" v-if="view !== 'relational'">
                                <input
                                    class="w-full px-2 py-1 mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                                    type="text"
                                    placeholder="Search all boxes"
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
												(multiselectArray = []),
													(multiselect = false)
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
                                class="relative"
                                v-for="(box, i) in boxesdata"
                                :key="i"
                            >
                                <Button
                                    v-if="box.matches && box.matches.length > 0"
                                    @click.native="selectBox(box, box.slug)"
                                    :item="box"
                                    :menuItems="menuItems"
                                    classes="text-yellow-500 hover:bg-yellow-200"
                                ></Button>
                            </li>
                        </ul>

                        <ul>
                            <li
                                class="relative flex items-center group"
                                :class="{
									'bg-pink-100 text-pink-500 hover:bg-pink-200':
										box.published === false
								}"
                                v-for="(box, i) in boxesdata"
                                :key="i"
                            >
                                <input
                                    type="checkbox"
                                    :name="box.slug"
                                    :id="box.slug"
                                    v-if="multiselect"
                                    @click="selectItem($event.target.checked, box.slug)"
                                    :checked="multiselectArray.includes(box.slug)"
                                    class="ml-2"
                                />
                                <Button
                                    v-if="box.matches && box.matches.length === 0"
                                    :class="{
										'bg-blue-100 text-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-blue-300':
											activeBox.slug === box.slug,
										'bg-pink-200 text-pink-500 hover:bg-pink-200':
											box.published === false &&
											activeBox.slug === box.slug
									}"
                                    @click.native="selectBox(box, box.slug)"
                                    :item="box"
                                    :menuItems="menuItems"
                                ></Button>
                            </li>
                            <li
                                v-if="boxes.data && boxes.data.length === 0"
                                class="italic text-center text-gray-500"
                            >
                                no boxes found
                                <button
                                    class="text-blue-500"
                                    @click="
										obtain_boxes({
											page: pagination.current_page,
											per_page: per_page
										})
									"
                                >
                                    refresh
                                </button>
                            </li>
                            <Pagination
                                v-if="boxes.last_page > 1"
                                :pagination="pagination"
                                @paginate="
									(loading = true),
										obtain_boxes({
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
            </transition>
        </div>

        <Options class="h-full"/>
    </div>
</template>

<script>
import {mapActions, mapMutations, mapState} from "vuex";
import selectParent from "../../mixins/selectParent";
import Pagination from "../global/Pagination.vue";
import EditPanel from "./EditPanel.vue";

export default {
    components: {EditPanel, Pagination},
    mixins: [selectParent],
    data() {
        return {
            unmatchedBox: {},
            loading: false,
            filter: "",
            component: "",
            pagination: {},
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
                    title: "Unlinked boxes",
                    classes: "text-blue-900 dark:text-blue-100",
                    show: true
                }
            ],
            multiselect: false,
            multiselectArray: [],
            sorted: false
        };
    },
    computed: {
        ...mapState({
            activeCategory: state => state.standardization.activeCategory,
            activeBox: state => state.standardization.activeBox,
            boxes: state => state.standardization.boxes,
            unmatchedBoxes: state => state.standardization.unmatchedBoxes,
            boxEdit: state => state.standardization.boxEdit,
            boxUnmatched: state => state.standardization.boxUnmatched,
            options: state => state.standardization.options,
            view: state => state.standardization.view,
            per_page: state => state.standardization.per_page
        }),
        menuItems() {
            if (this.view === "relational") {
                return [
                    {
                        action: "edit",
                        icon: "pencil",
                        title: "Edit",
                        classes: "text-blue-900 dark:text-blue-100",
                        show: true
                    },
                    {
                        action: "unlink",
                        icon: "unlink",
                        title: "Un-link",
                        classes: "text-yellow-500",
                        show: true
                    },
                    {
                        action: "relink",
                        icon: "link",
                        title: "Re-link",
                        classes: "text-blue-900 dark:text-blue-100",
                        show: true
                    },
                    {
                        action: "relations",
                        icon: "folder-tree",
                        title: "View relations",
                        classes: "text-blue-900 dark:text-blue-100",
                        show: true
                    }
                ];
            } else {
                return [
                    {
                        action: "edit",
                        icon: "pencil",
                        title: "Edit",
                        classes: "text-blue-900 dark:text-blue-100",
                        show: true
                    },
                    {
                        action: "delete",
                        icon: "trash",
                        title: "Delete",
                        classes: "text-red-500",
                        show: true
                    },
                    {
                        action: "relations",
                        icon: "folder-tree",
                        title: "View relations",
                        classes: "text-blue-900 dark:text-blue-100",
                        show: true
                    }
                ];
            }
        },
        boxesdata() {
            if (!this.sorted) {
                this.sorted = true;
                this.boxes.data.sort(function (a, b) {
                    var x = a.name.toLowerCase();
                    var y = b.name.toLowerCase();
                    if (x < y) {
                        return -1;
                    }
                    if (x > y) {
                        return 1;
                    }
                    return 0;
                });
            }
            return this.boxes.data;
        }

        // filtered_boxes() {
        // 	if (this.filter.length > 0) {
        // 		return this.boxes.data.filter(box => {
        // 			return Object.values(box).some(val => {
        // 				if (val !== null) {
        // 					return val
        // 						.toString()
        // 						.toLowerCase()
        // 						.includes(this.filter.toLowerCase());
        // 				}
        // 			});
        // 		});
        // 	}
        // 	return this.boxes.data;
        // }
    },
    watch: {
        boxes(v) {
            this.pagination = v;
            this.sorted = false
        },
        filter: _.debounce(function (val) {
            this.obtain_boxes({page: null, filter: val});
        }, 300),
        boxEdit(v) {
            if (v === true) {
                this.component = "EditPanel";
            } else {
                this.component = "";
            }
        },
        boxUnmatched(v) {
            if (v === true) {
                this.component = "MatchPanel";
            } else {
                this.component = "";
            }
        }
    },
    mounted() {
        this.showSettings();
    },
    methods: {
        showSettings() {
            if (this.view !== "relational") {
                this.mainMenuItems[0].show = true;
            } else {
                this.mainMenuItems[0].show = false;
            }
        },
        ...mapMutations({
            set_active_box: "standardization/set_active_box",
            set_active_option: "standardization/set_active_option",
            populate_boxes: "standardization/populate_boxes",
            populate_options: "standardization/populate_options",
            populate_unmatchedOptions: "standardization/populate_unmatchedOptions"
        }),
        ...mapActions({
            obtain_boxes: "standardization/obtain_boxes"
        }),

        matchButtonClicked(box) {
            this.$store.commit("standardization/set_box_unmatched", true);
            this.unmatchedBox = box;
        },
        getUnlinkedBoxes() {
            axios
                .get(`unlinked/boxes`)
                .then(response => {
                    this.populate_boxes(response.data);
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
                axios.delete(`boxes/${element}?force=true`).then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                });
            }
            setTimeout(() => {
                this.multiselectArray = [];
                this.multiselect = false;
                this.obtain_boxes({
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
            this.$store.commit("standardization/set_box_unmatched", false);
        },
        menuItemClicked(event, category, category_id) {
            switch (event) {
                case "edit":
                    this.$store.commit("standardization/set_box_edit", true);
                    break;
                case "unlink":
                    this.component = "UnlinkItem";
                    break;
                case "relink":
                    this.component = "RelinkItem";
                    break;
                case "relations":
                    this.component = "RelationsPanel";
                    break;
                case "show-unlinked":
                    this.getUnlinkedBoxes();
                    break;
                case "multi-select":
                    this.multiselect = true;
                    break;
                case "delete":
                    this.component = "DeleteItem";
                    break;
                default:
                    break;
            }
        }
    }
};
</script>
