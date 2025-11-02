<template>
  <div class="p-2 bg-white rounded shadow-md dark:bg-gray-700">
    <div class="flex justify-between my-2">
      <div class="text-xs font-bold tracking-wide uppercase">
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
          v-if="margin.mode === 'run'"
          class="relative w-10 h-4 mx-3 transition duration-200 ease-linear rounded-full cursor-pointer"
          :class="[
            runInfinityCheck == true ? 'bg-theme-400' : 'bg-gray-400',
            (i === margin.slots.lastIndexOf(margin.slots[margin.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'block'
              : 'hidden',
          ]"
        >
          <label
            :for="`activeStateRunState${i}`"
            class="absolute left-0 w-4 h-4 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
            :class="[
              runInfinityCheck ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-400',
            ]"
          ></label>
          <input
            :id="`activeStateRunState${i}`"
            v-model="infinityChecked"
            type="checkbox"
            checked=""
            :name="`activeStateRunState${i}`"
            class="w-full h-full appearance-none active:outline-none focus:outline-none"
            @click="activeStateRun(i, $event.target.checked)"
          />
        </div>
        <div
          v-else
          class="relative w-10 h-4 mx-3 transition duration-200 ease-linear rounded-full cursor-pointer"
          :class="[
            priceInfinityCheck == true ? 'bg-theme-400' : 'bg-gray-400',
            (i === margin.slots.lastIndexOf(margin.slots[margin.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'block'
              : 'hidden ',
          ]"
        >
          <label
            :for="`activeStatePriceState${i}`"
            class="absolute left-0 w-4 h-4 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
            :class="[
              priceInfinityCheck
                ? 'translate-x-6 border-theme-500'
                : 'translate-x-0 border-gray-400',
            ]"
          ></label>
          <input
            :id="`activeStatePriceState${i}`"
            v-model="activeStatePriceState"
            type="checkbox"
            :name="`activeStatePriceState${i}`"
            :value="i"
            class="w-full h-full appearance-none active:outline-none focus:outline-none"
            @click="activeStatePrice(i, $event.target.checked)"
          />
        </div>
        <span
          v-if="margin.mode === 'run'"
          :class="[
            (i === margin.slots.lastIndexOf(margin.slots[margin.slots.length - 1]) &&
              !slotData.to) ||
            slotData.to == '-1'
              ? 'flex'
              : 'hidden ',
          ]"
        >
          <font-awesome-icon :icon="['fas', 'infinity']" />
        </span>
        <span
          v-if="margin.mode === 'price'"
          :class="[
            (i === margin.slots.lastIndexOf(margin.slots[margin.slots.length - 1]) &&
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
          class="w-full px-2 py-1 pr-5 text-sm transition border rounded-l focus:outline-none focus:ring focus:ring-theme-800 focus:border-theme-300 dark:focus:border-theme-300 dark:bg-gray-700 dark:border-gray-900"
          type="number"
          :value="slotData.from"
          :min="margin.slots[i - 1] ? Number(margin.slots[i - 1].to) + 1 : 0"
          :max="Number(margin.slots[i].to) - 1"
          @keyup="inputUpdate(index, i, 'from', $event.target.value)"
        />
        <font-awesome-icon
          v-if="margin.mode === 'price'"
          class="absolute right-0 mt-2 mr-2 text-gray-500"
          :icon="['fal', 'euro-sign']"
        />
        <font-awesome-icon
          v-else
          class="absolute right-0 mt-2 mr-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>

      <section v-if="margin.mode === 'run'" class="relative flex w-1/2">
        <span
          class="absolute text-lg font-bold tracking-wide uppercase left-4"
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
          class="w-full px-2 pr-5 text-sm transition border rounded-r focus:outline-none focus:ring focus:ring-theme-800 focus:border-theme-300 dark:focus:border-theme-300 dark:bg-gray-700 dark:border-gray-900"
          type="number"
          :min="Number(margin.slots[i].from) + 1"
          :max="margin.slots[i + 1] ? margin.slots[i + 1].from - 1 : null"
          @keyup="inputUpdate(index, i, 'to', $event.target.value)"
        />
        <font-awesome-icon
          class="absolute right-0 mt-2 mr-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>
      <section v-else class="relative flex w-1/2">
        <span
          class="absolute text-lg font-bold tracking-wide uppercase left-4"
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
          class="w-full px-2 pr-5 text-sm transition border rounded-r focus:outline-none focus:ring focus:ring-theme-800 focus:border-theme-300 dark:focus:border-theme-300 dark:bg-gray-700 dark:border-gray-900"
          type="number"
          :min="Number(margin.slots[i].from) + 1"
          :max="margin.slots[i + 1] ? margin.slots[i + 1].from - 1 : null"
          @keyup="inputUpdate(index, i, 'to', $event.target.value)"
        />
        <font-awesome-icon
          class="absolute right-0 mt-2 mr-2 text-gray-500"
          :icon="['fal', 'square-sliders-vertical']"
        />
      </section>
    </form>
    <hr class="my-4" />
    <div class="flex my-2">
      <button
        value="percentage"
        class="px-2 py-1 text-xs border rounded-l dark:bg-gray-800 dark:border-black"
        :class="{
          'bg-theme-400 hover:bg-theme-500 text-themecontrast-400 border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500 dark:border-theme-600':
            slotData.type === 'percentage',
          'bg-gray-200 hover:bg-gray-300': slotData.type === 'fixed',
        }"
        @click="inputUpdate(index, i, 'type', $event.target.value)"
      >
        Percentage
      </button>
      <button
        value="fixed"
        class="px-2 py-1 text-xs border rounded-r dark:bg-gray-800 dark:border-black"
        :class="{
          'bg-theme-400 hover:bg-theme-500 text-themecontrast-400 border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500 dark:border-theme-600':
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
        class="w-full px-2 py-1 pr-5 text-sm transition border rounded focus:outline-none focus:ring focus:ring-theme-800 focus:border-theme-300 dark:focus:border-theme-300 dark:bg-gray-700 dark:border-gray-900"
        type="number"
        placeholder="33"
        :value="slotData.value"
        @change="inputUpdate(index, i, 'value', $event.target.value)"
      />
      <font-awesome-icon
        v-if="slotData.type == 'percentage'"
        class="absolute right-0 mt-2 mr-2 text-gray-500"
        :icon="['fal', 'percentage']"
      />
      <font-awesome-icon
        v-else
        class="absolute right-0 mt-2 mr-2 text-gray-500"
        :icon="['fal', 'euro-sign']"
      />
    </div>
    <a
      href="#"
      class="flex items-center justify-end mt-2 text-sm text-red-600"
      @click="removeMargin(index, i)"
    >
      <font-awesome-icon :icon="['fal', 'trash']" class="mr-1 fa-xs" />
      {{ $t("delete") }} {{ $t("margin") }}
    </a>
  </div>
</template>

<script>
import marginsMixin from "~/components/settings/margins/marginsMixin";
import _ from "lodash";

export default {
  mixins: [marginsMixin],
  props: {
    margin: {
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
  emits: ["remove-margin"],
  data() {
    return {
      infinityChecked: false,
    }
  },
  methods: {
    // remove margin
    removeMargin(margin, i) {
      this.infinityChecked = false;
      this.activeStateRun(i, false);
      this.change++;
      // clone store data to be allowed to manipulate
      const data = _.cloneDeep(this.margins);

      // remove slot from slots
      data[margin].slots.splice(i, 1);

      // update store with mutation
      this.update(data);
    },
  },
};
</script>

<style></style>
