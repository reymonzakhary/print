<template>
  <div>
    <div class="flex w-full flex-1 items-center">
      <div class="mr-4 flex items-center text-sm font-bold text-gray-400">
        #{{ item.id }}

        <a class="flex h-6 w-6 items-center justify-center rounded-full hover:bg-gray-200">
          <font-awesome-icon
            v-tooltip="item.reference"
            :icon="[item.reference ? 'far' : 'fal', 'message-lines']"
            class="fa-sm"
            :class="{ 'text-theme-500': item.reference }"
          />
        </a>

        <a class="flex h-6 w-6 items-center justify-center rounded-full hover:bg-gray-200">
          <font-awesome-icon
            v-tooltip="item.note"
            :icon="[item.note ? 'far' : 'fal', 'note']"
            class="fa-sm"
            :class="{ 'text-theme-500': item.note }"
          />
        </a>
      </div>
    </div>

    <div class="flex w-full flex-1 items-center">
      <VDropdown offset="-1" class="flex">
        <div
          v-tooltip="
            item.product && item.product.category.name
              ? item.product.category.name
              : item.product
                ? item.product.category.name
                : ''
          "
          class="cursor-pointer overflow-hidden truncate pr-2 text-theme-500"
          :class="{
            'w-40 text-red-500': view === 'grid',
            'w-36': view === 'list',
            'w-40': view === 'items-only',
          }"
        >
          {{
            item.product.category.name
              ? item.product.category.name
              : item.product
                ? item.product.category.name
                : ""
          }}
        </div>
        <template #popper>
          <div
            class="overflow-auto rounded bg-gray-900 p-4 text-white shadow"
            style="max-height: calc(100vh - 4rem)"
          >
            <b class="flex w-64 border-b border-black">
              #<span class="mr-2">{{ item.id }}</span>
              {{
                item.product.category.name ? item.product.category.name : item.product.category.name
              }}
            </b>
            <span
              v-for="(option, idx) in item.product.product"
              :key="'option_' + idx"
              class="flex w-64 justify-between border-b border-black text-sm"
            >
              <span>{{ option.key }}</span>
              <b>{{ option.value }}</b>
            </span>

            <template>
              <li
                v-for="prop in item.product.properties"
                :key="'prop_' + prop.key"
                class="flex w-full flex-wrap justify-between py-2"
              >
                <b
                  :class="
                    typeof prop.value === 'object'
                      ? 'sticky -top-4 z-0 w-full bg-gray-800 py-1 text-xs'
                      : 'w-1/2'
                  "
                  class="truncate"
                >
                  {{ prop.key }}
                </b>

                <span v-if="typeof prop.value === 'object'" class="w-full divide-y divide-dashed">
                  <div
                    v-for="(value, key, i) in prop.value"
                    :key="'value_' + key"
                    class="my-1 flex w-full flex-wrap"
                  >
                    <span class="w-1/2 truncate pr-2 lowercase"> {{ key }}: </span>
                    <span v-tooltip="value" class="w-1/2 max-w-prose truncate">
                      {{ value }}
                    </span>
                  </div>
                </span>

                <span v-else class="w-1/2 truncate text-right">
                  {{ prop.value }}
                </span>
              </li>
            </template>
          </div>
        </template>
      </VDropdown>
    </div>

    <button class="flex w-full flex-1 items-center" @click.stop="showStatusUI = item.id">
      <SalesStatusIndicator :status="item.status.code" outline />
    </button>

    <transition name="fade">
      <div v-if="showStatusUI === item.id">
        <ConfirmationModal classes="w-11/12" @on-close="showStatusUI = false">
          <template #modal-header>
            <span class="font-bold text-gray-400"> #{{ order.order_nr }} - #{{ item.id }}</span>
            {{
              item.product.category.name ? item.product.category.name : item.product.category.name
            }}
          </template>
          <template #modal-body>
            <OrderProductStatus
              class=""
              :order_id="order_id"
              :order_status="item.status"
              :item="item"
              :editable="item.supplier_id === $store.state.settings.me.tenant_id ? true : false"
              @on-close-status="showStatusUI = false"
            />
          </template>
        </ConfirmationModal>
      </div>
    </transition>

    <div
      v-tooltip="item.product.price.dlv.days + 'day(s)'"
      class="flex w-full flex-1 cursor-pointer items-center text-sm text-theme-500"
    >
      {{ item.delivery_date }}
    </div>

    <div class="flex flex-1 items-center">
      <span class="w-6">
        <font-awesome-icon
          v-if="item.supplier_type === 'standard' || item.supplier_type == 'manually selected'"
          :icon="['fal', 'square-sliders-vertical']"
          :title="item.supplier_type"
        />
        <font-awesome-icon
          v-if="item.supplier_type === 'manual' || item.supplier_type == 'manually overridden'"
          :icon="['fal', 'hand-holding-box']"
          :title="item.supplier_type"
        />
        <font-awesome-icon
          v-if="producerType === 1"
          v-tooltip="'own production'"
          :icon="['fal', 'box-taped']"
        />
        <font-awesome-icon
          v-if="producerType === 2"
          v-tooltip="'own production'"
          :icon="['fal', 'user-crown']"
        />
      </span>

      <Producerdropdown
        :order_id="order_id"
        :item="item"
        :selected_supplier_id="item.product.external_id"
        :selected-supplier-name="item.product.external_name"
        :original_supplier_id="item.product.tenant_id"
        :type="item.status?.code === 300 || item.status?.code === 302 ? 'editable' : ''"
        class="text-xs"
        style="max-width: 110px"
        @click.stop
      />
    </div>

    <!-- necesary for tailwind to incorporate these styles as they are dynamically defined -->
    <div class="from-amber-200 to-amber-600" />
    <div class="from-orange-200 to-orange-600" />
    <div class="from-blue-200 to-blue-600" />

    <div class="w-full flex-1 px-4 text-theme-500">
      <OrderFiles
        class="w-32 truncate"
        type="items"
        :object="item"
        :order_id="order_id"
        :index="i"
        :editable="false"
        :small-progress-bar="true"
      />
    </div>

    <div class="flex w-full flex-1 items-center justify-between">
      <span class="z-20">
        <ItemMenu
          menu-class="w-8 h-8 rounded-full hover:bg-gray-200"
          dropdown-class="w-48 text-sm right-8"
          :menu-items="menuItems"
          menu-icon="ellipsis-h"
          :index="i"
          :disabled="item.status.code === statusMap.CANCELED"
          @item-clicked="menuItemClicked($event, item)"
        />
      </span>
    </div>

    <component :is="rawComponent" :order_id="order.id" :item="item" />
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import OrderProductsJobticket from "#components";
import helper from "./mixins/helper";

export default {
  mixins: [helper],
  transition: "tweakOpacity",
  props: {
    order_id: null,
    order: Object,
    index: Number,
    item: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: false,
    },
  },
  emits: ["on-cancel-item"],
  setup() {
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const { handleError, handleSuccess } = useMessageHandler();
    const { statusMap } = useOrderStatus();
    return { api, permissions, handleError, handleSuccess, statusMap };
  },
  data() {
    return {
      menuClass: "text-right",
      clicked: [],
      hided: [],
      animated: false,
      producerType: 1,
      showStatusUI: false,
      component: "",
      menuItems: [
        {
          heading: this.$t("Item actions"),
          items: [
            {
              action: "cancel",
              icon: "xmark",
              title: this.$t("cancel item"),
              classes: "text-red-500",
              show: true,
            },
          ],
        },
      ],
    };
  },
  computed: {
    ...mapState({
      filesizes: (state) => state.fm.content.sizes,
      selectedDisk: (state) => state.fm.content.selectedDisk,
      sortSettings: (state) => state.fm.content.sort,
      progress: (state) => state.cart.progress,
    }),
  },
  watch: {
    order: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    detailitems: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    item: {
      deep: true,
      handler(v) {
        // if cancelled hide cancel option
        v.status.code === 305
          ? (this.menuItems[0].items[0].show = false)
          : (this.menuItems[0].items[0].show = true);
        return v;
      },
    },
    progress: {
      deep: true,
      immediate: true,
      handler(v) {
        return v;
      },
    },
  },
  methods: {
    rawComponent() {
      switch (this.component) {
        case "OrderProductsJobticket":
          return markRaw(OrderProductsJobticket);
        default:
          return h("div");
      }
    },
    menuItemClicked(event, item) {
      switch (event) {
        case "cancel":
          this.$emit("on-cancel-item");
          break;

        case "rerun_blueprint":
          this.rerunBlueprint(item.id);
          break;

        case "show_job_ticket":
          this.component = "OrderProductsJobticket";
          break;

        default:
          break;
      }
    },
    ...mapMutations({
      change_item: "orders/change_item",
    }),
    editable(item) {
      if (item.status) {
        switch (item.status.code) {
          case 300:
            return true;

          default:
            return false;
        }
      }
    },
    expandAdditionalInfo(row) {
      row._showDetails = !row._showDetails;
    },
    closeModal() {
      this.showStatusUI = false;
      this.component = "";
    },
  },
};
</script>
<style lang="scss" scoped>
.fade-transition {
  transition: opacity 0.4s ease;
}

.fade-enter,
.fade-leave {
  opacity: 0;
}
.table td {
  border-bottom: none !important;
  vertical-align: middle !important;
  white-space: nowrap;
}

.animate {
  position: absolute;
  display: flex;
  width: auto;
  top: -15px;
  @apply py-0;
  animation: send ease-in 1s;
}

@keyframes send {
  0% {
    left: 0;
    opacity: 1;
  }
  100% {
    left: 50vw;
    opacity: 0;
  }
}
</style>
