<template>
  <div>
    <section class="container py-10">
      <ProductFinderHeader class="w-full">
        <div class="mx-auto w-full xl:w-8/12">
          <h1 class="mb-2 text-4xl font-bold md:text-3xl">
            {{ $t("Find producers for your business") }}
          </h1>
          <p class="mb-4 hidden w-6/12 text-gray-500 md:block">
            {{
              // prettier-ignore
              $t("Request handshakes to be able to order products from these producers in the productfinder!")
            }}
          </p>
        </div>
        <div class="mx-auto flex w-auto items-center xl:w-8/12">
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

      <!-- <MarketPlaceUIRadioGroup
        v-model="activeView"
        :options="[
          { value: 'handshake', icon: ['fal', 'handshake'] },
          { value: 'no-handshake', icon: ['fal', 'hand-holding-hand'] },
          { value: 'both', label: $t('all') },
        ]"
        class="col-span-5 ml-auto mt-2 flex items-center justify-end md:col-span-1 md:mt-0"
      /> -->
    </section>

    <section v-if="isLoading" class="container grid grid-cols-5 gap-x-4">
      <div>
        <UIListSkeleton
          :key="'skeleton1'"
          class="w-32"
          :skeleton-line-height="4"
          :skeleton-line-amount="1"
        />
        <UIListSkeleton :key="'skeleton1'" :skeleton-line-height="8" :skeleton-line-amount="3" />
      </div>

      <div class="col-span-5 col-start-1 lg:col-span-4 2xl:col-span-3">
        <UIListSkeleton
          :key="'skeleton1'"
          class="w-32"
          :skeleton-line-height="4"
          :skeleton-line-amount="1"
        />
        <UIListSkeleton :key="'skeleton2'" :skeleton-line-height="10" :skeleton-line-amount="5" />
      </div>
    </section>

    <Transition name="fade">
      <ProducerZeroState
        v-show="emptyResult && zeroStateMsg && !isLoading"
        :message="zeroStateMsg"
      />
    </Transition>

    <main v-if="!emptyResult" class="mt-4 grid grid-cols-5 gap-x-8">
      <aside class="">
        <!-- <h2 class="font-bold tracking-wide uppercase">{{ $t("Filters") }}</h2>
        <ProducerFilter
          :filter="filter"
          @select-producer="handleSelectProducer"
          @select-view="emit('update:activeView', $event)"
        /> -->
      </aside>
      <ul class="col-span-5 col-start-1 lg:col-span-4 2xl:col-span-3">
        <h2 class="font-bold uppercase tracking-wide">
          {{ $t("Producers") }}
          <span class="ml-4 text-sm font-normal normal-case text-gray-600">
            <b> {{ filteredProducers.length }}</b>
            {{ $t("results") }}
          </span>
        </h2>

        <transition-group name="fade">
          <li v-for="(producer, index) in filteredProducers" :key="`user_${index}`">
            <ProducerSingle
              :key="producer.uuid"
              :producer="producer"
              :search="filter"
              class="my-6"
              :style="{ 'z-index': filteredProducers.length - index }"
              :selected="selectedProducer?.id === producer.id"
              :loading-categories="loadingCategories"
              :shared-categories="sharedCategories"
              @cleanup-shared-categories="((sharedCategories = []), (loadingCategories = true))"
              @select-producer="handleSelectProducer"
              @clicked-producer-details="
                navigateTo(
                  `/marketplace/producer-finder/${producer.name}?id=${producer.website_id}`,
                )
              "
            />
          </li>
        </transition-group>

        <li v-if="producerPagination.lastPage > 1" class="sticky bottom-0">
          <UIPagination
            class="mx-auto"
            :current-page="producerPagination.currentPage"
            :total-pages="producerPagination.lastPage"
            @update:page="(newPage) => retreiveProducers(newPage)"
          />
        </li>
      </ul>
    </main>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();

const { handleError } = useMessageHandler();
const { fetchProducers, fetchSharedCategories } = useMarketplaceRepository();

const activeView = ref("both");
const producers = ref([]);
const filteredProducers = ref([]);
const producerPagination = ref([]);
// const pages = ref([5, 10]);
const perPage = ref(5);
const page = ref(1);
const isLoading = ref(true);
const selectedProducer = ref(null);
const loadingCategories = ref(false);
const sharedCategories = ref([]);

const zeroStateMsg = computed(() => {
  if (filter.value !== null) return `${$t("No producers found with filter:")} ${filter.value}`;
  return $t("no producers found in this list!");
});

const emptyResult = computed(() => filteredProducers.value?.length === 0);
const filter = ref("");

watch(
  () => [filter.value, activeView.value],
  ([filterValue, activeView]) => {
    let filtered = producers.value;

    if (filterValue) {
      filtered = filtered.filter((producer) =>
        producer.producerInfo.company_name.toLowerCase().includes(filterValue.toLowerCase()),
      );
    }

    if (activeView === "handshake") {
      filtered = filtered.filter((producer) => producer.handshake === "accepted");
    } else if (activeView === "no-handshake") {
      filtered = filtered.filter((producer) => producer.handshake === "false");
    }

    filteredProducers.value = filtered;
  },
  { immediate: true, deep: true },
);

onMounted(() => {
  retreiveProducers(page.value);
});

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

      filteredProducers.value = result.data;
      producerPagination.value = result.meta;
      perPage.value = producerPagination.value.perPage;
      page.value = producerPagination.value.currentPage;
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
      loadingCategories.value = false;
    });
};

const handleSelectProducer = async (producer) => {
  selectedProducer.value = producer;
  try {
    loadingCategories.value = true;
    sharedCategories.value = await fetchSharedCategories(producer.website_id);
  } catch (error) {
    handleError(error);
  } finally {
    loadingCategories.value = false;
  }
};
</script>
