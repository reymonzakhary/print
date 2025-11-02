<template>
  <!-- delivery days -->
  <div class="px-4">
    <div class="flex w-full items-center text-sm font-bold uppercase tracking-wide">
      <font-awesome-icon class="text-gray-400" :icon="['fal', 'hourglass']" />
      <font-awesome-icon class="mr-2 text-gray-400" :icon="['fal', 'coin']" />
      {{ $t("production days") }}
    </div>

    <section
      v-for="(dlv, idx) in sheet_run.dlv_production"
      :key="'delivery_' + idx"
      class="my-1 flex items-center"
    >
      <input
        v-if="editable"
        v-model="dlv.days"
        type="number"
        class="input rounded-none rounded-l bg-white p-1 text-sm text-theme-900 dark:border-black dark:text-gray-100"
        style="min-width: 40px"
      />
      <div v-else>{{ dlv.days }}</div>
      <button
        v-if="editable"
        class="w-1/2 border border-theme-500 p-1 text-sm text-theme-500 hover:bg-theme-100"
        :class="{
          'cursor-default bg-theme-400 text-themecontrast-400 hover:bg-theme-400':
            dlv.mode === 'fixed',
        }"
        @click="((dlv.mode = 'fixed'), change++)"
      >
        {{ $t("fixed") }}
      </button>
      <button
        v-if="editable"
        class="w-1/2 border border-l-0 border-theme-500 p-1 text-sm text-theme-500 hover:bg-theme-100"
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
          v-if="dlv.mode === 'percentage' && editable"
          v-model="dlv.value"
          class="input w-24 rounded-none rounded-r border-green-500 p-1 pl-6 text-sm dark:border-green-500"
          type="number"
          placeholder="100"
          @change="change++"
        />
        <UICurrencyInput
          v-if="dlv.mode === 'fixed' && editable"
          v-model="dlv.value"
          input-class="w-24 rounded-none rounded-r border border-green-500 px-2 py-1 text-sm ring-green-200 focus:border-green-500 dark:border-green-500"
          @change="change++"
        />
        <div v-if="!editable" class="mx-4 flex items-center">
          <div class="mx-2">+</div>
          <span v-if="dlv.mode === 'percentage'">%</span>
          <span v-if="dlv.mode === 'fixed'">{{ getCurrencySymbol() }}</span>
          {{ dlv.value }}
        </div>
        <font-awesome-icon
          v-if="dlv.mode === 'percentage' && editable"
          class="absolute top-2 z-10 ml-2 text-green-500"
          :icon="['fal', 'percent']"
        />
      </div>
      <button
        v-if="editable"
        class="rounded-full p-2 text-sm text-red-600 hover:bg-red-100"
        @click="sheet_run.dlv_production.splice(idx, 1)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
      </button>
    </section>

    <button
      v-if="editable"
      class="ml-auto rounded-full px-2 py-1 text-sm text-theme-500 dark:bg-gray-900"
      @click="
        (sheet_run.dlv_production.push({
          days: 0,
          value: 0,
          mode: 'fixed',
        }),
        change++)
      "
    >
      <font-awesome-icon :icon="['fal', 'plus']" />
      {{ $t("add") }}
    </button>
  </div>
</template>

<script>
export default {
  name: "MachineColorsDlv",
  props: {
    sheet_run: {
      type: Object,
      required: true,
    },
    editable: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const { addToast } = useToastStore();
    const authStore = useAuthStore();
    const { getCurrencySymbol } = useMoney();
    return {
      addToast,
      authStore,
      getCurrencySymbol,
    };
  },
  data() {
    return {
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
      mode: 1,
      change: 0,
    };
  },
  watch: {
    sheet_run: {
      handler(v) {
        return v;
      },
      deep: true,
      immediate: true,
    },
    change(v) {
      if (v > 0 && v < 2) {
        this.addToast({
          message: this.$t("You made changes, be sure to save them"),
          type: "info",
        });
      }
    },
  },
};
</script>
