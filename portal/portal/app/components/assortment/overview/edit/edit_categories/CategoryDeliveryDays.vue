<template>
	<div class="flex w-full py-4">
		<ul
			class="flex flex-wrap justify-between w-full bg-white divide-x rounded-l shadow-md md:w-1/2"
		>
			<li
				class="p-2 text-center"
				v-for="(dlv_day, i) in selected_category.dlv_days"
				:key="dlv_day.slug"
			>
				{{ dlv_day.label }}
				{{ dlv_day.label }}

				<button
					@click="selected_category.dlv_days.splice(i, 1)"
					class="text-red-500"
				>
					<font-awesome-icon :icon="['fal', 'trash']" />
				</button>
			</li>
			<li
				class="flex items-center p-2"
				v-if="filtered_delivery_days.length > 0"
			>
				<select
					v-model="dlv"
					name="dlv"
					id="dlv"
					class="w-full p-1 rounded-none rounded-l input"
				>
					<template v-for="day in filtered_delivery_days">
						<option
							v-if="day.iso === language"
							:value="day"
							:key="`day_${day.label}`"
						>
							{{ day.days }}
							<span class="text-gray-500">{{ day.label }}</span>
						</option>
					</template>
				</select>
				<div class="flex items-center w-1/2">
					<button
						@click="updateDeliveryDays()"
						class="px-2 py-1 text-sm text-white bg-green-500 border border-green-600 rounded-r"
					>
						{{ $t("add") }}
					</button>
				</div>
			</li>
		</ul>

		<button
			@click="update_selected_category()"
			class="px-2 py-1 text-sm text-white bg-green-500 border border-green-600 rounded-r"
		>
			{{ $t("save delivery days") }}
		</button>
	</div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
export default {
	name: "CategoryQuantitiesDeliveryDays",
	data() {
		return {
			dlv: {},
		};
	},
	computed: {
		...mapState({
			delivery_days: (state) => state.delivery_days.delivery_days,
			selected_category: (state) => state.product_wizard.selected_category,
		}),
		...mapGetters({
			langauge: "settings/language",
		}),
		filtered_delivery_days() {
			return this.delivery_days.filter(
				(el) =>
					!this.selected_category.dlv_days.find(
						(dlv) => dlv.days === el.days
					)
			);
		},
	},
	created() {
		this.get_delivery_days();

		// this.dlv = this.selected_category.dlv_days
	},
	watch: {
		selected_category(v) {
			return v;
		},
	},
	methods: {
		...mapMutations({
			update_delivery_days: "product_wizard/update_delivery_days",
		}),

		...mapActions({
			get_delivery_days: "delivery_days/get_delivery_days",
			update_selected_category: "product_wizard/update_selected_category",
		}),

		updateDeliveryDays() {
			this.selected_category.dlv_days.push(this.dlv);
			this.update_delivery_days(this.selected_category.dlv_days);
		},
	},
};
</script>

<style>
</style>