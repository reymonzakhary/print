<template>
  <div class="flex">
    <!-- <div class="w-1/2 p-4" v-if="companies.length > 0">
         <h4 class="block py-2 tracking-wide">Select an existing company</h4>
			<label
				for="company"
				class="block text-xs font-bold tracking-wide uppercase"
			>
				Select your company
			</label>

			<select
				v-model="company"
				class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
			>
				<option v-for="company in companies">{{ company }}</option>
			</select>
		</div> -->

    <section class="w-1/2 p-4 mx-auto">
      <!-- <h4 class="block py-2 mb-2 tracking-wide">Create a new company</h4> -->

      <!-- NAME -->
      <span class="flex items-center justify-between">
        <label
          for="company"
          class="block text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("what company") }}
        </label>
        <span class="ml-2 text-xs text-orange-500">{{ $t("required") }}</span>
      </span>

      <div class="relative flex items-center pb-4">
        <input
          v-model="newCompany.name"
          type="text"
          class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        />
        <font-awesome-icon
          class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
          :icon="['fal', 'building']"
        />
      </div>

      <!-- DESCRIPTION -->
      <div class="py-4">
        <span class="flex items-center justify-between">
          <label
            for="description"
            class="block text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("company") }} {{ $t("description") }}
          </label>
          <span class="ml-2 text-xs text-gray-500">
            {{ $t("optional") }}
          </span>
        </span>

        <textarea
          v-model="newCompany.description"
          name="description"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        ></textarea>
      </div>

      <!-- COC -->
      <span class="flex items-center justify-between">
        <label
          for="company"
          class="block text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("chamber of commerce") }}
        </label>
        <span class="ml-2 text-xs text-gray-500">{{ $t("optional") }}</span>
      </span>
      <div class="relative pb-4">
        <input
          v-model="newCompany.coc"
          type="text"
          class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        />
        <font-awesome-icon
          class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
          :icon="['fal', 'cash-register']"
        />
      </div>

      <!-- TAX NR -->
      <span class="flex items-center justify-between mt-4">
        <label
          for="company"
          class="block text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("tax") }} {{ $t("number") }}
        </label>
        <span class="ml-2 text-xs text-gray-500">{{ $t("optional") }}</span>
      </span>

      <div class="relative pb-4">
        <input
          v-model="newCompany.tax_nr"
          type="text"
          class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        />
        <font-awesome-icon
          class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
          :icon="['fal', 'money-bill-wave']"
        />
      </div>

      <!-- URL -->
      <span class="flex items-center justify-between mt-4">
        <label
          for="company"
          class="block text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("website") }}
        </label>
        <span class="ml-2 text-xs text-gray-500">{{ $t("optional") }}</span>
      </span>

      <div class="relative pb-4">
        <input
          v-model="newCompany.url"
          type="text"
          class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        />
        <font-awesome-icon
          class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
          :icon="['fal', 'earth-europe']"
        />
      </div>

      <!-- EMAIL -->
      <span class="flex items-center justify-between mt-4">
        <label
          for="company"
          class="block text-xs font-bold tracking-wide uppercase"
        >
          {{ $t("email") }}
        </label>
        <span class="ml-2 text-xs text-gray-500">{{ $t("optional") }}</span>
      </span>

      <div class="relative pb-4">
        <input
          v-model="newCompany.email"
          type="text"
          class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
          @blur="$store.commit('users/set_company', newCompany)"
        />
        <font-awesome-icon
          class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
          :icon="['fal', 'envelope']"
        />
      </div>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    user_id: Number,
  },
  setup() {
	const api = useAPI();
	return { api };
  },
  data() {
    return {
      companies: {},
      newCompany: {
        name: "",
        description: "",
      },
    };
  },
  mounted() {
    this.getCompanies();
  },
  methods: {
    getCompanies() {
      // get companies
      this.api.get(`users/${this.user_id}/companies`).then((response) => {
        this.companies = response.data;
      });
    },
  },
};
</script>
