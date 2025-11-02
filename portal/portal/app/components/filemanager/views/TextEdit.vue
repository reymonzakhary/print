<template>
	<div class="fixed top-0 bottom-0 left-0 z-50 flex items-end justify-center w-full h-full p-4 bg-black">
		<button
			class="absolute top-0 right-0 flex items-center m-4 text-white"
			@click="$store.commit('fm/modal/clearModal')"
		>
			{{ $t("close") }}
			<font-awesome-icon
				:icon="['fad', 'circle-xmark']"
				class="ml-2"
			/>
		</button>

		<MonacoEditor
         v-if="Object.keys(selectedItem).length>0"
			v-model="code"
			:language="selectedItem ? $store.state.fm.settings.textExtensions[selectedItem.extension] : 'plaintext'"
			class="w-full h-full"
			:options="{ theme: 'vs-dark' }"
		/>

		<button
			class="px-4 py-1 mr-2 text-sm rounded-full text-themecontrast-400 bg-theme-400 hover:bg-theme-700"
			@click="updateFile"
		>
			{{ $t("update") }}
		</button>
	</div>
</template>

<script>
	export default {
		name: "TextEdit",
		data() {
			return {
				code: "",
				editor: "",
			};
		},
		computed: {
			selectedDisk() {
				return this.$store.getters["fm/content/selectedDisk"];
			},

			selectedItem() {
				return this.$store.getters["fm/content/selectedList"][0];
			},
		},
		watch: {
			code: {
				deep: true,
				handler(v) {
					return v;
				},
			},
			selectedItem: {
				deep: true,
				immediate: true,
				handler(v) {
					return v;
				},
			},
			selectedDisk: {
				deep: true,
				immediate: true,
				handler(v) {
					return v;
				},
			},
		},
		async mounted() {
			// get file for editF
			await this.$store
				.dispatch("fm/filemanager/getFile", {
					disk: this.selectedDisk,
					path: this.selectedItem.path,
				})
				.then(async (response) => {
					// add code
					if (this.selectedItem.extension === "json") {
						this.code = JSON.stringify(response, null, 4);
					} else if (response instanceof Blob) {
						this.code = await response.text();
					} else {
						this.code = response;
					}
				});
		},
		methods: {
			// Update file
			updateFile() {
				const value = this.code;
				const formData = new FormData();
				// add disk name
				formData.append("disk", this.selectedDisk);
				// add path
				formData.append("path", this.selectedItem.dirname);
				// add updated file
				formData.append("file", new Blob([value]), this.selectedItem.basename);

				this.$store.dispatch("fm/filemanager/updateFile", formData).then((response) => {
					// if file updated successfully
					if (response.result.status === "success") {
						// close modal window
						this.closeModal();
					}
				});
			},

			closeModal() {
				this.$store.commit("fm/modal/clearModal");
			},
		},
	};
</script>
