<template>
  <div class="rounded bg-white p-2 shadow-md dark:bg-gray-700">
    <div class="my-2 flex justify-between">
      <div class="text-xs font-bold uppercase tracking-wide">
        {{ $t("range") }}: {{ slotData.from }} -

        <span v-if="slotData.to == '-1'">
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </span>
        <span v-else>
          {{ slotData.to }}
        </span>
      </div>
      <div v-tooltip="'infinity'" class="flex items-center">
        <div
          v-if="discount.mode === 'run'"
          class="relative mx-3 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
          :class="[
            runInfinityCheck == true ? 'bg-theme-400' : 'bg-gray-400',
            (i === discount.slots.lastIndexOf(discount.slots[discount.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'block'
              : 'hidden',
          ]"
        >
          <label
            :for="`activeStateRunState${i}`"
            class="absolute left-0 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
            :class="[
              runInfinityCheck ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-400',
            ]"
          />
          <input
            :id="`activeStateRunState${i}`"
            v-model="infinityChecked"
            type="checkbox"
            checked=""
            :name="`activeStateRunState${i}`"
            class="h-full w-full appearance-none focus:outline-none active:outline-none"
            @click="activeStateRun(i, $event.target.checked)"
          />
        </div>
        <div
          v-else
          class="relative mx-3 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
          :class="[
            priceInfinityCheck == true ? 'bg-theme-400' : 'bg-gray-400',
            (i === discount.slots.lastIndexOf(discount.slots[discount.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'block'
              : 'hidden',
          ]"
        >
          <label
            :for="`activeStatePriceState${i}`"
            class="absolute left-0 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
            :class="[
              priceInfinityCheck
                ? 'translate-x-6 border-theme-500'
                : 'translate-x-0 border-gray-400',
            ]"
          />
          <input
            :id="`activeStatePriceState${i}`"
            v-model="activeStatePriceState"
            type="checkbox"
            :name="`activeStatePriceState${i}`"
            :value="i"
            class="h-full w-full appearance-none focus:outline-none active:outline-none"
            @click="activeStatePrice(i, $event.target.checked)"
          />
        </div>
        <span
          v-if="discount.mode === 'run'"
          :class="[
            (i === discount.slots.lastIndexOf(discount.slots[discount.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'flex'
              : 'hidden',
          ]"
        >
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </span>
        <span
          v-if="discount.mode === 'price'"
          :class="[
            (i === discount.slots.lastIndexOf(discount.slots[discount.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'flex'
              : 'hidden',
          ]"
        >
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </span>
      </div>
    </div>

    <form class="flex">
      <section class="relative flex w-1/2">
        <input
          class="w-full rounded-l border px-2 py-1 pr-5 text-sm transition focus:border-theme-300 focus:outline-none focus:ring focus:ring-theme-800 dark:border-gray-900 dark:bg-gray-700 dark:focus:border-theme-300"
          type="number"
          :value="slotData.from"
          :min="discount.slots[i - 1] ? Number(discount.slots[i - 1].to) + 1 : 0"
          :max="Number(discount.slots[i].to) - 1"
          @keyup="inputUpdate(index, i, 'from', $event.target.value)"
        />
        <font-awesome-icon
          v-if="discount.mode === 'price'"
          class="absolute right-0 mr-2 mt-2 text-gray-500"
          :icon="['fal', 'euro-sign']"
        />
        <font-awesome-icon
          v-else
          class="absolute right-0 mr-2 mt-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>

      <section v-if="discount.mode === 'run'" class="relative flex w-1/2">
        <span
          class="absolute left-4 text-lg font-bold uppercase tracking-wide"
          :class="[runInfinityCheck && slotData.to == '-1' ? 'block' : 'hidden']"
          >&infin;
        </span>

        <input
          :value="slotData.to != '-1' ? slotData.to : ''"
          :class="[
            runInfinityCheck && slotData.to == '-1'
              ? 'pointer-events-none bg-gray-200'
              : 'pointer-events-auto',
          ]"
          class="w-full rounded-r border px-2 pr-5 text-sm transition focus:border-theme-300 focus:outline-none focus:ring focus:ring-theme-800 dark:border-gray-900 dark:bg-gray-700 dark:focus:border-theme-300"
          type="number"
          :min="Number(discount.slots[i].from) + 1"
          :max="discount.slots[i + 1] ? discount.slots[i + 1].from - 1 : null"
          @keyup="inputUpdate(index, i, 'to', $event.target.value)"
        />
        <font-awesome-icon
          class="absolute right-0 mr-2 mt-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>
      <section v-else class="relative flex w-1/2">
        <span
          class="absolute left-4 text-lg font-bold uppercase tracking-wide"
          :class="[priceInfinityCheck && slotData.to == '-1' ? 'block' : 'hidden']"
          >&infin;</span
        >
        <input
          :value="slotData.to != '-1' ? slotData.to : ''"
          :class="[
            priceInfinityCheck && slotData.to == '-1'
              ? 'pointer-events-none bg-gray-200'
              : 'pointer-events-auto',
          ]"
          class="w-full rounded-r border px-2 pr-5 text-sm transition focus:border-theme-300 focus:outline-none focus:ring focus:ring-theme-800 dark:border-gray-900 dark:bg-gray-700 dark:focus:border-theme-300"
          type="number"
          :min="Number(discount.slots[i].from) + 1"
          :max="discount.slots[i + 1] ? discount.slots[i + 1].from - 1 : null"
          @keyup="inputUpdate(index, i, 'to', $event.target.value)"
        />
        <font-awesome-icon
          class="absolute right-0 mr-2 mt-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>
    </form>
    <hr class="my-4" />
    <div class="my-2 flex">
      <button
        value="percentage"
        class="rounded-l border px-2 py-1 text-xs dark:border-black dark:bg-gray-800"
        :class="{
          'border-theme-600 bg-theme-400 text-themecontrast-400 hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
            slotData.type === 'percentage',
          'bg-gray-200 hover:bg-gray-300': slotData.type === 'fixed',
        }"
        @click="inputUpdate(index, i, 'type', $event.target.value)"
      >
        Percentage
      </button>
      <button
        value="fixed"
        class="rounded-r border px-2 py-1 text-xs dark:border-black dark:bg-gray-800"
        :class="{
          'border-theme-600 bg-theme-400 text-themecontrast-400 hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
            slotData.type === 'fixed',
          'bg-gray-200 hover:bg-gray-300': slotData.type === 'percentage',
        }"
        @click="inputUpdate(index, i, 'type', $event.target.value)"
      >
        {{ $t("fixed") }}
      </button>
    </div>

    <div class="relative flex">
      <input
        class="w-full rounded border px-2 py-1 pr-5 text-sm transition focus:border-theme-300 focus:outline-none focus:ring focus:ring-theme-800 dark:border-gray-900 dark:bg-gray-700 dark:focus:border-theme-300"
        type="number"
        placeholder="33"
        :value="slotData.value"
        @change="inputUpdate(index, i, 'value', $event.target.value)"
      />
      <font-awesome-icon
        v-if="slotData.type == 'percentage'"
        class="absolute right-0 mr-2 mt-2 text-gray-500"
        :icon="['fal', 'percentage']"
      />
      <font-awesome-icon
        v-else
        class="absolute right-0 mr-2 mt-2 text-gray-500"
        :icon="['fal', 'euro-sign']"
      />
    </div>
    <a
      href="#"
      class="mt-2 flex items-center justify-end text-sm text-red-600"
      @click="removeDiscount(index, i)"
    >
      <font-awesome-icon :icon="['fal', 'trash']" class="fa-xs mr-1" />
      {{ $t("delete") }} {{ $t("discount") }}
    </a>
  </div>
</template>

<script>
import discountsMixin from "~/components/settings/discounts/discountsMixin";
import _ from "lodash";

export default {
  mixins: [discountsMixin],
  props: {
    discount: {
      type: Object,
      required: true,
    },
    slotData: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
  },
  emits: ["remove-discount"],
  data() {
    return {
      infinityChecked: false,
    };
  },
  methods: {
    // remove discount
    removeDiscount(discount, i) {
      this.infinityChecked = false;
      this.activeStateRun(i, false);
      this.change++;
      // clone store data to be allowed to manipulate
      const data = _.cloneDeep(this.discounts);

      // remove slot from slots
      data[discount].slots.splice(i, 1);

      // update store with mutation
      this.update(data);
    },
  },
};
</script>

<style></style>
