<template>
  <div>
    <AddProductHeader :step="1" to_component="AddProductOverview" />

    <section class="container">
      <ProductFinderHeader class="w-full">
        <div class="">
          <h1 class="mb-2 text-4xl font-bold md:text-3xl">
            {{ $t("Find producers for your assortment") }}
          </h1>
          <p class="mb-4 hidden w-6/12 text-gray-500 md:block">
            {{
              // prettier-ignore
              $t("Select a handshaked producer to be able to resell their products in your shop!")
            }}
          </p>
        </div>

        <div class="flex w-auto items-center">
          <ProductFinderSearchInput
            ref="searchBar"
            role="searchbox"
            class="mr-12 w-11/12"
            input-class="!ps-10"
            :placeholder="$t('Find producer')"
            :aria-label="$t('Search for producers')"
            @input="filter = $event.target.value"
          >
            <font-awesome-icon
              :icon="['fal', 'parachute-box']"
              class="absolute start-3 top-1/2 z-[2] -translate-y-1/2 text-gray-400"
            />
          </ProductFinderSearchInput>
          <div class="mt-2 flex items-center justify-end md:mt-0">
            <UIButton
              class="focus:shadow-outline rounded-none rounded-l border-r !p-3 focus:outline-none dark:border-gray-900 dark:bg-gray-900 dark:hover:bg-gray-950"
              variant="neutral-light"
              :class="{
                '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400':
                  activeView === 'no-handshake',
                '': activeView !== 'no-handshake',
              }"
              @click="activeView = 'no-handshake'"
            >
              <font-awesome-icon :icon="['fal', 'hand-holding-hand']" />
            </UIButton>
            <UIButton
              class="focus:shadow-outline rounded-none !p-3 focus:outline-none dark:bg-gray-900 dark:hover:bg-gray-950"
              variant="neutral-light"
              :class="{
                '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400':
                  activeView === 'handshake',
                '': activeView !== 'handshake',
              }"
              @click="activeView = 'handshake'"
            >
              <font-awesome-icon :icon="['fal', 'handshake']" />
            </UIButton>
            <UIButton
              class="focus:shadow-outline rounded-none rounded-r-sm border-l !p-2 !text-sm focus:outline-none dark:border-gray-900 dark:bg-gray-900 dark:hover:bg-gray-950"
              variant="neutral-light"
              :class="{
                '!bg-white !text-theme-500 shadow-md dark:!bg-gray-700 dark:text-theme-400':
                  activeView === 'both',
                '': activeView !== 'both',
              }"
              @click="activeView = 'both'"
            >
              {{ $t("all") }}
            </UIButton>
          </div>
        </div>
      </ProductFinderHeader>

      <Transition name="fade">
        <ProducerZeroState
          v-show="emptyResult && zeroStateMsg && !isLoading"
          :message="zeroStateMsg"
        />
      </Transition>

      <div v-if="!emptyResult" class="my-4 flex flex-wrap gap-4 py-4">
        <h2 class="w-full font-bold uppercase tracking-wide">
          {{ $t("Producers") }}
          <span class="ml-4 text-sm font-normal normal-case text-gray-600">
            <b> {{ filteredProducers.length }}</b>
            {{ $t("results") }}
          </span>
        </h2>

        <transition-group name="fade">
          <template v-if="isLoading">
            <UIListSkeleton
              v-for="i in 4"
              :key="'skeleton' + i"
              class="h-full w-1/6"
              :skeleton-line-height="30"
              :skeleton-line-amount="1"
            />
          </template>

          <div
            v-for="(supplier, index) in filteredProducers"
            :key="index"
            class="w-1/6 text-center"
          >
            <button
              class="relative w-full overflow-hidden rounded-md p-2 shadow transition-shadow duration-150"
              :class="{
                'cursor-not-allowed bg-gray-200 shadow-none': supplier.handshake !== 'accepted',
                'cursor-pointer bg-white shadow hover:shadow-xl': supplier.handshake === 'accepted',
              }"
              @click="supplier.handshake === 'accepted' && next(supplier)"
            >
              <div class="my-8">
                <HandshakeStatus :producer="supplier" position="top" />
                <figure class="mb-2 flex w-full items-center justify-center p-2">
                  <img :src="supplier.logo" class="max-h-24 object-contain" />
                </figure>
                <a class="font-bold" @click="next(supplier)">
                  {{ supplier.name }}
                </a>
              </div>
              <div class="h-6">
                <HandshakeIcon :producer="supplier" />
              </div>
            </button>
          </div>
        </transition-group>

        <div v-if="producerPagination.lastPage > 1" class="sticky bottom-0 w-full">
          <UIPagination
            class="mx-auto"
            :current-page="producerPagination.currentPage"
            :total-pages="producerPagination.lastPage"
            @update:page="(newPage) => retreiveProducers(newPage)"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useStore } from "vuex";

const store = useStore();
const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();
const { fetchProducers, fetchSharedCategories } = useMarketplaceRepository();

// data
const activeView = ref("both");
const filter = ref("");
const producers = ref([]);
const filteredProducers = ref([]);
const producerPagination = ref({});
const perPage = ref(5);
const page = ref(1);
const isLoading = ref(true);

// computed properties
// const supplier_id = computed(() => store.state.product.selected_producer.id);
// const selected_producer_categories = computed(
//   () => store.state.product.selected_producer_categories,
// );

// watchers
watch(
  () => [filter.value, activeView.value],
  ([filterValue, activeViewValue]) => {
    let filtered = producers.value;

    if (filterValue) {
      filtered = filtered.filter((producer) =>
        producer.producerInfo.company_name.toLowerCase().includes(filterValue.toLowerCase()),
      );
    }

    if (activeViewValue === "handshake") {
      filtered = filtered.filter((producer) => producer.handshake === "accepted");
    } else if (activeViewValue === "no-handshake") {
      filtered = filtered.filter((producer) => producer.handshake === "false");
    }

    filteredProducers.value = filtered;
  },
  { immediate: true, deep: true },
);

// lifecycle hook equivalent
onMounted(() => {
  retreiveProducers(page.value);
});

// methods
const getCategories = async (supplier_id) => {
  try {
    const response = await api.get(`suppliers/${supplier_id}/categories?per_page=999999999`);
    store.commit("product_wizard/set_selected_producer_categories", response.data);
  } catch (error) {
    handleError(error);
  }
};

const next = async (producer) => {
  store.commit("product_wizard/set_selected_producer", producer);
  store.commit("compare/set_flag", "add");

  try {
    await getCategories(producer.id);
    store.commit("product_wizard/set_wizard_component", "PrintProductCategorySearch");
  } catch (error) {
    handleError(error);
  }
};

const retreiveProducers = async (newPage) => {
  await fetchProducers(newPage)
    .then((result) => {
      producers.value = result.data;
      // Sort producers to show accepted handshakes first
      producers.value = result.data.sort((a, b) => {
        if (a.handshake === "accepted" && b.handshake !== "accepted") return -1;
        if (a.handshake !== "accepted" && b.handshake === "accepted") return 1;
        return 0;
      });
      filteredProducers.value = producers.value;
      producerPagination.value = result.meta;
      perPage.value = producerPagination.value.perPage;
      page.value = producerPagination.value.currentPage;
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
    });
};
</script>
