<template>
  <div class="flex h-full">
    <div class="z-40 w-80 h-full">
      <div
        class="z-50 relative flex items-center justify-between pr-2 mb-2 border-gray-400 dark:border-gray-900"
      >
        <div class="flex items-center">
          <span class="flex items-center ml-2">
            <img
              id="prindustry-logo"
              src="~assets/images/Prindustry-box.png"
              alt="Prindustry Logo"
              class="h-6 mr-1"
            />
            <p class="text-sm font-bold tracking-wide uppercase">{{ type }}</p>
          </span>

          <button
            v-if="!showBoxes && !showOptions"
            class="text-blue-500 dark:text-blue-300 ml-2"
            @click="component = 'NewPrindustryItem'"
          >
            <font-awesome-icon :icon="['fal', 'plus']" />
            new
          </button>
        </div>
        <ItemMenu
          v-if="mainMenuItems && !showBoxes && !showOptions"
          class="z-10"
          :menu-items="mainMenuItems"
          menu-icon="ellipsis-h"
          menu-class="z-50 w-6 h-6 text-sm text-blue-500 bg-blue-100 rounded-full dark:bg-blue-600 dark:text-blue-300"
          dropdown-class="z-50 right-0 border w-44 dark:border-black text-theme-900"
          @item-clicked="menuItemClicked($event)"
        />
      </div>

      <section class="z-0 h-full px-2 pb-2 overflow-y-auto overflow-x-hidden text-sm">
        <div
          class="h-full overflow-y-auto bg-white rounded-md shadow-md dark:bg-gray-800"
          style="max-height: calc(100vh - 15rem)"
        >
          <div
            class="sticky top-0 z-10 flex flex-col overflow-x-hidden py-3"
            :class="{ 'bg-white shadow-md rounded-t pb-0': multiselect }"
          >
            <!-- filter -->
            <div class="flex">
              <input
                v-model="filter"
                class="w-full px-2 py-1 mx-2 bg-white border border-blue-300 rounded shadow-md dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
                type="text"
                :placeholder="`Search all ${type}`"
              />
              <font-awesome-icon
                v-if="!filter"
                :icon="['fal', 'search']"
                class="absolute right-0 mt-2 mr-4 text-gray-600"
              />
              <UIButton
                v-if="filter"
                class="absolute right-0 mt-1 mr-2 !bg-transparent hover:bg-transparent"
                @click="filter = ''"
              >
                <font-awesome-icon :icon="['fal', 'times-circle']" />
              </UIButton>
            </div>

            <!-- multiselect ui -->
            <transition name="slide">
              <div v-if="multiselect" class="pt-2">
                <small class="flex justify-between w-full ml-2 text-gray-500">
                  <span class="flex items-center font-bold tracking-tight uppercase">
                    <font-awesome-icon :icon="['fal', 'ballot']" class="mr-1 fa-sm" />
                    multiselect
                  </span>
                  <span
                    class="flex items-center justify-center px-1 py-0 mx-1 font-bold text-blue-500"
                  >
                    {{ multiselectArray.length }}
                    selected
                  </span>
                  <button
                    class="mr-4 text-blue-900"
                    @click="((multiselectArray = []), (multiselect = false))"
                  >
                    <font-awesome-icon :icon="['fad', 'times-circle']" />
                    close
                  </button>
                </small>

                <transition name="slide">
                  <div
                    v-if="multiselectArray.length > 1"
                    class="flex items-center justify-between w-full px-2 py-1"
                  >
                    <button
                      class="p-1 text-blue-500 rounded hover:bg-blue-100"
                      @click="component = 'MergeItems'"
                    >
                      <font-awesome-icon :icon="['fal', 'code-merge']" />
                      merge
                    </button>
                    <button
                      v-if="type === 'options'"
                      class="p-1 text-blue-500 rounded hover:bg-blue-100"
                      @click="component = 'SetCalcRef'"
                    >
                      <font-awesome-icon :icon="['fal', 'asterisk']" />
                      set calc_ref
                    </button>
                    <button
                      v-tooltip="
                        `WARNING force deletes all ${type} and UNLINKS all boxes & options`
                      "
                      class="p-1 text-red-500 rounded hover:bg-red-100"
                      @click="multiForceDelete()"
                    >
                      <font-awesome-icon :icon="['fal', 'layer-minus']" />
                      delete
                    </button>
                    {{ component }}
                  </div>
                </transition>
              </div>
            </transition>
          </div>

          <!-- INFO: moved to list master data
           Items >> unlinked items during standardisation -->

          <!-- <ul
            v-if="unlinked && unlinked.length > 0 && !showBoxes && !showOptions"
            :class="{
              'bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 text-white font-semibold pb-1':
                unlinked && unlinked.length > 0,
            }"
          >
            <li>
              <input
                v-model="unlinkedfilter"
                type="text"
                class="border-purple-500 border-2 rounded-md w-full px-2 py-1 text-purple-500 text-normal"
                placeholder="filter unlinked items"
              />
            </li>
            <li
              v-for="(item, i) in filteredUnlinked"
              :key="`match_${i}`"
              class="bg-white dark:bg-gray-800"
            >
              <Button
                :item="item"
                :classes="
                  activeUnlinked.id === item.id
                    ? 'bg-purple-100 text-purple-500 hover:bg-purple-200 dark:bg-purple-700 dark:hover:bg-purple-800 dark:text-purple-300'
                    : 'text-purple-500 group hover:bg-gradient-to-r from-purple-100 via-pink-100 to-red-100 dark:from-purple-900 dark:via-pink-900 dark:to-red-900 dark:text-purple-300'
                "
                @click="((activeUnlinked.id = item.id), matchButtonClicked(item))"
              />
            </li>
          </ul> -->

          <!-- Items >> unmatched items during standardisation -->
          <ul
            v-if="unmatched && unmatched.length > 0 && !showBoxes && !showOptions && !unlinked"
            :class="{
              'bg-gradient-to-r from-red-500 via-pink-500 to-purple-500 text-white font-semibold pb-1':
                unmatched && unmatched.length > 0,
            }"
          >
            <li
              v-for="(item, i) in unmatched"
              :key="`match_${i}`"
              class="bg-white dark:bg-gray-800 relative flex items-center"
            >
              <Button
                :item="item"
                :classes="
                  activeUnmatched.id === item.id
                    ? 'bg-red-100 text-red-500 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-800 dark:text-red-300'
                    : 'text-red-500 group hover:bg-gradient-to-r from-red-100 via-pink-100 to-purple-100 dark:from-red-900 dark:via-pink-900 dark:to-purple-900 dark:text-red-300'
                "
                @click="((activeUnmatched.id = item.id), matchButtonClicked(item))"
              />
              <button
                class="w-5 h-5 text-center absolute text-xs rounded-full bg-red-100 text-red-500 hover:bg-red-500 hover:text-white right-2"
                @click="menuItemClicked('unlink', item)"
              >
                <font-awesome-icon :icon="['fal', 'trash-can-undo']" />
              </button>
            </li>
          </ul>

          <!-- Items >> Partially matched by AI -->
          <ul
            v-if="!showBoxes && !showOptions && !unlinked"
            :class="{
              'bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 text-white font-semibold pb-1':
                matched && matched.length > 0,
            }"
          >
            <li v-for="(item, i) in matched" :key="`match_${i}`" class="bg-white dark:bg-gray-800">
              <Button
                :item="item"
                :classes="
                  activeMatched.id === item.id
                    ? 'bg-gradient-to-r from-pink-100 via-purple-100 to-cyan-100'
                    : 'group hover:bg-gradient-to-r from-pink-100 via-purple-100 to-cyan-100 dark:from-pink-900 dark:via-purple-900 dark:to-cyan-900'
                "
                @click="((activeMatched.id = item.id), matchButtonClicked(item))"
              />
            </li>
          </ul>

          <!-- Items >> Fully standardized -->
          <ul>
            <li
              v-for="(item, i) in listItems"
              :key="i"
              class="relative flex items-center group w-full hover:bg-gray-100"
              :class="{
                'bg-green-50 text-green-500 hover:bg-green-100': item.checked,
                'bg-pink-100 text-pink-500 hover:bg-pink-200': !item.published,
                'bg-purple-100 text-purple-500 hover:bg-purple-200 dark:bg-purple-700 dark:hover:bg-purple-800 dark:text-purple-300':
                  unlinked,
              }"
            >
              <div v-if="multiselect" class="pl-2">
                <input
                  :id="item.slug"
                  type="checkbox"
                  :name="item.slug"
                  :checked="multiselectArray.some((selected) => selected.slug === item.slug)"
                  @click="selectItem($event.target.checked, item)"
                />
              </div>

              <Button
                :class="{
                  'bg-blue-100 text-blue-500 hover:bg-blue-200 dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-blue-300':
                    !item.checked && active.slug === item.slug,
                  'bg-green-100 text-green-500 hover:bg-green-200':
                    item.checked && active.name === item.name,
                  'bg-pink-200 text-pink-500 hover:bg-pink-200':
                    !item.published && active.name === item.name,
                }"
                :item="item"
                :menu-items="unlinked ? [] : menuItems"
                :multiselect="multiselect"
                :type="props.type"
                :unlinked="unlinked"
                @click.self="
                  unlinked
                    ? ((activeUnlinked.id = item.id), matchButtonClicked(item))
                    : emit('selectItem', item)
                "
                @button-clicked="
                  unlinked
                    ? ((activeUnlinked.id = item.id), matchButtonClicked(item))
                    : emit('selectItem', item)
                "
                @menu-item-clicked="menuItemClicked($event, item)"
              />
            </li>

            <li v-if="listItems && listItems.length === 0" class="italic text-center text-gray-500">
              no {{ props.type }} found
              <button
                class="text-blue-500"
                @click="
                  emit('paginate', {
                    page: pagination.current_page,
                    perPage: perPage,
                    filter: filter,
                  })
                "
              >
                refresh
              </button>
            </li>
          </ul>
        </div>
      </section>

      <section class="w-80 px-2 fixed bottom-4 z-10">
        <Pagination
          v-if="pagination?.last_page > 1 && !loading"
          class="bg-white rounded-md shadow dark:bg-gray-800"
          :pagination="pagination"
          @paginate="
            emit('paginate', {
              page: $event,
              perPage: perPage,
              filter: filter,
            })
          "
        />
        <div v-if="loading" class="py-2 text-center w-full text-white bg-blue-500 rounded">
          loading...
        </div>
      </section>
    </div>

    <transition name="fade" appear>
      <NewPrindustryItem
        v-if="component === 'NewPrindustryItem'"
        :item="active"
        :type="type"
        class="z-50"
        @on-close="component = ''"
      />
    </transition>

    <transition name="fade" appear>
      <MergeItems
        v-if="component === 'MergeItems'"
        :multiselect-array="multiselectArray"
        :type="type"
        class="z-50"
        @on-close="
          ((component = ''),
          emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter }))
        "
        @on-merge-items="merge"
      />
    </transition>

    <transition name="fade" appear>
      <DeleteItem
        v-if="component === 'DeleteItem'"
        :item="active"
        :type="type"
        class="z-50"
        @on-close="component = ''"
        @on-delete="deleteItem"
        @on-force-delete="forceDeleteItem"
        @on-deleted="
          (emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter }),
          (component = ''))
        "
      />
    </transition>

    <transition name="fade" appear>
      <SetCalcRef
        v-if="component === 'SetCalcRef'"
        :multiselect-array="multiselectArray"
        :type="type"
        class="z-50"
        @on-close="
          ((component = ''),
          emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter }))
        "
        @on-updated="
          emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter });
          component = '';
        "
      />
    </transition>

    <EditPanel
      :show="!!itemEdit"
      :item="active"
      :type="type"
      @close="itemEdit = false"
      @on-detached="
        ((itemEdit = false),
        emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter }))
      "
    />

    <StandardisationMatchPanel
      :show="unmatched && Object.keys(unmatchedItem).length > 0"
      :unmatched-item="unmatchedItem"
      :type="type"
      :unlinked="unlinked"
      @close="
        ((unmatchedItem = {}),
        (activeMatched = {}),
        (activeUnmatched = {}),
        emit('paginate', { page: pagination.current_page, perPage: perPage, filter: filter }))
      "
    />
  </div>
</template>

<script setup>
import _ from "lodash";
import Button from "./Button.vue";
import MergeItems from "./modals/MergeItems.vue";
import SetCalcRef from "./modals/SetCalcRef.vue";
import DeleteItem from "./modals/DeleteItem.vue";
import NewPrindustryItem from "./modals/NewPrindustryItem.vue";
import EditPanel from "./EditPanel.vue";

const props = defineProps({
  listItems: {
    type: Array,
    required: true,
  },
  unmatched: {
    type: Array,
    required: true,
  },
  unlinked: {
    type: Boolean,
    default: false,
  },
  matched: {
    type: Array,
    required: true,
  },
  active: {
    type: Object,
    required: true,
  },
  view: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    required: true,
  },
  perPage: {
    type: Number,
    required: true,
  },
  pagination: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    required: true,
  },
  // for relational data
  showBoxes: { type: Boolean, default: false },
  showOptions: { type: Boolean, default: false },
});

const emit = defineEmits([
  "filter",
  "paginate",
  "selectItem",
  "deleteItem",
  "show-unlinked",
  "merge-items",
  "on-edit",
  "onSaveItem",
  "on-unlink",
]);

// state
// main
const filter = ref("");
const component = ref(null);
const unmatchedItem = ref({});
const multiselect = ref(false);
const multiselectArray = ref([]);
// const activeItem = ref({});
const itemEdit = ref(false);
const activeMatched = ref({});
const activeUnmatched = ref({});
const activeUnlinked = ref({});

const unlinkedfilter = ref("");

// menu items
const mainMenuItems = [
  {
    action: "multi-select",
    icon: "ballot",
    title: "Multi select",
    classes: "text-blue-900 dark:text-blue-100",
    show: true,
  },
];

const menuItems = [
  {
    action: "edit",
    icon: "pencil",
    title: "Edit",
    classes: "text-blue-900 dark:text-blue-100",
    show: true,
  },
  {
    action: "set_flag",
    icon: "clipboard-check",
    title: "Toggle verified",
    classes: "text-green-500",
    show: true,
  },
  {
    action: "delete",
    icon: "trash",
    title: "Delete",
    classes: "text-red-500",
    show: true,
  },
];

const unmatchedMenuItems = [
  {
    action: "unlink",
    icon: "trash-can",
    title: "Unlink (will ignore this item, it can then be found in unlinked list for later use)",
    classes: "text-red-500",
    show: true,
  },
];

// watchers
// watch(props.edit, (val) => {
//   return val;
// });

// const filteredUnlinked = computed(() => {
//   if (unlinkedfilter.value.length > 0) {
//     return props.unlinked.filter((item) =>
//       item.name.toLowerCase().includes(unlinkedfilter.value.toLowerCase()),
//     );
//   }
//   return props.unlinked;
// });

watch(
  filter,
  _.debounce((val) => {
    emit("filter", val);
  }, 300),
);

// watch(itemUnmatched, (val) => {
//   component.value = val ? "MatchPanel" : "";
// });

// methods
const matchButtonClicked = (item) => {
  //   store.commit("standardization/set_item_unmatched", true);
  unmatchedItem.value = item;
};

const selectItem = (state, item) => {
  let selectedIds = multiselectArray.value.map((x) => x.id);
  console.log(state, item, selectedIds);
  if (state) {
    multiselectArray.value.push(item);
  } else if (selectedIds.includes(item.id)) {
    const position = multiselectArray.value.findIndex((x) => x.slug === item.slug);
    multiselectArray.value.splice(position, 1);
  }
};

const merge = (event) => {
  emit("merge-items", event);
};

const deleteItem = (element) => {
  emit("deleteItem", { data: element, force: false });
};

const forceDeleteItem = (element) => {
  emit("deleteItem", { data: element, force: true });
};

const multiForceDelete = () => {
  multiselectArray.value.forEach((element) => {
    emit("deleteItem", { data: element, force: false });
  });

  setTimeout(() => {
    multiselectArray.value = [];
    multiselect.value = false;
    emit("paginate", {
      page: pagination.value.current_page,
      perPage: props.perPage.value,
      filter: filter.value,
    });
  }, 500);
};

const checkItem = (item) => {
  const checkedItems = JSON.parse(localStorage.getItem(`${props.type}_checked_items`) || "[]");

  if (item.checked) {
    checkedItems.push(item.slug);
  } else {
    const index = checkedItems.indexOf(item.slug);
    if (index > -1) checkedItems.splice(index, 1);
  }
  localStorage.setItem(`${props.type}_checked_items`, JSON.stringify(checkedItems));
};

const menuItemClicked = (event, item) => {
  switch (event) {
    case "edit":
      itemEdit.value = true;
      break;
    case "set_flag":
      item.checked = !item.checked;
      checkItem(item);

      emit("onSaveItem", item);
      break;
    case "delete":
      component.value = "DeleteItem";
      break;
    case "multi-select":
      multiselect.value = true;
      break;
    case "show-unlinked":
      emit("show-unlinked");
      break;
    case "unlink":
      emit("on-unlink", item);
      break;
    default:
    // store.commit("standardization/set_item_edit", true);
  }
};
</script>
