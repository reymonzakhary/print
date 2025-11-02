<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'box-open']"/>
            Add standardized Prindustry {{ type }}
        </template>

        <template slot="modal-body">
            <section class="flex flex-col flex-wrap max-w-xl p-8">
                <div class="text-sm">
                    Add standardized Prindustry {{ type }}

                    <!-- box to add -->
                    <!-- if relational view -->
                    <span v-if="box && Object.keys(box).length > 0">
						to
						<b class="relative mx-1">
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >
								box
							</small>
							{{ box.name }}
						</b>
					</span>

                    <!-- category to add to -->
                    <span v-if="category && Object.keys(category).length > 0">
						in
						<b class="relative mx-1">
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >category</small
                            >
							{{ category.name }}
						</b>
					</span>
                </div>
                <div class="relative mt-4">
                    <div class="text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'box-open']"/>
                        <font-awesome-icon :icon="['fal', 'tag']" class="mr-1"/>
                        {{ type }} name:
                    </div>
                    <input
                        type="text"
                        v-model="filter"
                        @change="filter = $event.target.name"
                        @focus="magic_flag = true"
                        ref="newcatinput"
                        class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    />

                    <ul
                        class="absolute p-4 overflow-y-auto bg-gray-100 rounded-b dark:bg-gray-700 max-h-64"
                        v-show="magic_flag && result.data && result.data.length > 0"
                    >
                        <li
                            v-for="item in result.data"
                            :key="item.slug"
                            :value="item.slug"
                            @click="filter = item.name, magic_flag = false"
                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                        >
                            {{ item.name }}
                        </li>
                    </ul>
                </div>

                <div class="mt-2" v-if="type === 'box' || type === 'option'">
                    <div class="text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'info']" class="mr-1"/>
                        {{ type }} info:
                    </div>
                    <textarea
                        name="info"
                        id="info"
                        rows="3"
                        v-model="newItem.info"
                        class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    ></textarea>
                </div>

                <transition name="slide">
                    <div
                        class="w-full p-2 mt-2 text-white bg-yellow-500 rounded shadow"
                        v-if="error"
                    >
                        {{ error }}
                    </div>
                </transition>
            </section>
        </template>

        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
                @click="add()"
            >
                <font-awesome-icon :icon="['fal', 'plus']"/>
                Add {{ type }}
            </button>
        </template>
    </confirmation-modal>
</template>

<script>
import {mapActions, mapState} from "vuex";

export default {
    props: {
        type: String,
        category: Object,
        box: Object,
        item: Object | String,
        per_page: Number | String,
        view: String,
        current_page: Number | String
    },
    data() {
        return {
            newItem: {},
            error: "",
            new_cat: {},
            new_box: {},
            magic_flag: false,
            filter: "",
            result: []
        };
    },
    computed: {
        ...mapState({
            categories: state => state.standardization.categories,
            boxes: state => state.standardization.boxes,
            options: state => state.standardization.options
        })
    },
    watch: {
        filter: _.debounce(function (val) {
            this.newItem.name = this.filter;
            let prefix = "";
            switch (this.type) {
                case "box":
                    prefix = "boxes";
                    break;

                case "option":
                    prefix = "options";
                    break;
            }
            axios
                .get(`/${prefix}?per_page=99999999999&page=1&filter=${this.filter}`)
                .then(response => {
                    this.result = response.data;
                });
        }, 300),
        boxes(v) {
            return v;
        }
    },
    mounted() {
        this.new_cat = this.category;
        this.new_box = this.box;
    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_options: "standardization/obtain_options"
        }),
        add() {
            let url = "";
            switch (this.type) {
                case "category":
                    url = `categories`;
                    break;

                case "box":
                    url = `categories/${this.new_cat.slug}/boxes`;
                    break;

                case "option":
                    url = `categories/${this.new_cat.slug}/boxes/${this.new_box.slug}/options`;
                    break;

                default:
                    break;
            }
            this.axiosPost(url);
        },
        axiosPost(url) {
            axios
                .post(url, this.newItem)
                .then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                    switch (this.type) {
                        case "category":
                            this.obtain_categories({
                                page: this.current_page,
                                per_page: this.per_page
                            });
                            break;

                        case "box":
                            this.$parent.$parent.selectCategory(
                                this.new_cat,
                                this.new_cat.slug
                            );
                            break;

                        case "option":
                            this.$parent.$parent.selectBox(
                                this.new_box,
                                this.new_box.slug
                            );
                            break;
                        default:
                            this.obtain_categories({
                                page: this.current_page,
                                per_page: this.per_page
                            });
                            break;
                    }

                    this.closeModal();
                })
                .catch(err => {
                    this.set_notification({
                        text: err,
                        status: "red"
                    });
                });
        },
        closeModal() {
            this.$parent.closeModal();
        }
    }
};
</script>

<style>
</style>
