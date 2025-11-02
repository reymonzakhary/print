<template>
  <table class="items-table">
    <thead v-if="!noHeader" class="sticky top-[96px] z-20 w-full shadow-sm backdrop-blur-md">
      <tr>
        <th v-if="activeView === 'items-only'" class="w-16">{{ $t("order") }}</th>
        <th class="w-16">{{ $t("nr.") }}</th>
        <th>{{ $t("product") }}</th>
        <th v-if="activeView === 'items-only'" class="w-16">{{ $t("company") }}</th>
        <th :class="{ 'w-32': activeView !== 'items-only' }">{{ $t("delivery") }}</th>
        <th :class="{ 'w-24': activeView !== 'items-only' }">{{ $t("status") }}</th>
        <th>{{ $t("files") }}</th>
        <th :class="{ 'w-32': activeView !== 'items-only' }">{{ $t("producer") }}</th>
        <th class="w-20 2xl:w-44">
          <UIButton
            v-tooltip="$t('coming soon')"
            :icon="['fal', 'sliders']"
            variant="neutral-light"
            disabled
          />
        </th>
      </tr>
    </thead>
    <transition-group name="sales-list">
      <tbody
        v-for="quotation in props.quotations"
        :key="`item_list_${quotation.id}`"
        :class="[getHoverClass(quotation.id)]"
        @mouseover="handleMouseOver(quotation.id)"
        @mouseleave="emit('update:hoveredQuotation', null)"
      >
        <tr v-if="quotation && quotation.items.length === 0 && props.activeView === 'grid'">
          <td colspan="7" />
        </tr>
        <template v-else>
          <tr
            v-for="(item, index) in quotation.items"
            :key="`item_${item.id}`"
            :class="
              producedItems.find((producedItem) => producedItem.id === item.id) ? 'bg-theme-50' : ''
            "
          >
            <td
              v-if="activeView === 'items-only'"
              v-tooltip="`#${quotation.id}`"
              class="font-mono font-bold text-gray-400"
            >
              <span v-if="index === 0">#{{ quotation.id }}</span>
            </td>
            <td v-tooltip="`#${item.id}`" class="font-mono font-bold text-gray-400">
              #{{ item.id }}
            </td>
            <td>
              <SalesProductPreview :item="item" />
            </td>
            <td
              v-if="activeView === 'items-only'"
              v-tooltip="quotation.delivery_address?.company_name"
              class="font-mono font-bold text-gray-400"
            >
              <span v-if="index === 0">{{ quotation.delivery_address?.company_name ?? "-" }}</span>
            </td>
            <td>
              <span
                v-if="item.product.price?.dlv"
                v-tooltip="`${item.product.price.dlv.actual_days} ${$t('day')}`"
                class="text-xs"
              >
                {{ item.product.price.dlv.day }}
                {{ item.product.price.dlv.month }}
                {{
                  item.product.price.dlv.year == new Date().getFullYear()
                    ? ""
                    : item.product.price.dlv.year
                }}
              </span>
            </td>
            <td>
              <SalesStatusIndicator :status="item.status.code" class="max-w-32" outline />
            </td>
            <td>
              <!-- necesary for tailwind to incorporate these styles as they are dynamically defined -->
              <div class="from-amber-200 to-amber-600" />
              <div class="from-orange-200 to-orange-600" />
              <div class="from-blue-200 to-blue-600" />
              <OrderFiles
                class="max-h-9 max-w-20 overflow-hidden"
                type="items"
                :object="item"
                :order_id="quotation.id"
                :index="index"
                small-progress-bar
              />
            </td>
            <td class="!overflow-visible">
              <div class="!overflow-visible">
                <div class="flex items-center">
                  <div
                    :class="[
                      {
                        'rounded-l bg-gray-200 dark:bg-gray-900':
                          item.status.code !== 300 &&
                          getProducerDropdownType(item) === 'editable' &&
                          !item.product.connection,
                      },
                      'p-1',
                    ]"
                  >
                    <font-awesome-icon
                      v-if="
                        item.supplier_type === 'standard' ||
                        item.supplier_type == 'manually selected'
                      "
                      :icon="['fal', 'screwdriver-wrench']"
                      :title="item.supplier_type"
                      fixed-width
                    />
                    <font-awesome-icon
                      v-else-if="
                        item.supplier_type === 'manual' ||
                        item.supplier_type == 'manually overridden'
                      "
                      :icon="['fal', 'hand-holding-box']"
                      :title="item.supplier_type"
                      fixed-width
                    />
                    <font-awesome-icon
                      v-else-if="producerType === 1"
                      v-tooltip="'own production'"
                      :icon="['fal', 'box-taped']"
                      fixed-width
                    />
                    <font-awesome-icon
                      v-else-if="producerType === 2"
                      v-tooltip="'own production'"
                      :icon="['fal', 'user-crown']"
                      fixed-width
                    />
                  </div>

                  <div class="w-full">
                    <Producerdropdown
                      class="text-xs"
                      :order-id="quotation.id"
                      :item="item"
                      :selected-supplier-id="item.product.external_id"
                      :selected-supplier-name="item.product.external_name"
                      :original-supplier-id="item.product.external_id"
                      :type="getProducerDropdownType(item)"
                      :disabled="saving"
                    />
                  </div>
                </div>
              </div>
            </td>
            <td class="!overflow-visible">
              <div class="flex items-center justify-between pl-4">
                <div class="flex">
                  <input
                    v-if="
                      item.status.code === statusMap.NEW &&
                      me.tenant_id !== item.product.external_id &&
                      (selectedQutation === quotation.id || !selectedQutation) &&
                      quotation.items.filter((item) => item.product.external_id !== me.tenant_id)
                        .length > 1 &&
                      permissions.includes('orders-update')
                    "
                    type="checkbox"
                    class="mr-2"
                    :checked="producedItems.find((producedItem) => producedItem.id === item.id)"
                    @click.stop
                    @change="toggleItem(item.id, quotation.id)"
                  />
                  <ItemMenu
                    :disabled="
                      !isStatusInGroup(item.status.code, 'CANCELABLE') ||
                      saving ||
                      disableActions ||
                      item.product.connection
                    "
                    menu-icon="ellipsis-h"
                    menu-class="size-8 rounded-full hover:bg-gray-300 mr-2"
                    dropdown-class="w-48 text-sm right-8"
                    :menu-items="menuItems"
                    @item-clicked="menuItemClicked($event, quotation, item)"
                  />
                </div>
                <UIButton
                  v-if="
                    isStatusInGroup(item.status.code, 'NEXTABLE') &&
                    nextStatus(item.status.code) &&
                    me.tenant_id === item.product.external_id &&
                    permissions.includes('status-update')
                  "
                  v-tooltip.left="
                    !saving &&
                    !disableActions &&
                    `${$t('set item to')} ${beautify(nextStatus(item.status.code))}`
                  "
                  variant="outline"
                  :class="[
                    me.tenant_id === item.product.external_id &&
                      statusClassesMap[nextStatus(item.status.code)],
                    !saving &&
                      !disableActions &&
                      statusHoverClassesMap[nextStatus(item.status.code)],
                    'aspect-square w-fit 2xl:aspect-auto 2xl:w-full',
                  ]"
                  :icon="['fal', 'arrow-right']"
                  :icon-placement="'right'"
                  :disabled="saving || disableActions"
                  @click.stop="
                    handleItemToggleFunction(
                      quotation.id,
                      item.id,
                      nextStatus(item.status.code),
                      item,
                    )
                  "
                >
                  <span v-if="$screen.isXxl">{{ beautify(nextStatus(item.status.code)) }}</span>
                </UIButton>
                <UIButton
                  v-if="
                    (item.status.code === statusMap.NEW ||
                    item.status.code === statusMap.PROCESSING) &&
                    me.tenant_id !== item.product.external_id &&
                    (selectedQutation === quotation.id || !selectedQutation) &&
                    (item.id == producedItems[0]?.id || producedItems.length === 0) &&
                    permissions.includes('orders-update')
                  "
                  variant="outline"
                  v-tooltip.left="
                      item.status.code === statusMap.PROCESSING &&
                    `Waiting until your item produced`
                  "
                  :class="[
                    'aspect-square w-fit 2xl:aspect-auto 2xl:w-full',
                  me.tenant_id !== item.product.external_id &&
                      !saving &&
                      !disableActions &&
                      'aspect-square w-fit bg-green-100 text-green-500 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 2xl:aspect-auto 2xl:w-full',
                  ]"
                  :disabled="
                    saving ||
                    disableActions ||
                    (item.status.code !== statusMap.NEW &&
                      me.tenant_id !== item.product.external_id) ||
                    item.status.code === statusMap.PROCESSING
                  "
                  @click.stop="
                    handleItemToggleFunction(
                      quotation.id,
                      item.id,
                      nextStatus(item.status.code),
                      item,
                    )
                  "
                >
                  <span v-if="$screen.isXxl"
                    >{{ $t("Produce") }}
                    {{ producedItems.length > 1 ? `( ${producedItems.length} )` : "" }}</span
                  >
                </UIButton>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </transition-group>
  </table>
</template>

<script setup>
import { useStore } from "vuex";
import { computed } from "vue";

const props = defineProps({
  hoveredQuotation: {
    type: [Number, null],
    required: true,
  },
  quotations: {
    type: Array,
    required: true,
  },
  activeView: {
    type: String,
    default: "items-only",
  },
  noHeader: {
    type: Boolean,
    default: false,
  },
  disableActions: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "update:hoveredQuotation",
  "onCancelItem",
  "on-update-item-status",
  "onProduceItem",
]);

const { permissions } = storeToRefs(useAuthStore());

const producedItems = ref([]);
const selectedQutation = ref(null);
const toggleItem = (itemId, qutaionId) => {
  selectedQutation.value = qutaionId;
  const index = producedItems.value.findIndex((item) => item.id === itemId);
  if (index > -1) {
    producedItems.value.splice(index, 1);
  } else {
    producedItems.value.push({
      id: itemId,
    });
  }
};

const store = useStore();
const me = computed(() => store.state.settings.me);
const { t: $t } = useI18n();

const {
  statusMap,
  isStatusInGroup,
  statusClassesMap,
  statusHoverClassesMap,
  getNextStatus,
  beautify,
} = useOrderStatus();
const { saving } = storeToRefs(useSalesStore());

const producerType = ref(1);

const nextStatus = (status) => statusMap[getNextStatus(status)];

const getHoverClass = (quotationId) =>
  saving.value
    ? ""
    : {
        "!bg-gray-100 dark:!bg-gray-800": props.hoveredQuotation === quotationId,
      };

const getProducerDropdownType = (item) =>
  item.status.code === statusMap.DRAFT || item.status?.code === statusMap.NEW ? "editable" : "";

const handleMouseOver = (quotationId) => {
  emit("update:hoveredQuotation", quotationId);
};

const menuItems = [
  {
    heading: $t("Item actions"),
    items: [
      {
        action: "cancel",
        icon: "xmark",
        title: $t("cancel item"),
        classes: "text-red-500",
        show: true,
      },
    ],
  },
];

function menuItemClicked(event, quotation, item) {
  switch (event) {
    case "cancel":
      emit("onCancelItem", { quotationId: quotation.id, itemId: item.id });
      break;
    default:
      break;
  }
}

async function handleItemToggleFunction(quotationId, itemId, status, item) {
  if (item.status.code === statusMap.NEW && me.value.tenant_id !== item.product.external_id) {
    let data = {};
    if (producedItems.value.length > 0) {
      data = {
        items: producedItems.value,
      };
    } else {
      data = {
        items: [{ id: itemId }],
      };
    }
    emit("onProduceItem", { quotationId, itemId, status, data });
  } else {
    handleItemStatusChange(quotationId, itemId, status);
  }
}

async function handleItemStatusChange(quotationId, itemId, status) {
  emit("on-update-item-status", { quotationId, itemId, status });
}
</script>

<style lang="scss" scoped>
.items-table {
  @apply w-full table-fixed text-left text-sm;

  tbody {
    @apply border-b border-b-gray-200 dark:border-b-black;
  }

  thead {
    @apply h-8 border-b-2 bg-white/60 text-xs uppercase shadow-sm dark:border-b-black dark:bg-gray-700/60 dark:text-white dark:shadow-gray-900 !important;
  }

  tbody {
    tr {
      @apply h-12;
    }
  }

  td,
  th {
    @apply overflow-y-visible truncate px-2 py-1;
  }

  td:last-child,
  th:last-child {
    @apply text-right;
  }
}
</style>
