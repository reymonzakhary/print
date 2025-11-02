<template>
  <SidePanel
    transition="slideleftlarge-fast"
    width="w-full md:w-1/2"
    :full-height="true"
    @on-close="close()"
  >
    <template #side-panel-header>
      <h2
        class="fixed top-0 z-20 mr-2 flex w-full items-center justify-between rounded-tl bg-white/80 px-4 py-2 uppercase tracking-wide text-theme-900 shadow backdrop-blur-md dark:bg-gray-700/80 dark:text-theme-200 md:w-1/2"
      >
        <!-- <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" />
        <span class="text-gray-500">Edit</span>
        {{ $display_name(item.display_name) }}
        <font-awesome-icon
          :icon="['fad', 'circle-xmark']"
          class="z-20 mr-2 rounded-full cursor-pointer hover:text-black"
          @click="close()"
        /> -->
        <div class="flex w-full items-center">
          <!-- <button
            v-if="flag === 'from_boops'"
            class="mr-4 flex items-center text-theme-500"
            @click="navigateTo('/assortment')"
          >
            <font-awesome-icon :icon="['fal', 'angle-left']" class="mr-2" />
            {{ $t("back to assortment") }}
            <font-awesome-icon :icon="['fal', 'box-full']" class="ml-2" />
          </button> -->

          <div class="mx-auto flex items-center">
            <font-awesome-icon :icon="['fal', 'box-full']" />
            <font-awesome-icon :icon="['fal', 'tag']" class="mr-1" />
            {{ $display_name(selected_category.display_name) }}

            <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-4" />
            <span class="font-bold">
              <font-awesome-icon :icon="['fal', 'box-open']" class="mr-1" />
              {{ $display_name(item.display_name) }}
            </span>
            <div v-if="Object.keys(selected_category).length > 0">
              <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-4" />
              <span class="normal-case">
                <font-awesome-icon :icon="['fal', 'calculator']" class="mr-1" />
                {{
                  selected_category.price_build?.full_calculation === true
                    ? $t("full calculation")
                    : $t("semi calculation")
                }}
              </span>
            </div>
          </div>
        </div>

        <font-awesome-icon
          :icon="['fad', 'circle-xmark']"
          class="z-20 mr-2 cursor-pointer rounded-full hover:text-black"
          @click="close()"
        />
      </h2>
    </template>

    <template #side-panel-content>
      <div class="z-10 mt-8 bg-gray-200 px-4 py-8 dark:border-gray-900 dark:bg-gray-800">
        <section class="mx-auto w-full md:w-2/3">
          <div class="text-sm font-bold uppercase tracking-widest">
            {{ $t("Edit fields") }}
          </div>
          <BoxesEditForm
            :item="item"
            type="box"
            class="mt-2 rounded bg-white p-4 shadow-md dark:bg-gray-700"
          />
        </section>
      </div>
      <div
        class="sticky bottom-0 top-8 flex border-t bg-white/80 p-4 shadow-md backdrop-blur-md dark:border-gray-900 dark:bg-gray-700/80 dark:text-theme-200"
      >
        <button
          class="mx-auto rounded-full bg-green-500 px-4 py-2 text-white transition-colors duration-75 hover:bg-green-600"
          @click="saveItem()"
        >
          {{ $t("save") }}
        </button>
      </div>
    </template>
  </SidePanel>
</template>

<script>
import {mapMutations, mapState} from "vuex";
export default {
  name: "EditRole",
  emits: ["on-close", "on-updated"],
  setup() {
    const api = useAPI();
    const boopsStore = useBoxesAndOptions();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, boopsStore };
  },
  computed: {
    ...mapState({
      item: (state) => state.assortmentsettings.item,
      flag: (state) => state.assortmentsettings.flag,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
  },
  methods: {
    ...mapMutations({
      set_selected_boops: "product_wizard/set_selected_boops",
    }),
    close() {
      this.$emit("on-close");
    },
    saveItem() {
      this.item["category_id"] = this.selected_category.id;
      this.api
        .put(`boxes/${this.item.slug}`, this.item)
        .then(async (response) => {
          this.handleSuccess(response);
          this.$emit("on-updated", response.data);
          this.boopsStore.getBoxes();
          await this.getCategory(this.selected_category.slug);
          this.close();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    getCategory(slug) {
      this.api
        .get(`categories/${slug}`)
        .then((response) => {
          const category = response.data;
          this.set_selected_boops(category.boops[0]?.boops ?? []);
          this.$forceUpdate();
        })
        .catch((error) => this.handleError(error));
    },
  },
};
</script>
