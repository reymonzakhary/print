<template>
  <section v-if="(props.address && props.address.id !== null) || editable && !isHaveExternalItem">
    <div class="mb-1 flex items-center justify-between">
      <p class="text-xs font-bold uppercase tracking-wide">
        {{ props.method !== 'pickup' ? $t("delivery") : $t("pickup") }} <small v-if="props.optional">*{{ $t("optional") }}</small>
      </p>
      <UIButton
        v-if="
          editable &&
          !isHaveExternalItem &&
          hasPermissionGroup(permissions.members.groups.addressCreate) &&
          props.method !== 'pickup'
        "
        :icon="['fal', 'plus']"
        variant="link"
        :disabled="(isUsingTeamAddresses && teams && teams.length === 0) || !props.user"
        :class="buttonClass"
        @click="emit('on-new-address')"
      >
        {{ $t("new address") }}
      </UIButton>
    </div>
    <div class="grid grid-cols-2">
      <SalesTeamSelector
        v-if="isUsingTeamAddresses && !onlyShow"
        v-tooltip.right="
          props.method === 'pickup'
            ? $t(`selecting a team is abundant with delivery method 'pickup' selected`)
            : ''
        "
        class="col-span-2"
        :class="{
          'mb-2': editable,
        }"
        :model-value="props.team"
        :overview-class="[props.overviewClass, 'px-2 py-1']"
        :disabled="props.method === 'pickup' || props.disabled"
        no-header
        @update:model-value="emit('update:team', $event)"
      />
      <UIVSelect
        v-if="editable && !isHaveExternalItem"
        :model-value="props.method"
        :options="methodOptions"
        :class="dropdownClass"
        :disabled="props.methodDisabled"
        :placeholder="$t('select a method')"
        :reduce="(method) => method.value"
        class="rounded-none rounded-l"
        @update:model-value="emit('update:method', $event)"
      />
      <UIVSelect
        v-if="editable && !isHaveExternalItem"
        v-tooltip.right="
          props.method === 'delivery' && isUsingTeamAddresses && !props.team
            ? $t('select a team first')
            : ''
        "
        :disabled="
          (props.method === 'delivery' && isUsingTeamAddresses && !props.team) || props.disabled
        "
        :model-value="
          chooseableAddresses.find((address) => address.id === props.address?.id) || props.address
        "
        :options="chooseableAddresses"
        label="full_address"
        :class="dropdownClass"
        :placeholder="$t('select an address')"
        class="rounded-none rounded-r"
        @update:model-value="emit('update:address', $event)"
      >
        <template #option="full_address">
          <span v-if="full_address.id === null" class="text-sm font-bold capitalize">
            {{ full_address.address }}
          </span>
          <div v-else class="w-full py-1 text-xs">
            <p v-if="full_address.type">
              <span class="text-sm font-bold">{{ full_address.type }}:</span>
              <small class="ml-1">
                {{ full_address.team_address ? "Team" : "" }}
              </small>
            </p>
            <p v-if="full_address.full_name" class="font-bold text-gray-700">
              {{ full_address.full_name }}
            </p>
            <p v-if="full_address.company_name" class="text-gray-700">
              {{ full_address.company_name }}
            </p>
            <p>{{ full_address.address }} {{ full_address.number }}</p>
            <p>{{ full_address.zip_code }} {{ full_address.city }}</p>
          </div>
        </template>
      </UIVSelect>
    </div>
    <div
      v-if="props.address && props.address.id !== null"
      class="-mt-1 rounded-b bg-gray-200 p-2 pt-3 text-xs dark:bg-gray-900"
      :class="overviewClass"
    >
      <ul>
        <li v-if="props.address.full_name" class="list-grid">
          <font-awesome-icon :icon="['fal', 'user']" />
          <span>{{ props.address.full_name }}</span>
        </li>
        <li v-if="props.address.company_name" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'building']" />
          {{ props.address.company_name }}
        </li>
        <li v-if="props.address.tax_nr" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'mobile-android']" />
          {{ props.address.tax_nr }}
        </li>
        <li v-if="props.address.address" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'map-location-dot']" />
          {{ props.address.address }} {{ props.address.number }}
        </li>
        <li v-if="props.address.zip_code" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'city']" />
          {{ props.address.zip_code }} {{ props.address.city }}
        </li>
        <li v-if="props.address.region" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'earth-europe']" />
          {{ props.address.region }}
        </li>
      </ul>
    </div>
  </section>
  <section v-else class="text-xs">
    <font-awesome-icon :icon="['fal', 'circle-exclamation']" class="mr-1" />
    {{ $t("No address selected") }}
  </section>
</template>

<script setup>
const props = defineProps({
  team: {
    type: [Number, String, null],
    required: true,
  },
  user: {
    type: [Object, null],
    default: null,
  },
  method: {
    type: String,
    required: true,
  },
  address: {
    type: [Object, null],
    required: true,
  },
  dropdownClass: {
    type: String,
    default: "",
  },
  overviewClass: {
    type: [String, Object],
    default: "",
  },
  buttonClass: {
    type: String,
    default: "",
  },
  optional: {
    type: Boolean,
    default: false,
  },
  onlyShow: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  methodDisabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:address", "update:method", "update:team", "on-new-address"]);

const { permissions, hasPermissionGroup } = usePermissions();

const { t: $t } = useI18n();

const {
  isEditable,
  isHaveExternalItem,
  isUsingTeamAddresses,
  teams,
  deliveryAddresses,
  pickupAddresses,
  salesContext,
} = storeToRefs(useSalesStore());

/**
 * Reset address when delivery addresses or pickup addresses change (another customer is selected)
 */
watch(
  [() => deliveryAddresses.value, () => pickupAddresses.value],
  () => {
    emit("update:address", null);
  },
  { deep: true },
);

const methodOptions = ref([
  { value: "delivery", label: $t("delivery") },
  { value: "pickup", label: $t("pickup") },
]);

watch(
  () => salesContext.value,
  (newValue) => !newValue && (methodOptions.value = [{ value: "delivery", label: $t("delivery") }]),
  { immediate: true },
);

const editable = computed(() => isEditable.value && !props.onlyShow);
const chooseableAddresses = computed(() => {
  if (props.method === "pickup") {
    return pickupAddresses.value;
  }

  if (deliveryAddresses.value && deliveryAddresses.value.length > 0) {
    return deliveryAddresses.value.filter((address) => {
      return !props.team || `${address.team_id}` === `${props.team}`;
    });
  }
  return deliveryAddresses.value;
});
</script>

<style lang="scss" scoped>
.list-grid {
  display: grid;
  grid-template-columns: 25px auto;

  &:not(:last-child) {
    @apply mb-1;
  }
}
</style>
