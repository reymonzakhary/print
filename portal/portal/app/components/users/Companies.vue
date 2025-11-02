<template >
   <div
      class="w-full p-4 mb-2 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:border-black dark:bg-gray-700"
      v-if="user.companies.length > 0 && permissions.includes('read-companies')"
   >
      <p class="font-bold">
         <font-awesome-icon :icon="['fal', 'building']" /> {{ $t("company") }}
      </p>

      <div
         v-for="(company, i) in user.companies"
         :key="`comapny_${i}`"
         class="mt-2"
      >
         <p>{{ company.name }}</p>
         <p class="text-sm text-gray-600">{{ company.description }}</p>
         <p>{{ company.coc }}</p>
         <p>{{ company.tax_nr }}</p>
         <p v-if="company.url">
            <font-awesome-icon :icon="['fal', 'earth-europe']" class="mr-1 fa-sm" />
            <a
               :href="company.url"
               target="_blank"
               class="text-sm text-theme-500"
               >{{ company.url }}
            </a>
         </p>
         <p v-if="company.email">
            <font-awesome-icon
               :icon="['fal', 'envelope']"
               class="mr-1 fa-sm"
            /><a :href="company.email" target="_blank">{{ company.email }}</a>
         </p>
      </div>
   </div>
</template>

<script>
import { mapState } from "vuex";

export default {
   name: "companies",
   data() {
      return {
         companies: [],
      };
   },
   computed: {
      ...mapState({
         user: (state) => state.users.selected_user,
      }),
   },
};
</script>

<style>
</style>