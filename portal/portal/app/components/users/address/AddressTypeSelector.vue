<template>
  <UISwitchList class="rounded bg-gray-200">
    <UISwitchListItem
      v-model="hasAddressType"
      class="rounded-t !bg-gray-200"
      name="addressType"
      :label="$t('Address type')"
    />
    <UISwitchList v-if="hasAddressType">
      <UISwitchListItem
        v-for="(type, index) in ['delivery', 'invoice']"
        :key="index"
        class="font-normal capitalize"
        :name="type"
        :label="type"
        :model-value="localAddressType === type"
        @update:model-value="$event && (localAddressType = type)"
      />
    </UISwitchList>
  </UISwitchList>
</template>

<script>
export default {
  name: "AddressTypeSelector",
  props: {
    addressType: {
      type: [String, Boolean],
      default: false,
    },
  },
  emits: ["addressTypeChange"],
  data() {
    return {
      hasAddressType: false,
      localAddressType: "",
    };
  },
  watch: {
    addressType: {
      handler(newVal) {
        this.hasAddressType = !!newVal;
        this.localAddressType = newVal;
      },
      immediate: true,
    },
    localAddressType(newVal) {
      this.emitAddressType(newVal);
    },
    customAddressType(newVal) {
      if (this.localAddressType === "custom") {
        this.emitAddressType(newVal);
      }
    },
  },
  methods: {
    emitAddressType(value) {
      this.$emit("addressTypeChange", value);
    },
  },
};
</script>
