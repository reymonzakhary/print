<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'unlink']" class="mr-2"/>
            Unlink {{ item.name }}
        </template>

        <template slot="modal-body">
            <section class="flex flex-wrap max-w-xl p-8">
                <div class="mx-auto my-8 text-sm">
					<span>
						<span v-if="category && Object.keys(category).length > 0">
							<b class="relative p-1 mx-1 border rounded">
								<small
                                    class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                                >
									category
								</small>
								{{ category.name }}
							</b>
						</span>

						<span
                            v-if="
								category &&
									Object.keys(category).length > 0 &&
									box &&
									Object.keys(box).length > 0
							"
                        >
							<font-awesome-icon
                                :icon="['fal', 'chevron-right']"
                                class="mx-1"
                            />
						</span>
					</span>

                    <!-- box to add -->
                    <span v-if="box && Object.keys(box).length > 0">
						<b class="relative p-1 mx-1 border rounded">
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >
								box
							</small>
							{{ box.name }}
						</b>
					</span>

                    <span>
						<font-awesome-icon :icon="['fal', 'unlink']" class="mx-1"/>

						<b
                            class="relative p-1 mx-1 bg-yellow-100 border border-yellow-200 rounded"
                        >
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >
								{{ type }}
							</small>
							{{ item.name }}
						</b>
					</span>
                </div>

                <!-- <div class="w-full">
                    Type <b>{{ item.name }}</b> to
                    <span class="text-yellow-500">unlink</span>
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
            </section>
        </template>

        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-yellow-600 rounded-full hover:bg-yellow-800"
                @click="unlinkItem()"
            >
                <font-awesome-icon :icon="['fal', 'unlink']"/>
                Unlink
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
        current_page: Number | String
    },
    data() {
        return {
            new_name: "",
            error: ""
        };
    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_options: "standardization/obtain_options"
        }),
        unlinkItem() {
            // if (this.item.name === this.new_name) {
            let url = "";
            switch (this.type) {
                case "box":
                    url = `categories/${this.category.slug}/boxes/${this.item.slug}`;
                    break;

                case "option":
                    url = `categories/${this.category.slug}/boxes/${this.box.slug}/options/${this.item.slug}`;
                    break;
                default:
                    url = `categories/${this.category.slug}/`;
                    break;
            }
            this.axiosUnlink(url);
            // } else {
            // 	this.error = "validation not correct";
            // 	setTimeout(() => {
            // 		this.error = "";
            // 	}, 1500);
            // }
        },
        axiosUnlink(url) {
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
                                per_page: this.per_page
                            });
                            break;

                        case "box":
                            this.$parent.$parent.selectCategory(
                                this.category,
                                this.category.slug
                            );
                            break;

                        case "option":
                            this.$parent.$parent.selectBox(this.box, this.box.slug);
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
