<template>
  <div class="">
    <!-- Search input and layout switches -->
    <div class="mt-4 flex w-full items-center justify-between space-x-2">
      <div class="mx-auto flex md:w-1/2">
        <UIInputText
          v-model="searchQuery"
          autocomplete="off"
          name="catalogueFilter"
          type="search"
          :placeholder="capitalizeFirstLetter($t('search on material or weight')) + '...'"
          class="w-full"
          input-class="w-full border-2 border-theme-500"
        />
        <div class="ml-4 flex items-center">
          <UIButton
            class="focus:shadow-outline rounded-none rounded-l border-r !p-3 focus:outline-none dark:border-gray-900 dark:bg-gray-900 dark:hover:bg-gray-950"
            variant="neutral-light"
            :class="{
              '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400':
                !wideView,
              '': wideView,
            }"
            :title="$t('table view')"
            @click="wideView = false"
          >
            <font-awesome-icon :icon="['fal', 'table-list']" />
          </UIButton>
          <UIButton
            class="focus:shadow-outline rounded-none rounded-r border-l !p-3 focus:outline-none dark:bg-gray-900 dark:hover:bg-gray-950"
            variant="neutral-light"
            :class="{
              '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400': wideView,
              '': !wideView,
            }"
            :title="$t('detailed view')"
            @click="wideView = true"
          >
            <font-awesome-icon :icon="['fal', 'diagram-cells']" />
          </UIButton>
        </div>
      </div>

      <UIButton
        :icon="['fal', 'layer-group']"
        class="ml-auto capitalize"
        :disabled="showNewMaterialModal"
        @click="showNewMaterialModal = true"
      >
        {{ $t("new paper") }}
      </UIButton>
    </div>

    <!-- Catalog items list -->
    <Transition name="slidedownslow">
      <ul v-if="shownCatalogue.length > 0" class="transition" :class="{ 'mt-4': !wideView }">
        <CatalogSingle
          v-for="(paper, index) in shownCatalogue"
          :key="`paper_${index}`"
          :class="{ 'my-1 py-3': wideView }"
          :paper="paper"
          :materials="materials"
          :grs="grs"
          :index="index"
          :editable="editable"
          :new-paper="newPaper"
          :wide-view="wideView"
          :catalogue="shownCatalogue"
          @on-copy="$emit('onCopyCatalog', $event)"
          @on-add="$emit('onAddCatalog', $event)"
          @on-save="$emit('onSaveCatalog', $event)"
          @on-delete="$emit('onDeleteCatalog', $event)"
          @on-edit="$emit('onEditCatalog', $event)"
          @on-cancel="$emit('onCancelCatalog', $event)"
          @on-cancel-edit="$emit('onCancelCatalogEdit', $event)"
        />
      </ul>
      <div
        v-else
        class="mt-4 block w-full rounded bg-white p-4 text-center text-lg italic shadow-md shadow-gray-200 transition"
      >
        <font-awesome-icon
          :icon="['fal', 'triangle-exclamation']"
          class="fa-fw mr-2 text-gray-500"
        />
        {{ $t("nothing found") }}
      </div>
    </Transition>

    <!-- Modal for adding new material -->
    <BulkMaterialCreator
      v-if="showNewMaterialModal"
      :catalogue="catalogue"
      :materials="materials"
      :grs="grs"
      :categories="categories"
      @close="showNewMaterialModal = false"
      @add-single-material="
        emit('onAddSingleMaterial');
        showNewMaterialModal = false;
      "
      @add-material="emit('onAddCatalog', $event)"
    />
  </div>
</template>

<script setup>
import BulkMaterialCreator from "./BulkMaterialCreator.vue";

const { capitalizeFirstLetter } = useUtilities();
const { handleError } = useMessageHandler();

const props = defineProps({
  catalogue: {
    type: Array,
    required: true,
  },
  editable: {
    type: [Number, null],
    required: false,
    default: null,
  },
  newPaper: {
    type: [Number, null],
    required: false,
    default: null,
  },
});

const emit = defineEmits([
  "onEditCatalog",
  "onDeleteCatalog",
  "onSaveCatalog",
  "onAddCatalog",
  "onCopyCatalog",
  "onCancelCatalog",
  "onCancelCatalogEdit",
  "onAddSingleMaterial",
]);

/**
 * API Calls
 */

// Get the materials from the API
const { data: materials, error: mtrlsError } = useLazyAPI("/options?ref=material&per_page=99999", {
  transform: (data) => data.data,
  default: () => [],
});
watch(mtrlsError, (error) => error && handleError(error));

// Get the weights from the API
const { data: grs, error: grsError } = useLazyAPI("/options?ref=weight&per_page=99999", {
  transform: (data) => data.data,
  default: () => [],
});
watch(grsError, (error) => error && handleError(error));

// Get the categories from the API
const { data: categories, error: categoriesError } = useLazyAPI("/categories?per_page=99999", {
  transform: (data) => data.data,
  default: () => [],
});
watch(categoriesError, (error) => error && handleError(error));

// refs
const catalogueData = ref(props.catalogue);
const showNewMaterialModal = ref(false);

/**
 * Search for both the material and weight using fuzzy search
 */
const {
  searchQuery,
  filteredResults: shownCatalogue,
} = useFuzzySearch(catalogueData, {
  keys: ["material", "grs"],
  threshold: 0.3,
});

// Update catalogues with new catalogues
watch(
  () => props.catalogue,
  (newVal) => {
    catalogueData.value = newVal;
  },
  { deep: true },
);

/**
 * Wide View Settings
 */
const wideView = ref(false);
watch(wideView, (newValue) => localStorage.setItem("catalogWideView", newValue));
onMounted(() => {
  wideView.value = localStorage.getItem("catalogWideView") === "true";
});
</script>
