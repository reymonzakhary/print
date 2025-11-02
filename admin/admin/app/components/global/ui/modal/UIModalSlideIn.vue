<template>
  <Teleport to="body">
    <div v-show="showElement" class="absolute top-0 h-screen w-screen z-[9999] overflow-hidden">
      <Transition name="fadeIn">
        <div
          v-if="show"
          class="absolute w-full h-full cursor-pointer bg-black/25 dark:bg-black/75"
          @click.self="$emit('onBackdropClick', $event)"
        />
      </Transition>
      <Transition name="slideIn">
        <div
          v-if="show"
          class="flex absolute top-0 right-0 flex-col h-screen bg-white rounded-l dark:bg-gray-800"
          v-bind="$attrs"
        >
          <UICardHeader
            class="px-4 !bg-gray-200 dark:!bg-gray-900 !text-gray-800 dark:text-gray-200 rounded-tr-none"
            @on-close="$emit('onClose', $event)"
          >
            <template #left>
              <UICardHeaderTitle :icon="icon" :title="title" class="dark:text-themecontrast-900" />
            </template>
            <template #right>
              <UIButton
                :icon="['fas', 'times']"
                variant="neutral"
                class="!h-5 !text-gray-300"
                @click="$emit('onClose', $event)"
              />
            </template>
          </UICardHeader>
          <div class="overflow-y-auto h-full">
            <slot />
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: "SlideInModal",
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: "",
    },
    icon: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["onBackdropClick", "onClose"],
  data() {
    return {
      showElement: false,
    };
  },
  watch: {
    show: {
      handler(show) {
        if (show) {
          this.showElement = true;
        } else {
          setTimeout(() => {
            this.showElement = false;
          }, 300);
        }
      },
      immediate: true,
    },
  },
  mounted() {
    document.addEventListener("keyup", this.onKeyUp);
  },
  beforeUnmount() {
    document.removeEventListener("keyup", this.onKeyUp);
  },
  methods: {
    onKeyUp(event) {
      if (event.key === "Escape") {
        this.$emit("onClose", event);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.slideIn-enter-active {
  animation: slide-in 0.3s ease-out;
  animation-fill-mode: forwards;
}

.slideIn-leave-active {
  animation: slide-in 0.3s reverse;
  animation-fill-mode: forwards;
}

.fadeIn-enter-active {
  animation: fade-in 0.3s ease-out;
  animation-fill-mode: forwards;
}

.fadeIn-leave-active {
  animation: fade-in 0.3s reverse;
  animation-fill-mode: forwards;
}

@keyframes slide-in {
  0% {
    opacity: 0.75;
    transform: translateX(100%);
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fade-in {
  0% {
    opacity: 0;
  }

  100% {
    opacity: 1;
  }
}
</style>
