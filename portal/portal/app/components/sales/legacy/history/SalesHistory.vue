<template>
  <section>
    <div
      class="fixed left-0 top-0 z-50 h-screen w-screen bg-black opacity-75"
      @click="$emit('on-close')"
    />
    <transition name="slide-fade">
      <UILoader
        v-if="!slideHistory"
        class="-translate-1/2 absolute left-1/2 top-1/2 z-50 text-white"
      />
      <section
        v-else
        ref="historyContainer"
        class="history absolute right-0 top-1/2 z-50 h-[calc(100%_-_48px)] w-full -translate-y-1/2 overflow-auto rounded-l bg-white pb-4 pl-4 pr-4 dark:bg-gray-700 dark:text-white md:w-3/5"
      >
        <div
          class="sticky top-0 z-50 -mx-4 flex items-center justify-between rounded-t bg-white px-4 py-2"
        >
          <header class="flex items-center bg-white">
            <font-awesome-icon
              class="fa-xs mr-2 mt-[2px] aspect-square"
              :icon="['fas', 'clock-rotate-left']"
            />
            <p class="font-bold">{{ $t("event timeline") }}</p>
          </header>
          <UIButton variant="neutral-light" :icon="['fad', 'xmark']" @click="$emit('on-close')" />
        </div>
        <p v-if="!monthHistory" class="text-center text-gray-500">
          {{ $t("empty history") }}
        </p>
        <div v-if="monthHistory" ref="history" class="history-wrapper">
          <ul
            v-for="(value, month, i) in monthHistory"
            :key="i"
            class="relative mx-0 my-2 list-none p-0"
          >
            <div class="history-line absolute border-r border-theme-500" />
            <div class="sticky top-[47px] z-40 mb-4 w-full bg-white py-4">
              <h2
                class="ml-3 border-b border-b-theme-500 bg-white pb-1 text-sm font-bold uppercase text-theme-500"
              >
                {{ month }}
              </h2>
            </div>
            <li v-for="event in value" :key="event.id">
              <SalesHistoryItem :event="event" />
            </li>
          </ul>

          <font-awesome-icon
            v-if="loading"
            class="fa-spin fa-2x mx-auto my-5 block text-theme-500"
            :icon="['fad', 'spinner-third']"
          />
        </div>
      </section>
    </transition>
  </section>
</template>

<script>
export default {
  props: {
    salesId: {
      type: [Number, String],
      required: true,
    },
    salesType: {
      type: String,
      required: true,
    },
  },
  emits: ["on-close"],
  setup(props) {
    const { handleError } = useMessageHandler();
    const salesRepository =
      props.salesType === "quotation" ? useQuotationRepository() : useOrderRepository();
    return {
      handleError,
      salesRepository,
    };
  },
  data: () => ({
    loading: false,
    slideHistory: false,
    history: null,
    latestPage: 1,
  }),
  computed: {
    monthHistory() {
      if (this.history && Object.keys(this.history).length > 0) {
        return this.history;
      } else {
        return false;
      }
    },
  },
  watch: {
    slideHistory(val) {
      if (!val) {
      }
    },
  },
  async mounted() {
    try {
      const [data] = await this.salesRepository.getHistory({
        id: this.salesId,
        page: this.latestPage,
        perPage: 999999,
      });
      this.history = data;
      this.slideHistory = true;
    } catch (error) {
      this.handleError(error);
      this.$emit("on-close");
    }
  },
};
</script>

<style scoped>
.history .history-line {
  height: calc(100% - 48px);
  left: 119.5px;
  top: 45px;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.1s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter,
.slide-fade-leave-to {
  transform: translateX(300px);
  opacity: 0;
}
</style>
