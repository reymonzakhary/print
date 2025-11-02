<template>
	<div class="relative p-4">
		<transition name="fade">
			<component
				:is="component"
				:item="activeTenant"
				:type="type"
			></component>
		</transition>

		<header class="p-2 font-bold uppercase tracking wide">
			<!-- <button
                class="mr-4 text-blue-500"
                @click="$parent.component = 'TenantsTable'"
            >
                <font-awesome-icon :icon="['fal', 'chevron-left']" /> Back
            </button> -->
			<font-awesome-icon :icon="['fal', 'users']" />
			Tenants
			<button
				class="ml-2 text-sm font-normal text-blue-500 transition hover:underline"
				@click="$parent.component = 'NewTenant'"
			>
				<font-awesome-icon :icon="['fal', 'plus']" />
				<font-awesome-icon :icon="['fal', 'user-crown']" />
				Add Tenant
			</button>
		</header>

		<div
			v-if="loading === true"
			class="absolute py-2 text-center text-white bg-blue-500 rounded w-60 bottom-5"
		>
			loading...
		</div>
		<div
			v-else
			class="h-full p-4 mb-10 overflow-hidden bg-white rounded shadow dark:divide-gray-900 dark:bg-gray-800"
			style="max-height: calc(100vh - 10rem)"
		>
			<!-- flexbox table -->
			<header
				class="sticky top-0 flex px-2 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase border-b backdrop-blur"
			>
				<div class="flex-grow-0 w-8 mr-4"></div>
				<div class="flex-grow-0 mr-4">id</div>

				<div class="flex-1">company name</div>
				<div class="flex-1">internal domain</div>
				<div class="flex-1">tenant id</div>
				<div class="flex-1" v-tooltip="'host_id'">supplier id</div>

				<div class="flex-1">modules</div>
				<div class="flex-1">created at</div>
				<div class="flex-1">updated at</div>
				<div class="flex-1">actions</div>
			</header>

			<div
				class="flex flex-wrap items-center px-2 py-2 transition-colors group hover:bg-gray-100 dark:hover:bg-gray-900"
				:class="{
					'bg-blue-100 text-blue-500': activeTenant.id === tenant.id,
				}"
				v-for="(tenant, i) in filtered_tenants"
				:key="`tenant_${i}`"
			>
				<!-- <pre>{{ tenant }}</pre> -->
				<div
					class="flex-grow-0 mr-4 font-mono text-sm font-bold text-gray-400"
				>
					<div
						class="w-8 h-8 overflow-hidden border border-gray-200 flex items-center bg-white p-1"
					>
						<img
							:src="tenant.logo"
							:alt="tenant.name + '_logo'"
							class="object-contain object-center"
						/>
					</div>
				</div>
				<div
					class="flex-grow-0 mr-4 font-mono text-sm font-bold text-gray-400"
				>
					#{{ tenant.id }}
				</div>

				<div class="flex-1 font-semibold">
					{{ tenant.company_name }}
				</div>
				<div class="flex-1 font-semibold">
					{{ tenant.fqdn }}
				</div>
				<div class="flex-1 font-mono text-xs text-gray-500">
					{{ tenant.tenant_id }}
				</div>
				<div class="flex-1 font-mono text-xs text-gray-500">
					{{ tenant.host_id }}
				</div>
				<div class="flex-1">
					<span class="text-green-500">
						{{ pickDuplicate(tenant.configure.namespaces).length }}
					</span>
					<span class="text-gray-500">/{{ apps.length }}</span>
					<!-- <div class="flex items-center">
						<div class="border rounded">
							<img src="" alt="module logo" />
							Full calculation
						</div>
						<div class="border rounded">
							<img src="" alt="module logo" />
							CMS
						</div>
						<div class="border rounded">
							<img src="" alt="module logo" />
							Plugins
						</div>
						<div class="border rounded">
							<img src="" alt="module logo" />
							Preflight
						</div>
						<div class="border rounded">
							<img src="" alt="module logo" />
							Chili
						</div>
					</div> -->
				</div>
				<div class="flex-1">
					<span class="mr-2 text-sm">
						<font-awesome-icon
							:icon="['fal', 'calendar']"
							class="fa-sm"
						/>
						{{ moment(tenant.created_at).format("DD-MM-YYYY") }}
					</span>
					<span
						class="p-1 font-mono text-sm text-gray-500 bg-gray-100 rounded group-hover:bg-white"
					>
						<font-awesome-icon :icon="['fal', 'clock']" class="fa-sm" />
						{{ moment(tenant.created_at).format("HH:MM") }}
					</span>
				</div>
				<div class="flex-1">
					<span class="mr-2 text-sm">
						<font-awesome-icon
							:icon="['fal', 'calendar']"
							class="text-sm"
						/>
						{{ moment(tenant.updated_at).format("DD-MM-YYYY") }}
					</span>
					<span
						class="p-1 font-mono text-sm text-gray-500 bg-gray-100 rounded group-hover:bg-white"
					>
						<font-awesome-icon :icon="['fal', 'clock']" class="fa-sm" />
						{{ moment(tenant.updated_at).format("HH:MM") }}
					</span>
				</div>
				<div class="flex items-center flex-1">
					<!-- <ItemMenu :menuItems="menuItems"></ItemMenu> -->
					<div
						class="flex items-center px-2 py-1 mr-6 space-x-1 bg-gray-100 rounded group-hover:bg-white"
					>
						<!-- <small class="text-gray-500">login</small> -->
						<small class="text-gray-500"
							><font-awesome-icon
								:icon="['fal', 'external-link']"
								class="mr-2"
							/>
						</small>
						<a
							class="px-2 py-1 text-sm text-white bg-blue-300 rounded-full hover:bg-blue-400"
							:href="`https://${tenant.fqdn}/`"
							target="_blank"
						>
							site
						</a>
						<a
							class="px-2 py-1 text-sm text-white bg-blue-400 rounded-full hover:bg-blue-500"
							:href="`https://${tenant.fqdn}/manager/login`"
							target="_blank"
						>
							manager
						</a>
					</div>
					<button
						class="px-2 py-1 text-sm text-white bg-blue-500 rounded-full hover:bg-blue-600"
						@click.stop.prevent="showDetails(tenant)"
					>
						details
					</button>
				</div>
				<!-- <div class="relative flex-1">
					{{ tenant.shared_categories.length }}
					<font-awesome-icon
						:icon="['fal', 'info']"
						@click="show_categories = tenant.id"
					/>

					<div
						class="absolute flex p-2 bg-blue-100 rounded"
						v-if="show_categories === tenant.id"
					>
						<button @click="show_categories = false">close</button>
						<div
							v-for="(category, index) in tenant.shared_categories"
							:key="`tenant_category_${index}`"
						>
							{{ category.name }}
						</div>
					</div>
				</div> -->

				<!-- <div class="flex-1 capitalize">
                    <button class="text-blue-500">
                        <font-awesome-icon :icon="['fal', 'cloud-download-alt']" />
                        Import assortment
                    </button>
                </div> -->
			</div>
		</div>
		<!-- {{ pagination }} -->
		<Pagination
			class="relative mx-auto mt-10"
			:pagination="pagination"
			@paginate="
				(loading = true),
					obtain_tenants({
						per_page: pagination.per_page,
						page: pagination.current_page,
					}).then(() => {
						loading = false;
					})
			"
		></Pagination>
		<transition name="slide">
			<TenantsEditPanel
				v-if="activeTenantDetail"
				:apps="apps"
			></TenantsEditPanel>
		</transition>
	</div>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import Pagination from "../global/Pagination.vue";
import TenantsEditPanel from "./TenantsEditPanel.vue";
import ItemMenu from "../global/ItemMenu.vue";
import moment from "moment";

export default {
	components: { Pagination, TenantsEditPanel, ItemMenu },
	data() {
		return {
			moment: moment,
			filter: "",
			show_categories: false,
			activeTenantDetail: false,
			menuItems: [
				{
					action: "edit",
					icon: "pencil",
					title: "Edit",
					classes: "text-blue-900",
					show: true,
				},

				{
					action: "delete",
					icon: "trash",
					title: "Delete",
					classes: "text-red-500",
					show: true,
				},
			],
			type: "producer",
			component: "",
			loading: false,
			apps: [],
		};
	},
	computed: {
		...mapState({
			tenants: (state) => state.tenants.tenants,
			pagination: (state) => state.pagination.pagination,
			activeTenant: (state) => state.tenants.activeTenant,
		}),
		filtered_tenants() {
			if (this.filter.length > 0) {
				return this.tenants.filter((tenant) => {
					return Object.values(tenant).some((val) => {
						if (val !== null) {
							return val
								.toString()
								.toLowerCase()
								.includes(this.filter.toLowerCase());
						}
					});
				});
			}
			return this.tenants;
		},
	},
	watch: {
		pagination(v) {
			return v;
		},
		activeTenantDetail(v) {
			return v;
		},
	},
	mounted() {
		this.loading = true;
		this.obtain_tenants({
			per_page: 20,
			page: 1,
		}).then((response) => {
			this.loading = false;
		});
		this.obtainApps();
	},
	methods: {
		...mapMutations({
			set_active_tenant: "tenants/set_active_tenant",
		}),
		...mapActions({
			obtain_tenants: "tenants/obtain_tenants",
		}),
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
		obtainApps() {
			axios
				.get("/apps")
				.then((response) => (this.apps = response.data.data))
				.catch((error) =>
					this.$store.dispatch("notification/handle_error", error)
				);
		},
		closeModal() {
			this.component = "";
		},
		showDetails(tenant) {
			this.set_active_tenant(tenant);
			this.activeTenantDetail = true;
		},
		/**
		 * This processes the menu clicks
		 * @param {String} event menu-item clicked
		 * @return {Function}  respective funtion gets executed
		 * @todo create functions for the current console.log's
		 */
		menuItemClicked(event, tenant) {
			switch (event) {
				case "edit":
					this.$store.commit("tenants/set_category_edit", true);
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

<style></style>
