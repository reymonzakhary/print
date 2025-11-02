<template>
	<form class="flex flex-wrap">
		<div class="w-full pb-8 px-4 mb-8 border-b">
			<header class="font-bold uppercase tracking wide">General info</header>
			<!-- <fieldset class="w-1/2 mt-4">
		   	<label class="text-xs font-bold tracking-wide uppercase text-gray-500">
		   		<font-awesome-icon :icon="['fal', 'user']" />
		   		<font-awesome-icon :icon="['fal', 'tag']" class="mr-1" />
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
			<section class="flex w-1/2">
				<!-- <div class="flex w-1/2"> -->
				<fieldset class="w-1/3 pr-1 mt-4">
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
				<fieldset class="w-1/3 pr-1 mt-4">
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
				<fieldset class="w-1/3 pl-1 mt-4">
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
				<!-- </div> -->
			</section>
			<section class="w-1/2">
				<!-- <div class="flex w-1/2"> -->
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
				<!-- </div> -->
			</section>
		</div>

		<div class="w-full md:w-1/2 pb-8 px-4 mb-8 border-b">
			<header class="w-full font-bold uppercase tracking wide">
				Company info
			</header>
			<section class="">
				<!-- <div class="w-1/2"> -->
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'building']" class="mr-1" />
						Company name:
					</label>
					<input
						type="text"
						v-model="companyName"
						ref="newtenantinput"
						placeholder="My company"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'building']" class="mr-1" />
						Company description:
					</label>
					<input
						type="text"
						v-model="companyDescription"
						ref="newtenantinput"
						placeholder="My company description"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon
							:icon="['fal', 'money-check-dollar-pen']"
							class="mr-1"
						/>
						Tax nr:
					</label>
					<input
						type="text"
						v-model="companyTaxNr"
						ref="newtenantinput"
						placeholder="########"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon
							:icon="['fal', 'user-visor']"
							class="mr-1"
						/>
						Chamber of commerce:
					</label>
					<input
						type="text"
						v-model="companyCOC"
						ref="newtenantinput"
						placeholder="sun microsystems"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'globe']" class="mr-1" />
						callback :
					</label>
					<input
						type="text"
						v-model="companyUrl"
						ref="newtenantinput"
						placeholder="sun microsystems"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="p-4 mt-4 border rounded">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'shield']" class="mr-1" />
						Authorization:
					</label>
					<select
						name="authorization"
						ref="newtenantinput"
						v-model="authorization"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					>
						<option value=""></option>
						<option value="Bearer">Bearer Token</option>
						<option value="password">Password</option>
					</select>
					<div class="mt-4" v-if="authorization === 'Bearer'">
						<label
							class="text-xs font-bold tracking-wide uppercase text-gray-500"
						>
							<font-awesome-icon
								:icon="['fal', 'id-badge']"
								class="mr-1"
							/>
							token
						</label>
						<input
							type="text"
							v-model="authToken"
							ref="newtenantinput"
							placeholder="lkjsadfbsakjdgsfdasgfwaestrsajawsbpoadgjhaasdgf"
							class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
						/>
					</div>
					<div class="mt-4" v-if="authorization === 'password'">
						<label
							class="mt-4 text-xs font-bold tracking-wide uppercase text-gray-500"
						>
							<font-awesome-icon
								:icon="['fal', 'user-visor']"
								class="mr-1"
							/>
							auth username
						</label>
						<input
							type="text"
							v-model="authUsername"
							ref="newtenantinput"
							placeholder="company_request_username"
							class="w-full px-2 py-1 mb-4 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
						/>
						<label
							class="mt-4 text-xs font-bold tracking-wide uppercase text-gray-500"
						>
							<font-awesome-icon
								:icon="['fal', 'user-visor']"
								class="mr-1"
							/>
							auth password
						</label>
						<input
							type="password"
							v-model="authPassword"
							ref="newtenantinput"
							placeholder="password"
							class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
						/>
					</div>
				</fieldset>
				<!-- </div> -->
			</section>
		</div>

		<div class="w-full md:w-1/2 pb-8 px-4 mb-8 border-b">
			<header class="w-full font-bold uppercase tracking wide">
				Company address
			</header>
			<section class="">
				<!-- <div class="w-1/2"> -->
				<!-- COUNTRY -->
				<fieldset class="mt-4" v-if="countries.length > 1">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
						for="country"
					>
						<font-awesome-icon :icon="['fal', 'globe']" class="mr-1" />
						Country
					</label>
					<select
						name="countries"
						id="countries"
						v-model="addressCountry"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					>
						<option disabled value="">Select option</option>
						<option
							v-for="country in countries"
							:value="country.id"
							:key="country.id"
						>
							{{ country.name }}
						</option>
					</select>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'building']" class="mr-1" />
						Street:
					</label>
					<input
						type="text"
						v-model="addressStreet"
						ref="newtenantinput"
						placeholder="My company"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'building']" class="mr-1" />
						Nr.:
					</label>
					<input
						type="text"
						v-model="addressNumber"
						ref="newtenantinput"
						placeholder="My company description"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon :icon="['fal', 'building']" class="mr-1" />
						Zipcode:
					</label>
					<input
						type="text"
						v-model="addressZipcode"
						ref="newtenantinput"
						placeholder="########"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<fieldset class="mt-4">
					<label
						class="text-xs font-bold tracking-wide uppercase text-gray-500"
					>
						<font-awesome-icon
							:icon="['fal', 'user-visor']"
							class="mr-1"
						/>
						City:
					</label>
					<input
						type="text"
						v-model="addressCity"
						ref="newtenantinput"
						placeholder="sun microsystems"
						class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
					/>
				</fieldset>
				<!-- </div> -->
			</section>
		</div>

		<footer class="flex justify-center my-4 w-full">
			<button
				v-if="!loading"
				class="px-4 py-1 mt-4 text-lg text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
				@click.prevent="createUser()"
			>
				<font-awesome-icon :icon="['fal', 'plus']" />
				Create new user
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
			// general info
			gender: "",
			first_name: "",
			last_name: "",
			username: "",
			email: "",
			password: "",

			// Company info
			companyName: "",
			companyDescription: "",
			companyTaxNr: "",
			companyUrl: "",
			companyCOC: "",

			// Company address
			addressCountry: "",
			addressStreet: "",
			addressNumber: "",
			addressZipcode: "",
			addressCity: "",

			// Connection info
			authorization: "",
			authToken: "",
			authUsername: "",
			authPassword: "",

			// component
			countries: [],
			loading: false,
		};
	},
	mounted() {
		axios.get("/countries").then((response) => {
			this.countries = response.data.data;
			if (this.countries.length > 0) {
				this.addressCountry = this.countries[141].id;
			}
		});
	},
	methods: {
		onFileChange(event) {
			this.files = event.target.files || event.dataTransfer.files;
			if (!this.files.length) {
			}
		},
		async createUser() {
			// this.loading = true;
			const data = new FormData();

			// add file
			data.append("gender", this.gender);
			data.append("first_name", this.first_name);
			data.append("last_name", this.last_name);
			data.append("username", this.username);
			data.append("email", this.email);
			data.append("password", this.password);

			data.append("company_name", this.companyName);
			data.append("description", this.companyDescription);
			data.append("tax_nr", this.companyTaxNr);
			data.append("url", this.companyUrl);
			data.append("coc", this.companyCOC);

			data.append("country_id", this.addressCountry);
			data.append("address", this.addressStreet);
			data.append("number", this.addressNumber);
			data.append("zip_code", this.addressZipcode);
			data.append("city", this.addressCity);

			data.append("authorization", this.authorization);
			data.append("authToken", this.authToken);
			data.append("authUsername", this.authUsername);
			data.append("authPassword", this.authPassword);

			const config = {
				headers: {
					"content-type": "multipart/form-data",
				},
			};

			await axios
				.post(`users`, data, config)
				.then((response) => {
					this.loading = false;
					this.$parent.$parent.component = "usersTable";
					this.set_notification({
						text: response.message,
						status: "red",
					});
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
