<template>
  <div class="h-full w-full">
    <!-- accordion -->
    <section v-show="!sortBoops" class="mx-auto flex w-full flex-col">
      <div
        v-if="Object.keys(activeCategory).length > 0"
        class="z-50 relative w-full flex items-center justify-between border-b pb-3 mb-2 border-gray-200 dark:border-gray-900"
      >
        <div class="flex items-center justify-between w-full">
          <span class="flex items-center flex-1">
            <img
              id="prindustry-logo"
              src="~assets/images/Prindustry-box.png"
              alt="Prindustry Logo"
              class="h-6 mr-1"
            />
            <p class="text-sm font-bold tracking-wide uppercase truncate">
              {{ activeCategory.name }}
            </p>
          </span>

          <div v-if="activeCategory.suppliers?.length > 0" class="flex-1">
            <button
              class="px-2 transition-colors rounded bg-gradient-to-r from-pink-500 via-purple-500 group to-cyan-500 inline-block text-transparent bg-clip-text hover:text-white hover:from-pink-400 hover:via-purple-400 hover:to-cyan-400 hover:bg-clip-border"
              @click="emit('onLoadManifest')"
            >
              <font-awesome-icon
                :icon="['fal', 'plus']"
                class="text-pink-500 group-hover:text-white"
              />
              Load manifest
            </button>
            <span class="text-gray-500 italic text-xs ml-2 whitespace-nowrap">
              <font-awesome-icon :icon="['fal', 'circle-info']" class="" /> from linked supplier
            </span>
          </div>
          <span v-else class="text-gray-500 italic ml-2 flex-1">
            <font-awesome-icon :icon="['fal', 'face-sad-cry']" class="" /> no linked suppliers...
          </span>

          <button
            class="border-green-500 px-2 hover:bg-green-100 bg-green-50 rounded-md border text-green-500 grow-0 ml-8"
            @click="saveManifest(activeCategory, selectedBoops)"
          >
            <font-awesome-icon :icon="['fal', 'check']" />
            save manifest
          </button>
        </div>
        <!-- <ItemMenu
          v-if="mainMenuItems"
          class="z-10"
          :menu-items="mainMenuItems"
          menu-icon="ellipsis-h"
          menu-class="z-50 w-6 h-6 text-sm text-blue-500 bg-blue-100 rounded-full dark:bg-blue-600 dark:text-blue-300"
          dropdown-class="z-50 right-0 border w-44 dark:border-black text-theme-900"
          @item-clicked="menuItemClicked($event)"
        /> -->
      </div>

      <transition v-if="Object.keys(activeCategory).length > 0" name="slide">
        <p v-show="!sortOps" class="my-2 flex w-full justify-between italic text-gray-500">
          review boxes and options
          <button
            v-if="activeCategory.boops && activeCategory.boops.length > 0"
            class="ml-4 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="sortBoops = true"
          >
            <font-awesome-icon :icon="['fal', 'arrows-repeat']" />
            reorder
          </button>
          <button
            v-if="!showBoxes"
            class="ml-4 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="emit('onAddBox')"
          >
            <font-awesome-icon :icon="['fal', 'box-open']" />
            <font-awesome-icon :icon="['fal', 'plus']" />
            add box
          </button>
          <button
            v-if="showBoxes"
            class="ml-4 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="emit('onAddBoxClose')"
          >
            <font-awesome-icon :icon="['fad', 'circle-xmark']" />
            close
          </button>
        </p>
      </transition>

      <EditBoopsAccordion
        v-if="activeCategory.boops && activeCategory.boops.length > 0"
        :data="activeCategory.boops"
        :active-box="activeBox"
        :divided="activeCategory.divided"
        class="h-full w-full overflow-y-auto"
        style="max-height: calc(100vh - 15rem)"
        @remove-box="removeBox($event)"
        @remove-option="removeOption($event)"
        @toggle-active-box="activeBox = $event"
      >
        <template #default="{ box, index }">
          <section v-show="!sortOps">
            <!-- BOOPS MENU -->
            <div class="mb-2 flex flex-wrap items-center justify-between">
              <p class="italic text-gray-500">
                review
                {{ box.name }}
                options
              </p>
              <button
                class="text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                @click="sortOps = true"
              >
                <font-awesome-icon :icon="['fal', 'arrows-repeat']" />
                reorder
              </button>
              <button
                v-if="!showOptions"
                class="text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                @click="emit('onAddOption', index)"
              >
                <font-awesome-icon :icon="['fal', 'plus']" />
                add option
              </button>
              <button
                v-if="showOptions"
                class="ml-4 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
                @click="emit('onAddOptionClose')"
              >
                <font-awesome-icon :icon="['fad', 'circle-xmark']" />
                close
              </button>
            </div>

            <div class="h-full overflow-y-auto" style="max-height: calc(100vh - 28rem)">
              <!-- OPTIONS MULTISELECT MENU -->
              <transition name="slide">
                <div
                  v-if="activeBox === index && selectedOptionsList.length > 0"
                  class="sticky top-0 rounded-t bg-white px-2 py-1 text-xs text-gray-500 shadow"
                >
                  <span class="font-bold">{{ selectedOptionsList.length }}</span>
                  selected
                  <UIButton
                    variant="inverted-danger"
                    class="ml-8 bg-gray-100"
                    @click="removeMultipleBoops(index)"
                  >
                    <font-awesome-icon
                      aria-hidden="true"
                      :icon="['fal', 'layer-minus']"
                      class="mr-1"
                    />
                    remove
                  </UIButton>
                </div>
              </transition>

              <!-- OPTIONS -->
              <div
                v-for="(option, idx) in box.ops"
                :key="'option_' + option.slug"
                class="group flex items-center justify-between border-t px-2 py-1 first:border-t-0 hover:bg-gray-200 dark:border-black dark:hover:bg-black"
              >
                <label class="flex items-center text-xs font-bold uppercase tracking-wide md:mx-2">
                  <!-- <div
                    class="flex items-center justify-center flex-shrink-0 w-3 h-3 mr-2 bg-white border border-gray-400 rounded-sm cursor-pointer dark:border-black dark:bg-gray-700 focus-within:border-theme-500"
                    
                  > -->
                  <input
                    type="checkbox"
                    class="mr-2"
                    :checked="selectedOptionsList.includes(option.id)"
                    @change="toggle(option.id)"
                  />
                  <!-- <svg
                      class="hidden w-4 h-4 pointer-events-none fill-current text-theme-500 dark:text-theme-400"
                      viewBox="0 0 20 20"
                    >
                      <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                    </svg>
                  </div> -->

                  {{ option.name }}

                  <small v-tooltip="'system key'" class="ml-2 text-gray-500">
                    {{ option.system_key }}
                  </small>
                </label>
                <span>
                  <!-- <button
                    class="invisible rounded-full px-2 text-theme-500 hover:bg-theme-100 group-hover:visible dark:hover:bg-theme-800"
                    @click.stop="menuItemClicked('editOption', option)"
                  >
                    <font-awesome-icon aria-hidden="true" :icon="['fal', 'pencil']" />
                  </button> -->
                  <button
                    class="invisible rounded-full px-2 text-red-500 hover:bg-red-100 group-hover:visible dark:hover:bg-red-800"
                    @click.stop="removeOption(index, idx)"
                  >
                    <font-awesome-icon aria-hidden="true" :icon="['fal', 'trash-can']" />
                  </button>
                </span>
              </div>
            </div>
          </section>

          <!-- SORT OPTION >> -->
          <div
            v-if="sortOps"
            class="h-full overflow-y-auto"
            style="max-height: calc(100vh - 28rem)"
          >
            <p class="mb-2 flex w-full justify-between italic text-gray-600">
              Sort options below
              <button
                class="ml-4 text-green-500 hover:text-green-600 dark:hover:text-green-400"
                @click="(emit('onAddBoops', selectedBoops), (sortOps = false))"
              >
                <font-awesome-icon :icon="['fal', 'check']" />
                done
              </button>
            </p>

            <draggable :list="box.ops" item-key="optionList">
              <template #item="{ element: option }">
                <div
                  class="group flex items-center justify-between border-t px-2 py-1 first:border-t-0 hover:bg-gray-200"
                >
                  <label>
                    {{ option.name }}
                  </label>
                  <font-awesome-icon :icon="['fal', 'grip-lines']" />
                </div>
              </template>
            </draggable>
          </div>
        </template>
      </EditBoopsAccordion>

      <div v-else class="mb-2 mt-20 hidden h-full w-full justify-center font-bold lg:flex">
        <div class="flex h-full w-full flex-col flex-wrap items-center justify-center text-center">
          <p v-if="Object.keys(activeCategory).length" class="text-xl font-bold text-gray-400">
            empty manifest
          </p>
          <p
            v-if="Object.keys(activeCategory).length === 0"
            class="text-xl font-bold text-gray-400"
          >
            No category selected
          </p>

          <div class="my-8 flex items-start justify-center">
            <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
            <font-awesome-icon :icon="['fad', 'box-open']" class="fa-5x my-4 text-gray-400" />
            <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
          </div>

          <button
            class="ml-4 rounded-full border border-theme-500 px-2 py-1 text-theme-500 hover:text-theme-600 dark:hover:text-theme-400"
            @click="emit('onAddBox')"
          >
            <font-awesome-icon :icon="['fal', 'box-open']" />
            <font-awesome-icon :icon="['fal', 'plus']" />
            add box
          </button>
        </div>
      </div>
    </section>

    <!-- SORT BOXES -->
    <section v-if="sortBoops" class="mx-auto flex w-full flex-col p-2 md:w-1/3 lg:w-1/2">
      <p class="my-2 flex w-full justify-between italic text-gray-600">
        Sort boxes below
        <button
          class="ml-4 text-green-500 hover:text-green-600 dark:hover:text-green-400"
          @click="(emit('onAddBoops', selectedBoops), (sortBoops = false))"
        >
          <font-awesome-icon :icon="['fal', 'check']" />
          <!-- <font-awesome-icon :icon="['fal', 'plus']" /> -->
          done
        </button>
      </p>

      <draggable
        :list="activeCategory.boops"
        item-key="boxesList"
        :animation="200"
        :group="'description'"
        :disabled="false"
        :ghost-class="'ghost'"
        @start="drag = true"
        @end="drag = false"
      >
        <template #item="{ element: box }">
          <div
            class="group mb-1 flex cursor-pointer justify-between rounded bg-white p-2 shadow-md transition hover:shadow-lg dark:bg-gray-700"
          >
            {{ box.name }}
            <div class="flex items-center">
              <!-- <small
                v-if="divided && divider !== 'null'"
                v-tooltip="$t('divider')"
                class="mr-8 text-gray-500"
              >
                {{ box.divider }}
              </small> -->

              <font-awesome-icon :icon="['fal', 'grip-lines']" />
            </div>
          </div>
        </template>
      </draggable>
    </section>
  </div>
</template>

<script setup>
import _ from "lodash";
import EditBoopsAccordion from "./EditBoopsAccordion.vue";
import LoadManifestPanel from "./LoadManifestPanel.vue";

const manifestRepository = useManifestRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  activeCategory: { type: Object, default: () => ({}) },
  showBoxes: { type: Boolean, default: false },
  showOptions: { type: Boolean, default: false },
});

const emit = defineEmits([
  "doneOrdering",
  "ordering",
  "onAddBox",
  "onAddBoxCLose",
  "onAddBoxClose",
  "onAddOption",
  "onAddOptionClose",
  "onAddBoops",
  "onLoadManifest",
  "onSaveManifest",
]);

// State
const selectedBoops = ref(props.activeCategory.boops);
const activeBox = ref(null);
const selectedOptionsList = ref([]);
const searchBoop = ref(false);
const searchUrl = ref("");
const searchType = ref("");
const sortBoops = ref(false);
const sortOps = ref(false);
const drag = ref(false);
const loadManifest = ref(false);

// Watchers
// watch(selected_boops, (newVal) => {
//    selectedBoops.value = _.cloneDeep(newVal);
// }, { deep: true, immediate: true });

watch(activeBox, () => {
  selectedOptionsList.value = [];
  emit("onAddOptionClose");
});

watch(sortBoops, (v) => {
  if (v) {
    emit("ordering");
  } else {
    emit("doneOrdering");
  }
});

watch(sortOps, (v) => {
  if (v) {
    emit("ordering");
  } else {
    emit("doneOrdering");
  }
});

watch(searchBoop, (v) => {
  if (v) {
    emit("ordering");
  } else {
    emit("doneOrdering");
  }
});

// Methods
const toggle = (id) => {
  if (!selectedOptionsList.value.includes(id)) {
    selectedOptionsList.value.push(id);
  } else {
    const index = selectedOptionsList.value.findIndex((x) => x === id);
    selectedOptionsList.value.splice(index, 1);
  }
};

const removeOption = (index, idx) => {
  props.activeCategory.boops[index].ops.splice(idx, 1);
};

const removeBox = (e) => {
  props.activeCategory.boops.splice(e.index, 1);
};

const removeMultipleBoops = (index) => {
  props.activeCategory.boops[index].ops = props.activeCategory.boops[index].ops.filter(
    (x) => !selectedOptionsList.value.includes(x.id),
  );
  selectedOptionsList.value = [];
};

const saveManifest = async (activeCategory) => {
  await manifestRepository
    .saveManifest(activeCategory.id, activeCategory)
    .then((response) => {
      emit("onSaveManifest");
      handleSuccess(response);
    })
    .catch((error) => {
      if (error.response._data.message == "Manifest already exists") {
        manifestRepository
          .updateManifest(activeCategory.id, activeCategory)
          .then((response) => handleSuccess(response))
          .catch((error) => handleError(error));
      } else {
        handleError(error);
      }
    });
};
</script>
