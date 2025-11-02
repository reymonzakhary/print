<template>
	<div :class="darkModeClass">
		<div class="fixed top-0 flex w-screen text-white bg-blue-500">
			<nav
				class="flex items-center justify-between w-screen h-12 px-2 md:w-screen"
			>
				<div class="flex items-center">
					<img
						src="images/Prindustry_logo_wit.png"
						alt="Prindustry Logo"
						id="prindustry-logo"
						class="h-8 mr-1 max-w-none"
					/>
					<span class="mt-2 text-sm uppercase">Admin Manager</span>
				</div>

				<ul class="flex items-center justify-around">
					<!-- <li class="flex items-center">
						<small>darkmode</small>
						<div
							@click="toggleDarkMode"
							class="flex items-center mr-4 cursor-pointer focus:outline-none"
						>
							<i :class="darkswitch"></i>
						</div>
					</li> -->

					<li
						class="flex items-center justify-center border-r border-blue-700 md:ml-8"
					>
						<small class="mr-2">darkmode</small>
						<div class="flex items-center pr-4">
							<font-awesome-icon
								:icon="['fad', 'sun']"
								class="fa-lg"
								:class="{ 'text-yellow-500': !checked }"
							/>
							<div
								class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
								:class="[checked ? 'bg-green-400' : 'bg-gray-300']"
							>
								<label
									for="toggle"
									class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
									:class="[
										checked
											? 'translate-x-6 border-green-500'
											: 'translate-x-0 border-gray-300',
									]"
								></label>
								<input
									type="checkbox"
									id="toggle"
									name="toggle"
									class="w-full h-full appearance-none active:outline-none focus:outline-none"
									v-model="checked"
									@change="check($event)"
								/>
							</div>
							<font-awesome-icon
								:icon="['fad', 'moon-stars']"
								class="fa-lg"
								:class="{ 'text-white': checked }"
							/>
						</div>
					</li>

					<li class="flex items-center md:ml-8">
						<a class="mr-3 font-semibold">
							{{ $page.props.auth.username }}
						</a>
						<a
							class="px-2 text-sm text-red-500 bg-red-100 rounded-full hover:bg-red-200 hover:text-red-600"
							href=""
							@click.prevent="logout()"
						>
							Logout
						</a>
					</li>
				</ul>
			</nav>
		</div>

		<div class="grid h-screen grid-cols-12 pt-12 bg-blue-500">
			<!-- sidebar -->
			<div
				class="hidden mx-2 overflow-y-auto text-center lg:grid lg:col-span-1"
				id="sidebar"
			>
				<!-- Tablet / desktop page navigation -->
				<nav class="flex flex-col space-y-4 text-white">
					<a
						class="flex flex-col items-center p-2 transition rounded-md hover:bg-blue-200 dark:hover:bg-blue-700"
						:class="{
							'bg-blue-100 dark:bg-blue-900 shadow-md shadow-blue-600 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 font-bold dark:text-blue-300':
								currentUrl.pathname === '/',
						}"
						href="/"
					>
						<i
							class="my-2 fa-tachometer-fast fa-2x"
							:class="currentUrl.pathname === '/' ? 'fad' : 'fal'"
						></i>
						Dashboard
					</a>

					<a
						class="flex flex-col items-center p-2 transition rounded-md hover:bg-blue-200 dark:hover:bg-blue-700"
						:class="{
							'bg-blue-100 dark:bg-blue-900 shadow-md shadow-blue-600 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 font-bold dark:text-blue-300':
								currentUrl.pathname === '/tenants',
						}"
						href="/tenants"
					>
						<i
							class="my-2 fa-users-crown fa-2x"
							:class="currentUrl.pathname === '/tenants' ? 'fad' : 'fal'"
						></i>
						Tenants
					</a>

					<a
						class="flex flex-col items-center p-2 transition rounded-md hover:bg-blue-200 dark:hover:bg-blue-700"
						:class="{
							'bg-blue-100 dark:bg-blue-900 shadow-md shadow-blue-600 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 font-bold dark:text-blue-300':
								currentUrl.pathname === '/users',
						}"
						href="/users"
					>
						<i
							class="my-2 fa-users fa-2x"
							:class="currentUrl.pathname === '/users' ? 'fad' : 'fal'"
						></i>
						Users
					</a>

					<!-- <a
						class="flex flex-col items-center p-2 transition rounded-md hover:bg-blue-200 dark:hover:bg-blue-700"
						:class="{
							'bg-blue-100 dark:bg-blue-900 shadow-md shadow-blue-600 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 font-bold dark:text-blue-300':
								currentUrl.pathname === '/contracts',
						}"
						href="/contracts"
					>
						<i
							class="my-2 fa-file-contract fa-2x"
							:class="
								currentUrl.pathname === '/contracts' ? 'fad' : 'fal'
							"
						></i>
						Contracts
					</a> -->

					<a
						class="flex flex-col items-center p-2 transition rounded-md hover:bg-blue-200 dark:hover:bg-blue-700"
						:class="{
							'bg-blue-100 dark:bg-blue-900 shadow-md shadow-blue-600 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 font-bold dark:text-blue-300':
								currentUrl.pathname === '/standardization',
						}"
						href="/standardization"
					>
						<i
							class="my-2 fa-digging fa-2x"
							:class="
								currentUrl.pathname === '/standardization'
									? 'fad'
									: 'fal'
							"
						></i>
						Standardisation
					</a>
				</nav>
			</div>

			<!-- content area -->
			<main
				style="max-height: calc(100vh - 3rem)"
				class="col-span-10 bg-gray-100 rounded-t-lg shadow-md dark:bg-gray-900 dark:text-white lg:col-span-11 md:rounded-tr-none md:rounded-bl-lg"
				id="content-area"
			>
				<slot />

				<Notification />
			</main>
		</div>
	</div>
</template>

<script>
export default {
	name: "Authenticated",
	data() {
		return {
			darkMode: false,
			darkModeAuto: false,
			darkModeClass: "text-blue-900 dark:text-gray-100 dark:bg-black",
			checked: false,
		};
	},
	created() {
		this.currentUrl = new URL(window.location.href);
	},
	mounted() {
		this.setStates();
	},
	watch: {
		darkMode(v) {
			console.log("darkMode value: " + v);
			if (v) {
				this.darkModeClass =
					"text-blue-900 dark:text-gray-100 dark:bg-black dark";
			} else {
				this.darkModeClass =
					"text-blue-900 dark:text-gray-100 dark:bg-black";
			}
		},
	},
	methods: {
		setStates() {
			console.log(
				"LS auto theme " + window.localStorage.getItem("auto_theme")
			);
			console.log(
				"LS active theme " + window.localStorage.getItem("active_theme")
			);
			if (
				window.localStorage.getItem("auto_theme") &&
				window.localStorage.getItem("active_theme")
			) {
				// active theme
				if (window.localStorage.getItem("active_theme") === "dark") {
					console.log("dark");
					this.swapColorScheme("dark");
				} else {
					console.log("light");
					this.swapColorScheme("light");
				}
			}
		},

		check(event) {
			if (event.target.checked) {
				this.swapColorScheme("dark");
			} else {
				this.swapColorScheme("light");
			}
		},

		swapColorScheme(scheme) {
			// this.cur = scheme
			if (scheme === "dark") {
				this.darkMode = true;
				this.checked = true;
			} else {
				this.darkMode = false;
				this.checked = false;
			}
			window.localStorage.setItem("active_theme", scheme);
		},

		logout() {
			axios
				.post(this.$page.props.logoutPage, {
					_token: this.$page.props.csrf_token,
				})
				.then((res) => {
					if (res.status === 204) {
						window.location.href = "/login";
					}
				})
				.catch((e) => {
					this.errors = e.response.data.errors;
				});
		},
	},
};
</script>

<style scoped>
</style>
