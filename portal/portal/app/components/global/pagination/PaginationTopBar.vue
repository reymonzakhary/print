<template>
	<div>
		<div class="flex text-themecontrast-400" v-if="!loading">
			<p class="text-xs">
				{{ $t("page") }}
				<select
					class="text-sm border border-white rounded text-themecontrast-400 bg-theme-400"
					@change="
						set_loader(true),
							toggle_endofresults(false),
							climbTheCanion(),
							$emit('paginationChangedTop', {
								page: $event.target.value,
								type: 'set',
							})
					"
				>
					<option
						:value="i"
						v-for="i in pagination.last_page"
						:key="i"
						:selected="i === pagination.current_page"
					>
						{{ i }}
					</option>
				</select>
				{{ $t("of") }}
				<span class="text-sm">{{ pagination.last_page }}</span>
			</p>
			<span class="flex items-center pl-2 mx-3 border-l">
				<!-- <span class="flex items-center mx-1">
					<p class="mr-2 text-xs">{{ $t("sort by") }}</p>
					<select
						v-model="sort"
						class="text-sm border border-white rounded text-themecontrast-400 bg-theme-400"
						@change="sort = $event.target.value"
					>
						<option value="price">{{ $t("price") }} </option>
						<option value="qty"> {{ $t("quantity") }} </option>
						<option value="dlv"> {{ $t("delivery") }} </option>
						<option value="supplier"> {{ $t("supplier") }} </option>
						<option value="pm"> {{ $t("printing method") }} </option>
					</select>
				</span>

				<button class="flex items-center px-2 rounded hover:bg-theme-500">
					<font-awesome-icon
						:icon="['fad', 'sort-up']"
						:class="sortdir === 'asc' ? 'block' : 'hidden'"
						@click="sortdir = 'desc'"
					/>

					<font-awesome-icon
						:icon="['fad', 'sort-down']"
						:class="sortdir === 'desc' ? 'block' : 'hidden'"
						@click="sortdir = 'asc'"
					/>
				</button> -->

				<select
					class="text-sm border border-white rounded bg-theme-400"
					@change="
						set_loader(true),
							set_pagination({
								current_page: pagination.current_page,
								per_page: $event.target.value,
								last_page: pagination.last_page,
								total: pagination.total,
							}),
							toggle_endofresults(false),
							climbTheCanion(),
							$emit('paginationChangedTop', {
								page: pagination.current_page,
								type: 'set',
							})
					"
				>
					<option value="10" :selected="pagination.per_page === 10">
						10
					</option>
					<option value="25" :selected="pagination.per_page === 25">
						25
					</option>
					<option value="50" :selected="pagination.per_page === 50">
						50
					</option>
					<option value="100" :selected="pagination.per_page === 100">
						100
					</option>
					<option value="250" :selected="pagination.per_page === 250">
						250
					</option>
					<option value="500" :selected="pagination.per_page === 500">
						500
					</option>
				</select>
				<p class="ml-2 text-xs text-white">{{ $t("per page") }}</p>
			</span>
		</div>
		<font-awesome-icon
			:icon="['fad', 'spinner-third']"
			spin
			class="text-white"
			v-if="loading"
		/>
	</div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import pagination from "~/mixins/pagination";

export default {
	mixins: [pagination],
	mounted() {
		this.set_loader(false);
	},
	computed: {
		...mapState({
			pagination: (state) => state.pagination.pagination,
			loading: (state) => state.pagination.loading,
		}),
	},
	methods: {
		...mapMutations({
			set_pagination: "pagination/set_pagination",
			set_loader: "pagination/set_loader",
		}),
	},
};
</script>

<style>
</style>