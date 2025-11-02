<template>
  <div class="relative">
    <div v-if="unmatchedItem.percentage && !unlinked" class="mb-4 z-10">
      <section class="flex">
        <div class="w-1/2 p-4 py-10 text-sm border-r">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">
            {{ unmatchedItem.tenant_name }}
          </p>
          <p class="font-bold tracking-wide text-gray-700 dark:text-gray-400 mt-2 uppercase">
            {{ type }}
          </p>
          <span class="font-bold text-4xl block dark:text-white">{{ unmatchedItem.name }}</span>
          <p
            class="mt-4 font-bold tracking-wide uppercase block"
            :class="{
              'bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 inline-block text-transparent bg-clip-text':
                unmatchedItem.percentage && unmatchedItem.percentage >= 85,
              'bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-500 inline-block text-transparent bg-clip-text':
                unmatchedItem.percentage && unmatchedItem.percentage <= 85,
            }"
          >
            {{ Math.round((unmatchedItem.percentage + Number.EPSILON) * 100) / 100 }}% similarity
          </p>
        </div>
        <!-- <div class="w-1/2 p-4 py-10 bg-gray-100">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">context</p>
          <span v-if="item.name">
            <p class="mt-2 font-bold">Category:</p>
            <p>{{ category.name }}</p>
          </span>
          <span v-else>
            <p class="mt-2">
              This is a category, so we are unable to display the relational context
            </p>
          </span>
          <span v-if="box.name">
            <p class="mt-2 font-bold">Box:</p>
            <p>{{ box.name }}</p>
          </span>
        </div> -->
      </section>

      <UICardHeader
        :title="`Matched ${type}`"
        :subtitle="`Matched ${type} with ${item.tenant_name}'s ${item.name}`"
        :use-tabs="true"
        class="max-h-[42px] !rounded-none"
      >
        <template #left>
          <UICardHeaderTitle title="Attach with" :icon="['fal', 'paperclip']" />
        </template>
        <template #center>
          <div class="flex w-full flex-col md:w-auto md:flex-row">
            <UICardHeaderTab
              label="Auto matched"
              :icon="['fal', 'bolt-auto']"
              :active="activeMatchUi === 'auto'"
              @click="activeMatchUi = 'auto'"
            />
            <UICardHeaderTab
              label="Prindustry standard"
              :icon="['fal', 'hand-holding-box']"
              :active="activeMatchUi === 'standard'"
              @click="activeMatchUi = 'standard'"
            />
            <UICardHeaderTabSeperator class="mx-2" />
            <UICardHeaderTab
              label="Add as new"
              :icon="['fal', 'sparkles']"
              :active="activeMatchUi === 'new'"
              @click="activeMatchUi = 'new'"
            />
          </div>
        </template>
      </UICardHeader>

      <div
        class="relative flex flex-wrap w-full bg-gray-100 dark:bg-gray-800 p-4 pt-6 pb-10 overflow-hidden min-h-[400px]"
      >
        <PrindustryLogo
          class="absolute -right-12 top-1/2 -translate-y-1/2 text-gray-200 dark:text-gray-900 z-0 h-[750px]"
        />
        <Transition name="fade" mode="out-in">
          <MatchingAuto
            v-if="activeMatchUi === 'auto'"
            :type="type"
            :match="unmatchedItem"
            match-type="matches"
            :item="item"
            class="w-full py-10 z-10"
            @close="close"
          />
        </Transition>

        <Transition name="fade" mode="out-in">
          <MatchingSelect
            v-if="activeMatchUi === 'standard'"
            :type="type"
            :match="unmatchedItem"
            match-type="matches"
            class="w-full py-10 z-10"
            @close="close"
          />
        </Transition>

        <Transition name="fade" mode="out-in">
          <MatchingNew
            v-if="activeMatchUi === 'new'"
            :type="type"
            :match="unmatchedItem"
            match-type="matches"
            class="w-full py-10 z-10"
            @on-add="close"
          />
        </Transition>
      </div>
    </div>

    <div v-if="!unmatchedItem.percentage && !unlinked" class="mb-4 z-10">
      <section class="flex">
        <div class="w-1/2 p-4 py-10 text-sm border-r">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">
            {{ unmatchedItem.tenant_name }}
          </p>
          <p class="font-bold tracking-wide text-gray-700 dark:text-gray-400 mt-2 uppercase">
            {{ type }}
          </p>
          <span class="font-bold text-4xl dark:text-white">{{ unmatchedItem.name }}</span>
          <p class="mt-4 font-bold tracking-wide text-red-500 uppercase">Unmatched!</p>
        </div>
        <!-- <div class="w-1/2 p-4 py-10 bg-gray-100">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">context</p>
          <span v-if="category.name">
            <p class="mt-2 font-bold">Category:</p>
            <p>{{ category.name }}</p>
          </span>
          <span v-else>
            <p class="mt-2">
              This is a category, so we are unable to display the relational context
            </p>
          </span>
          <span v-if="box.name">
            <p class="mt-2 font-bold">Box:</p>
            <p>{{ box.name }}</p>
          </span>
        </div> -->
      </section>

      <UICardHeader
        :title="`Matched ${type}`"
        :subtitle="`Matched ${type} with ${item.tenant_name}'s ${item.name}`"
        :use-tabs="true"
        class="max-h-[42px] !rounded-none"
      >
        <template #left>
          <UICardHeaderTitle title="Attach with" :icon="['fal', 'paperclip']" />
        </template>
        <template #center>
          <div class="flex w-full flex-col md:w-auto md:flex-row">
            <UICardHeaderTab
              v-tooltip="'This item is unmatched'"
              label="Auto matched"
              :icon="['fal', 'bolt-auto']"
              :active="activeUnmatchUi === 'auto'"
              disabled
              @click="activeUnmatchUi = 'auto'"
            />
            <UICardHeaderTab
              label="Prindustry standard"
              :icon="['fal', 'hand-holding-box']"
              :active="activeUnmatchUi === 'standard'"
              @click="activeUnmatchUi = 'standard'"
            />
            <UICardHeaderTabSeperator class="mx-2" />
            <UICardHeaderTab
              label="Add as new"
              :icon="['fal', 'sparkles']"
              :active="activeUnmatchUi === 'new'"
              @click="activeUnmatchUi = 'new'"
            />
          </div>
        </template>
      </UICardHeader>

      <div
        class="relative flex flex-wrap w-full bg-gray-100 dark:bg-gray-800 p-4 pt-6 pb-10 overflow-hidden min-h-[400px]"
      >
        <PrindustryLogo
          class="absolute -right-12 top-1/2 -translate-y-1/2 text-gray-200 dark:text-gray-900 z-0 h-[750px]"
        />
        <Transition name="fade" mode="out-in">
          <MatchingSelect
            v-if="activeUnmatchUi === 'standard'"
            :type="type"
            :match="unmatchedItem"
            match-type="unmatched"
            class="w-full py-10 z-10"
            @close="close"
          />
        </Transition>
        <Transition name="fade" mode="out-in">
          <MatchingNew
            v-if="activeUnmatchUi === 'new'"
            :items="items"
            :match="unmatchedItem"
            :type="type"
            match-type="unmatched"
            class="w-full py-10 z-10"
            @on-add="close"
          />
        </Transition>
      </div>
    </div>

    <div v-if="unlinked" class="mb-4 z-10">
      <section class="flex">
        <div class="w-1/2 p-4 py-10 text-sm border-r">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">
            {{ unmatchedItem.tenant_name }}
          </p>
          <p class="font-bold tracking-wide text-gray-700 dark:text-gray-400 mt-2 uppercase">
            {{ type }}
          </p>
          <span class="font-bold text-4xl dark:text-white">{{ unmatchedItem.name }}</span>
          <p class="mt-4 font-bold tracking-wide text-purple-500 uppercase">Unlinked!</p>
        </div>
        <!-- <div class="w-1/2 p-4 py-10 bg-gray-100">
          <p class="text-xs font-bold tracking-wide text-gray-500 uppercase">context</p>
          <span v-if="category.name">
            <p class="mt-2 font-bold">Category:</p>
            <p>{{ category.name }}</p>
          </span>
          <span v-else>
            <p class="mt-2">
              This is a category, so we are unable to display the relational context
            </p>
          </span>
          <span v-if="box.name">
            <p class="mt-2 font-bold">Box:</p>
            <p>{{ box.name }}</p>
          </span>
        </div> -->
      </section>

      <UICardHeader
        :title="`Matched ${type}`"
        :subtitle="`Matched ${type} with ${item.tenant_name}'s ${item.name}`"
        :use-tabs="true"
        class="max-h-[42px] !rounded-none"
      >
        <template #left>
          <UICardHeaderTitle title="Attach with" :icon="['fal', 'paperclip']" />
        </template>
        <template #center>
          <div class="flex w-full flex-col md:w-auto md:flex-row">
            <UICardHeaderTab
              v-tooltip="'This item is unmatched'"
              label="Auto matched"
              :icon="['fal', 'bolt-auto']"
              :active="activeUnmatchUi === 'auto'"
              disabled
              @click="activeUnmatchUi = 'auto'"
            />
            <UICardHeaderTab
              label="Prindustry standard"
              :icon="['fal', 'hand-holding-box']"
              :active="activeUnmatchUi === 'standard'"
              @click="activeUnmatchUi = 'standard'"
            />
            <UICardHeaderTabSeperator class="mx-2" />
            <UICardHeaderTab
              label="Add as new"
              :icon="['fal', 'sparkles']"
              :active="activeUnmatchUi === 'new'"
              @click="activeUnmatchUi = 'new'"
            />
          </div>
        </template>
      </UICardHeader>

      <div
        class="relative flex flex-wrap w-full bg-gray-100 dark:bg-gray-800 p-4 pt-6 pb-10 overflow-hidden min-h-[400px]"
      >
        <PrindustryLogo
          class="absolute -right-12 top-1/2 -translate-y-1/2 text-gray-200 dark:text-gray-900 z-0 h-[750px]"
        />
        <Transition name="fade" mode="out-in">
          <MatchingSelect
            v-if="activeUnmatchUi === 'standard'"
            :type="type"
            :match="unmatchedItem"
            match-type="suppliers"
            class="w-full py-10 z-10"
            @close="close"
          />
        </Transition>
        <Transition name="fade" mode="out-in">
          <MatchingNew
            v-if="activeUnmatchUi === 'new'"
            :items="items"
            :match="unmatchedItem"
            :type="type"
            match-type="suppliers"
            class="w-full py-10 z-10"
            @on-add="close"
          />
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
// import { useStore } from "vuex";
// import match from "../../mixins/match"
import MatchingAuto from "./inputs/MatchingAuto.vue";
import MatchingSelect from "./inputs/MatchingSelect.vue";
import MatchingNew from "./inputs/MatchingNew.vue";
import PrindustryLogo from "./../global/PrindustryLogo.vue";
import _ from "lodash";

// const store = useStore();
const emit = defineEmits(["close"]);

const props = defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
  unmatchedItem: {
    type: Object,
    default: () => ({}),
  },
  items: {
    type: [Array, Object],
    default: () => [],
  },
  box: {
    type: Object,
    default: () => null,
  },
  type: {
    type: String,
    default: "",
  },
  unlinked: {
    type: Boolean,
    default: false,
  },
});

const activeMatchUi = ref("auto");
const activeUnmatchUi = ref("standard");

// const matches = computed(() => {
//   if (props.item.matches) {
//     return _.orderBy(props.item.matches, "similarity.percentage", "desc");
//   }
//   return null;
// });

const close = () => {
  emit("close");
  //   store.commit("standardization/set_category_unmatched", false);
  //   store.commit("standardization/set_box_unmatched", false);
  //   store.commit("standardization/set_option_unmatched", false);
};
</script>

<style>
.line1 {
  width: 60px;
  height: 40px;
  @apply border-b-2 border-gray-500 border-opacity-75;
  -webkit-transform: translateY(-52px) translateX(5px) rotate(45deg);
  position: absolute;
}

.line2 {
  width: 60px;
  height: 40px;
  @apply border-b-2 border-blue-500 border-opacity-75;
  -webkit-transform: translateY(-10px) translateX(-22px) rotate(-45deg);
  position: absolute;
}
</style>
