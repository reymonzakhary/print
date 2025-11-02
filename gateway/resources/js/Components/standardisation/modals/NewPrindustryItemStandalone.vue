<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'box-open']"/>
            Add standalone standardized Prindustry {{ type }}
        </template>

        <template slot="modal-body">
            <section class="flex flex-col flex-wrap max-w-xl p-8">
                <div class="text-sm">
                    Add standalone standardized Prindustry {{ type }}
                </div>
                <div class="mt-4">
                    <div class="text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'box-open']"/>
                        <font-awesome-icon :icon="['fal', 'tag']" class="mr-1"/>
                        {{ type }} name:
                    </div>
                    <input
                        type="text"
                        v-model="newItem.name"
                        ref="newcatinput"
                        class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    />
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
import {mapActions} from "vuex";

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
            boxes: [],
            categories: []
        };
    },
    mounted() {
        this.new_cat = this.category;
        this.new_box = this.box;
        if (this.type === "box") {
            axios.get(`/categories?per_page=99999999`).then(response => {
                this.categories = response.data.data;
            });
        }
        if (this.type === "option") {
            axios.get(`/categories?per_page=99999999`).then(response => {
                this.categories = response.data.data;
            });
            axios.get(`/boxes?per_page=99999999`).then(response => {
                this.boxes = response.data.data;
            });
        }
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
                    url = `boxes`;
                    break;

                case "option":
                    url = `options`;
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
                            if (this.view !== "relational") {
                                this.obtain_boxes({
                                    page: this.current_page,
                                    per_page: this.per_page
                                });
                            } else {
                                this.$parent.$parent.selectCategory(
                                    this.category,
                                    this.category.slug
                                );
                            }
                            break;

                        case "option":
                            if (this.view !== "relational") {
                                this.obtain_options({
                                    page: this.current_page,
                                    per_page: this.per_page
                                });
                            } else {
                                this.$parent.$parent.selectBox(this.box, this.box.slug);
                            }
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
                        text: err.response.message,
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
