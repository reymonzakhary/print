<template>
    <confirmation-modal>
        <template slot="modal-header">
            <font-awesome-icon :icon="['fal', 'trash']" class="mr-2"/>
            Relink {{ type }} <b>{{ item.name }}</b>
        </template>

        <template slot="modal-body">
            <section class="flex p-4 min-w-min">
                <div class="my-8 text-sm min-w-min">
					<span>
						<span v-if="category && Object.keys(category).length > 0">
							<b class="relative p-1 mx-1 border rounded">
								<small
                                    class="absolute top-0 left-0 -mt-4 font-normal text-gray-500 min-w-max"
                                >
									from category
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
						<font-awesome-icon
                            :icon="['fal', 'chevron-right']"
                            class="mx-1"
                        />

						<b
                            class="relative p-1 mx-1 bg-blue-100 border border-blue-200 rounded"
                        >
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >
								{{ type }}
							</small>
							{{ item.name }}
						</b>
					</span>
                    <span>
						<font-awesome-icon :icon="['fal', 'link']" class="mx-1"/>

						<b
                            class="relative p-1 mx-1"
                        >
							<small
                                class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"
                            >
								to {{ type === "box" ? "category" : "box" }}
							</small>
							<v-select
                                :options="type === 'box' ? categories : boxes"
                                label="name"
                                class="inline-block text-sm bg-green-100 border-green-200 rounded min-w-max"
                                style="min-width: 10rem"
                                v-model="selected"
                            >
							</v-select>
						</b>
					</span>
                </div>
            </section>
        </template>
        <template slot="confirm-button">
            <button
                class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
                @click="relink()"
            >
                <font-awesome-icon :icon="['fal', 'link']"/>
                Re-link
            </button>
        </template>
    </confirmation-modal>
</template>

<script>
import match from "../../../mixins/match";

export default {
    mixins: [match],
    props: {
        items: Array | Object,
        item: Object,
        match: Object,
        category: Object,
        box: Object,
        i: Number,
        type: String,
        matchType: String
    },
    data() {
        return {
            selected: (this.box) ? this.box : this.category,
            boxes: [],
            categories: []
        };
    },
    mounted() {
        if (this.type === "box") {
            axios.get(`/categories?per_page=99999999`).then(response => {
                this.categories = response.data.data;
            });
        }
        if (this.type === "option") {
            axios.get(`/categories/${this.category.slug}/boxes`).then(response => {
                this.boxes = response.data.data;
            });
        }
    },
    methods: {
        relink() {
            let url = "";
            switch (this.type) {
                case "box":
                    url = `/categories/${this.category.slug}/boxes/${this.item.slug}/attach`;
                    break;
                case "option":
                    url = `/categories/${this.category.slug}/boxes/${this.box.slug}/options/${this.item.slug}/attach`;
                    break;

                default:
                    break;
            }
            axios
                .post(url, {
                    slug: this.selected.slug
                })
                .then(response => {
                    this.set_notification({
                        text: response.data.message,
                        status: "green"
                    });
                    switch (this.type) {
                        case "box":
                            this.$parent.$parent.selectCategory(
                                this.selected,
                                this.selected.slug
                            );
                            break;

                        case "option":
                            this.$parent.$parent.selectBox(this.selected, this.selected.slug);
                            break;
                        default:
                            this.$parent.$parent.selectCategory(
                                this.selected,
                                this.selected.slug
                            );
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
