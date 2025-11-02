<template>
  <div class="p-4">
    <header class="relative flex flex-wrap justify-between font-bold tracking-wide uppercase">
      <div class="w-full text-left sm:w-1/3">
        <button class="text-theme-500 hover:text-theme-700" @click="back">
          <font-awesome-icon :icon="['fal', 'chevron-left']" />
          {{ $t("back") }}
        </button>
      </div>

      <div class="w-full my-4 text-center sm:w-1/3 sm:my-0">
        <font-awesome-icon :icon="['fal', 'box-full']" />
        <font-awesome-icon :icon="['fal', 'tag']" class="text-xs" />
        {{ $t("adding new category") }}
        <span class="font-normal"> - {{ $t("step") + " " + step }}</span>
      </div>

      <div class="w-full text-center sm:w-1/3">
        <div class="flex">
          <div class="flex-1">
            <div
              class="flex items-center w-6 h-6 mx-auto text-sm rounded-full"
              :class="step >= 1 ? 'bg-theme-300' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 1 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'search']" />
              </span>
            </div>
          </div>

          <div class="flex items-center content-center w-1/6 align-middle align-center">
            <div class="items-center flex-1 w-full align-middle rounded bg-theme-100 align-center">
              <div
                class="h-1 text-xs leading-none text-center rounded bg-gradient-to-r from-theme-300 to-theme-400"
                :style="step == 1 ? 'width: 50%' : step > 1 ? 'width: 100%' : 'width: 0%'"
              ></div>
            </div>
          </div>

          <div class="flex-1">
            <div
              class="flex items-center w-6 h-6 mx-auto text-sm rounded-full"
              :class="step >= 2 ? 'bg-theme-400' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 2 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'box-open']" />
              </span>
            </div>
          </div>

          <div class="flex items-center content-center w-1/6 align-middle align-center">
            <div class="items-center flex-1 w-full align-middle rounded bg-theme-100 align-center">
              <div
                class="h-1 text-xs leading-none text-center rounded bg-gradient-to-r from-theme-400 to-theme-500"
                :style="step == 2 ? 'width: 50%' : step > 2 ? 'width: 100%' : 'width: 0%'"
              ></div>
            </div>
          </div>

          <div class="flex-1">
            <div
              class="flex items-center w-6 h-6 mx-auto text-sm rounded-full"
              :class="step >= 3 ? 'bg-theme-400' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 3 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'chart-network']" />
              </span>
            </div>
          </div>

          <div class="flex items-center content-center w-1/6 align-middle align-center">
            <div class="items-center flex-1 w-full align-middle rounded bg-theme-100 align-center">
              <div
                class="h-1 text-xs leading-none text-center text-gray-500 rounded bg-gradient-to-r from-theme-500 to-green-500"
                :style="step == 3 ? 'width: 50%' : step > 3 ? 'width: 100%' : 'width: 0%'"
              ></div>
            </div>
          </div>

          <div class="flex-1">
            <div
              class="flex items-center w-6 h-6 mx-auto text-sm rounded-full"
              :class="step >= 4 ? 'bg-green-500' : 'bg-gray-300 '"
            >
              <span class="w-full text-center" :class="step >= 4 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'box-full']" />
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>
  </div>
</template>

<script>
import mixin from "~/components/assortment/add_products/customProductMixin.js";

export default {
  mixins: [mixin],
  props: {
    to_component: {
      type: String,
      required: true,
    },
    step: {
      type: Number,
      required: true,
    },
  },
  methods: {
    back() {
      if (this.step === 2) {
        if (
          confirm(
            // prettier-ignore
            this.$t("are you sure? You will have to remove this category and start over if you wish "),
          )
        ) {
          this.set_component(this.to_component);
        } else {
          // do nothing
          return;
        }
      }
      this.set_component(this.to_component);
    },
  },
};
</script>
