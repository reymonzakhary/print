<template>
  <div class="h-full">
    <UICardHeader class="z-30 mt-4 max-h-[42px] rounded backdrop-blur">
      <template #center>
        <div class="flex">
          <div class="mx-auto flex">
            <UICardHeaderTab
              :icon="['fal', 'box-open']"
              :label="$t('boxes')"
              :active="active_nav === 'boxes'"
              @click="active_nav = 'boxes'"
            />
            <UICardHeaderTab
              :icon="['fal', 'shapes']"
              :label="$t('options')"
              :active="active_nav === 'options'"
              @click="active_nav = 'options'"
            />
          </div>
        </div>
      </template>

      <template #right>
        <UIButton :icon="['fal', 'sync']" @click="refresh">
          {{ $t("Refresh") }}
        </UIButton>
      </template>
    </UICardHeader>

    <section
      class="z-0 m-4 mx-auto flex max-h-[calc(100vh-10rem)] flex-grow flex-col rounded bg-white pb-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900 md:w-5/12"
    >
      <UICardHeader class="sticky top-0 z-10">
        <template #left>
          <UICardHeaderTitle :title="listTitle" />
        </template>
        <template #center>
          <UIInputText
            v-if="active_nav === 'boxes'"
            v-model="boxNavigation.query"
            name="query"
            :icon="['fal', 'filter']"
            :placeholder="`${$t('Search')} ${listTitle}`"
          />
          <UIInputText
            v-if="active_nav === 'options'"
            v-model="optionNavigation.query"
            name="query"
            :icon="['fal', 'filter']"
          />
        </template>
        <template v-if="active_nav === 'boxes'" #right>
          <div class="flex items-center gap-2">
            <span class="font-bold uppercase dark:text-theme-100">{{ $t("show") }}</span>
            <UIVSelect
              v-model="boxNavigation.perPage"
              :options="[10, 20, 50, 100]"
              class="w-16 text-xs"
            />
          </div>
        </template>
      </UICardHeader>

      <Boxes v-if="active_nav === 'boxes'" class="h-full overflow-y-auto" :items="boxes" />
      <Options v-if="active_nav === 'options'" class="h-full overflow-y-auto" :items="options" />

      <UIPagination
        v-if="active_nav === 'boxes' && boxNavigation.lastPage > 1"
        :total-pages="boxNavigation.lastPage"
        :current-page="boxNavigation.currentPage"
        :visible-pages-count="3"
        manual-page-change
        no-page-jump
        class="p-2 px-4 text-sm"
        @update:page="boxNavigation.currentPage = $event"
      />
      <UIPagination
        v-if="active_nav === 'options' && optionNavigation.lastPage > 1"
        :total-pages="optionNavigation.lastPage"
        :current-page="optionNavigation.currentPage"
        :visible-pages-count="3"
        manual-page-change
        no-page-jump
        class="p-2 px-4 text-sm"
        @update:page="optionNavigation.currentPage = $event"
      />
    </section>
  </div>
</template>

<script setup>
import { useStore } from "vuex";

const { t: $t } = useI18n();

/**
 * Pagination
 */
const boxNavigation = ref({
  perPage: 20,
  lastPage: 0,
  total: 0,
  query: "",
  currentPage: 1,
});
const optionNavigation = ref({
  perPage: 20, // Capped at always 20 by the back-end.
  lastPage: 0,
  total: 0,
  query: "",
  currentPage: 1,
});

/**
 * Boxes
 */
const { data: boxes, refresh: refreshBoxes } = useLazyAPI("/boxes", {
  query: computed(() => ({
    per_page: boxNavigation.value.perPage,
    page: boxNavigation.value.currentPage,
    filter: boxNavigation.value.query,
  })),
  default: () => [],
  transform: ({ data }) => data,
  onResponse: ({ response }) => {
    boxNavigation.value.total = response._data.meta.total;
    boxNavigation.value.lastPage = response._data.meta.last_page;
  },
});

/**
 * Options
 */
const { data: options, refresh: refreshOptions } = useLazyAPI("/options", {
  query: computed(() => ({
    per_page: optionNavigation.value.perPage,
    page: optionNavigation.value.currentPage,
    filter: optionNavigation.value.query,
  })),
  transform: ({ data }) => data,
  default: () => [],
  onResponse: ({ response }) => {
    optionNavigation.value.total = response._data.meta.total;
    optionNavigation.value.lastPage = response._data.meta.last_page;
  },
  immediate: false,
});

// Refresh method for both api calls
const refresh = () => (active_nav.value === "boxes" ? refreshBoxes() : refreshOptions());

/**
 * Tab Navigation
 */
const active_nav = ref("boxes");
watch(active_nav, refresh);

// List title
const listTitle = computed(() => (active_nav.value === "boxes" ? $t("boxes") : $t("options")));

/**
 * No clue why this is but
 * it was in the legacy code so
 * I'm keeping it here
 */
const store = useStore();
store.commit("product_wizard/set_selected_category", {});
</script>
