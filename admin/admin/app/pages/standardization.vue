<template>
  <div class="relative h-full w-full">
    <div
      class="absolute inset-x-0 -top-3 z-0 transform-gpu -rotate-180 overflow-hidden px-36 blur-3xl opacity-50"
      aria-hidden="true"
    >
      <div
        class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-cyan-500 to-pink-500 opacity-50"
        style="
          clip-path: polygon(
            74.1% 44.1%,
            100% 61.6%,
            97.5% 26.9%,
            85.5% 0.1%,
            80.7% 2%,
            72.5% 32.5%,
            60.2% 62.4%,
            52.4% 68.1%,
            47.5% 58.3%,
            45.2% 34.5%,
            27.5% 76.7%,
            0.1% 64.9%,
            17.9% 100%,
            27.6% 76.8%,
            76.1% 97.7%,
            74.1% 44.1%
          );
        "
      />
    </div>
    <div
      class="relative flex items-center w-full justify-between mb-4 pb-4 gap-x-4 border-b z-10 dark:border-gray-950"
    >
      <div class="">
        Show
        <select
          id="perPage"
          name="perPage"
          class="mx-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
          @change="setPerPage($event.target.value)"
        >
          <option :value="10" :selected="perPage === 10">10</option>
          <option :value="20" :selected="perPage === 20">20</option>
          <option :value="50" :selected="perPage === 5">50</option>
          <option :value="100" :selected="perPage === 100">100</option>
        </select>
        items per column
      </div>

      <div class="flex">
        <button
          class="px-2 py-1 transition-colors rounded-l outline-none"
          :class="
            view === 'list all'
              ? 'bg-white shadow-md text-blue-500 dark:bg-gray-800 dark:text-blue-300'
              : 'hover:bg-gray-300 dark:hover:bg-black bg-gray-200 dark:bg-gray-950'
          "
          @click="toggleView('list all')"
        >
          <font-awesome-icon :icon="['fal', 'list']" />
          Standardization
        </button>
        <button
          class="px-2 py-1 transition-colors rounded-r outline-none"
          :class="
            view === 'relational'
              ? 'bg-white shadow-md text-blue-500 dark:bg-gray-800 dark:text-blue-300'
              : 'hover:bg-gray-300 dark:hover:bg-black bg-gray-200 dark:bg-gray-950'
          "
          @click="toggleView('relational')"
        >
          <font-awesome-icon :icon="['fal', 'network-wired']" rotation="270" />
          Prindustry manifest
        </button>
        <button
          class="px-2 py-1 transition-colors rounded-r outline-none"
          :class="
            view === 'unlinked'
              ? 'bg-white shadow-md text-blue-500 dark:bg-gray-800 dark:text-blue-300'
              : 'hover:bg-gray-300 dark:hover:bg-black bg-gray-200 dark:bg-gray-950'
          "
          @click="toggleView('unlinked')"
        >
          <font-awesome-icon :icon="['fal', 'link-slash']" rotation="270" />
          Unlinked
        </button>
      </div>
    </div>

    <section class="flex mx-auto w-full justify-center gap-x-8">
      <TransitionGroup name="list" appear>
        <!-- CATEGORIES -->
        <List
          key="categories"
          :list-items="standardizationRepository.categories.value"
          :unmatched="unmatchedCats"
          :matched="matchedCats"
          :pagination="standardizationRepository.pagination.value"
          :per-page="perPage"
          :active="activeCategory"
          :view="view"
          :loading="loading"
          :unlinked="view === 'unlinked' ? true : false"
          type="categories"
          class="z-30 h-full mt-6 xl:mt-0"
          @select-item="setActiveCategory($event)"
          @on-save-item="standardizationRepository.updateCategory($event.slug, $event)"
          @merge-list-items="obtainCategories()"
          @filter="categoryFilter = $event"
          @unmatched="unmatchedCats = $event"
          @on-unlink="unlinkCategory($event)"
          @paginate="
            view === 'list all' || view === 'relational'
              ? obtainCategories($event)
              : getUnlinkedCategories($event)
          "
        />

        <!-- RELATIONAL -->
        <transition key="relational" name="slide">
          <div
            v-if="view === 'relational'"
            class="z-30 h-full mt-6 w-1/2 xl:mt-0 flex items-center justify-evenly"
          >
            <font-awesome-icon
              :icon="['fas', 'caret-right']"
              class="text-6xl my-auto text-gray-300 mr-8"
            />
            <section class="w-full">
              <div
                v-if="Object.keys(activeCategory).length > 0"
                class="relative mx-auto mb-4 flex items-center w-full"
              >
                <label for="divided" class="text-sm font-bold uppercase tracking-widest">
                  <font-awesome-icon :icon="['fal', 'split']" class="fa-fw mr-2 text-gray-500" />
                  divided
                </label>

                <UISwitch
                  :value="activeCategory.divided ?? false"
                  name="divided"
                  @input="activeCategory.divided = $event"
                />
                <font-awesome-icon
                  v-tooltip="
                    'when enabling this toggle you will be able to add boxes to a divider group to have them seperately calculated. For example: a book with a cover and the pages within'
                  "
                  :icon="['fal', 'circle-info']"
                  class="fa-fw ml-2"
                />
                <div
                  v-if="activeCategory.divided"
                  class="relative mx-auto flex items-center md:w-1/2"
                >
                  <UIButton
                    :class="{ '!bg-green-500 !text-white': manageDivider }"
                    class="shadow"
                    @click="manageDivider = !manageDivider"
                  >
                    {{ manageDivider ? "Done managing divider" : "Manage divider" }}
                  </UIButton>
                </div>
              </div>

              <div v-if="manageDivider" class="mx-auto">
                <ManageBoopsDivider :selected-boops="activeCategory.boops" />
              </div>
              <EditBoops
                v-if="!manageDivider"
                :active-category="activeCategory"
                :show-boxes="showBoxes"
                :show-options="showOptions"
                @ordering="ordering = true"
                @done-ordering="ordering = false"
                @on-add-box="((showBoxes = true), (showOptions = false))"
                @on-add-box-close="showBoxes = false"
                @on-add-option="
                  (((showOptions = true), (showBoxes = false)), (boxToAddOptionTo = $event))
                "
                @on-add-option-close="showOptions = false"
                @on-update-boops="activeCategory.boops = $event"
                @on-load-manifest="loadManifest = true"
                @on-save-manifest="obtainCategories"
              />
              <LoadManifestPanel
                v-if="loadManifest"
                :type="'category'"
                :item="activeCategory"
                @on-select-producer="getManifestFromProducer(activeCategory.id, $event.id)"
                @close="loadManifest = false"
              />
            </section>
            <font-awesome-icon
              v-if="showBoxes || showOptions"
              :icon="['fas', 'caret-left']"
              class="text-6xl my-auto text-gray-300 ml-8"
            />
          </div>
        </transition>

        <div key="boxes-options" class="min-w-80 flex gap-x-6">
          <TransitionGroup name="list" appear>
            <!-- BOXES -->
            <List
              v-if="
                view === 'list all' ||
                view === 'unlinked' ||
                (Object.keys(activeCategory).length && showBoxes)
              "
              key="boxes"
              :list-items="standardizationRepository.boxes.value"
              :unmatched="unmatchedBoxes"
              :unlinked="view === 'unlinked' ? true : false"
              :matched="matchedBoxes"
              :pagination="standardizationRepository.boxPagination.value"
              :per-page="perPage"
              :active="activeBox"
              :view="view"
              :loading="boxLoading"
              :show-boxes="showBoxes"
              type="boxes"
              class="z-30 h-full mt-6 xl:mt-0"
              @select-item="setActiveBox($event)"
              @on-save-item="standardizationRepository.updateBox($event.slug, $event)"
              @merge-list-items="obtainBoxes()"
              @filter="boxFilter = $event"
              @unmatched="unmatchedBoxes = $event"
              @show-unlinked="getUnlinkedBoxes"
              @on-unlink="unlinkBox($event)"
              @paginate="
                view === 'list all'
                  ? obtainBoxes($event)
                  : view === 'relational'
                    ? getBoxes
                    : getUnlinkedBoxes($event)
              "
            />

            <!-- OPTIONS -->
            <List
              v-if="
                view === 'list all' ||
                view === 'unlinked' ||
                (Object.keys(activeCategory).length && showOptions)
              "
              key="options"
              :list-items="standardizationRepository.options.value"
              :unmatched="unmatchedOptions"
              :unlinked="view === 'unlinked' ? true : false"
              :matched="matchedOptions"
              :pagination="standardizationRepository.optionPagination.value"
              :per-page="perPage"
              :active="activeOption"
              :view="view"
              :loading="optionLoading"
              :show-options="showOptions"
              type="options"
              class="z-30 h-full mt-6 xl:mt-0"
              @select-item="setActiveOption($event)"
              @on-save-item="standardizationRepository.updateOption($event.slug, $event)"
              @merge-list-items="obtainOptions()"
              @filter="optionFilter = $event"
              @unmatched="unmatchedOptions = $event"
              @show-unlinked="getUnlinkedOptions"
              @on-unlink="unlinkOption($event)"
              @paginate="
                view === 'list all'
                  ? obtainOptions($event)
                  : view === 'relational'
                    ? getOptions($event)
                    : getUnlinkedOptions($event)
              "
            />
          </TransitionGroup>
        </div>
      </TransitionGroup>
    </section>

    <transition v-if="pageLoading" name="fade">
      <PageLoader class="z-50" />
    </transition>
  </div>
</template>

<script setup>
import List from "../components/standardisation/List.vue";
import EditBoops from "../components/standardisation/EditBoops.vue";
import ManageBoopsDivider from "../components/standardisation/ManageBoopsDivider.vue";
import LoadManifestPanel from "~/components/standardisation/LoadManifestPanel.vue";

const standardizationRepository = useStandardizationRepository();
const manifestRepository = useManifestRepository();
const { handleError, handleSuccess } = useMessageHandler();

// category
const unlinkedCats = ref([]); // unlinked categories
const unmatchedCats = ref([]); // unmatched categories
const matchedCats = ref([]); // matched categories
const activeCategory = ref({}); // active category
const categoryFilter = ref(""); // filter categories
const loading = ref(false); // loading state

// boxes
const unlinkedBoxes = ref([]);
const unmatchedBoxes = ref([]);
const matchedBoxes = ref([]);
const activeBox = ref({});
const boxFilter = ref("");
const boxLoading = ref(false);
const boxToAddOptionTo = ref(null);

// options
const unlinkedOptions = ref([]);
const unmatchedOptions = ref([]);
const matchedOptions = ref([]);
const activeOption = ref({});
const optionFilter = ref("");
const optionLoading = ref(false);

// relational or list all
const view = ref("list all");
const showBoxes = ref(false);
const showOptions = ref(false);
const manageDivider = ref(false);
const loadManifest = ref(false);

// pagination
const pageLoading = ref(false);
const perPage = ref(parseInt(20));

// Methods
const obtainCategories = (e) => {
  loading.value = true;
  standardizationRepository
    .getCategories({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: categoryFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateCategories(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      loading.value = false;
    });

  standardizationRepository
    .getUnmatchedCategories()
    .then((response) => {
      unmatchedCats.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
  standardizationRepository
    .getMatchedCategories()
    .then((response) => {
      matchedCats.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
};

const obtainBoxes = (e) => {
  boxLoading.value = true;
  standardizationRepository
    .getBoxes({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: boxFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateBoxes(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      boxLoading.value = false;
    });

  standardizationRepository
    .getUnmatchedBoxes()
    .then((response) => {
      unmatchedBoxes.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
  standardizationRepository
    .getMatchedBoxes()
    .then((response) => {
      matchedBoxes.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
};
const getBoxes = (e) => {
  standardizationRepository
    .getBoxes({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: boxFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateBoxes(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      boxLoading.value = false;
    });
};

const getOptions = (e) => {
  standardizationRepository
    .getOptions({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: optionFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateOptions(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      optionLoading.value = false;
    });
};
const obtainOptions = (e) => {
  optionLoading.value = true;
  standardizationRepository
    .getOptions({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: optionFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateOptions(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      optionLoading.value = false;
    });

  standardizationRepository
    .getUnmatchedOptions()
    .then((response) => {
      unmatchedOptions.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
  standardizationRepository
    .getMatchedOptions()
    .then((response) => {
      matchedOptions.value = response;
    })
    .catch((error) => {
      handleError(error);
    });
};

const obtainData = async () => {
  //   togglePageLoading();
  if (view.value === "relational") {
    obtainCategories();
    getBoxes();
    getOptions();
    //  togglePageLoading();
  }
  if (view.value === "list all") {
    obtainCategories();
    obtainBoxes();
    obtainOptions();
    //  togglePageLoading();
  }
};
const obtainUnlinkedData = async () => {
  getUnlinkedCategories();
  getUnlinkedBoxes();
  getUnlinkedOptions();
};

// Unlinking category
const unlinkCategory = (category) => {
  standardizationRepository
    .deleteUnmatchedCategory(category)
    .then((response) => {
      handleSuccess(response);
      obtainCategories();
    })
    .catch((error) => {
      handleError(error);
    });
};
const getUnlinkedCategories = (e) => {
  standardizationRepository
    .getUnlinkedCategories({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: optionFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateCategories(response);
    })
    .catch((error) => {
      handleError(error);
    });
};

// Unlinking boxes
const unlinkBox = (category) => {
  standardizationRepository
    .deleteUnmatchedBox(category)
    .then((response) => {
      handleSuccess(response);
      obtainBoxes();
    })
    .catch((error) => {
      handleError(error);
    });
};
const getUnlinkedBoxes = (e) => {
  standardizationRepository
    .getUnlinkedBoxes({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: boxFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateBoxes(response);
    })
    .catch((error) => {
      handleError(error);
    });
};

// Unlinking options
const unlinkOption = (category) => {
  standardizationRepository
    .deleteUnmatchedOption(category)
    .then((response) => {
      handleSuccess(response);
      obtainOptions();
    })
    .catch((error) => {
      handleError(error);
    });
};
const getUnlinkedOptions = (e) => {
  standardizationRepository
    .getUnlinkedOptions({
      perPage: perPage.value,
      page: e?.page ?? 1,
      filter: optionFilter.value ?? "",
    })
    .then((response) => {
      standardizationRepository.populateOptions(response);
    })
    .catch((error) => {
      handleError(error);
    });
};

const togglePageLoading = () => {
  pageLoading.value = !pageLoading.value;
};

const toggleView = (newView) => {
  view.value = newView;
  localStorage.setItem("standardization-view", newView);
  obtainData();
  if (newView === "list all") {
    //  togglePageLoading();
  }
};

const setPerPage = (value) => {
  perPage.value = value;
  obtainData();
};

const setActiveCategory = (category) => {
  // when view is relational and active category is not empty
  if (view.value === "relational" && category.id) {
    activeCategory.value = category;
    manifestRepository
      .getManifest(category.id)
      .then((response) => {
        activeCategory.value.boops = response.data.boops ?? [];
        activeCategory.value.suppliers = response.data.suppliers ?? [];
        activeCategory.value.divided = response.data.divided ?? false;
      })
      .catch((error) => {
        handleError(error);
      });
  } else {
    // when view is standardisation
    activeCategory.value = category;
  }
};
const setActiveBox = (box) => {
  if (view.value === "relational" && Object.keys(activeCategory.value).length > 0) {
    if (!activeCategory.value.boops?.find((b) => b.id === box.id) || activeCategory.value.divided) {
      activeCategory.value.boops.push(box);
    } else {
      handleError("Box already exists in this category");
    }
  } else {
    activeBox.value = box;
  }
};

const setActiveOption = (option) => {
  if (
    view.value === "relational" &&
    Object.keys(activeCategory.value).length > 0 &&
    activeBox.value &&
    activeCategory.value.boops[boxToAddOptionTo.value]
  ) {
    if (!activeCategory.value.boops[boxToAddOptionTo.value].ops) {
      activeCategory.value.boops[boxToAddOptionTo.value].ops = [];
    }
    if (!activeCategory.value.boops[boxToAddOptionTo.value].ops.find((o) => o.id === option.id)) {
      activeCategory.value.boops[boxToAddOptionTo.value].ops.push(option);
    } else {
      handleError("Option already exists in this box");
    }
  } else {
    activeOption.value = option;
  }
};

const getManifestFromProducer = (id, producer_id) => {
  manifestRepository
    .getManifestFromProducer(id, producer_id)
    .then((response) => {
      activeCategory.value.boops = response.data.boops ?? [];
      activeCategory.value.divided = response.data.divided ?? false;
      handleSuccess(response);
      loadManifest.value = false;
    })
    .catch((error) => {
      handleError(error);
    });
};

// Watchers
watch(view, (newVal) => {
  if (newVal === "list all") {
    //  togglePageLoading();
    setActiveCategory({});
    setActiveBox({});
    setActiveOption({});
    //  obtainData();
  }

  //   if (newVal === "boxes & options") {
  //     setActiveCategory({});
  //     setActiveBox({});
  //     setActiveOption({});
  //   }

  if (newVal === "relational") {
    setActiveCategory({});
    setActiveBox({});
    setActiveOption({});
    //  populateCategories([]);
    //  populateBoxes([]);
    //  populateOptions([]);
    //  obtainCategories({ perPage: perPage.value, page: 1 });
  }
  if (newVal === "unlinked") {
    setActiveCategory({});
    setActiveBox({});
    setActiveOption({});
    standardizationRepository.populateCategories([]);
    standardizationRepository.populateBoxes([]);
    standardizationRepository.populateOptions([]);
    obtainUnlinkedData();
  }
});

watch(perPage, () => {
  obtainData();
});
watch(categoryFilter, () => {
  if (view.value === "list all" || view.value === "relational") {
    obtainCategories();
  }
  if (view.value === "unlinked") {
    getUnlinkedCategories();
  }
});
watch(boxFilter, () => {
  if (view.value === "list all" || view.value === "relational") {
    obtainBoxes();
  }
  if (view.value === "unlinked") {
    getUnlinkedBoxes();
  }
});
watch(optionFilter, () => {
  if (view.value === "list all" || view.value === "relational") {
    obtainOptions();
    console.log("asdasd");
  }
  if (view.value === "unlinked") {
    getUnlinkedOptions();
  }
});
watch(standardizationRepository.categories, (categories) => {
  if (categories && categories.length > 0) {
    const checkedItems = JSON.parse(localStorage.getItem("categories_checked_items") || "[]");
    checkedItems.forEach((itemSlug) => {
      const category = categories.find((cat) => cat.slug === itemSlug);
      if (category) {
        category.checked = true;
      }
    });
  }
});

// Lifecycle hooks
onMounted(() => {
  // togglePageLoading();
  view.value = localStorage.getItem("standardization-view") ?? "list all";
  obtainData();
});
</script>
