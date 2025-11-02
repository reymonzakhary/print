<template>
    <div>
        <div
            class="absolute top-0 px-2 -mt-3 tracking-widest text-gray-400 bg-white left-2"
        >
            Edit fields
        </div>
        <span class="relative block mt-4">
			<label
                for="name"
                class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
            >
				Name
			</label>
			<input
                type="text"
                v-model="selected.name"
                name="name"
                class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            />
		</span>
        <span class="relative block mt-6">
			<label
                for="name"
                class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
            >
				Description
			</label>
			<textarea
                v-model="selected.description"
                name="description"
                class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            />
		</span>

        <div class="relative flex items-center mt-4">
            <font-awesome-icon
                :icon="['fal', 'heart-rate']"
                class="mr-2 text-blue-500"
            />
            Published
            <div
                class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                :class="[selected.published ? 'bg-blue-500' : 'bg-gray-300']"
            >
                <label
                    for="published"
                    class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                    :class="[
						selected.published
							? 'translate-x-6 border-blue-500'
							: 'translate-x-0 border-gray-300'
					]"
                ></label>
                <input
                    type="checkbox"
                    id="published"
                    name="published"
                    class="w-full h-full appearance-none active:outline-none focus:outline-none"
                    v-model="selected.published"
                />
            </div>
        </div>

        <div class="relative flex items-center mt-4" v-if="type === 'box'">
            <font-awesome-icon
                :icon="['fal', 'vector-square']"
                class="mr-2 text-blue-500"
            />
            m2
            <div
                class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                :class="[selected.sqm ? 'bg-blue-500' : 'bg-gray-300']"
            >
                <label
                    for="sqm"
                    class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                    :class="[
						selected.sqm
							? 'translate-x-6 border-blue-500'
							: 'translate-x-0 border-gray-300'
					]"
                ></label>
                <input
                    type="checkbox"
                    id="sqm"
                    name="sqm"
                    class="w-full h-full appearance-none active:outline-none focus:outline-none"
                    v-model="selected.sqm"
                />
            </div>
        </div>

        <span
            class="relative block my-6"
            v-if="type === 'box' || type === 'option'"
        >
			<label
                for="input_type"
                class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
            >
				Input type
			</label>
			<select
                v-model="selected.input_type"
                name="input_type"
                id="input_type"
                class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            >
				<option value="multi_select" v-if="type === 'box'">
					Multiselect
				</option>
				<option value="single_select" v-if="type === 'box'">
					Single select
				</option>
				<option value="radio" v-if="type === 'option'">Radio</option>
				<option value="checkbox" v-if="type === 'option'">Checkbox</option>
				<option value="text" v-if="type === 'option'">Text</option>
				<option value="number" v-if="type === 'option'">Number</option>
				<option value="select" v-if="type === 'option'">Select</option>
			</select>
		</span>

        <span class="relative block my-6" v-if="type === 'option'">
			<label
                for="incremented_by"
                class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
            >
				Incremented by
			</label>
			<input
                type="number"
                v-model="selected.incremental_by"
                name="incremented_by"
                class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            />
		</span>

        <div class="flex items-center justify-end">
            <!-- <button
                class="flex items-center px-2 py-1 ml-4 text-gray-500 border border-gray-500 rounded hover:bg-gray-100"
                @click="$parent.edit = false"
            >
                <font-awesome-icon :icon="['fal', 'times-circle']" class="mr-2" />
                Cancel
            </button> -->
            <button
                class="flex items-center px-2 py-1 ml-4 text-green-500 border border-green-500 rounded hover:bg-green-100"
                @click="updateItem()"
            >
                <font-awesome-icon :icon="['fal', 'check']" class="mr-2"/>
                Save
            </button>
        </div>
    </div>
</template>

<script>
import {mapActions} from "vuex";
import selectParent from "../../../mixins/selectParent";

export default {
    mixins: [selectParent],
    props: {
        item: Object,
        selected: Object,
        type: String,
        category: Object,
        box: Object,
        per_page: Number | String,
        view: String,
        current_page: Number | String
    },
    methods: {
        ...mapActions({
            obtain_categories: "standardization/obtain_categories",
            obtain_boxes: "standardization/obtain_boxes",
            obtain_options: "standardization/obtain_options"
        }),
        updateItem() {
            let url = "";
            switch (this.type) {
                case "category":
                    url = `categories/${this.item.slug}`;
                    break;
                case "box":
                    url = `boxes/${this.item.slug}`;
                    break;
                case "option":
                    url = `options/${this.item.slug}`;
                    break;

                default:
                    break;
            }
            let object = {
                name: this.selected.name,
                description: this.selected.description,
                published: this.selected.published,
                sqm: this.selected.sqm,
                input_type: this.selected.input_type,
                incremental_by: this.selected.incremental_by,
                checked: this.selected.checked
            };

            axios
                .put(url, object)
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
                            this.$store.commit(
                                "standardization/set_category_edit",
                                false
                            );
                            break;

                        case "box":
                            if (this.view !== "relational") {
                                this.obtain_boxes({
                                    page: this.current_page,
                                    per_page: this.per_page
                                });
                            } else {
                                this.selectCategory(
                                    this.category,
                                    this.category.slug
                                );
                            }
                            this.$store.commit("standardization/set_box_edit", false);
                            break;

                        case "option":
                            if (this.view !== "relational") {
                                this.obtain_options({
                                    page: this.current_page,
                                    per_page: this.per_page
                                });
                            } else {
                                this.selectBox(this.box, this.box.slug);
                            }
                            this.$store.commit(
                                "standardization/set_option_edit",
                                false
                            );
                            break;
                        default:
                            this.obtain_categories({
                                page: this.current_page,
                                per_page: this.per_page
                            });
                            this.$store.commit(
                                "standardization/set_category_edit",
                                true
                            );
                            break;
                    }
                })
                .catch(error => {
                    this.responseMessage = error;
                    this.responseStatusColor = "yellow";
                });
        }
    }
};
</script>

<style>
</style>
