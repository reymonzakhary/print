<template>
  <div ref="dropdown" class="relative w-full">
    <button
      v-tooltip="selectedSupplierName"
      class="input relative flex items-center justify-between overflow-hidden truncate rounded-none rounded-r px-2 py-1 text-xs focus:outline-none focus:ring-0"
      :class="{
        'cursor-default border-0 bg-transparent shadow-none hover:bg-transparent':
          disabled || (item && item.status.code !== 300 && type !== 'editable') || item.product.connection,
      }"
      aria-haspopup="true"
      aria-controls="dropdown-menu"
      @click="
        !disabled &&
        !item.product.connection &&
        ((item && item.status.code === 300) || type === 'editable' ? (show = !show) : '')
      "
    >
      <span class="truncate">{{ selectedSupplierName }}</span>
      <font-awesome-icon
        v-if="!disabled && !item.product.connection && ((item && item.status.code === 300) || type === 'editable')"
        :icon="['fas', 'angle-down']"
        class="ml-2"
      />
    </button>

    <transition name="slide" target="div">
      <div
        v-if="show"
        class="firefox:bg-opacity-100 absolute left-0 z-50 w-64 rounded border bg-white/60 text-sm shadow-lg backdrop-blur-md dark:bg-gray-900/60"
        role="menu"
      >
        <!-- TODO: -->
        <!-- <a
          href="#"
          class="block border-b px-3 py-2 hover:bg-white dark:border-gray-900 dark:hover:bg-gray-800"
        >
          <font-awesome-icon :icon="['fal', 'square-sliders-vertical']" />
          {{ $t("standard producer") }}
        </a> -->
        <button
          class="block w-full border-b px-3 py-2 text-left hover:bg-white dark:border-gray-900 dark:hover:bg-gray-800"
          @click="
            (eventStore.emit('updateItem', {
              id: item.id,
              object: { supplier_id: originalSupplierId },
            }),
            update({
              id: item.id,
              object: {
                supplier_id: originalSupplierId,
              },
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'box-taped']" />
          {{ $t("producer from product") }} <br />
          <small class="text-gray-500">{{ originalSupplierName }}</small>
        </button>
        <button
          class="block w-full border-b px-3 py-2 text-left hover:bg-white dark:border-gray-900 dark:hover:bg-gray-800"
          @click="
            (eventStore.emit('updateItem', {
              id: item.id,
              object: {
                supplier_id: $store.state.settings.me.tenant_id,
              },
            }),
            update({
              id: item.id,
              object: {
                supplier_id: $store.state.settings.me.tenant_id,
              },
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'user-crown']" />
          {{ $t("produce internally") }}
          <br />
          <!-- TODO: add own tenant name -->
          <!-- <small class="text-gray-500">
						{{ $store.state.settings.me.tenant_name }}
					</small> -->
        </button>
        <!-- TODO: -->
        <!-- <a
					href="#"
					class="block px-3 py-2 border-b hover:bg-white dark:hover:bg-gray-800 dark:border-gray-900"
				>
					<font-awesome-icon :icon="['fal', 'hand-holding-box']" />
					{{ $t("select producer") }}
					<font-awesome-icon :icon="['fal', 'external-link']" />
				</a> -->
      </div>
    </transition>
  </div>
</template>

<script>
import cO from "~/plugins/directives/click-outside";
import { mapActions } from "vuex";

export default {
  mixins: [cO],
  props: {
    orderId: {
      required: true,
      type: Number,
    },
    selectedSupplierId: {
      type: String,
      default: "",
    },
    selectedSupplierName: {
      type: String,
      default: "",
    },
    originalSupplierId: {
      type: String,
      default: "",
    },
    originalSupplierName: {
      type: String,
      default: "",
    },
    type: {
      type: String,
      default: "",
    },
    item: {
      type: Object,
      default: () => ({}),
    },
    disabled: Boolean,
  },
  setup() {
    const eventStore = useEventStore();
    return { eventStore };
  },
  data() {
    return {
      show: false,
      tenantName: "",
    };
  },
  watch: {
    selectedSupplierName(v) {
      return v;
    },
  },
  mounted() {
    window.addEventListener("click", this.handleOutsideClick);
  },
  beforeUnmount() {
    window.removeEventListener("click", this.handleOutsideClick);
    this.eventStore.off("updateItem");
  },
  methods: {
    close() {
      this.show = false;
    },
    handleOutsideClick(event) {
      const el = this.$refs.dropdown;
      if (event && !el.contains(event.target)) {
        this.close();
      }
    },
    ...mapActions({
      update_product: "orders/update_product",
    }),
    update(data) {
      this.update_product({
        ordertype: this.type,
        order_id: this.orderId,
        item_id: data.id,
        object: data.object,
      });
      this.close();
    },
  },
};
</script>
