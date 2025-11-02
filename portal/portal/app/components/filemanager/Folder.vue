<template>
	<div class="w-full pb-2 pr-2 sm:w-1/2 lg:w-1/3">
		<div
			class="flex flex-wrap px-4 py-2 bg-white rounded shadow-md cursor-pointer shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
			:class="[
				{
					'bg-theme-100 text-theme-600 dark:text-theme-400': checkSelect(
						'directories',
						directory.path
					),
				},
			]"
			@click="selectItem('directories', directory.path, $event)"
			@dblclick.stop="selectDirectory(directory.path)"
			@contextmenu.prevent="
				selectItem('directories', directory.path, $event),
					contextMenu(directory, $event)
			"
		>
			<section class="w-1/3">
				<label
					class="flex items-center text-xs font-bold tracking-wide uppercase"
				>
					<div
						class="flex items-center justify-center flex-shrink-0 w-3 h-3 mr-2 bg-white border border-gray-400 rounded-sm dark:border-black dark:bg-gray-700 focus-within:border-theme-500"
					>
						<input
							type="checkbox"
							class="absolute opacity-0"
							:checked="checkSelect('directories', directory.path)"
							@click="check($event)"
						/>
						<svg
							class="hidden w-4 h-4 pointer-events-none fill-current text-theme-500 dark:text-theme-400"
							viewBox="0 0 20 20"
						>
							<path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
						</svg>
					</div>
				</label>

				<font-awesome-icon
					:icon="['fas', 'folder']"
					class="cursor-move text-theme-500 fa-3x handle"
				/>
			</section>

			<section class="flex flex-wrap w-2/3">
				<p class="w-full font-bold capitalize truncate">
					{{ directory.basename }}
				</p>

				<span class="w-1/2">
					<p>{{ directory.files }} {{ $t("files") }}</p>
					<p
						class="text-xs tracking-tighter text-gray-600 whitespace-no-wrap"
					>
						{{ $t("last") }} {{ $t("update") }}
					</p>
				</span>
				<span class="w-1/2 text-right">
					<p>-</p>
					<!-- <p class="text-sm text-gray-600">{{folder.timestamp}}</p> -->
					<!-- <p
						class="text-xs tracking-tighter text-gray-600 whitespace-no-wrap"
					>
						{{ lastUpdated }}
					</p> -->
				</span>
			</section>
		</div>
	</div>
</template>

<script>
import moment from "moment";
import { mapMutations } from "vuex";
import managerhelper from "~/components/filemanager/mixins/managerhelper";

export default {
	mixins: [managerhelper],
	props: ["directory"],
	computed: {
		// lastUpdated() {
		// 	const miliseconds = this.directory.timestamp * 1000;
		// 	const date = new Date(miliseconds);
		// 	return moment(date).format("DD-MM-YYYY HH:MM");
		// 	// return `${date.getMonth()} ${date.getDay()} ${date.getFullYear()} `
		// },
		formatBytes(bytes, decimals = 2) {
			if (bytes === 0) return "0 Bytes";

			const k = 1024;
			const dm = decimals < 0 ? 0 : decimals;
			const sizes = [
				"Bytes",
				"KB",
				"MB",
				"GB",
				"TB",
				"PB",
				"EB",
				"ZB",
				"YB",
			];

			const i = Math.floor(Math.log(bytes) / Math.log(k));

			return (
				parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i]
			);
		},
	},
	methods: {
		check(event) {
			const type = "directories";
			if (event.target.checked) {
				this.$store.commit(`fm/content/setSelected`, {
					type,
					path: this.directory.path,
				});
			} else {
				setTimeout(() => {
					this.$store.commit(`fm/content/removeSelected`, {
						type,
						path: this.directory.path,
					});
				}, 50);
			}
		},
	},
};
</script>

<style>
</style>