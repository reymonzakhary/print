<template>
  <div class="h-full">
    <AddCustomProductHeader :step="1" to_component="AddProductOverview" />

    <transition name="fade">
      <SidePanel v-if="edit_cat" @on-close="toggle_edit_cat(false)">
        <template #side-panel-header>
          <h2 class="p-4 font-bold uppercase tracking-wide text-theme-900">
            <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" />
            <span class="text-gray-500">Edit</span>
          </h2>
        </template>

        <template #side-panel-content>
          <CustomProductCategoryForm :category="active_custom_category" :edit="true" />
        </template>
      </SidePanel>
    </transition>

    <div class="mx-auto w-full p-4">
      <div class="mt-8 flex flex-col content-center md:mt-0">
        <section class="mx-auto mt-2 md:w-1/2">
          <div class="flex items-center">
            <p class="flex text-sm font-bold uppercase tracking-wide">
              {{ $t("Select custom category") }}
            </p>
            <button class="ml-auto flex text-theme-500" @click.prevent="newCat = !newCat">
              {{ !newCat ? "+ create new" : "x close" }}
            </button>
          </div>

          <div v-if="categories && categories.length > 0 && !newCat" class="flex flex-col">
            <input
              v-model="filter"
              type="text"
              class="input mx-auto my-4 border-2 border-theme-400 p-2 text-sm text-theme-900 shadow-lg shadow-theme-100 dark:text-white"
              placeholder="Search category"
            />

            <template v-if="!newCat">
              <CustomProductCategory
                v-for="cat in filtered_categories"
                :key="cat.id"
                :cat="cat"
                :i="0"
                class="w-full"
              />
            </template>
          </div>
          <div v-else-if="categories && categories.length === 0" class="italic text-gray-500">
            {{ $t("no custom categories created yet.") }}
          </div>
        </section>

        <section v-if="newCat || (categories && categories.length == 0)" class="mx-auto">
          <p class="mt-8 text-sm font-bold uppercase tracking-wide">
            {{ $t("create new custom category") }}
          </p>
          <CustomProductCategoryForm />
        </section>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapActions, mapState } from "vuex";

export default {
  data() {
    return {
      newCat: false,
      filter: "",
    };
  },
  computed: {
    ...mapState({
      categories: (state) => state.product.custom_categories,
      active_custom_category: (state) => state.product.active_custom_category,
      edit_cat: (state) => state.product.edit_cat,
    }),

    filtered_categories() {
      if (this.filter.length > 0) {
        return this.categories.filter((cat) => {
          return Object.values(cat).some((val) => {
            if (val !== null) {
              return val.toString().toLowerCase().includes(this.filter.toLowerCase());
            }
          });
        });
      }
      return this.categories;
    },
  },
  created() {
    this.get_custom_categories();
  },
  methods: {
    ...mapActions({
      get_custom_categories: "product/get_custom_categories",
    }),
    ...mapMutations({
      set_active_custom_category: "product/set_active_custom_category",
      set_component: "product_wizard/set_wizard_component",
      toggle_edit_cat: "product/toggle_edit_cat",
    }),
    close() {
      this.toggle_edit_cat(false);
    },
  },
};
</script>
