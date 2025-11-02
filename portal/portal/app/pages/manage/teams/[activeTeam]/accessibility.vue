<template>
  <div class="grid h-full grid-cols-12 gap-4 overflow-hidden">
    <div class="col-span-4 h-full overflow-y-auto">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('Print Categories')" />
        </template>
        <template #right>
          <div class="flex gap-3">
            <VDropdown
              v-model:shown="showPrintCategoryPopover"
              :triggers="['click']"
              placement="bottom-end"
            >
              <UIButton icon="plus" class="!h-6" />
              <template #popper>
                <div class="max-h-96 w-72 p-2">
                  <UIInputText
                    v-model="printCategoryQuery"
                    name="printCategoryQuery"
                    placeholder="Search categories"
                    class="mb-1"
                  />
                  <font-awesome-icon
                    v-if="!availablePrintCategories.length"
                    :icon="['fal', 'spinner']"
                    class="text-theme-500"
                    spin
                  />
                  <ul>
                    <li v-for="category in queriedPrintCategories" :key="category.id">
                      <button
                        class="group flex w-full items-center truncate px-2 py-1 text-left transition-colors duration-75 hover:bg-gray-200 dark:hover:bg-gray-900"
                        @click="handleAddCategory(category.id)"
                      >
                        <Thumbnail
                          v-if="Array.isArray(category.media) && category.media[0]"
                          disk="tenancy"
                          :file="{ path: category.media[0] }"
                          class="pr-1"
                        />
                        <font-awesome-icon
                          v-else
                          class="mr-1 text-xs text-gray-300"
                          fw
                          :icon="['fal', 'image-slash']"
                        />
                        <span class="truncate">
                          {{ category.name }}
                        </span>
                        <div class="ml-auto flex items-center">
                          <font-awesome-icon
                            v-tooltip="$t('this category is usable in webshop')"
                            class="mr-2 text-theme-500"
                            :class="
                              category.published
                                ? 'text-theme-500'
                                : 'text-gray-300 dark:text-gray-600'
                            "
                            :icon="['fal', 'heart-rate']"
                          />
                        </div>
                      </button>
                    </li>
                  </ul>
                </div>
              </template>
            </VDropdown>
            <button
              v-if="selectedPrintCategories.length > 1 && permissions.includes('teams-accessibility-delete')"
              v-tooltip="$t('Unlink selected category from team')"
              icon="link-slash"
              variant="inverted-danger"
              class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
              @click="handleUnlinkCategory(selectedPrintCategories)"
            >
              <font-awesome-icon icon="link-slash" />
            </button>
          </div>
        </template>
      </UICardHeader>
      <UICard>
        <div v-if="loading" class="pb-0.5 pt-2 text-center">
          <SkeletonLine v-for="i in 6" :key="i" class="mx-2 mb-2 !h-12" />
        </div>
        <div v-else-if="printCategories.length === 0" class="py-6 text-center">
          <div class="text-center text-gray-400">
            <font-awesome-icon :icon="['fal', 'box-open']" class="fa-2x -ml-1 mb-2" />
            <p>{{ $t("No products added yet") }}</p>
            <SalesActionButton
              variant="neutral"
              class="mx-auto mt-2 !w-fit px-8"
              @click="showPrintCategoryPopover = true"
            >
              {{ $t("Add Product") }}
            </SalesActionButton>
          </div>
        </div>
        <TransitionGroup v-else name="category-list" tag="ul">
          <li
            v-for="category in printCategories"
            :key="category.id"
            class="flex w-full items-center justify-between border-b border-b-gray-200 p-4 px-2 text-left"
            :class="{
              'bg-theme-50 text-theme-800': selectedPrintCategories.find(
                (item) => item === category.id,
              ),
              'hover:bg-gray-50': !selectedPrintCategories.find((item) => item === category.id),
            }"
          >
            <span>
              <input
                type="checkbox"
                class="me-2"
                v-if="permissions.includes('teams-accessibility-delete')"
                :checked="selectedPrintCategories.find((item) => item === category.id)"
                @click.stop
                @change="toggleCategory(category, 'print')"
              />
              <span class="truncate">{{ category.name }}</span>
            </span>
            <button
              v-if="selectedPrintCategories.length < 2 && permissions.includes('teams-accessibility-delete')"
              v-tooltip="$t('Unlink category from team')"
              icon="link-slash"
              variant="inverted-danger"
              class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
              @click="handleUnlinkCategory([category.id])"
            >
              <font-awesome-icon icon="link-slash" />
            </button>
          </li>
        </TransitionGroup>
      </UICard>
    </div>
    <div class="col-span-4 h-full overflow-y-auto">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('Custom Categories')" />
        </template>
        <template #right>
          <div class="flex gap-3">
            <VDropdown
              v-model:shown="showCustomCategoryPopover"
              :triggers="['click']"
              placement="bottom-end"
            >
              <UIButton icon="plus" class="!h-6" />
              <template #popper>
                <div class="max-h-96 w-56 p-2">
                  <UIInputText
                    v-model="customCategoryQuery"
                    name="customCategoryQuery"
                    placeholder="Search categories"
                    class="mb-1"
                  />
                  <font-awesome-icon
                    v-if="!availableCustomCategories.length"
                    :icon="['fas', 'spinner']"
                    class="text-theme-500"
                    spin
                  />
                  <ul>
                    <li v-for="category in queriedCustomCategories" :key="category.id">
                      <button
                        class="group flex w-full items-center truncate px-2 py-1 text-left transition-colors duration-75 hover:bg-gray-200 dark:hover:bg-gray-900"
                        @click="handleAddCategory(category.id)"
                      >
                        <Thumbnail
                          v-if="Array.isArray(category.media) && category.media[0]"
                          disk="tenancy"
                          :file="{ path: category.media[0] }"
                          class="pr-1"
                        />
                        <font-awesome-icon
                          v-else
                          class="mr-1 text-xs text-gray-300"
                          fw
                          :icon="['fal', 'image-slash']"
                        />
                        <span class="truncate">
                          {{ category.name }}
                        </span>
                        <div class="ml-auto flex items-center">
                          <font-awesome-icon
                            v-tooltip="$t('this category is usable in webshop')"
                            class="mr-2 text-theme-500"
                            :class="
                              category.published
                                ? 'text-theme-500'
                                : 'text-gray-300 dark:text-gray-600'
                            "
                            :icon="['fal', 'heart-rate']"
                          />
                        </div>
                      </button>
                    </li>
                  </ul>
                </div>
              </template>
            </VDropdown>
            <button
              v-if="
                selectedCustomCategories.length > 1 &&
                permissions.includes('teams-accessibility-delete')
              "
              v-tooltip="$t('Unlink selected category from team')"
              icon="link-slash"
              variant="inverted-danger"
              class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
              @click="handleUnlinkCategory(selectedCustomCategories)"
            >
              <font-awesome-icon icon="link-slash" />
            </button>
          </div>
        </template>
      </UICardHeader>
      <UICard>
        <div v-if="loading" class="pb-0.5 pt-2 text-center">
          <SkeletonLine v-for="i in 6" :key="i" class="mx-2 mb-2 !h-12" />
        </div>
        <div v-else-if="customCategories.length === 0" class="py-6 text-center">
          <div class="text-center text-gray-400">
            <font-awesome-icon :icon="['fal', 'box-open']" class="fa-2x -ml-1 mb-2" />
            <p>{{ $t("No products added yet") }}</p>
            <SalesActionButton
              variant="neutral"
              class="mx-auto mt-2 !w-fit px-8"
              @click="showCustomCategoryPopover = true"
            >
              {{ $t("Add Product") }}
            </SalesActionButton>
          </div>
        </div>
        <TransitionGroup v-else name="category-list" tag="ul" class="flex-1 overflow-y-auto">
          <li
            v-for="category in customCategories"
            :key="category.id"
            class="flex w-full items-center justify-between border-b border-b-gray-200 p-4 px-2 text-left"
            :class="{
              'bg-theme-50 text-theme-800': selectedCustomCategories.find(
                (item) => item === category.id,
              ),
              'hover:bg-gray-50': !selectedCustomCategories.find((item) => item === category.id),
            }"
          >
            <span>
              <input
                type="checkbox"
                class="me-2"
                v-if="permissions.includes('teams-accessibility-delete')"
                :checked="selectedCustomCategories.find((item) => item === category.id)"
                @click.stop
                @change="toggleCategory(category, 'custom')"
              />
              <span class="truncate">{{ category.name }}</span>
            </span>
            <button
              v-if="selectedCustomCategories.length < 2 && permissions.includes('teams-accessibility-delete')"
              v-tooltip="$t('Unlink category from team')"
              icon="link-slash"
              variant="inverted-danger"
              class="inline-flex aspect-square h-6 items-center justify-center rounded-full border border-red-200 !bg-transparent bg-white !p-0 text-xs text-red-500 transition hover:!bg-red-200"
              @click="handleUnlinkCategory([category.id])"
            >
              <font-awesome-icon icon="link-slash" />
            </button>
          </li>
        </TransitionGroup>
      </UICard>
    </div>
    <div class="col-span-4 h-full overflow-y-auto">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('Products')" />
        </template>
        <template #right>
          <UIButton v-tooltip="$t('coming soon')" icon="plus" class="!h-6" disabled />
        </template>
      </UICardHeader>
      <UICard>
        <div v-if="loading" class="pb-0.5 pt-2 text-center">
          <SkeletonLine v-for="i in 6" :key="i" class="mx-2 mb-2 !h-12" />
        </div>
        <div v-else-if="products.length === 0" class="py-6 text-center">
          <div class="text-center text-gray-400">
            <font-awesome-icon :icon="['fal', 'box-open']" class="fa-2x -ml-1 mb-2" />
            <p>{{ $t("No products added yet") }}</p>
            <SalesActionButton
              v-tooltip="$t('coming soon')"
              variant="neutral"
              class="mx-auto mt-2 !w-fit px-8"
              disabled
            >
              {{ $t("Add Product") }}
            </SalesActionButton>
          </div>
        </div>
        <ul v-else>
          <li v-for="product in products" :key="product.id">
            <div>
              <span>{{ product.name }}</span>
              <span>{{ product.description }}</span>
            </div>
            <button class="btn btn-primary">Delete</button>
          </li>
        </ul>
      </UICard>
    </div>
  </div>
</template>

<script setup>
const route = useRoute();

const api = useAPI();
const { t: $t } = useI18n();
const { addToast } = useToastStore();
const { confirm } = useConfirmation();
const { handleError } = useMessageHandler();
const teamRepository = useTeamRepository();
const { permissions } = storeToRefs(useAuthStore());
const selectedPrintCategories = ref([]);
const selectedCustomCategories = ref([]);

const toggleCategory = (category, type) => {
  if (type === "custom") {
    const index = selectedCustomCategories.value.findIndex((item) => item === category.id);
    if (index > -1) {
      selectedCustomCategories.value.splice(index, 1);
    } else {
      selectedCustomCategories.value.push(category.id);
    }
  } else if (type === "print") {
    const index = selectedPrintCategories.value.findIndex((item) => item === category.id);
    if (index > -1) {
      selectedPrintCategories.value.splice(index, 1);
    } else {
      selectedPrintCategories.value.push(category.id);
    }
  }
};

const activeTeam = computed(() => route.params.activeTeam);

const loading = ref(false);
const products = ref([]);

const printCategories = ref([]);
const availablePrintCategories = ref([]);
const printCategoryQuery = ref("");
const showPrintCategoryPopover = ref(false);
const queriedPrintCategories = computed(() =>
  availablePrintCategories.value
    .filter(
      (category) =>
        !printCategories.value.some((printCategory) => printCategory.id === category.id),
    )
    .filter((category) =>
      category.name.toLowerCase().includes(printCategoryQuery.value.toLowerCase()),
    ),
);
watch(
  () => showPrintCategoryPopover.value,
  async (value) => {
    if (!value || availablePrintCategories.value.length > 0) return;
    await fetchAvailablePrintCategories();
  },
);

const customCategories = ref([]);
const availableCustomCategories = ref([]);
const customCategoryQuery = ref("");
const showCustomCategoryPopover = ref(false);
const queriedCustomCategories = computed(() =>
  availableCustomCategories.value
    .filter(
      (category) =>
        !customCategories.value.some((customCategory) => customCategory.id === category.id),
    )
    .filter((category) =>
      category.name.toLowerCase().includes(customCategoryQuery.value.toLowerCase()),
    ),
);
watch(
  () => showCustomCategoryPopover.value,
  async (value) => {
    if (!value || availableCustomCategories.value.length > 0) return;
    await fetchAvailableCustomCategories();
  },
);

async function fetchAvailablePrintCategories() {
  try {
    const { data } = await api.get("/categories?per_page=9999");
    availablePrintCategories.value = data;
  } catch (error) {
    handleError(error);
  }
}
async function fetchAvailableCustomCategories() {
  try {
    const { data } = await api.get("/custom/categories?per_page=9999");
    availableCustomCategories.value = data;
  } catch (error) {
    handleError(error);
  }
}

async function fetchCategories() {
  try {
    loading.value = true;
    const data = await teamRepository.getTeamCategories(activeTeam.value);
    customCategories.value = data.categories;
    printCategories.value = data.print_categories;
    products.value = data.products;
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function handleUnlinkCategory(selectedCategories) {
  try {
    await confirm({
      title: $t("Unlink category from team"),
      message: $t("Are you sure you want to unlink this category from the team?"),
      confirmOptions: {
        label: $t("link-slash"),
        variant: "danger",
      },
    });

    await teamRepository.removeCategoryFromTeam(activeTeam.value, selectedCategories);
    selectedCustomCategories.value = [];
    selectedPrintCategories.value = [];
    fetchCategories();
    addToast({
      type: "success",
      message: $t("The category has been unlinked from the team."),
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  }
}

async function handleAddCategory(id) {
  try {
    await teamRepository.addCategoryToTeam(activeTeam.value, id);
    addToast({
      type: "success",
      message: $t("The category has been linked to the team."),
    });
    await fetchCategories();
  } catch (error) {
    handleError(error);
  }
}

function emptyCategories() {
  customCategories.value = [];
  printCategories.value = [];
  products.value = [];
}

watch(
  () => activeTeam.value,
  async () => {
    emptyCategories();
    await fetchCategories();
  },
  { immediate: true },
);
</script>

<style lang="scss" scoped>
.v-popper--shown {
  div[data-popper-shown] {
    @apply z-[51] drop-shadow-2xl;
  }

  &:before {
    content: "";
    @apply absolute left-0 top-0 z-50 h-full w-full bg-gray-900/20;
  }
}
</style>
