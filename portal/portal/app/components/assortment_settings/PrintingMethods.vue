<template>
  <div class="p-4">
    <section class="mx-auto w-1/2">
      <div
        class="rounded bg-white p-4 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
      >
        <h2 class="font-bold uppercase tracking-wide">
          {{ $t("printing methods") }}
        </h2>
        <ul v-if="printing_methods" class="divide-y dark:divide-black">
          <li class="flex items-center justify-between p-2">
            <div class="my-2 flex items-center">
              <font-awesome-icon :icon="['fal', 'fill-drip']" class="mr-1" />
              <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />

              <input
                v-model="newPrintingMethod"
                type="text"
                class="w-full rounded-l border bg-white px-2 py-1 text-sm shadow-inner transition-all duration-100 hover:bg-gray-50 focus:border-theme-200 focus:outline-none focus:ring dark:border-gray-900 dark:bg-gray-700"
                :placeholder="$t('new printing method')"
              />
              <button
                class="rounded-r border border-green-600 bg-green-500 px-2 py-1 text-sm text-white"
                @click="(create_printing_method(newPrintingMethod), (newPrintingMethod = ''))"
              >
                {{ $t("add") }}
              </button>
            </div>
          </li>

          <template v-if="printing_methods.length > 0">
            <li v-for="(method, i) in printing_methods" :key="i">
              <div
                class="group flex cursor-pointer items-center justify-between rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-900"
                @click="!show.includes(method.id) ? show.push(method.id) : retract(method.id)"
              >
                <span>
                  <font-awesome-icon :icon="['fal', 'fill-drip']" />
                  {{ method.name }}
                </span>
                <span>
                  <button
                    class="invisible px-2 text-red-500 group-hover:visible"
                    @click="showRemoveItem = method.id"
                  >
                    <font-awesome-icon :icon="['fal', 'trash-can']" />
                  </button>
                </span>
              </div>

              <transition name="fade">
                <PrintingMethodsRemoveItem
                  v-if="showRemoveItem === method.id"
                  :item="method"
                  @close="showRemoveItem = false"
                />
              </transition>
            </li>
          </template>
        </ul>

        <!-- <vue-nestable v-model="reorderPrintingMethods">
					<vue-nestable-handle slot-scope="{ item }" :item="item">
                  <font-awesome-icon :icon="['fal', 'window-restore']" />
						{{ item.name }}
					</vue-nestable-handle>
				</vue-nestable> -->
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
export default {
  name: "PrintingMethods",
  data() {
    return {
      newPrintingMethod: "",
      reorderPrintingMethods: [],
      show: [],
      showRemoveItem: false,
    };
  },
  head() {
    return {
      title: `${this.$t("printing methods")} | Prindustry Manager`,
    };
  },
  created() {
    this.get_printing_methods();
    if (this.printing_methods && this.printing_methods.length > 0) {
      this.show.push(this.printing_methods[0].id);
      // this.reorderPrintingMethods = [...this.printing_methods];
    }
  },
  computed: {
    ...mapState({
      resource: (state) => state.printing_methods.resource,
      printing_methods: (state) => state.printing_methods.printing_methods,
    }),
  },
  watch: {
    printing_methods(newVal) {
      this.reorderPrintingMethods = [...newVal];
    },
  },
  methods: {
    ...mapActions({
      get_printing_methods: "printing_methods/get_printing_methods",
      create_printing_method: "printing_methods/create_printing_method",
      delete_printing_method: "printing_methods/delete_printing_method",
      update_printing_method: "printing_methods/update_printing_method",
    }),
    retract(id) {
      const index = this.show.indexOf(id);
      this.show.splice(index, 1);
    },
  },
};
</script>

<style></style>
