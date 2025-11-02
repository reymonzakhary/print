<template>
  <SidePanel
    :width="showRuns ? 'w-full md:w-11/12' : 'w-full md:w-1/3'"
    transition="slideleftlarge-fast"
    :full-height="true"
    @on-close="close()"
  >
    <template #side-panel-header>
      <h2
        class="fixed top-0 z-20 mr-2 flex w-full items-center justify-between rounded-tl bg-white/80 px-4 py-2 uppercase tracking-wide text-theme-900 shadow backdrop-blur-md dark:bg-gray-700/80 dark:text-theme-200"
        :class="showRuns ? 'md:w-11/12' : 'md:w-1/2'"
      >
        <div class="flex w-full items-center">
          <!-- <button
            v-if="flag === 'from_boops'"
            class="mr-4 flex items-center text-theme-500"
            @click="navigateTo('/assortment') && close()"
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
              <font-awesome-icon :icon="['fal', 'shapes']" class="mr-1" />
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
      <div
        class="z-10 mt-8 flex h-full flex-wrap justify-between bg-gray-200 py-4 dark:bg-gray-800"
      >
        <div class="relative flex w-full p-0 md:p-4" :class="{ 'lg:w-1/3': showRuns }">
          <div class="w-full">
            <div class="text-sm font-bold uppercase tracking-widest">
              {{ $t("Edit fields") }}
            </div>

            <OptionsEditForm type="option" :config-editable="showRuns" class="mt-2" />
          </div>
        </div>

        <div v-if="showRuns" class="relative mt-8 w-full p-4 lg:mt-0 lg:w-2/3">
          <div class="mb-2 text-sm font-bold uppercase tracking-widest">
            {{ $t("option price per") }}
            <template
              v-if="
                selected_category &&
                selected_category.price_build &&
                selected_category.price_build.full_calculation
              "
            >
              {{
                item.calculation_method === "sheet"
                  ? $t("sheet")
                  : item.calculation_method === "qty"
                    ? $t("quantity")
                    : item.calculation_method === "sqm"
                      ? $t("sqm")
                      : $t("lm")
              }}
            </template>
            <template v-else>
              {{ $t("quantity") }}
            </template>
          </div>

          <CalculationQuantities
            :full-calculation="selected_category.price_build.full_calculation"
          />
        </div>
      </div>

      <div
        class="sticky bottom-0 top-8 flex border-t bg-white/80 px-4 py-2 shadow-md backdrop-blur-md dark:border-gray-900 dark:bg-gray-700/80 dark:text-theme-200"
      >
        <button
          class="mx-auto rounded-full bg-green-500 px-4 py-2 text-white transition-colors duration-75 hover:bg-green-600"
          @click="saveItem()"
        >
          <font-awesome-icon :icon="['fad', 'floppy-disk']" />
          {{ $t("save") }}
        </button>
      </div>
    </template>
  </SidePanel>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  name: "SupplieritemsEditPanel",
  props: {
    showRunsPanel: {
      type: Boolean,
      default: true,
    },
  },
  emits: ["on-close", "on-updated"],
  setup() {
    const api = useAPI();
    const instance = getCurrentInstance();
    const boopsStore = useBoxesAndOptions();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess, instance, boopsStore };
  },
  computed: {
    ...mapState({
      item: (state) => state.assortmentsettings.item,
      flag: (state) => state.assortmentsettings.flag,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
    showRuns() {
      return Object.keys(this.selected_category).length > 0;
    },
  },
  methods: {
    ...mapMutations({
      set_active_category: "product/set_active_category",
      set_selected_category: "product_wizard/set_selected_category",
      set_selected_boops: "product_wizard/set_selected_boops",
      set_boops: "product/set_boops",
    }),
    close() {
      this.$emit("on-close");
    },
    saveItem() {
      const payload = this.item;
      payload?.runs?.filter((run) => {
        run.dlv_production?.forEach((dlv, i) => {
          if (dlv.days === null) {
            run.dlv_production.splice(i, 1);
          }
          dlv.days = Number(dlv.days);
        });
      });

      // set dimension
      if (!payload.dimension) {
        payload.dimension = "2d";
      }

      let url = "";
      if (this.showRunsPanel)
        url = `categories/${this.selected_category.id}/options/${this.item.id}`;
      else url = `/options/${this.item.id}`;

      this.api
        .put(url, payload)
        .then((response) => {
          this.api.get(`categories/${this.selected_category.slug}`).then((response) => {
            const cat = response.data;
            this.set_active_category([cat.id, cat.slug, cat.name]);
            this.set_selected_category(cat);
            this.set_boops(cat.boops[0]);
          });
          this.$emit("on-updated", response.data);
          this.handleSuccess(response);
          this.close();
          this.boopsStore.getOptions();
          this.getCategory(this.selected_category.slug);
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
    isRouteActive(url) {
      const pathSegments = this.$route.path.split("/").filter((segment) => segment);
      const lastSegment = pathSegments[pathSegments.length - 1];
      const firstSegment = pathSegments[0];
      if (lastSegment === url || firstSegment === url) {
        return true;
      } else {
        return false;
      }
    },
  },
};
</script>
