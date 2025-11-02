<template>
  <div class="py-4">
    <header class="relative flex flex-wrap justify-between uppercase tracking-wide">
      <div class="w-full text-left sm:w-1/3">
        <button class="text-theme-500 hover:text-theme-700" @click="back">
          <font-awesome-icon :icon="['fal', 'chevron-left']" />
          {{ $t("back") }}
        </button>
      </div>

      <div class="my-4 w-full text-center sm:my-0 sm:w-1/3">
        <font-awesome-icon :icon="['fal', 'photo-film-music']" />
        <font-awesome-icon :icon="['fal', 'tag']" class="text-xs" />
        <span class="font-bold">{{ $t("creating mediasource") }}</span>
      </div>

      <div class="w-full text-center sm:w-1/3">
        <div class="flex">
          <div class="flex-1">
            <div
              class="mx-auto flex h-6 w-6 items-center rounded-full text-sm"
              :class="step >= 1 ? 'bg-theme-300' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 1 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'tag']" />
              </span>
            </div>
          </div>

          <div class="align-center flex w-1/6 content-center items-center align-middle">
            <div class="align-center w-full flex-1 items-center rounded bg-theme-100 align-middle">
              <div
                class="h-1 rounded bg-gradient-to-r from-theme-300 to-theme-400 text-center text-xs leading-none"
                :style="step == 1 ? 'width: 50%' : step > 1 ? 'width: 100%' : 'width: 0%'"
              />
            </div>
          </div>

          <div class="flex-1">
            <div
              class="mx-auto flex h-6 w-14 items-center rounded-full text-sm"
              :class="step >= 3 ? 'bg-theme-500' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 3 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'hard-drive']" />
                <font-awesome-icon :icon="['fal', 'folder']" />
                <font-awesome-icon :icon="['fal', 'file']" />
              </span>
            </div>
          </div>

          <div class="align-center flex w-1/6 content-center items-center align-middle">
            <div class="align-center w-full flex-1 items-center rounded bg-theme-100 align-middle">
              <div
                class="h-1 rounded bg-gradient-to-r from-theme-400 to-theme-500 text-center text-xs leading-none"
                :style="step == 2 ? 'width: 50%' : step > 2 ? 'width: 100%' : 'width: 0%'"
              />
            </div>
          </div>

          <div class="flex-1">
            <div
              class="mx-auto flex h-6 w-6 items-center rounded-full text-sm"
              :class="step >= 3 ? 'bg-theme-500' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 3 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'key']" />
              </span>
            </div>
          </div>

          <div class="align-center flex w-1/6 content-center items-center align-middle">
            <div class="align-center w-full flex-1 items-center rounded bg-theme-100 align-middle">
              <div
                class="h-1 rounded bg-gradient-to-r from-theme-500 to-green-500 text-center text-xs leading-none"
                :style="step == 3 ? 'width: 50%' : step > 3 ? 'width: 100%' : 'width: 0%'"
              />
            </div>
          </div>

          <div class="flex-1">
            <div
              class="mx-auto flex h-6 w-6 items-center rounded-full text-sm"
              :class="step >= 4 ? 'bg-green-500' : 'bg-gray-300'"
            >
              <span class="w-full text-center" :class="step >= 4 ? 'text-white' : 'text-gray-500'">
                <font-awesome-icon :icon="['fal', 'photo-film-music']" />
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
import { mapMutations } from "vuex";

export default {
  mixins: [mixin],
  props: {
    to_page: {
      type: String,
      required: false,
    },
    to_component: {
      type: String,
      required: false,
    },
    step: {
      type: Number,
      required: true,
    },
  },
  methods: {
    ...mapMutations({
      setSelectedMS: "mediasource/setSelectedMS",
      setMSComponent: "mediasource/setMSComponent",
    }),
    back() {
      if (this.to_page) {
        this.$router.push(this.to_page);
      }
      if (this.to_component) {
        this.setMSComponent(this.to_component);
      }
    },
  },
};
</script>
