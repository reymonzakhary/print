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
        <th :class="{ 'w-36 max-w-36': activeView !== 'items-only' }">{{ $t("producer") }}</th>
        <th class="w-12">
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
          <tr v-for="(item, index) in quotation.items" :key="`item_${item.id}`">
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
            <td class="w-96">
              <span v-if="item.product.price?.dlv">
                {{ item.product.price.dlv.days }} {{ $t("days") }}
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
                class="max-h-9 max-w-32 overflow-hidden"
                type="items"
                :object="item"
                :order_id="quotation.id"
                :index="index"
                small-progress-bar
              />
            </td>
            <td class="relative !overflow-visible" :colspan="showMenu ? 1 : 2">
              <div class="flex items-center">
                <div
                  :class="[
                    {
                      'rounded-l bg-gray-200 dark:bg-gray-900':
                        item.status.code !== 300 && getProducerDropdownType(item) === 'editable',
                    },
                    'absolute p-1',
                  ]"
                >
                  <font-awesome-icon
                    v-if="
                      item.supplier_type === 'standard' || item.supplier_type == 'manually selected'
                    "
                    :icon="['fal', 'screwdriver-wrench']"
                    :title="item.supplier_type"
                    fixed-width
                  />
                  <font-awesome-icon
                    v-else-if="
                      item.supplier_type === 'manual' || item.supplier_type == 'manually overridden'
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
                    class="pl-5 text-xs"
                    :order-id="quotation.id"
                    :item="item"
                    :selected-supplier-id="item.product.external_id"
                    :selected-supplier-name="item.product.external_name"
                    :original-supplier-id="item.product.external_id"
                    :type="getProducerDropdownType(item)"
                    :disabled="saving || !hasPermission('quotations-items-producer-update')"
                  />
                </div>
              </div>
            </td>

            <td v-if="showMenu" class="flex justify-end !overflow-visible">
              <ItemMenu
                v-if="showMenu"
                :disabled="!isStatusInGroup(item.status.code, 'CANCELABLE') || saving"
                menu-icon="ellipsis-h"
                menu-class="w-8 h-8 rounded-full hover:bg-gray-300"
                dropdown-class="w-48 text-sm right-8"
                :menu-items="menuItems"
                @item-clicked="menuItemClicked($event, quotation, item)"
              />
            </td>
          </tr>
        </template>
      </tbody>
    </transition-group>
  </table>
</template>

<script setup>
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
  isBin: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:hoveredQuotation", "onCancelItem"]);

const { permissions: newPermissions, hasPermissionGroup, hasPermission } = usePermissions();
const { t: $t } = useI18n();
const { permissions } = storeToRefs(useAuthStore());
const { statusMap, isStatusInGroup } = useOrderStatus();
const { saving } = storeToRefs(useSalesStore());

const canAccessQuotationDetails = computed(
  () =>
    (!props.isBin && hasPermissionGroup(newPermissions.quotations.groups.moduleDetails)) ||
    (props.isBin && hasPermission("quotations-trashed-read")),
);

const producerType = ref(1);

const getHoverClass = (quotationId) => {
  if (!canAccessQuotationDetails.value) return;
  if (!props.hoveredQuotation || props.hoveredQuotation !== quotationId) return "";
  return "!bg-gray-100 dark:!bg-gray-800";
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
        show: permissions.value.includes("quotations-update"),
      },
    ],
  },
];

const showMenu = computed(() => {
  return permissions.value.includes("quotations-update");
});

function menuItemClicked(event, quotation, item) {
  switch (event) {
    case "cancel":
      emit("onCancelItem", { quotationId: quotation.id, itemId: item.id });
      break;
    default:
      break;
  }
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
