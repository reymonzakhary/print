<template>
  <Teleport to="body">
    <div tabindex="0" @keyup.esc.stop="close()">
      <!-- card wrapper -->
      <div
        :style="`z-index: ${zIndex}`"
        class="fixed left-0 top-0 flex h-screen w-screen cursor-pointer items-center justify-center"
        @click.self="closeButton && close()"
      >
        <slot name="modal-wrapper-start" />
        <!-- card component -->
        <transition appear name="fade">
          <div
            class="flex max-h-screen cursor-default flex-col rounded-lg bg-white shadow-xl dark:bg-gray-700"
            :class="[classes, { 'overflow-y-visible': noScroll, 'overflow-y-auto': !noScroll }]"
            role="dialog"
            aria-labelledby="modalTitle"
            aria-describedby="modalDescription"
          >
            <!-- header -->
            <header
              id="modalTitle"
              class="sticky top-0 rounded-t-lg bg-gray-200 p-2 pr-8 dark:bg-gray-800 dark:text-white"
            >
              <!-- dynamic header -->
              <slot name="modal-header" />
              <button
                v-if="closeButton === true"
                class="absolute right-0 top-0 mr-3 mt-3 flex items-center justify-center transition-colors hover:text-gray-700"
                title="Close modal"
                aria-label="close"
                @click="close()"
              >
                <font-awesome-icon :icon="['fad', 'circle-xmark']" aria-hidden="true" />
              </button>
            </header>

            <!-- body -->
            <section
              id="modalDescription"
              :class="['h-full flex-1 p-4 text-gray-700 dark:text-gray-400', bodyClasses]"
            >
              <!-- dynamic content ... -->
              <slot name="modal-body" />
            </section>

            <!-- footer -->
            <footer
              v-if="!noFooter"
              class="sticky bottom-0 flex items-center justify-end rounded-b-lg p-2 backdrop-blur-md"
            >
              <slot v-if="cancelButton" name="cancel-button">
                <button
                  class="mr-2 rounded-full bg-gray-300 px-4 py-1 text-sm capitalize transition-colors hover:bg-gray-400 dark:bg-gray-800 dark:text-white dark:hover:bg-black"
                  :aria-label="$t('cancel')"
                  @click="close()"
                >
                  {{ $t("cancel") }}
                </button>
              </slot>

              <!-- dynamic confirm button -->
              <slot name="confirm-button" />
            </footer>
          </div>
        </transition>
        <slot name="modal-wrapper-end" />
      </div>

      <!-- background -->
      <transition appear name="fade">
        <div
          :style="`z-index: ${zIndex - 1}`"
          class="firefox:bg-opacity-75 fixed left-0 top-0 h-screen w-screen bg-black bg-opacity-50 backdrop-blur-sm"
        />
      </transition>
    </div>
  </Teleport>
</template>

<script>
export default {
  props: {
    classes: {
      type: [String, Object],
      default: "w-11/12 sm:w-1/2 lg:w-1/3 xl:w-1/4",
    },
    bodyClasses: {
      type: [String, Object],
      default: "",
      required: false,
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
    noFooter: {
      type: Boolean,
      default: false,
      required: false,
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
