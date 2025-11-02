<template>
	<div>
		<section
			class="flex flex-wrap justify-start w-full p-1 mt-2 overflow-hidden"
			:style="legless ? 'max-height: 8rem' : 'max-height: none'"
		>
			<!-- {{directories}} -->
			<div v-if="directories.length === 0" class="mt-2 italic text-gray-500">
				{{ $t("no folders") }}
			</div>
			<template v-for="(directory, i) in directories">
				<!-- <FolderPlaceholder v-if="loading" :key="i" /> -->
				<Folder :directory="directory" :position="i" :key="i" />
			</template>
		</section>
		<div
			v-if="directories.length > 3"
			class="w-full h-1 bg-gray-100 border-t dark:bg-gray-800 dark:border-black"
		></div>

		<button
			v-if="directories.length > 3"
			@click="legless = !legless"
			v-html="legless ? 'show all' : 'show less'"
			class="block px-2 py-1 mx-auto rounded-full text-theme-500 hover:text-theme-500 hover:bg-theme-100"
		></button>
	</div>
</template>

<script>
import { mapState } from "vuex";

export default {
	props: {
		parentId: { type: Number, required: true },
	},
	data() {
		return {
			// selected: false,
			legless: true,
			loading: true,
			disk: "",
		};
	},
	watch: {
		// directories(v){
		//    this.loading = false
		// }
	},

	computed: {
		directories() {
			return this.$store.getters[`fm/content/directories`];
		},
	},
};
</script>

<style>
input:checked + svg {
	display: block;
}
</style>