<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'code-merge']" class="mr-2"/>
            Merge {{ type === "category" ? "categories" : "boxes" }}
        </template>

        <template slot="modal-body">
            <section class="flex p-4 min-w-max">
                <div class="flex items-center my-8 text-sm min-w-max">
                    <!-- box to add -->
                    <div class="flex flex-col">
                        <div
                            v-for="item in multiselectArray"
                            :key="item"
                            style="min-width: 10rem"
                        >
                            <b
                                class="relative block p-1 mx-1 my-2 border rounded min-w-max"
                                style="min-width: 10rem"
                            >
                                <small
                                    class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                                >
                                    selected
                                    {{ type === "category" ? "categories" : "boxes" }}
                                </small>
                                {{ item }}
                            </b>
                        </div>
                    </div>
                    <div class="mx-2">
                        <p class="text-xs text-gray-500">merge into</p>
                        <font-awesome-icon
                            :icon="['fal', 'code-merge']"
                            rotation="90"
                            class="fa-lg"
                        />
                    </div>

                    <span>
						<div class="relative flex items-center mb-4 text-sm">
							<div
                                class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                                :class="[newname ? 'bg-blue-500' : 'bg-gray-300']"
                            >
								<label
                                    for="toggle"
                                    class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                                    :class="[
										newname
											? 'translate-x-6 border-blue-500'
											: 'translate-x-0 border-gray-300'
									]"
                                ></label>
								<input
                                    type="checkbox"
                                    id="toggle"
                                    name="toggle"
                                    class="w-full h-full appearance-none active:outline-none focus:outline-none"
                                    v-model="newname"
                                />
							</div>

							Create new {{ type === "category" ? "category" : "box" }}?
						</div>
						<div class="">
							<b class="relative p-1" v-if="newname">
								<small
                                    class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                                >
									type new name
								</small>
								<input
                                    type="text"
                                    v-model="name"
                                    class="inline-block px-2 py-1 text-sm border rounded min-w-max"
                                />
							</b>
							<b class="relative p-1 m-1" v-else>
								<small
                                    class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                                >
									select exisiting name
								</small>
								<v-select
                                    :options="multiselectArray"
                                    label="name"
                                    class="inline-block text-sm rounded min-w-max"
                                    style="min-width: 10rem"
                                    v-model="name"
                                >
								</v-select>
							</b>
						</div>
					</span>
                </div>
            </section>
        </template>
        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
                :disabled="loading"
                @click="merge()"
            >
                <font-awesome-icon
                    :icon="['fal', 'code-merge']"
                    rotate="270"
                    class="mx-1"
                />
                Merge
                <font-awesome-icon
                    :icon="['fad', 'spinner-third']"
                    class="text-theme-500 fa-spin"
                    v-if="loading"
                />
            </button>
        </template>
    </confirmation-modal>
</template>

<script>
import {mapActions} from "vuex";

export default {
    props: {
        multiselectArray: Array,
        type: String,
        per_page: Number | String,
        current_page: Number | String,
        filter: String
    },
    data() {
        return {
            newname: false,
            name: "",
            loading: false
        };
    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes"
        }),
        merge() {
            this.loading = true;
            let prefix = "";
            switch (this.type) {
                case "category":
                    prefix = `categories`;
                    break;
                case "box":
                    prefix = `boxes`;
                    break;
                default:
                    break;
            }
            axios
                .post(`merge/${prefix}?new=${this.newname}`, {
                    name: this.name,
                    // categories: this.multiselectArray,
                    boxes: this.multiselectArray
                })
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
                    }
                    this.loading = false;
                    this.closeModal();
                })
                .catch(err => {
                    this.set_notification({
                        text: err,
                        status: "red"
                    });
                    this.loading = false;
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
