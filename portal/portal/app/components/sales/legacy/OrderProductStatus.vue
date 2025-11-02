<template>
  <div class="">
    <font-awesome-icon
      :icon="['fad', 'circle-xmark']"
      class="absolute right-2 top-2 text-theme-900"
      @click.stop="$emit('onCloseStatus')"
    />

    <section class="z-20 flex flex-wrap items-center justify-center space-x-4 px-8 py-4">
      <p class="mb-4 w-full text-center text-sm font-bold uppercase tracking-wide text-theme-900">
        {{ $t("sequential statusses") }}
      </p>

      <div
        v-for="status in statuses.sequential_item_statuses"
        :key="`sequential_${status.code}`"
        v-tooltip="`${status.code}: ${status.description}`"
        class="relative z-10 block rounded p-2 transition"
        :class="[
          statusColor(status, true, true, true),
          {
            'border-2 border-theme-500 font-bold italic': order_status.code === status.code,
          },
          { 'cursor-pointer border hover:shadow-md': editable },
        ]"
        @click="editable ? update(status.code) : ''"
      >
        {{ $t(status.name) }}
        <div
          class="absolute left-[49%] mx-auto mt-4 h-2 w-2 rounded-full border border-theme-500 bg-white"
          :class="status.code === order_status.code ? 'bg-theme-400' : ''"
        />
      </div>
    </section>

    <div class="relative z-0 mx-8 flex" :class="editable ? '-mt-[6px]' : '-mt-[7px]'">
      <font-awesome-icon :icon="['fad', 'play']" class="absolute -left-4 -top-2 text-theme-500" />
      <div class="z-0 block w-full border-t border-theme-500" />
      <font-awesome-icon
        :icon="['fad', 'flag-checkered']"
        class="absolute -right-4 -top-2 text-theme-500"
      />
    </div>

    <section
      class="mt-16 flex w-full flex-wrap items-center justify-center space-x-4 rounded border bg-gray-100 p-4"
    >
      <p class="mb-4 w-full text-center text-sm font-bold uppercase tracking-wide text-theme-900">
        {{ $t("other statusses") }}
      </p>

      <div
        v-for="status in statuses.deviant_statuses"
        :key="status.code"
        v-tooltip="`${status.code}: ${status.description}`"
        class="rounded bg-white p-2 transition"
        :class="[
          statusColor(status, true, true, true),
          {
            'border-2 border-theme-500 font-bold italic': order_status.code === status.code,
          },
          { 'cursor-pointer border hover:shadow-md': editable },
        ]"
        @click="editable ? update(status.code) : ''"
      >
        {{ status.name }}
      </div>
    </section>
  </div>
</template>

<script>
import helper from "./mixins/helper";
import { mapActions, mapState } from "vuex";

export default {
  mixins: [helper],
  props: {
    order_id: {
      required: true,
      type: Number,
    },
    order_status: {
      required: false,
      type: Object,
    },
    item: {
      required: true,
      type: Object,
    },
    editable: {
      required: false,
      type: Boolean,
      default: true,
    },
  },
  emits: ["onCloseStatus"],
  computed: {
    ...mapState({
      statuses: (state) => state.statuses.statuses,
    }),
  },
  methods: {
    ...mapActions({
      update_product: "orders/update_product",
    }),
    update(code) {
      this.update_product({
        ordertype: this.ordertype,
        order_id: this.order_id,
        item_id: this.item.id,
        object: { st: code },
      });
      this.$emit("onCloseStatus");
    },
  },
};
</script>
