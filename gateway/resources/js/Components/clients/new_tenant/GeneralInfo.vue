<template>
	<form class="flex flex-col flex-wrap">
		<header class="font-bold uppercase tracking wide">Owner</header>

		<section class="flex w-full pb-8 border-b">
			<fieldset class="w-1/3 mt-4 mr-1">
				<label
					for="gender"
					class="text-xs font-bold tracking-wide uppercase text-gray-500"
				>
					Salutation
				</label>

				<div class="flex items-center mt-2">
					<input
						type="radio"
						id="male"
						name="gender"
						value="male"
						v-model="gender"
					/>
					<label
						for="male"
						class="ml-1 mr-2 text-sm"
						:class="{
							'font-bold text-theme-500': gender === 'male',
						}"
					>
						Mr.
					</label>
					<input
						type="radio"
						id="female"
						name="gender"
						value="female"
						v-model="gender"
					/>
					<label
						for="female"
						class="ml-1 mr-2 text-sm"
						:class="{
							'font-bold text-theme-500': gender === 'female',
						}"
					>
						Ms.
					</label>
					<input
						type="radio"
						id="other"
						name="gender"
						value="other"
						v-model="gender"
					/>
					<label
						for="other"
						class="ml-1 mr-2 text-sm"
						:class="{
							'font-bold text-theme-500': gender === 'other',
						}"
					>
						Other
					</label>
				</div>
			</fieldset>
			<fieldset class="w-1/3 mt-4 mr-1">
				<label
					class="text-xs font-bold tracking-wide uppercase text-gray-500"
				>
					<font-awesome-icon :icon="['fal', 'user']" />
					<font-awesome-icon :icon="['fal', 'tag']" class="mr-1" />
					First name:
				</label>
				<input
					type="text"
					v-model="first_name"
					ref="newtenantinput"
					placeholder="John"
					class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
				/>
			</fieldset>
			<fieldset class="w-1/3 mt-4 ml-1">
				<label
					class="text-xs font-bold tracking-wide uppercase text-gray-500"
				>
					<font-awesome-icon :icon="['fal', 'user']" />
					<font-awesome-icon :icon="['fal', 'tag']" class="mr-1" />
					Last name:
				</label>
				<input
					type="text"
					v-model="last_name"
					ref="newtenantinput"
					placeholder="Doe"
					class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
				/>
			</fieldset>
		</section>

		<header class="font-bold uppercase tracking wide mt-8">System</header>

		<fieldset class="mt-4">
			<label class="text-xs font-bold tracking-wide uppercase text-gray-500">
				<font-awesome-icon :icon="['fal', 'globe']" class="mr-1" />
				Url prefix:
			</label>
			<div class="flex mt-1 rounded-md">
				<span
					class="inline-flex items-center px-3 text-sm text-gray-500 border border-r-0 border-gray-300 rounded-l-md bg-gray-50 dark:bg-gray-900 dark:border-black"
				>
					https://
				</span>
				<input
					type="text"
					v-model="fqdn"
					ref="newtenantinput"
					placeholder="johndoe"
					class="w-full px-2 py-1 bg-white border border-blue-400 dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
				/>
				<span
					class="inline-flex items-center px-3 text-sm text-gray-500 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 dark:bg-gray-900 dark:border-black"
				>
					.prindustry.com
				</span>
			</div>
		</fieldset>

		<!-- <fieldset class="mt-4">
            <label class="text-xs font-bold tracking-wide uppercase">
                <font-awesome-icon :icon="['fal', 'user-visor']" class="mr-1" />
                Username:
            </label>
            <input
                type="text"
                v-model="username"
                ref="newtenantinput"
                placeholder="Johnny"
                class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            />
        </fieldset> -->

		<section class="flex my-2">
			<fieldset class="mt-4 mr-1">
				<label
					class="text-xs font-bold tracking-wide uppercase text-gray-500"
				>
					<font-awesome-icon :icon="['fal', 'at']" class="mr-1" />
					Email:
				</label>
				<input
					type="text"
					v-model="email"
					ref="newtenantinput"
					placeholder="john.doe@somesite.com"
					class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
				/>
			</fieldset>

			<fieldset class="mt-4 ml-1">
				<label
					class="text-xs font-bold tracking-wide uppercase text-gray-500"
				>
					<font-awesome-icon :icon="['fal', 'lock']" class="mr-1" />
					Password:
				</label>
				<input
					type="text"
					v-model="password"
					placeholder="********"
					class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
				/>
			</fieldset>
		</section>

		<fieldset class="mt-4">
			<label class="text-xs font-bold tracking-wide uppercase text-gray-500">
				<font-awesome-icon :icon="['fal', 'image-polaroid']" class="mr-1" />
				Logo:
			</label>
			<input
				type="file"
				@change="onFileChange($event)"
				class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
			/>
		</fieldset>

		<footer class="flex justify-center my-8">
			<button
				v-if="!loading"
				class="px-4 py-1 mt-4 text-lg text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
				@click.prevent="createTenant()"
			>
				<font-awesome-icon :icon="['fal', 'plus']" />
				Create new tenant
			</button>
			<div v-else>
				<font-awesome-layers class="fa-4x">
					<font-awesome-icon
						:icon="['fal', 'computer-classic']"
						class="text-blue-500"
					/>
					<font-awesome-icon
						:icon="['fal', 'cog']"
						spin
						transform="shrink-10"
						class="text-black"
					/>
				</font-awesome-layers>
			</div>
		</footer>
	</form>
</template>

<script>
export default {
	data() {
		return {
			first_name: "",
			last_name: "",
			fqdn: "",
			gender: "",
			username: "",
			email: "",
			password: "",
			files: "",
			loading: false,
		};
	},
	methods: {
		onFileChange(event) {
			this.files = event.target.files || event.dataTransfer.files;
			if (!this.files.length) {
			}
		},
		async createTenant() {
			this.loading = true;
			const data = new FormData();

			// add file
			data.append("gender", this.gender);
			data.append("first_name", this.first_name);
			data.append("last_name", this.last_name);
			data.append("fqdn", this.fqdn);
			data.append("username", this.username);
			data.append("email", this.email);
			data.append("password", this.password);
			data.append("logo", this.files[0]);

			const config = {
				headers: {
					"content-type": "multipart/form-data",
				},
			};

			await axios
				.post(`clients`, data, config)
				.then((response) => {
					this.loading = false;
					this.$parent.component = "TenantsTable";
				})
				.catch((error) => {
					this.set_notification({
						text: error.response.message,
						status: "red",
					});
					this.loading = false;
				});
		},
	},
};
</script>

<style>
</style>
