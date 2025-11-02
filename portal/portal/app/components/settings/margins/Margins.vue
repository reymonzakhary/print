<template>
  <div>
    <div v-if="margins.length > 0">
      <div v-for="(margin, index) in margins" :key="index" class="flex flex-wrap">
        <template v-if="margin.status == 1">
          <!-- Margins Modes -->
          <MarginsMode :margin="margin" @save-margins="$emit('save-margins', $event)" />

          <!-- Margin slots -->
          <p class="flex mt-4 text-xs font-bold tracking-wide uppercase">
            {{ $t("slots") }}
          </p>
          <div
            class="flex flex-wrap items-stretch w-full space-x-0 space-y-2 sm:space-x-2 sm:space-y-0"
          >
            <div
              v-for="(slot, i) in margin.slots"
              :key="i"
              class="w-full sm:w-1/3 md:1/6 lg:w-1/3 xl:w-2/12"
            >
              <MarginsSlot
                :margin="margin"
                :slot-data="slot"
                :i="i"
                :index="index"
                @save-margins="$emit('save-margins', $event)"
              />
            </div>

            <transition name="fade">
              <div
                v-if="
                  margin.status == true &&
                  ((!runInfinityCheck && margin.mode === 'run') ||
                    (!priceInfinityCheck && margin.mode === 'price'))
                "
                class="flex flex-col items-center w-full p-2 text-center bg-gray-200 rounded cursor-pointer sm:w-1/3 md:1/6 lg:w-1/3 xl:w-2/12 dark:bg-gray-900 group"
                @click="addMargin(index)"
              >
                <p class="font-bold text-gray-400 text-xm">
                  {{ $t("add another margin") }}
                </p>

                <font-awesome-icon
                  :icon="['fad', 'hand-holding-dollar']"
                  class="my-6 text-gray-400 fa-4x"
                />
                <button
                  class="px-2 py-1 text-sm transition border rounded-full text-theme-500 border-theme-500 hover:bg-theme-200"
                >
                  <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
                  {{ $t("add margin") }}
                </button>
              </div>
            </transition>
          </div>
        </template>
      </div>
    </div>

    <!-- No margins yet, create new margin -->
    <div v-else>
      <div class="w-full sm:w-1/4 md:1/6 lg:w-2/6 xl:w-2/12">
        <div
          class="flex flex-col items-center p-2 my-4 text-center bg-gray-200 rounded cursor-pointer dark:bg-gray-900 group"
          @click="newMargin()"
        >
          <p class="font-bold text-gray-400 text-xm">
            {{ $t("add new margin") }}
          </p>

          <font-awesome-icon :icon="['fad', 'hand-holding-dollar']" class="my-4 text-gray-400 fa-4x" />

          <button
            class="px-2 py-1 text-sm transition border rounded-full text-theme-500 border-theme-500 hover:bg-theme-200"
          >
            <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
            {{ $t("add margin") }}
          </button>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end w-full pr-4 mx-auto mt-4 lg:w-2/3 lg:pr-0">
      <button
        class="px-2 py-1 mx-1 text-white transition-colors duration-75 bg-green-500 rounded-full hover:bg-green-600"
        @click="$emit('saveMargins', margins)"
      >
        {{ $t("save") }} {{ $t("margins") }}
      </button>
    </div>
  </div>
</template>

<script>
import marginsMixin from "~/components/settings/margins/marginsMixin";

export default {
  mixins: [marginsMixin],
  props: {
    getMargins: {
      type: Function,
      required: true,
    },
  },
  emits: ["saveMargins", "save-margins"],
  created() {
    this.getMargins();
  },
};
</script>
