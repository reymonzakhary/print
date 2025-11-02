<template>
  <section>
    <transition name="fade" appear class="overflow-auto">
      <div
        v-show="show"
        class="firefox:bg-opacity-75 fixed left-0 right-0 top-0 z-50 h-screen w-screen bg-black bg-opacity-50 backdrop-blur-sm"
        @click="close"
      />
    </transition>
    <transition :name="transition" appear class="overflow-auto">
      <article
        v-if="show"
        class="fixed right-0 z-50 h-full overflow-auto rounded-l bg-white text-black shadow-md dark:bg-gray-700 dark:text-theme-100"
        :class="[width, !fullHeight ? 'top-8' : 'top-0']"
        :style="!fullHeight ? 'max-height: calc(100vh - 4rem)' : ' '"
      >
        <!-- header -->
        <header class="">
          <!-- dynamic header -->
          <slot name="side-panel-header" />

          <button
            class="absolute right-0 top-0 mr-3 mt-3 flex items-center justify-center text-theme-900 hover:text-theme-500"
            aria-label="close"
            @click="close"
          >
            <font-awesome-icon :icon="['fad', 'circle-xmark']" />
          </button>
        </header>

        <section>
          <!-- dynamic content ... -->
          <slot name="side-panel-content" />
        </section>

        <!-- footer -->
        <footer class="relative">
          <!-- dynamic footer -->
          <slot name="side-panel-footer" />
        </footer>
      </article>
    </transition>
  </section>
</template>

<script>
export default {
  props: {
    width: {
      type: String,
      default: "w-full md:w-1/2",
    },
    transition: {
      type: String,
      default: "slideleftlarge",
    },
    fullHeight: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onClose"],
  data() {
    return {
      show: false,
    };
  },
  created() {
    this.show = true;
  },
  methods: {
    close() {
      this.show = false;
      this.$emit("onClose");
    },
  },
};
</script>
