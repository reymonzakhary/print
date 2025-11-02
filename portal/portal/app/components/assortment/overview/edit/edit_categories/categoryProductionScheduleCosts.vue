<template>
  <section class="w-full p-2">
    <header
      class="grid grid-cols-6 items-center justify-between text-sm font-bold uppercase tracking-wide"
    >
      <div class="flex-1">
        <!-- <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'bow-arrow']" /> -->
        {{ $t("days") }}
      </div>
      <div class="flex-1">{{ $t("max qty") }}</div>

      <div class="col-span-2">
        {{ $t("mode") }}
      </div>
      <div class="ml-auto flex-1">
        {{ $t("amount") }}
      </div>
    </header>
    <section
      v-for="(dlv, idx) in dlvs"
      :key="'delivery_' + idx"
      class="my-1 grid grid-cols-6 items-center"
    >
      <!-- <div class="flex"> -->
      <input
        v-model="dlv.days"
        type="number"
        :placeholder="$t('days')"
        class="input rounded-none rounded-l bg-white p-1 text-sm text-theme-900 dark:border-black dark:text-gray-100"
        style="min-width: 40px"
      />
      <input
        v-model="dlv.max_qty"
        type="number"
        :placeholder="1500"
        class="input rounded-none bg-white p-1 text-sm text-theme-900 dark:border-black dark:text-gray-100"
        style="min-width: 40px"
      />
      <!-- </div> -->
      <button
        class="border border-theme-500 p-1 text-sm text-theme-500 hover:bg-theme-100"
        :class="{
          'cursor-default bg-theme-400 text-themecontrast-400 hover:bg-theme-400':
            dlv.mode === 'fixed',
        }"
        @click="((dlv.mode = 'fixed'), change++)"
      >
        {{ $t("fixed") }}
      </button>
      <button
        class="border border-l-0 border-theme-500 p-1 text-sm text-theme-500 hover:bg-theme-100"
        :class="{
          'cursor-default bg-theme-400 text-themecontrast-400 hover:bg-theme-400':
            dlv.mode === 'percentage',
        }"
        @click="((dlv.mode = 'percentage'), change++)"
      >
        {{ $t("percentage") }}
      </button>
      <div class="relative flex">
        <input
          v-if="dlv.mode === 'percentage'"
          v-model="dlv.value"
          class="input rounded-none rounded-r border-green-500 p-1 pl-6 text-sm dark:border-green-500"
          type="number"
          placeholder="100"
          @change="change++"
        />
        <UICurrencyInput
          v-if="dlv.mode === 'fixed'"
          v-model="dlv.value"
          input-class="rounded-none rounded-r border-green-500 px-2 py-1 text-sm ring-green-200 focus:border-green-500 dark:border-green-500"
          :options="{
            precision: 5,
          }"
          @input="change++"
        />
        <font-awesome-icon
          v-if="dlv.mode === 'percentage'"
          class="absolute top-2 z-10 ml-2 text-green-500"
          :icon="['fal', 'percent']"
        />
      </div>
      <button
        class="rounded-full p-2 text-sm text-red-600 hover:bg-red-100"
        @click="dlvs.splice(idx, 1)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
      </button>
    </section>
    <button
      class="ml-auto rounded-full px-2 py-1 text-sm text-theme-500 dark:bg-gray-900"
      @click="
        (addDlv({
          index: i,
          dlv: {
            days: 0,
            max_qty: 1500,
            mode: 'fixed',
            value: 3000000,
          },
        }),
        change++)
      "
    >
      <font-awesome-icon :icon="['fal', 'plus']" />
      {{ $t("add") }}
    </button>
  </section>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";

const props = defineProps({
  productionDlv: {
    type: Array,
    required: false,
    default: () => [
      {
        days: 0,
        max_qty: 1500,
        mode: "fixed",
        value: 3000000,
      },
    ],
  },
});

const change = ref(0);
const dlvs = ref([]);

const emit = defineEmits(["on-update-production-dlv"]);
onMounted(() => {
  if (props.productionDlv) dlvs.value = props.productionDlv;
});

watch([dlvs, change], () => {
  emit("on-update-production-dlv", dlvs.value);
});
const addDlv = (dlv) => {
  dlvs.value.push(dlv);
};
</script>
