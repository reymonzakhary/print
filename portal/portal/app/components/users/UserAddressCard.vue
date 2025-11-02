<template>
  <div class="mt-2">
    <AddressCardHeader
      :type="address.type"
      :disabled="disabled"
      :may-update="mayUpdate"
      :may-delete="mayDelete"
      @update-address="$emit('update-address')"
      @delete-address="$emit('delete-address')"
    />
    <UICard class="rounded-t !py-0" :class="!address.type ? `border border-theme-100` : ``">
      <div
        class="relative flex w-full flex-wrap rounded border-theme-100 bg-white p-2 shadow-md shadow-gray-200 dark:border-black dark:bg-gray-700 dark:shadow-gray-900"
        :class="address.type ? 'rounded-tl-none border' : 'border-t'"
      >
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("street") }}
          </p>
          <p>{{ address.address }}</p>
        </span>
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("house number") }}
          </p>
          <p>{{ address.number }}</p>
        </span>
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("zipcode") }}
          </p>
          <p>{{ address.zip_code }}</p>
        </span>
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("city") }}
          </p>
          <p>{{ address.city }}</p>
        </span>
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("region") }}
          </p>
          <p>{{ address.region }}</p>
        </span>
        <span class="mb-2 w-1/2">
          <p class="text-xs font-bold uppercase tracking-wide">
            {{ $t("country") }}
          </p>
          <p>
            {{ address.country?.name }}
          </p>
        </span>
      </div>
    </UICard>
  </div>
</template>

<script>
export default {
  name: "UserAddress",
  props: {
    address: {
      type: Object,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    mayUpdate: {
      type: Boolean,
      default: false,
    },
    mayDelete: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["delete-address", "edit-address", "update-address"],
  data() {
    return {
      showRemoveAddressModal: false,
    };
  },
};
</script>
