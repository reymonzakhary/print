<template>
	<div class="flex h-full" style="max-height: calc(100vh - 5rem)">
		<transition name="fade">
			<component
				:is="component"
				:category="activeCategory"
				:box="activeBox"
				:item="activeOption"
				:items="options"
				:unmatchedItem="unmatchedOption"
				:per_page="per_page"
				:current_page="pagination.current_page"
				:filter="filter"
				:view="view"
				type="option"
				class="z-50"
			></component>
		</transition>

		<div class="z-20 w-64 h-full ml-2">
			<div class="relative flex items-center justify-between pr-2 mb-2 ml-2">
				<div class="flex items-center">
					<p class="text-sm font-bold tracking-wide uppercase">Options</p>
					<div
						class="relative ml-2 group"
						v-if="
							(Object.keys(activeCategory).length > 0 &&
								Object.keys(activeBox).length > 0) ||
							view === 'boxes & options' ||
							view === 'list all'
						"
					>
						<button class="text-blue-500">
							new
							<font-awesome-icon :icon="['fal', 'angle-down']" />
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
									Relational option
								</button>
							</li>
							<li>
								<button
									class="w-full p-2 text-left text-blue-500 hover:bg-blue-100"
									@click="component = 'NewPrindustryItemStandalone'"
								>
									<font-awesome-icon :icon="['fal', 'dice-one']" />
									Standalone option
								</button>
							</li>
						</ul>
					</div>
				</div>
				<ItemMenu
					class="z-10 text-sm text-blue-500 bg-blue-100 rounded-full dark:bg-blue-800 dark:text-blue-300"
					:menuItems="mainMenuItems"
					menuIcon="ellipsis-h"
					menuClass="z-20"
					@item-clicked="menuItemClicked($event)"
				/>
			</div>

			<transition name="list" tag="nav">
				<section
					v-show="options.data"
					class="h-full px-2 pb-2 overflow-auto text-sm"
				>
					<ul
						v-if="unmatchedOptions && unmatchedOptions.length > 0"
						class="mb-3 bg-white border border-red-500 rounded-md shadow-md group dark:bg-gray-800"
					>
						<li
							class="relative"
							v-for="(option, i) in unmatchedOptions"
							:key="i"
						>
							<Button
								@click.native="matchButtonClicked(option)"
								:item="option"
								classes="text-red-500 hover:bg-red-100"
							></Button>
						</li>
					</ul>

					<div
						class="h-full overflow-y-auto bg-white rounded-md shadow-md dark:bg-gray-800"
						style="max-height: calc(100vh - 10rem)"
					>
						<div
							class="sticky top-0 z-10 flex w-full py-3"
							v-if="view === 'list all'"
						>
							<input
								class="w-full px-2 py-1 mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
								type="text"
								placeholder="Search all options"
								v-model="filter"
							/>
							<font-awesome-icon
								:icon="['fal', 'filter']"
								class="absolute right-0 mt-2 mr-4 text-gray-600"
							/>
						</div>

						<ul>
							<li
								class="relative"
								v-for="(option, i) in options.data"
								:key="i"
							>
								<Button
									v-if="option.matches && option.matches.length > 0"
									@click.native="selectOption(option, option.slug)"
									:item="option"
									:menuItems="menuItems"
									classes="text-yellow-500 hover:bg-yellow-200"
								></Button>
							</li>
						</ul>

						<ul>
							<li
								class="relative"
								:class="{
									'bg-pink-100 text-pink-500 hover:bg-pink-200':
										option.published === false,
								}"
								v-for="(option, i) in options.data"
								:key="i"
							>
								<Button
									v-if="option.matches && option.matches.length === 0"
									:class="{
										'bg-blue-100 text-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-blue-300':
											activeOption.slug === option.slug,
										'bg-pink-200 text-pink-500 hover:bg-pink-200':
											option.published === false &&
											activeOption.slug === option.slug,
									}"
									@click.native="selectOption(option, option.slug)"
									:item="option"
									:menuItems="menuItems"
								></Button>
							</li>
							<li
								v-if="options.data && options.data.length === 0"
								class="italic text-center text-gray-500"
							>
								no categories found
								<button
									class="text-blue-500"
									@click="
										obtain_options({
											page: pagination.current_page,
											per_page: per_page,
										})
									"
								>
									refresh
								</button>
							</li>
							<Pagination
								v-if="options.last_page > 1"
								:pagination="pagination"
								@paginate="
									(loading = true),
										obtain_options({
											per_page: per_page,
											page: pagination.current_page,
										}).then((resp) => {
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

		<Values class="h-full" />

		<!-- <transition name="fade">
            <EditPanel
                v-if="activeOption && $store.state.standardization.optionEdit"
                :items="options"
                :item="activeOption"
                :category="activeCategory"
                :box="activeBox"
                type="options"
                class="z-50"
            ></EditPanel>
        </transition>

        <transition name="fade">
            <MatchPanel
                v-if="$store.state.standardization.optionUnmatched"
                :unmatchedItem="unmatchedOption"
                :items="options"
                :category="activeCategory"
                :box="activeBox"
                type="options"
                class="z-50"
            ></MatchPanel>
        </transition> -->
	</div>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";

export default {
	data() {
		return {
			unmatchedOption: {},
			loading: false,
			filter: "",
			component: "",
			pagination: {},
			mainMenuItems: [
				{
					action: "show-unlinked",
					icon: "unlink",
					title: "Unlinked options",
					classes: "text-blue-900 dark:text-blue-100",
					show: true,
				},
			],
			sorted: false,
		};
	},
	computed: {
		...mapState({
			options: (state) => state.standardization.options,
			unmatchedOptions: (state) => state.standardization.unmatchedOptions,
			activeCategory: (state) => state.standardization.activeCategory,
			activeBox: (state) => state.standardization.activeBox,
			activeOption: (state) => state.standardization.activeOption,
			optionEdit: (state) => state.standardization.optionEdit,
			optionUnmatched: (state) => state.standardization.optionUnmatched,
			view: (state) => state.standardization.view,
			per_page: (state) => state.standardization.per_page,
		}),
		optionsdata() {
			// if (!this.sorted) {
			//     this.sorted = true;
			//     this.options.data.sort(function (a, b) {
			//         var x = a.name.toLowerCase();
			//         var y = b.name.toLowerCase();
			//         if (x < y) {
			//             return -1;
			//         }
			//         if (x > y) {
			//             return 1;
			//         }
			//         return 0;
			//     });
			// }
			return this.options.data;
		},
		menuItems() {
			if (this.view === "relational") {
				return [
					{
						action: "edit",
						icon: "pencil",
						title: "Edit",
						classes: "text-blue-900 dark:text-blue-100",
						show: true,
					},
					{
						action: "unlink",
						icon: "unlink",
						title: "Unlink",
						classes: "text-yellow-500",
						show: true,
					},
					{
						action: "relink",
						icon: "link",
						title: "Re-link",
						classes: "text-blue-900 dark:text-blue-100",
						show: true,
					},
					{
						action: "relations",
						icon: "folder-tree",
						title: "View relations",
						classes: "text-blue-900 dark:text-blue-100",
						show: true,
					},
				];
			} else {
				return [
					{
						action: "edit",
						icon: "pencil",
						title: "Edit",
						classes: "text-blue-900 dark:text-blue-100",
						show: true,
					},
					{
						action: "delete",
						icon: "trash",
						title: "Delete",
						classes: "text-red-500",
						show: true,
					},
					{
						action: "relations",
						icon: "folder-tree",
						title: "View relations",
						classes: "text-blue-900 dark:text-blue-100",
						show: true,
					},
				];
			}
		},
		// filtered_options() {
		// 	if (this.filter.length > 0) {
		// 		return this.options.filter((option) => {
		// 			return Object.values(option).some((val) => {
		// 				if (val !== null) {
		// 					return val
		// 						.toString()
		// 						.toLowerCase()
		// 						.includes(this.filter.toLowerCase());
		// 				}
		// 			});
		// 		});
		// 	}
		// 	return this.options;
		// },
	},
	watch: {
		options(v) {
			this.pagination = v;
			this.sorted = false;
		},
		filter: _.debounce(function (val) {
			this.obtain_options({ page: null, filter: val });
		}, 300),
		optionEdit(v) {
			if (v === true) {
				this.component = "EditPanel";
			} else {
				this.component = "";
			}
		},
		optionUnmatched(v) {
			if (v === true) {
				this.component = "MatchPanel";
			} else {
				this.component = "";
			}
		},
	},
	methods: {
		...mapMutations({
			set_active_option: "standardization/set_active_option",
			populate_options: "standardization/populate_options",
		}),
		...mapActions({
			obtain_options: "standardization/obtain_options",
		}),
		selectOption(option, slug) {
			this.set_active_option([]);
			this.set_active_option(option);
		},
		matchButtonClicked(option) {
			this.$store.commit("standardization/set_option_unmatched", true);
			this.unmatchedOption = option;
		},
		getUnlinkedOptions() {
			axios
				.get(`unlinked/options`)
				.then((response) => {
					this.populate_options(response.data);
				})
				.catch((err) => {
					console.log(err.response);
				});
		},
		closeModal() {
			this.component = "";
		},
		close() {
			this.$store.commit("standardization/set_option_unmatched", false);
		},
		menuItemClicked(event, category, category_id) {
			switch (event) {
				case "edit":
					this.$store.commit("standardization/set_option_edit", true);
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
					this.getUnlinkedOptions();
					break;
				case "delete":
					this.component = "DeleteItem";
					break;
				default:
					break;
			}
		},
	},
};
</script>

<style>
</style>
