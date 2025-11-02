<template>
  <Teleport to="body">
    <div tabindex="0" @keyup.esc.stop="close()">
      <!-- card wrapper -->
      <div
        :style="`z-index: ${zIndex}`"
        class="fixed top-0 left-0 flex items-center justify-center w-screen h-screen cursor-pointer"
        @click.self="closeButton && close()"
      >
        <!-- card component -->
        <div
          class="flex flex-col w-auto min-w-min max-h-screen bg-white rounded-lg shadow-xl cursor-default dark:bg-gray-700"
          :class="[classes, { 'overflow-y-visible': noScroll, 'overflow-y-auto': !noScroll }]"
          role="dialog"
          aria-labelledby="modalTitle"
          aria-describedby="modalDescription"
        >
          <!-- header -->
          <header
            id="modalTitle"
            class="sticky top-0 p-2 pr-8 bg-gray-200 rounded-t-lg dark:bg-gray-800 dark:text-white"
          >
            <!-- dynamic header -->
            <slot name="modal-header" />
            <button
              v-if="closeButton === true"
              class="absolute top-0 right-0 flex items-center justify-center mt-3 mr-3 transition-colors hover:text-gray-700"
              title="Close modal"
              aria-label="close"
              @click="close()"
            >
              <font-awesome-icon :icon="['fad', 'times-circle']" aria-hidden="true" />
            </button>
          </header>

          <!-- body -->
          <section id="modalDescription" class="h-full p-4 text-gray-700 dark:text-gray-400">
            <!-- dynamic content ... -->
            <slot name="modal-body" />
          </section>

          <!-- footer -->
          <footer
            class="sticky bottom-0 flex items-center justify-end p-2 rounded-b-lg backdrop-blur-md"
          >
            <slot v-if="cancelButton" name="cancel-button">
              <button
                class="px-4 py-1 mr-2 text-sm capitalize transition-colors bg-gray-300 rounded-full hover:bg-gray-400 dark:bg-gray-800 dark:text-white dark:hover:bg-black"
                aria-label="cancel"
                @click="close"
              >
                cancel
              </button>
            </slot>

            <!-- dynamic confirm button -->
            <slot name="confirm-button" />
          </footer>
        </div>
      </div>

      <!-- background -->
      <div
        :style="`z-index: ${zIndex - 1}`"
        class="fixed top-0 left-0 w-screen h-screen bg-black bg-opacity-50 firefox:bg-opacity-75 backdrop-blur-sm"
      />
    </div>
  </Teleport>
</template>

<script>
export default {
  props: {
    classes: {
      type: String,
      default: "w-11/12 sm:w-1/2 lg:w-1/3 xl:w-1/4",
    },
    closeButton: {
      type: Boolean,
      default: true,
      required: false,
    },
    cancelButton: {
      type: Boolean,
      default: true,
      required: false,
    },
    noScroll: {
      type: Boolean,
      default: false,
      required: false,
    },
    zIndex: {
      type: Number,
      default: 50,
      required: false,
      validator: (value) => value >= 50,
    },
  },
  emits: ["onClose"],
  methods: {
    close() {
      this.$emit("onClose");
    },
  },
};
</script>
