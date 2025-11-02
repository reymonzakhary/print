<template>
  <div>
    <div class="flex flex-nowrap gap-4 overflow-x-scroll">
      <!-- New Address -->
      <div
        class="grid h-32 w-32 shrink-0 cursor-pointer place-items-center overflow-hidden text-ellipsis rounded border-2 border-dashed border-gray-300 p-4 text-gray-300 hover:border-theme-500 hover:text-theme-300 focus:border-theme-300"
        @click="selectAddress(null)"
      >
        <font-awesome-icon :icon="['fal', 'plus']" class="text-4xl" />
      </div>

      <div v-if="isFetching" class="grid h-32 w-32 place-items-center">
        <UILoader />
      </div>

      <div
        v-else-if="existingAddresses.length === 0"
        class="grid h-32 w-32 shrink-0 place-items-center rounded p-4 text-center text-sm text-gray-400"
      >
        <span>
          <font-awesome-icon :icon="['fal', 'face-frown']" class="mb-1 text-2xl" />
          <p>{{ $t("No Addresses Found") }}</p>
        </span>
      </div>

      <!-- Existing Addresses -->
      <div
        v-for="address in existingAddresses"
        v-else
        :key="address.id"
        class="h-32 w-32 shrink-0 cursor-pointer overflow-hidden text-ellipsis rounded border border-gray-300 p-4 hover:border-theme-500 focus:border-theme-300"
        @click="selectAddress(address)"
      >
        <span class="block w-full overflow-hidden text-ellipsis whitespace-nowrap">{{
          address.address
        }}</span>
        <span class="block">{{ address.zip_code }} </span>
        <span class="block">{{ address.city }}</span>
        <span class="block w-full overflow-hidden text-ellipsis whitespace-nowrap">{{
          address.region
        }}</span>
      </div>
    </div>
    <span
      v-if="existingAddresses.length > 2"
      class="float-right mt-1 inline-block text-sm text-gray-500"
    >
      {{ $t("Scroll for more") }}
      <font-awesome-icon :icon="['fal', 'arrow-right']" class="inline" />
    </span>
  </div>
</template>

<script>
export default {
  name: "AddressSearchExistingByZipcode",
  props: {
    zipCode: {
      type: String,
      required: true,
    },
    countryId: {
      type: String,
      required: true,
    },
  },
  emits: ["select-address"],
  setup() {
    const api = useAPI();
    return {
      api,
    };
  },
  data() {
    return {
      isFetching: false,
      existingAddresses: [],
      debounceTimeout: null,
    };
  },
  fetch() {
    this.fetchAddresses();
  },
  watch: {
    zipCode: {
      handler(value) {
        if (value && value.length < 4) return;
        this.fetchAddresses();
      },
      immediate: true,
    },
  },
  methods: {
    fetchAddresses() {
      this.isFetching = true;
      this.api
        .post(`countries/${this.countryId}/addresses/search`, {
          zip_code: this.zipCode,
        })
        .then((response) => {
          this.existingAddresses = response.data;
        })
        .catch((err) => {
          this.handleError(err);
        })
        .finally(() => (this.isFetching = false));
    },
    selectAddress(address) {
      this.$emit("select-address", address);
    },
  },
};
</script>
