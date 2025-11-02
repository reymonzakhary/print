<template>
  <div>
    <div class="filters divide-y">
      <section class="-ml-2 py-4 pb-1">
        <label class="text-xs font-bold uppercase tracking-wide">
          <font-awesome-icon :icon="['fal', 'star']" class="ml-2" />
          {{ $t("rating") }}
        </label>
        <UIFiltersStars class="my-2" description @on-star-select="" />
      </section>

      <section class="py-4">
        <label class="text-xs font-bold uppercase tracking-wide">
          <font-awesome-icon :icon="['fal', 'earth-europe']" class="" />
          {{ $t("delivering product in") }}
        </label>
        <UIVSelect
          v-model="state.operatingCountries"
          :options="['Canada', 'United States']"
          clearable
          multiple
          placeholder="All"
          class="my-2 w-full lg:w-full"
        />
      </section>

      <section class="py-4">
        <label class="text-xs font-bold uppercase tracking-wide">
          <font-awesome-icon :icon="['fal', 'tag']" class="" />
          {{ $t("tags") }}
        </label>
        <UIVSelect
          v-model="state.tags"
          multiple
          :options="[
            'books',
            'clothing',
            'electronics',
            'food',
            'furniture',
            'health',
            'jewelry',
            'toys',
          ]"
          clearable
          placeholder="All"
          class="my-2 w-full lg:w-full"
        />
      </section>

      <!-- <div>
        <label for="category" class="block text-sm font-medium text-gray-700">Category:</label>
        <select
          id="category"
          v-model="state.category"
          class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
          <option value="">All</option>
          <option value="fruits">Fruits</option>
          <option value="vegetables">Vegetables</option>
          <option value="dairy">Dairy</option>
        </select>
      </div>

      <div>
        <label for="priceRange" class="block text-sm font-medium text-gray-700">Price Range:</label>
        <select
          id="priceRange"
          v-model="state.priceRange"
          class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
          <option value="">All</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <div>
        <label for="availability" class="block text-sm font-medium text-gray-700"
          >Availability:</label
        >
        <select
          id="availability"
          v-model="state.availability"
          class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
          <option value="">All</option>
          <option value="inStock">In Stock</option>
          <option value="outOfStock">Out of Stock</option>
        </select>
      </div>

      <button
        @click="applyFilters"
        class="inline-flex justify-center w-full px-4 py-2 mt-4 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Apply Filters
      </button> -->
    </div>
  </div>
</template>

<script setup>
// Define your reactive state
const state = reactive({
  operatingCountries: [],
  tags: [],
  // Your state properties here
});
</script>

<style scoped>
/* Your styles here */
</style>
