<template>
	<div>
		<SidePanel classes="w-3/4 h-full min-h-[85vh]">
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
							<span class="text-gray-500">Tenant</span>
							{{ activeTenant.company_name }}
						</h2>
					</span>
				</div>
			</template>

			<template slot="side-panel-content">
				<div class="p-4">
					<div class="flex gap-2">
						<div class="w-1/2">
							<h3 class="font-bold tracking-wide uppercase">Company</h3>
							<div
								class="relative flex flex-wrap p-2 pt-6 mx-auto bg-gray-100 border rounded"
							>
								<img
									:src="activeTenant.logo"
									:alt="activeTenant.name + '_logo'"
									class="absolute flex items-center object-contain object-center h-20 p-2 overflow-hidden transform -translate-x-1/2 bg-white border rounded shadow w-52 left-1/2 -top-14"
								/>
								<button
									v-if="!updateFile"
									@click="updateFile = true"
									class="absolute top-0 w-5 h-5 text-xs text-center text-white transform translate-x-20 bg-blue-500 border rounded-full shadow left-1/2"
								>
									<font-awesome-icon
										:icon="['fal', 'pencil']"
										class="fa-sm"
									/>
								</button>
								<button
									v-if="updateFile"
									@click="updateFile = false"
									class="absolute top-0 w-5 h-5 text-xs text-center text-white transform translate-x-20 bg-red-500 border rounded-full shadow left-1/2"
								>
									<font-awesome-icon
										:icon="['fal', 'times']"
										class="fa-sm"
									/>
								</button>
								<input
									v-if="updateFile"
									type="file"
									@change="onFileChange($event)"
									class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
								/>

								<div class="flex items-end justify-center w-full">
									<div
										class="mr-4 font-mono text-sm font-bold text-gray-400"
									>
										#{{ activeTenant.id }}
									</div>
									<div class="mr-4">
										{{ activeTenant.company_name }}
									</div>
									<div class="">
										{{ activeTenant.fqdn }}
									</div>
								</div>
								<div class="w-full mt-4">
									<div
										class="flex flex-col justify-around w-full font-mono text-xs text-center text-gray-500 md:flex-row"
									>
										<div v-tooltip="'tenant id'">
											{{ activeTenant.tenant_id }}
										</div>
										<span class="mx-4">/</span>
										<div v-tooltip="'supplier id (host_id)'">
											{{ activeTenant.host_id }}
										</div>
									</div>

									<div
										class="flex justify-around w-full pt-2 mt-4 border-t"
									>
										<div class="flex items-center text-black">
											created:
											<span class="mx-2 text-sm">
												<font-awesome-icon
													:icon="['fal', 'calendar']"
													class="fa-sm"
												/>
												{{
													moment(activeTenant.created_at).format(
														"DD-MM-YYYY"
													)
												}}
											</span>
											<span
												class="p-1 font-mono text-sm text-gray-500 bg-gray-100 rounded"
											>
												<font-awesome-icon
													:icon="['fal', 'clock']"
													class="fa-sm"
												/>
												{{
													moment(activeTenant.created_at).format(
														"HH:MM"
													)
												}}
											</span>
										</div>
										<div class="flex items-center text-black">
											updated:
											<span class="mx-2 text-sm">
												<font-awesome-icon
													:icon="['fal', 'calendar']"
													class="text-sm"
												/>
												{{
													moment(activeTenant.updated_at).format(
														"DD-MM-YYYY"
													)
												}}
											</span>
											<span
												class="p-1 font-mono text-sm text-gray-500 bg-gray-100 rounded"
											>
												<font-awesome-icon
													:icon="['fal', 'clock']"
													class="fa-sm"
												/>
												{{
													moment(activeTenant.updated_at).format(
														"HH:MM"
													)
												}}
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="w-1/2">
							<h3 class="font-bold tracking-wide uppercase">Owner</h3>
							<div
								class="relative flex flex-wrap p-2 pt-6 mx-auto bg-gray-100 border rounded"
							>
								<!-- <div
									class="absolute w-12 h-12 overflow-hidden transform -translate-x-1/2 border rounded-full shadow left-1/2 -top-6"
								>
									logo
								</div> -->
								<div class="flex items-end justify-center w-full">
									<div class="mr-4">
										{{ activeTenant.name }}
									</div>
									<div class="">
										{{ activeTenant.email }}
									</div>
								</div>
								<div
									class="w-full mt-1 font-mono text-xs text-center text-gray-500"
								>
									{{ activeTenant.supplier_id }}
								</div>
							</div>
						</div>
					</div>

					<!-- Actions -->
					<div
						class="flex items-center pb-2 mt-8 font-semibold tracking-wide uppercase border-b"
					>
						Actions
					</div>

					<div class="flex items-center py-2 mt-4 space-x-2">
						<button
							class="px-3 py-1 text-sm font-bold text-white uppercase transition-colors bg-blue-500 rounded-full hover:bg-blue-600"
						>
							<font-awesome-icon
								:icon="['fal', 'pencil']"
								class="mr-1 fa-sm"
							/>
							Edit
						</button>
						<button
							class="px-3 py-1 text-sm font-bold text-white uppercase transition-colors bg-red-500 rounded-full hover:bg-red-600"
						>
							<font-awesome-icon
								:icon="['fal', 'exclamation-triangle']"
								class="mr-1 fa-sm"
							/>
							Delete
						</button>
					</div>

					<!-- Modules -->
					<div
						class="flex items-center pb-2 mt-8 font-semibold tracking-wide uppercase border-b"
					>
						modules
						<div class="ml-2 font-mono">
							{{ pickDuplicate(activeTenant.configure.namespaces).length
							}}<span class="text-gray-500">/{{ apps.length }}</span>
						</div>
					</div>

					<div class="flex items-center py-2 mt-4">
						<div
							class="grid items-stretch justify-center grid-cols-6 gap-4"
						>
							<div
								class="border rounded"
								v-for="app in filtered_apps"
								:key="app.name"
							>
								<!-- <img src="" alt="module logo" /> -->
								<div
									class="p-2 font-bold bg-gray-200"
									:class="{
										'bg-green-500 text-white rounded-t':
											activeTenant.configure.namespaces.find(
												(ns) => ns.namespace === app.name
											),
										'opacity-50':
											app.name === 'core' || app.name === 'auth',
									}"
								>
									<font-awesome-icon
										:icon="[
											'fal',
											activeTenant.configure.namespaces.find(
												(ns) => ns.namespace === app.name
											)
												? 'check-square'
												: 'square',
										]"
										class="fa-sm"
									/>
									{{ app.name }}
								</div>
								<div class="p-2">
									<div
										class="flex flex-col text-xs"
										v-for="area in app.areas"
										:key="`${app.name}_${area.name}`"
									>
										<span class="flex">
											<font-awesome-icon
												v-if="
													activeTenant.configure.namespaces.find(
														(ns) => ns.namespace === app.name
													)
												"
												:icon="['fal', 'check']"
												class="mr-2 text-green-500"
											/>
											{{ area.name }}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</SidePanel>
	</div>
</template>

<script>
import SidePanel from "../global/SidePanel.vue";
import { mapActions, mapMutations, mapState } from "vuex";
import moment from "moment";

export default {
	props: {
		apps: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			moment: moment,
			updateFile: false,
		};
	},
	computed: {
		...mapState({
			activeTenant: (state) => state.tenants.activeTenant,
		}),
		filtered_apps() {
			return this.apps.sort((a, b) => {
				const nameA = a.name.toUpperCase(); // ignore upper and lowercase
				const nameB = b.name.toUpperCase(); // ignore upper and lowercase
				if (nameA < nameB) {
					return -1;
				}
				if (nameA > nameB) {
					return 1;
				}

				// names must be equal
				return 0;
			});
		},
	},
	methods: {
		close() {
			this.$parent.activeTenantDetail = false;
		},
		pickDuplicate(arr) {
			const res = [];
			arr.forEach((iterator) => {
				if (!res.includes(iterator.namespace)) {
					console.log("not inlcuded");
					res.push(iterator.namespace);
				}
			});

			return res;
		},
		async onFileChange(e) {
			const data = new FormData();

			// add file
			const files = e.target.files || e.dataTransfer.files;
			data.append("logo", files[0]);

			const config = {
				headers: {
					"content-type": "multipart/form-data",
				},
			};

			await axios
				.post(`clients/${this.activeTenant.id}/media`, data, config)
				.then((response) => {
					console.log(response);
					this.$store.dispatch("notification/handle_success", response);
					this.updateFile = false;
				})
				.catch((error) => {
					console.log(error);
					this.$store.dispatch("notification/handle_error", error);
					this.updateFile = false;
				});
		},
	},
	components: { SidePanel },
};
</script>

<style></style>