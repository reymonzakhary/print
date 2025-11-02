<template>
  <div>
    <div v-if="discounts.length > 0">
      <div v-for="(discount, index) in discounts" :key="discount.id" class="flex flex-wrap">
        <template v-if="discount.status == 1">
          <!-- Discounts Modes -->
          <DiscountsMode :discount="discount" @save-discounts="$emit('save-discounts', $event)" />

          <!-- Discount slots -->
          <p class="mt-4 flex text-xs font-bold uppercase tracking-wide">
            {{ $t("slots") }}
          </p>
          <div
            class="flex w-full flex-wrap items-stretch space-x-0 space-y-2 sm:space-x-2 sm:space-y-0"
          >
            <div
              v-for="(slot, i) in discount.slots"
              :key="i"
              class="md:1/6 w-full sm:w-1/3 lg:w-1/3 xl:w-2/12"
            >
              <DiscountsSlot
                :discount="discount"
                :slot-data="slot"
                :i="i"
                :index="index"
                @save-discounts="$emit('save-discounts', $event)"
              />
            </div>

            <transition name="fade">
              <div
                v-if="
                  discount.status == true &&
                  ((!runInfinityCheck && discount.mode === 'run') ||
                    (!priceInfinityCheck && discount.mode === 'price'))
                "
                class="md:1/6 group flex w-full cursor-pointer flex-col items-center rounded bg-gray-200 p-2 text-center dark:bg-gray-900 sm:w-1/3 lg:w-1/3 xl:w-2/12"
                @click="addDiscount(index)"
              >
                <p class="text-xm font-bold text-gray-400">
                  {{ $t("add another discount") }}
                </p>

                <font-awesome-icon
                  :icon="['fad', 'hand-holding-dollar']"
                  class="fa-4x my-6 text-gray-400"
                />
                <button
                  class="rounded-full border border-theme-500 px-2 py-1 text-sm text-theme-500 transition hover:bg-theme-200"
                >
                  <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
                  {{ $t("add discount") }}
                </button>
              </div>
            </transition>
          </div>
        </template>
      </div>
    </div>

    <!-- No discounts yet, create new discount -->
    <div v-else>
      <div class="md:1/6 w-full sm:w-1/4 lg:w-2/6 xl:w-2/12">
        <div
          class="group my-4 flex cursor-pointer flex-col items-center rounded bg-gray-200 p-2 text-center dark:bg-gray-900"
          @click="newDiscount()"
        >
          <p class="text-xm font-bold text-gray-400">
            {{ $t("add new discount") }}
          </p>

          <font-awesome-icon :icon="['fad', 'hand-holding-dollar']" class="fa-4x my-4 text-gray-400" />

          <button
            class="rounded-full border border-theme-500 px-2 py-1 text-sm text-theme-500 transition hover:bg-theme-200"
          >
            <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
            {{ $t("add discount") }}
          </button>
        </div>
      </div>
    </div>

    <div class="mx-auto mt-4 flex w-full items-center justify-end pr-4 lg:w-2/3 lg:pr-0">
      <button
        class="mx-1 rounded-full bg-green-500 px-2 py-1 text-white transition-colors duration-75 hover:bg-green-600"
        @click="$emit('saveDiscounts', discounts)"
      >
        {{ $t("save") }} {{ $t("discounts") }}
      </button>
    </div>
  </div>
</template>

<script>
import discountsMixin from "~/components/settings/discounts/discountsMixin";

export default {
  mixins: [discountsMixin],
  props: {
    getDiscounts: {
      type: Function,
      required: true,
    },
  },
  emits: ["saveDiscounts", "save-discounts"],
  created() {
    this.getDiscounts();
  },
};
</script>
