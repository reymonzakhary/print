<template>
  <div class="flex items-center justify-between">
    <span class="text-gray-500">
      <font-awesome-icon :icon="['fal', 'calendar-day']" />
      {{ edited_moment(props.createdAt) }}
    </span>
    <div class="flex items-center">
      <UIButton
        v-if="permissions.includes('orders-history-access')"
        :icon="['fa', 'clock-rotate-left']"
        variant="link"
        @click="emit('on-show-history')"
      />
      <ItemMenu
        menu-icon="ellipsis-h"
        menu-class="w-8 h-8 rounded-full hover:bg-gray-300 dark:hover:bg-gray-700"
        dropdown-class="w-48 text-sm"
        :menu-items="menuItems"
        @item-clicked="menuItemClicked($event, quotation)"
      />
    </div>
  </div>
</template>

<script setup>
import moment from "moment";

const { t: $t } = useI18n();
const { permissions } = storeToRefs(useAuthStore());
const { isExternal, criticalFlag } = useSalesStore();
const { isStatusInGroup } = useOrderStatus();

const props = defineProps({
  createdAt: {
    type: String,
    required: true,
  },
  status: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["on-show-history", "on-archive-order", "on-print-order"]);

const menuItems = [
  {
    heading: $t("Order actions"),
    items: [
      {
        action: "print",
        icon: "print",
        title: $t("print order"),
        show: true,
      },
      {
        action: "delete",
        icon: "cabinet-filing",
        title: $t("archive order"),
        show:
          permissions.value.includes("orders-delete") &&
          !isExternal &&
          criticalFlag !== "archived" &&
          isStatusInGroup(props.status, "ARCHIVABLE"),
      },
    ],
  },
];

function menuItemClicked(event, quotation) {
  switch (event) {
    case "print":
      emit("on-print-order", quotation);
      break;
    case "delete":
      emit("on-archive-order", quotation);
      break;
    default:
      break;
  }
}

function edited_moment(datestr) {
  datestr = datestr ? datestr : moment().format("YYYY-MM-DD LTS");
  return moment(datestr, "YYYY-MM-DD LTS").calendar(null, {
    sameDay: "HH:mm",
    lastDay: "[Yesterday]",
    lastWeek: "DD-MM-YYYY",
    sameElse: "DD-MM-YYYY",
  });
}
</script>
