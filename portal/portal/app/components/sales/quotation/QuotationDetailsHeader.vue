<template>
  <div class="flex items-center justify-between">
    <span class="text-gray-500">
      <font-awesome-icon :icon="['fal', 'calendar-day']" />
      {{ edited_moment(props.createdAt) }}
    </span>
    <div class="flex items-center">
      <UIButton
        v-if="permissions.includes('quotations-history-access')"
        :icon="['fa', 'clock-rotate-left']"
        variant="link"
        @click="emit('on-show-history')"
      />
      <ItemMenu
        v-if="
          (permissions.includes('quotations-delete') && !isExternal) ||
          isStatusInGroup(props.status, 'PRINTABLE')
        "
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

const props = defineProps({
  createdAt: {
    type: String,
    required: true,
  },
  status: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(["on-show-history", "on-delete-quotation", "on-print-quotation"]);

const { isStatusInGroup } = useOrderStatus();
const { isExternal } = storeToRefs(useSalesStore());
const { permissions } = storeToRefs(useAuthStore());

const menuItems = [
  {
    heading: $t("Quotation actions"),
    items: [
      {
        action: "print",
        icon: "print",
        title: $t("Print quotation"),
        show: isStatusInGroup(props.status, "PRINTABLE"),
      },
      {
        action: "delete",
        icon: "trash-can",
        title: $t("delete quotation"),
        classes: "text-red-500",
        show: permissions.value.includes("quotations-delete") && !isExternal.value,
      },
    ],
  },
];

function menuItemClicked(event, quotation) {
  switch (event) {
    case "print":
      emit("on-print-quotation", quotation);
      break;
    case "delete":
      emit("on-delete-quotation", quotation);
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
