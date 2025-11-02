<template>
  <div class="h-full">
    <section class="flex flex-wrap">
      <div class="w-full h-auto lg:block lg:w-1/4">
        <div>
          <div
            v-if="!boops || (boops && boops.length === 0)"
            class="flex flex-col flex-wrap items-center w-full h-full p-4 ml-2 text-center bg-gray-200 rounded dark:bg-gray-900 justify- dark:bg-gray-black"
          >
            <p class="text-xl font-bold text-gray-400">
              {{ $t("no boops available for this category") }}
            </p>
            <div class="flex items-start justify-center my-8">
              <font-awesome-icon :icon="['fal', 'clouds']" class="m-4 text-gray-300 fa-2x" />
              <font-awesome-icon :icon="['fad', 'bars']" class="my-4 text-gray-400 fa-5x" />
              <font-awesome-icon :icon="['fal', 'clouds']" class="m-4 text-gray-300 fa-3x" />
            </div>
          </div>
          <transition-group v-else name="list" tag="nav" class="flex w-full h-full p-2 text-sm">
            <template v-for="(box, index) in boops" :key="index" class="h-full">
              <nav
                v-show="index <= activeIndex"
                class="flex-shrink-0 h-full mx-1 bg-white rounded shadow-md min-w-64 dark:bg-gray-700"
                :style="'z-index:' + (28 - index)"
              >
                <p
                  class="px-2 py-1 text-sm font-bold tracking-wide uppercase border-b dark:border-gray-900"
                >
                  {{ $display_name(box.display_name) }}
                </p>
                <!-- Filter options -->
                <div class="field has-padding-10"></div>
                <nav>
                  <ul>
                    <li v-for="(item, i) in box.ops" :key="i" class="w-1/8">
                      <button
                        v-if="checkExclude(item) === true"
                        class="relative flex items-center justify-between w-full px-2 py-2 text-left group hover:bg-gray-200 dark:hover:bg-black"
                        :class="{
                          'bg-theme-100 text-theme-500':
                            activeItems.length > 0 &&
                            activeItems.findIndex((x) => x.slug === item.slug) > -1,
                        }"
                        @click="setActiveOption(box, item, index + 1)"
                      >
                        <div
                          v-if="!item.dynamic || item.dynamic === false"
                          v-tooltip="
                            $display_name(item.display_name).length > 30
                              ? $display_name(item.display_name)
                              : ''
                          "
                          class="flex items-center pr-8 truncate"
                        >
                          <Thumbnail
                            v-if="item.media && item.media.length > 0 && item.media[0]"
                            disk="assets"
                            :file="{ path: item.media[0] }"
                            class="flex px-1"
                          />
                          {{ $display_name(item.display_name) }}
                          <span
                            v-if="item.description"
                            v-tooltip="item.description"
                            class="flex flex-1 ml-2 text-sm text-gray-500 truncate"
                            >- {{ item.description }}</span
                          >
                        </div>

                        <form v-if="item.dynamic" class="flex items-center w-full mr-8">
                          <fieldset
                            v-if="
                              activeItems &&
                              activeItems.length > 0 &&
                              activeItems.findIndex((x) => x.slug === item.slug) > -1
                            "
                            class="flex items-center w-full"
                          >
                            <UIInputText
                              :prefix="`${$t('height')} ${item.unit}`"
                              :min="item.minimum_height"
                              :max="item.maximum_height"
                              :step="item.incremental_by"
                              :placeholder="item.height"
                              :value="item.height"
                              type="number"
                              @blur="setFormat($event, 'height')"
                            ></UIInputText>
                            <div class="mx-2">x</div>
                            <UIInputText
                              :prefix="`${$t('width')} ${item.unit}`"
                              :min="item.minimum_width"
                              :max="item.maximum_width"
                              :step="item.incremental_by"
                              :placeholder="item.width"
                              :value="item.width"
                              type="number"
                              @blur="setFormat($event.target.value, 'height')"
                            ></UIInputText>
                          </fieldset>
                          <div v-else>
                            {{ $display_name(item.display_name) }}
                            <span
                              v-tooltip="item.description"
                              class="ml-2 text-sm text-gray-500 truncate"
                              >- {{ item.description }}</span
                            >
                          </div>
                        </form>

                        <button
                          v-if="
                            index + 1 === boops.length &&
                            activeItems.length === boops.length &&
                            activeItems[index].id === item.id &&
                            permissions.includes('print-assortments-categories-read') &&
                            permissions.includes('print-assortments-categories-update')
                          "
                          class="px-2 mr-8 border rounded-full text-theme-500 border-theme-500 hover:bg-theme-200"
                          @click.prevent="activateDetails()"
                        >
                          {{ $t("view prices") }}
                        </button>
                        <span
                          class="absolute top-0 right-0 z-10 invisible mr-1 group-hover:visible"
                        >
                        </span>
                      </button>
                      <button
                        v-else
                        disabled
                        class="w-full px-2 py-2 text-left text-gray-300 truncate cursor-not-allowed dark:text-gray-700"
                      >
                        {{ $display_name(item.display_name) }}
                        <span v-tooltip="item.description" class="ml-2 text-sm truncate"
                          >- {{ item.description }}</span
                        >
                      </button>
                    </li>
                  </ul>
                </nav>
              </nav>
            </template>
          </transition-group>
          <OptionsEditPanel v-if="component" />
        </div>
      </div>

      <div class="w-full lg:w-3/4">
        <Result />
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import dummyData from "./data.json";
import _ from "lodash";

/**
 * step one get object from boops service
 * collect the selected data
 * generate a md5 hash from it
 * post it to hash service
 * response with price and quant || add price and quant
 **/
export default {
  name: "PrintProductBoops",
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    return { api, permissions };
  },
  data() {
    return {
      activeIndex: "",
      activeObject: {},
      activeItems: [],
      collection: {},
      exclude: {},
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("Edit"),
              classes: "",
              show: false,
            },
          ],
        },
      ],
      boops: dummyData.boops,
      component: false,
    };
  },
  computed: {
    ...mapState({
      category: (state) => state.product.active_category,
      // boops: (state) => state.product.boops,
    }),
  },
  watch: {
    category() {
      this.activeIndex = 0;
      // this.getBoops();
    },
    boops(v) {
      // console.log(v);
      return v;
    },
    collection: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    activeItems: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  created() {
    if (
      this.permissions.includes("print-assortments-options-update") &&
      this.permissions.includes("print-assortments-options-list")
    ) {
      this.menuItems[0].items[0].show = true;
    }
  },
  methods: {
    ...mapMutations({
      set_boops: "product/set_boops",
      set_loading_boops: "product/set_loading_boops",
      set_active_collection: "product/set_active_collection",
      set_active_items: "product/set_active_items",

      set_item: "assortmentsettings/set_item",
      set_runs: "assortmentsettings/set_runs",
      set_flag: "assortmentsettings/set_flag",
    }),
    setFormat(val, dim) {
      if (this.collection._format) {
        if (dim === "height") {
          this.collection._format.height = parseInt(val);
        } else if (dim === "width") {
          this.collection._format.width = parseInt(val);
        }
      } else {
        this.collection = Object.assign(this.collection, {
          _format: {
            width: 0,
            height: 0,
          },
        });
        this.setFormat(val, dim);
      }
    },
    setActiveOption(box, item, index) {
      if (item.dynamic) {
        this.setFormat(item.height, "height");
        this.setFormat(item.width, "width");
      }
      /** @activeIndex int hold the box position **/
      this.activeIndex = index;
      this.activeItems.length = index - 1;
      this.activeItems.splice(index, 1);
      this.activeItems.push(item);
      // this.collection += item.id + "-";

      /** @exclude array: reset the array **/
      for (let i = index; i <= Object.keys(this.exclude).length && i >= index; i - 1) {
        delete this.exclude[Object.keys(this.exclude)[i - 1]];
      }

      this.collection = Object.assign(this.collection, {
        [box.slug]: item.slug,
      });

      /** @activeObject Object follow the current steps **/
      Object.values(this.activeObject).forEach((v, k) => {
        if (k > index - 1) {
          delete this.activeObject[k];
        }
      });

      /** Updating the current active object **/
      this.activeObject = Object.assign(this.activeObject, {
        [index - 1]: {
          index: index - 1,
          name: item.name,
          exclude: item.excludes,
        },
      });

      // clone the object
      const excl = { ...this.exclude };

      /** add the selected excludes to exclude object by box **/
      Object.values(this.activeObject).forEach((v) => {
        // add 'singles' & 'exclude with' object
        excl[item.slug] = { singles: [], with: [], exclude: [] };

        // add excludes
        if (v.exclude && v.exclude.filter((a) => a.length === 1).length > 0) {
          excl[item.slug].singles = _.cloneDeep(v.exclude);
        }

        // if excludes with other option combination: add the option itself
        if (v.exclude && v.exclude.filter((a) => a.length > 1).length > 0) {
          const withPocket = excl[item.slug].with;
          const exclPocket = excl[item.slug].exclude;

          // add all values to 'with object'
          for (let idx = 0; idx < v.exclude.length; idx++) {
            const excl = v.exclude[idx];

            withPocket.push(_.clone(excl));
          }

          withPocket.forEach((element) => {
            // add the item itself for better comparing
            element.unshift(item.id);

            // pop the excluded (last value)
            element.pop();
          });

          // add the exlcuded (last value) to excluded array
          for (let indx = 0; indx < v.exclude.length; indx++) {
            const element = v.exclude[indx];
            exclPocket.push(_.clone(element[element.length - 1]));
          }
        }
      });

      // reassign the object
      this.exclude = excl;

      setTimeout(() => {
        this.$parent.scrollToEnd();
      }, 200);
    },
    checkExclude(item) {
      /**
       * if item matches any exclude return false
       * to disable the option in the selection
       */

      // current selection to compare to the combination
      const actives = [];
      let flag = false;

      // console.log(item);

      // set active items in flat id's array
      for (let i = 0; i < this.activeItems.length; i++) {
        const active = this.activeItems[i];
        actives.push(active.id);
      }

      // loop trough excludes
      for (const key in this.exclude) {
        // get the key to make unique identifier
        if (Object.hasOwnProperty.call(this.exclude, key)) {
          const excl = this.exclude[key];

          // if exclude is single exclude, exclude immediately
          if (
            excl.singles.length > 0 &&
            excl.singles.filter((a) => a.length > 0 && a.length < 2 && a == item.id).length > 0
          ) {
            return false;
          }

          // global index array to reference excludeWith, exclude & count by the same index
          const excludeIndex = [];
          // counter array, will hold the same indexes as global index array
          const count = [];

          // if exclude is combined exclude
          // loop trough 'exclude with' object
          if (excl.with.length > 0) {
            for (let ix = 0; ix < excl.with.length; ix++) {
              const withArray = excl.with[ix];

              // assign global index with excludeWith index
              if (!excludeIndex.includes(ix)) {
                excludeIndex.push(ix);
              }
              // same for count so we can reference them
              if (!count.includes(ix)) {
                count.push(ix);
              }

              // we need to count something, so lets set 0 if there is no number
              if (!count[ix]) {
                count[ix] = 0;
              }

              // get the selected items
              actives.forEach((active) => {
                /**
                 * if selected items match items in 'exclude with' array, up the counter
                 * now we can see if the matches === the exclude with array length so we
                 * know if we reached the'with' path
                 **/
                if (withArray.includes(active)) {
                  count[ix]++;
                }
              });
            }
          }

          /**
           * if matches === to include length we know we reached the combination
           * if item is in the exclude array we know we need to exclude
           **/

          // we loop trought the index array so we know at what indexes we need compare
          excludeIndex.forEach((index) => {
            // WARNING: keep this logs to clarify what is happening when need to debug in the future
            // console.log("count " + count[index]);
            // console.log("withLength " + excl.with[index].length);
            // console.log(count[index] == excl.with[index].length);
            // console.log(" --- ");

            // console.log("excl " + excl.exclude[index]);
            // console.log("item " + item.id);
            // console.log(excl.exclude[index] === item.id);
            // console.log("-------------------");

            /**
             * so for the same index
             * if count[1] === to excl.with[1].length we know that for this index the first
             * requirement is met
             * if excl.exclude[1] === item.id we know that indeed this option is excluded with
             * the previous combination
             **/
            if (count[index] == excl.with[index].length && excl.exclude[index] === item.id) {
              // return false here will only stop the foreach
              // instead set the flag so we can return false later
              flag = true;
            }
          });
        }
      }

      // read the flag and exclude if flag is true
      if (flag) {
        return false;
      }

      // item does not match any excludes, so continue
      return true;
    },
    async menuItemClicked(event, option) {
      switch (event) {
        case "edit":
          await this.api.get(`/options?per_page=999999`).then((response) => {
            const opt = response.data.find((op) => op.slug === option.slug);
            if (opt) {
              this.set_item(opt);
              this.set_runs(opt.runs);
              this.set_flag("from_boops");
              this.component = true;
            }
          });
          break;

        default:
          break;
      }
    },
    activateDetails() {
      // console.log(this.collection);
      // the collection of options, seperated by '-' dashes
      // remove the last dash
      // let collection = this.collection.substring(
      // 	0,
      // 	this.collection.length - 1
      // );
      // create an md5 from it
      // let hash = md5(collection);
      // store it in the store
      this.set_active_collection(this.collection);
      // store the active options (same as the on from the collection, but with all the data instead of only hashed id's)
      this.set_active_items(this.activeObject);

      // trigger the details view to be loaded with de designated tab
      // this.activate_details(true);
      this.$router.push(`/assortment/shop?cat=${this.category[1]}`);
    },
  },
};
</script>
