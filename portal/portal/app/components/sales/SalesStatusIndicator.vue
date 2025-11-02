<template>
  <div
    class="group relative w-full truncate rounded px-2 py-1 text-center text-xs"
    :class="[
      (me.tenant_id === props.item?.product?.external_id ||
        props.item?.status?.code !== statusMap.NEW) &&
        statusClassesMap[props.status],
      nextStatus &&
      !isArchived &&
      !isEditable &&
      me.tenant_id === props.item?.product?.external_id &&
      permissions.includes('orders-update')
        ? statusHoverClassesMap[statusMap[nextStatus]]
        : '',
      me.tenant_id !== props.item?.product?.external_id &&
        props.item?.status?.code === statusMap.NEW &&
        'w-fit bg-green-100 text-green-500 dark:bg-green-900 2xl:aspect-auto 2xl:w-full',
      me.tenant_id !== props.item?.product?.external_id &&
        props.item?.status?.code === statusMap.NEW &&
        !isEditable &&
        permissions.includes('orders-update') &&
        'hover:bg-green-200 dark:hover:bg-green-800',
      // me.tenant_id !== props.item?.product?.external_id &&
      //   props.item?.status?.code !== statusMap.NEW &&
      //   'aspect-square w-fit bg-green-200 text-green-500 dark:bg-green-900 2xl:aspect-auto 2xl:w-full',
      {
        '!border-transparent !bg-transparent !px-0 !text-left': props.outline,
        'cursor-pointer':
          nextStatus &&
          !isArchived &&
          permissions.includes('orders-update') &&
          !isEditable &&
          (me.tenant_id === props.item?.product?.external_id ||
            props.item?.status?.code === statusMap.NEW),
      },
    ]"
    @click="handleSetNextStatus"
  >
    <font-awesome-icon
      :icon="[props.outline ? 'far' : 'fal', getStatusIcon(name)]"
      class="mr-1 rounded-full"
      :class="props.outline ?? statusClassesMap[props.status]"
    />
    <!-- <font-awesome-icon
      v-if="props.outline"
      :icon="['far', 'circle']"
      class="mr-1 rounded-full"
      :class="statusClassesMap[props.status]"
    /> -->
    <span
      :class="{
        'group-hover:hidden':
          nextStatus && !isArchived && !isEditable && me.tenant_id === props.item?.product?.external_id && permissions.includes('orders-update'),
      }"
    >
      <span
        v-if="
          me.tenant_id === props.item?.product?.external_id ||
          props.item?.status?.code !== statusMap.NEW
        "
        >{{ nameBeautified }}</span
      >
      <span
        v-if="
          me.tenant_id !== props.item?.product?.external_id &&
          props.item?.status?.code === statusMap.NEW
        "
        >{{ $t("Produce") }}</span
      >
      <!--      <span-->
      <!--        v-if="-->
      <!--          me.tenant_id !== props.item?.product?.external_id &&-->
      <!--          props.item?.status?.code !== statusMap.NEW-->
      <!--        "-->
      <!--        >{{ $t("Produced") }}</span-->
      <!--      >-->
      <span v-if="itemStatuses.length"> {{ itemsEqualToOrder }}/{{ itemStatuses.length }} </span>
    </span>
    <span
      v-if="
        nextStatus &&
        !isArchived &&
        permissions.includes('orders-update') &&
        !isEditable &&
        me.tenant_id === props.item?.product?.external_id
      "
      :class="['hidden', { 'group-hover:inline-block': nextStatus }]"
    >
      {{ $t("set item to") }} {{ beautify(nextStatus) }}
    </span>
    <button
      v-if="nextStatus && !props.outline && !isEditable && permissions.includes('orders-update') && (me.tenant_id === props.item?.product?.external_id ||
          props.item?.status?.code === statusMap.NEW)"
      class="absolute right-0 top-0 aspect-square h-full bg-transparent"
    >
      <font-awesome-icon
        :icon="['fas', 'arrow-right']"
        class="absolute inset-0 m-auto"
        :class="statusClassesMap[nextStatus]"
      />
    </button>
  </div>
</template>

<script setup>
import { useStore } from "vuex";
import { computed } from "vue";

const {
  statusMap,
  getStatusName,
  getStatusIcon,
  isStatusInGroup,
  beautify,
  getNextStatus,
  statusClassesMap,
  statusHoverClassesMap,
} = useOrderStatus();

const { saving } = storeToRefs(useSalesStore());
const store = useStore();
const me = computed(() => store.state.settings.me);

const props = defineProps({
  status: {
    type: Number,
    required: true,
  },
  itemStatuses: {
    type: Array,
    default: () => [],
  },
  isArchived: {
    type: Boolean,
    default: false,
  },
  isEditable: {
    type: Boolean,
    default: false,
  },
  outline: {
    type: Boolean,
    default: false,
  },
  withUpdate: {
    type: Boolean,
    default: false,
  },
  item: {
    type: Object,
    default: () => {},
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:status", "produce:item"]);

const { permissions } = storeToRefs(useAuthStore());

const itemsEqualToOrder = computed(() => {
  return props.itemStatuses.filter((itemStatus) => itemStatus === props.status).length;
});

const name = computed(() => {
  return getStatusName(props.status);
});

const nameBeautified = computed(() => {
  return beautify(name.value);
});

const nextStatus = computed(() => {
  if (props.withUpdate && props.status === statusMap.EDITING) return getStatusName(statusMap.NEW);

  return (
    isStatusInGroup(props.status, "NEXTABLE") && props.withUpdate && getNextStatus(props.status)
  );
});

function handleSetNextStatus(event) {
  if (
    !nextStatus.value ||
    props.isEditable ||
    saving.value ||
    !permissions.value.includes("orders-update") ||
    props.disabled
  )
    return;
  event.stopPropagation();
  if (
    props.item?.status?.code === statusMap.NEW &&
    me.value.tenant_id !== props.item?.product?.external_id
  ) {
    emit("produce:item");
  } else if (
    props.item?.status?.code !== statusMap.NEW &&
    me.value.tenant_id !== props.item?.product?.external_id
  ) {
    return;
  } else {
    emit("update:status", statusMap[nextStatus.value]);
  }
}
</script>
