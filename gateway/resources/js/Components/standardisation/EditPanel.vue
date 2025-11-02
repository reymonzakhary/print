<template>
    <div>
        <SidePanel>
            <template slot="side-panel-header">
                <div class="flex items-center p-4">
					<span class="flex items-center">
						<img
                            src="images/Prindustry-box.png"
                            alt="Prindustry Logo"
                            id="prindustry-logo"
                            class="h-6 mr-1"
                        />

						<h2 class="font-bold tracking-wide uppercase">
							<span class="text-gray-500">{{ type }}</span>
							{{ item.name }}
						</h2>
					</span>

                    <button
                        class="flex items-center px-2 py-1 ml-4 text-gray-500 rounded outline-none hover:bg-gray-100"
                        @click="edit = false"
                        v-if="edit === item.slug"
                    >
                        <font-awesome-icon
                            :icon="['fal', 'times-circle']"
                            class="mr-2"
                        />
                        Cancel
                    </button>
                    <button
                        v-else
                        class="flex items-center px-2 py-1 ml-4 text-blue-500 rounded outline-none hover:bg-blue-100"
                        @click="edit = item.slug"
                    >
                        <font-awesome-icon
                            :icon="['fal', 'pencil-alt']"
                            class="mr-2"
                        />
                        Edit
                    </button>
                </div>
            </template>

            <template slot="side-panel-content">
                <div class="p-4">
                    <div
                        v-if="edit === item.slug"
                        class="relative block p-4 my-8 border rounded"
                    >
                        <EditForm
                            :item="item"
                            :selected="selected"
                            :type="type"
                            :category="category"
                            :box="box"
                            :per_page="per_page"
                            :current_page="current_page"
                            :view="view"
                        ></EditForm>
                    </div>

                    <transition name="slide">
                        <div
                            :class="
								`bg-${responseStatusColor}-500 text-${responseStatusColor}-800 rounded p-2`
							"
                            v-if="responseMessage !== ''"
                        >
                            {{ responseMessage }}
                        </div>
                    </transition>

                    <!-- CONNECTED SUPPLIER ITEMS -->
                    <p class="mt-4 mb-2 text-sm font-bold tracking-wide uppercase">
                        <font-awesome-icon :icon="['fal', 'link']" class="mr-1"/>
                        <font-awesome-icon
                            :icon="['fal', 'parachute-box']"
                            class="mr-1"
                        />
                        <span class="text-gray-500">Connected supplier </span>
                        {{
                            type === "category"
                                ? "categories"
                                : type === "box"
                                    ? "boxes"
                                    : "options"
                        }}
                    </p>
                    <ul class="divide-y">
                        <li
                            v-for="(supplierItem, i) in item.suppliers"
                            :key="supplierItem.id"
                            class="p-2 my-2 transition-colors first:rounded-t last:rounded-b"
                        >
                            <div class="flex items-center justify-between">
								<span class="flex">
									<span class="italic capitalize">
										{{ supplierItem.tenant_name }}
									</span>
									's
									<span class="ml-2">
										{{ supplierItem.name }}
									</span>
								</span>
                                <span class="flex">
									<button
                                        v-if="editLinked !== supplierItem.slug"
                                        class="flex items-center px-2 py-1 text-sm text-blue-500 rounded hover:text-blue-600 hover:bg-blue-100"
                                        @click="editLinked = supplierItem.slug"
                                    >
										<font-awesome-icon
                                            :icon="['fal', 'pencil-alt']"
                                            class="mr-2"
                                        />
										Edit
									</button>
									<button
                                        v-if="editLinked !== supplierItem.slug"
                                        class="flex items-center px-2 py-1 text-sm text-red-500 rounded hover:text-red-600 hover:bg-red-100"
                                        @click="
											$store.commit(
												'standardization/set_edit_data',
												supplierItem
											),
												($parent.component = 'DetachSupplierItem'),
												close()
										"
                                    >
										<font-awesome-icon
                                            :icon="['fal', 'unlink']"
                                            class="mr-2"
                                        />
										Detach
									</button>
									<button
                                        v-else
                                        class="flex items-center px-2 py-1 text-sm text-gray-500 rounded hover:text-gray-600"
                                        @click="editLinked = false"
                                    >
										<font-awesome-icon
                                            :icon="['fal', 'times-circle']"
                                            class="mr-2"
                                        />
										Close
									</button>
								</span>
                            </div>

                            <transition-group name="fade">
                                <MatchingSelect
                                    v-if="editLinked === supplierItem.slug"
                                    key="matching_select"
                                    :items="items"
                                    :match="supplierItem"
                                    :i="i"
                                    matchType="suppliers"
                                    class="w-full px-4 bg-gray-100"
                                ></MatchingSelect>
                                <MatchingNew
                                    v-if="editLinked === supplierItem.slug"
                                    key="matching_new"
                                    :items="items"
                                    :match="supplierItem"
                                    :i="i"
                                    matchType="suppliers"
                                    class="w-full px-4 bg-gray-100"
                                ></MatchingNew>
                            </transition-group>
                        </li>
                    </ul>
                </div>
            </template>
        </SidePanel>
    </div>
</template>

<script>
import SidePanel from "../global/SidePanel.vue";

import Vue from "vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import EditForm from "./inputs/EditForm.vue";

Vue.component("v-select", vSelect);

export default {
    props: {
        item: Object,
        items: Array | Object,
        category: Object,
        box: Object,
        type: String,
        per_page: Number | String,
        view: String,
        current_page: Number | String
    },
    data() {
        return {
            selected: this.item,
            edit: null,
            editLinked: false,
            modal: "",

            responseMessage: "",
            responseStatusColor: ""
        };
    },
    components: {SidePanel, EditForm},
    methods: {
        close() {
            this.$store.commit("standardization/set_category_edit", false);
            this.$store.commit("standardization/set_box_edit", false);
            this.$store.commit("standardization/set_option_edit", false);
        }
    }
};
</script>

<style>
</style>
