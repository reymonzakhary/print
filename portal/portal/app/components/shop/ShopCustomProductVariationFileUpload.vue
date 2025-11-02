<template>
	<div class="flex items-center w-full">
		<input
			hidden
			type="file"
			:id="'fileUpload_' + option.id + index"
			:name="'fileUpload_' + option.id + index"
			@change.prevent="fileChanged"
		/>

		<div
			v-if="!file"
			class="flex items-center w-full mb-1 ml-2 text-sm italic text-center text-orange-500"
		>
			<font-awesome-icon
				:icon="['fad', 'triangle-exclamation']"
				class="mr-1"
			/>
			{{ $t("no file added") }}
		</div>

		<div
			v-else
			class="flex items-center w-full mb-1 ml-2 text-sm italic text-center text-theme-500"
		>
			{{ file.name }}
		</div>

		<button
			class="w-full px-2 py-1 text-sm transition-colors duration-75 bg-gray-200 border rounded-full dark:bg-gray-800 dark:hover:bg-black dark:border-gray-900 hover:bg-gray-300"
			@click.prevent="chooseFiles"
		>
			<font-awesome-icon :icon="['fal', 'upload']" class="mr-1" />
			{{ !file ? "Upload file" : "Change file" }}
			<font-awesome-icon
				:icon="['fad', 'spinner-third']"
				class="text-theme-500 fa-spin"
				v-if="loading"
			/>
		</button>
	</div>
</template>

<script>
import { mapMutations } from "vuex";

export default {
	props: {
		option: Object,
		validation: Object,
		editable: Boolean,
		index: Number,
	},
	data() {
		return {
			file: "",
			loading: false,
		};
	},
	methods: {
		...mapMutations({
			change_item: "orders/change_item",
			update_item: "orders/update_item",
			change_single_item: "orders/change_single_item",
			update_fileList: "orders/update_fileList",
		}),
		chooseFiles() {
			document
				.getElementById("fileUpload_" + this.option.id + this.index)
				.click(function (event) {
					event.stopPropagation();
				});
		},
		fileChanged(event) {
			this.loading = true;
			var files = event.target.files || event.dataTransfer.files;
			if (!files.length) {
				return;
			}
			this.createFile(files[0]);
		},

		createFile(file) {
			this.file = file;
			// add file
			let i = this.$parent.$parent.sendVariations.findIndex(
				(option) => option.id === this.option.id
			);

			this.$parent.$parent.sendObject.set(
				`variations[${i}][${this.validation.key}]`,
				file
			);

			this.loading = false;
		},
	},
};
</script>