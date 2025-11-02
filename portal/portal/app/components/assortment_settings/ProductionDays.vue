<template>
	<div class="p-4">
		<section class="w-1/2 mx-auto">
			<div
				class="p-4 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
			>
				<h2 class="font-bold tracking-wide uppercase">
					{{ $t("minimum production days") }}
				</h2>
				<p class="text-sm text-gray-500">
					Add production days which are more expensive than your normal
					production. <br />
					These are the defaults which will be available to add to every
					category with one of the calculation options (not collection
					price), but will be overwritten by settings on option.
				</p>
				<ul v-if="production_days" class="divide-y dark:divide-black">
					<li class="flex items-center justify-between p-2">
						<div class="flex items-center my-2">
							<font-awesome-icon
								:icon="['fal', 'hourglass']"
								class="mr-1"
							/>
							<font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />

							<!-- <input
								type="text"
								class="w-full px-2 py-1 text-sm transition-all duration-100 bg-white border rounded-l shadow-inner hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-900 focus:outline-none focus:ring focus:border-theme-200"
								v-model="newName"
								:placeholder="$t('delivery name')"
							/> -->

							<input
								type="number"
								class="w-full px-2 py-1 text-sm transition-all duration-100 bg-white border rounded-l shadow-inner hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-900 focus:outline-none focus:ring focus:border-theme-200"
								v-model="newDays"
								min="0"
								:placeholder="$t('days')"
							/>
							<div class="box-content flex items-stretch">
								<button
									@click="newMode = 'fixed'"
									class="px-2 py-1 text-sm border border-theme-500 text-theme-500 hover:bg-theme-100"
									:class="{
										'bg-theme-400 text-themecontrast-400 hover:bg-theme-400 cursor-default':
											newMode === 'fixed',
									}"
								>
									{{ $t("fixed") }}
								</button>
								<button
									@click="newMode = 'percentage'"
									class="px-2 py-1 text-sm border border-l-0 border-theme-500 text-theme-500 hover:bg-theme-100"
									:class="{
										'bg-theme-400 text-themecontrast-400 hover:bg-theme-400 cursor-default':
											newMode === 'percentage',
									}"
								>
									{{ $t("percentage") }}
								</button>
							</div>

							<div class="relative flex items-center justify-between">
								<!-- {{ $t("interval") }} -->
								<input
									class="w-24 px-2 py-1 pl-5 text-sm rounded-none input"
									type="number"
									step="0.01"
									min="0.00001"
									v-model="newValue"
								/>
								<font-awesome-icon
									class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-400"
									:icon="[
										'fal',
										newMode === 'fixed' ? 'euro-sign' : 'percent',
									]"
								/>
							</div>

							<button
								class="px-2 py-1 text-sm text-white bg-green-500 border border-green-600 rounded-r"
								@click="
									create_production_day({
										name: newName,
										days: newDays,
										mode: newMode,
										price: newValue,
									}),
										(newName = ''),
										(newDays = null)
								"
							>
								{{ $t("add") }}
							</button>
						</div>
					</li>

					<template v-if="production_days.length > 0">
						<template v-for="(day, i) in production_days">
							<li v-if="day.iso === language">
								<div
									class="flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 group"
								>
									<span class="flex items-center w-full">
										<font-awesome-icon :icon="['fal', 'hourglass']" />
										<!-- <h2 class="w-1/4 ml-4">{{ day.label }}</h2> -->
										<b class="w-1/4 ml-4">
											{{ day.days }}
											<small class="font-normal text-gray-500">
												{{ $t("days") }}
											</small>
										</b>
										<span v-if="day.price > 0">
											<font-awesome-icon
												class="text-gray-400"
												:icon="[
													'fal',
													day.mode === 'fixed'
														? 'euro-sign'
														: 'percent',
												]"
											/>
											{{ day.price }}
											<small class="font-normal text-gray-500">
												{{ $t("additional price") }}
											</small>
										</span>
									</span>

									<span>
										<button
											class="invisible px-2 text-red-500 group-hover:visible"
											@click="showRemoveItem = day.id"
										>
											<font-awesome-icon
												:icon="['fal', 'trash-can']"
											/>
										</button>
										<!-- <font-awesome-icon
										v-if="day.production_days.length > 0"
										:icon="[
											'fal',
											show.includes(day.id)
												? 'caret-up'
												: 'caret-down'
										]"
									/> -->
									</span>
								</div>

								<!-- <transition name="slide">
								<div
									v-show="show.length > 0 && show.includes(day.id)"
								>
									<div
										v-for="(resource, idx) in day.production_days"
										:key="idx + '_' + resource.id"
										class="flex py-2 pl-8 day hover:text-theme-500"
									>
										<span>
											<font-awesome-icon :icon="['fal', 'window']" />
											{{ resource.title }}
										</span>
										<span>
											<button
												v-tooltip="'remove resource from day'"
												class="invisible px-2 text-red-500 rounded-full group-hover:visible hover:bg-red-100"
												@click="
													update_production_day({
														id: day.id,
														day: 'detach',
														type: 'resource',
														resource_id: resource.id
													}),
														get_production_days()
												"
											>
												<font-awesome-icon
													:icon="['fal', 'link-slash']"
												/>
											</button>
										</span>
									</div>
								</div>
							</transition> -->

								<transition name="fade">
									<ProductionDaysRemoveItem
										v-if="showRemoveItem === day.id"
										:item="day"
									/>
								</transition>
							</li>
						</template>
						<li>
							<div
								class="flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 group"
							>
								<span class="flex items-center w-full">
									<font-awesome-icon :icon="['fal', 'hourglass']" />
									<!-- <h2 class="w-1/4 ml-4">{{ $t('normal') }}</h2> -->
									<b class="w-1/4 ml-4">
										{{ Math.max(...daysArray) + 1 }}
										<small class="font-normal text-gray-500">
											{{ $t("days") }}
										</small>
									</b>

									<small class="font-normal text-gray-500">
										{{ $t("no additional price") }}
									</small>
								</span>
							</div>
						</li>
					</template>
				</ul>

				<!-- <vue-nestable v-model="reorderDeliveryDays">
					<vue-nestable-handle slot-scope="{ item }" :item="item">
                  <font-awesome-icon :icon="['fal', 'window-restore']" />
						{{ item.name }}
					</vue-nestable-handle>
				</vue-nestable> -->
			</div>
		</section>
	</div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
export default {
	head() {
		return {
			title: `${this.$t("production days")} | Prindustry Manager`,
		};
	},
	data() {
		return {
			newName: "",
			newDays: null,
			newMode: "fixed",
			newValue: 0.00001,
			reorderDeliveryDays: [],
			show: [],
			showRemoveItem: false,
			daysArray: [],
		};
	},
	created() {
		this.get_production_days();
		if (this.production_days && this.production_days.length > 0) {
			this.show.push(this.production_days[0].id);
			this.production_days.forEach((day) => this.daysArray.push(day.days));
			// this.reorderDeliveryDays = [...this.production_days];
		}
	},
	computed: {
		...mapState({
			// resource: state => state.production_days.resource,
			production_days: (state) => state.production_days.production_days,
		}),
		...mapGetters({
			langauge: "settings/language",
		}),
	},
	watch: {
		production_days(newVal) {
			return newVal;
			// this.reorderDeliveryDays = [...this.production_days];
		},
	},
	methods: {
		...mapActions({
			get_production_days: "production_days/get_production_days",
			create_production_day: "production_days/create_production_day",
			delete_production_day: "production_days/delete_production_day",
			update_production_day: "production_days/update_production_day",
		}),
		retract(id) {
			let index = this.show.indexOf(id);
			this.show.splice(index, 1);
		},
	},
};
</script>

<style>
</style>