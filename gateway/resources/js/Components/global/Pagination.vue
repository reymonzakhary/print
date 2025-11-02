<template>
	<ul
		class="absolute flex justify-between bg-white rounded-md shadow dark:bg-gray-800 bottom-5 w-60"
	>
		<li key="transition_one">
			<a
				class="block px-3 py-2 transition-colors rounded-l-md"
				:class="{
					'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
						pagination.current_page === 1,
					' text-blue-500 hover:bg-blue-500 hover:text-white':
						pagination.current_page > 1,
				}"
				href="#"
				@click.prevent="change(pagination.first_page)"
			>
				<font-awesome-icon :icon="['fal', 'chevron-double-left']" />
			</a>
		</li>
		<li key="transition_two">
			<a
				class="block px-3 py-2 transition-colors"
				:class="{
					'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
						pagination.current_page === 1,
					' text-blue-500 hover:bg-blue-500 hover:text-white':
						pagination.current_page > 1,
				}"
				href="#"
				@click.prevent="change(pagination.current_page - 1)"
			>
				<font-awesome-icon :icon="['fal', 'chevron-left']" />
			</a>
		</li>
		<li v-for="page in pages" :key="page">
			<a
				:class="[
					page == pagination.current_page
						? 'text-white bg-blue-500 border-blue-600'
						: 'hover:text-white hover:bg-blue-500 text-blue-500',
					'block px-3 py-2  transition-colors',
				]"
				href="#"
				@click.stop="change(page)"
			>
				{{ page }}
			</a>
		</li>
		<li key="transition_prelast">
			<a
				class="block px-3 py-2 transition-colors hover:text-white"
				:class="{
					'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
						pagination.current_page === pagination.last_page,
					' text-blue-500 hover:bg-blue-500 hover:text-white':
						pagination.current_page < pagination.last_page,
				}"
				href="#"
				@click.prevent="change(pagination.current_page + 1)"
			>
				<font-awesome-icon :icon="['fal', 'chevron-right']" />
			</a>
		</li>
		<li key="transition_last">
			<a
				class="block px-3 py-2 transition-colors rounded-r-md"
				:class="{
					'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
						pagination.current_page === pagination.last_page,
					' text-blue-500 hover:bg-blue-500 hover:text-white':
						pagination.current_page < pagination.last_page,
				}"
				href="#"
				@click.prevent="change(pagination.last_page)"
			>
				<font-awesome-icon :icon="['fal', 'chevron-double-right']" />
			</a>
		</li>
	</ul>
</template>

<script>
export default {
	props: {
		pagination: {
			type: Object,
			required: true,
		},
		offset: {
			type: Number,
			default: 1,
		},
	},
	computed: {
		pages() {
			if (!this.pagination.to) {
				return null;
			}
			let from = this.pagination.current_page - this.offset;
			if (from < 1) {
				from = 1;
			}
			let to = from + this.offset * 2;
			if (to >= this.pagination.last_page) {
				to = this.pagination.last_page;
			}
			let pages = [];
			for (let page = from; page <= to; page++) {
				pages.push(page);
			}
			return pages;
		},
	},
	methods: {
		change: function (page) {
			this.pagination.current_page = page;
			this.$emit("paginate");
		},
	},
};
</script>
