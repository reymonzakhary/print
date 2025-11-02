<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'trash']" class="mr-2"/>
            Delete {{ item.name }}
        </template>

        <template slot="modal-body">
            <form class="flex flex-col flex-wrap p-8">
				<span>
					<font-awesome-icon :icon="['fal', 'trash']" class="mr-2"/>
					Delete {{ type }} {{ item.name }}
				</span>

                <fieldset class="my-4 text-sm">
                    <input
                        type="checkbox"
                        name="force_delete"
                        id="force_delete"
                        @change="force = !force"
                    />
                    <label for="force_delete">
                        force delete?
                        <span class="ml-2 text-gray-500">
							unlinks all related
							{{ type === "category" ? "boxes" : "options" }}
						</span>
                    </label>
                </fieldset>

                <fieldset v-if="force" class="my-4">
                    <div class="text-yellow-600">
                        <font-awesome-icon
                            :icon="['fal', 'exclamation-triangle']"
                            class="mr-2"
                        />
                        Warning, this will unlink ALL
                        {{ type === "category" ? "boxes" : "options" }} and DELETE the
                        {{ type }}. This cannot be undone!
                    </div>
                    <!-- <div class="w-full">
                        Type <b>{{ item.name }}</b> to
                        <span class="text-red-500">delete</span>
                    </div>
                    <input
                        type="text"
                        v-model="new_name"
                        ref="deletecatinput"
                        class="w-full px-2 py-1 mt-2 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                    />

                    <transition name="slide">
                        <div
                            class="w-full p-2 mt-2 text-white bg-yellow-500 rounded shadow"
                            v-if="error"
                        >
                            {{ error }}
                        </div>
                    </transition> -->
                </fieldset>
            </form>
        </template>

        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-red-600 rounded-full hover:bg-red-800"
                :class="{'border-2 border-yellow-400': force}"
                @click="deleteItem()"
            >
                <font-awesome-icon :icon="['fal', 'trash']"/>
                {{ (force) ? 'Force' : '' }} Delete
            </button>
        </template>
    </confirmation-modal>
</template>

<script>
import {mapActions} from "vuex";

export default {
    props: {
        category: Object,
        box: Object,
        option: Object,
        item: Object,
        type: String,
        per_page: Number | String,
        current_page: Number | String,
        filter: String
    },
    data() {
        return {
            new_name: "",
            error: "",
            force: false
        };
    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_options: "standardization/obtain_options"
        }),
        deleteItem() {
            // if (this.item.name === this.new_name) {
            let url = "";
            switch (this.type) {
                case "category":
                    url = `categories/${this.item.slug}`;
                    break;

                case "box":
                    url = `/boxes/${this.item.slug}`;
                    break;

                case "option":
                    url = `options/${this.item.slug}`;
                    break;
                default:
                    url = `categories/${this.category.slug}`;
                    break;
            }
            this.axiosDelete(url);
            // } else {
            // 	this.error = "validation not correct";
            // 	setTimeout(() => {
            // 		this.error = "";
            // 	}, 1500);
            // }
        },
        axiosDelete(url) {
            if (this.force) {
                url = url + "?force=true";
            }
            axios
                .delete(url)
                .then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                    switch (this.type) {
                        case "category":
                            this.obtain_categories({
                                page: this.current_page,
                                per_page: this.per_page,
                                filter: this.filter
                            });
                            break;

                        case "box":
                            this.obtain_boxes({
                                page: this.current_page,
                                per_page: this.per_page,
                                filter: this.filter
                            });
                            break;

                        case "option":
                            this.obtain_options({
                                page: this.current_page,
                                per_page: this.per_page,
                                filter: this.filter
                            });
                            break;
                        default:
                            this.obtain_categories({
                                page: this.current_page,
                                per_page: this.per_page,
                                filter: this.filter
                            });
                            break;
                    }

                    this.closeModal();
                })
                .catch(err => {
                    this.set_notification({
                        text: err.response.data.message,
                        status: "red"
                    });
                    this.closeModal();
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
