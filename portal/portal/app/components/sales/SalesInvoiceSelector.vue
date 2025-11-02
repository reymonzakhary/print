<template>
  <section>
    <div class="flex items-center justify-between mb-1">
      <p class="text-xs font-bold tracking-wide uppercase">
        {{ $t("invoice address") }}
      </p>
      <UIButton
        v-if="editable && !isHaveExternalItem && !noShow"
        :icon="['fal', 'plus']"
        variant="link"
        @click="emit('on-new-address')"
      >
        {{ $t("new address") }}
      </UIButton>
    </div>
    <UIVSelect
      v-if="editable && !isHaveExternalItem && !noShow"
      v-bind="$attrs"
      :disabled="disabled"
      :model-value="props.modelValue"
      :options="addresses"
      label="full_address"
      :placeholder="$t('select an address')"
      @update:model-value="emit('update:modelValue', $event)"
    >
      <template #option="address">
        <div class="w-full py-1 text-xs">
          <p v-if="address.type" class="text-sm font-bold">{{ address.type }}:</p>
          <p v-if="address.full_name" class="font-bold text-gray-700">
            {{ address.full_name }}
          </p>
          <p v-if="address.company_name" class="text-gray-700">
            {{ address.company_name }}
          </p>
          <p>{{ address.address }} {{ address.number }}</p>
          <p>{{ address.zip_code }} {{ address.city }}</p>
        </div>
      </template>
    </UIVSelect>
    <div
      v-if="props.modelValue"
      class="p-2 pt-3 -mt-1 text-xs bg-gray-200 rounded-b dark:bg-gray-900"
    >
      <ul>
        <li v-if="props.modelValue.full_name" class="list-grid">
          <font-awesome-icon :icon="['fal', 'user']" />
          <span>{{ props.modelValue.full_name }}</span>
        </li>
        <li v-if="props.modelValue.company_name" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'building']" />
          {{ props.modelValue.company_name }}
        </li>
        <li v-if="props.modelValue.tax_nr" class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'mobile-android']" />
          {{ props.modelValue.tax_nr }}
        </li>
        <li class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'map-location-dot']" />
          {{ props.modelValue.address }} {{ props.modelValue.number }}
        </li>
        <li class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'city']" />
          {{ props.modelValue.zip_code }} {{ props.modelValue.city }}
        </li>
        <li class="list-grid">
          <font-awesome-icon class="mr-1" :icon="['fal', 'earth-europe']" />
          {{ props.modelValue.region }}
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [Object, null],
    required: true,
  },
  addresses: {
    type: Array,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    required: true,
  },
  team: {
    type: [Number, String, null],
    required: true,
  },
  noShow: {
    type: Boolean,
    default: false,
  },
  onlyShow: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "update:team", "on-new-address"]);

const { isEditable, isHaveExternalItem } = storeToRefs(useSalesStore());

const editable = computed(() => isEditable.value && !props.onlyShow);
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
