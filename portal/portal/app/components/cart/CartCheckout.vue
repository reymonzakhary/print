<template>
  <div
    class="sticky top-0 flex flex-col w-full h-full p-4 pb-40 xl:rounded-r"
    style="max-height: calc(100vh - 100px)"
  >
    <header class="flex items-center justify-between w-full">
      <p class="font-bold first-letter:uppercase">{{ $t("delivery") }}</p>
    </header>

    <section class="mt-4">
      <div class="flex justify-between">
        <p class="text-sm font-bold tracking-wide first-letter:uppercase">
          {{ $t("for") }}
        </p>
      </div>

      <div
        v-if="me && me.profile"
        class="flex items-center p-2 mt-1 text-xs bg-gray-200 rounded dark:bg-gray-900"
      >
        <img :src="me.profile.avatar" :alt="me.profile.avatar" class="mr-2 rounded-full" />
        <div>
          <p>
            <span class="font-bold text-gray-600">#{{ me.id }}</span>
            {{ salutation }}
            {{ me.profile.first_name }}
            {{ me.profile.middle_name }}
            {{ me.profile.last_name }}
          </p>
          <p>
            <font-awesome-icon :icon="['fal', 'envelope']" />
            {{ me.email }}
          </p>
        </div>
      </div>
    </section>

    <!-- delivery -->
    <section class="mt-6">
      <div class="flex items-center">
        <p class="text-sm font-bold tracking-wide first-letter:uppercase">
          {{ $t("ship to") }}
        </p>
        <button
          class="flex items-center ml-auto text-sm transition-colors text-theme-500 hover:text-theme-600"
          @click="handleNewAddress"
        >
          <font-awesome-icon class="block mr-1" :icon="['fal', 'plus']" />
          {{ $t("new address") }}
        </button>
      </div>

      <div class="flex">
        <div v-if="addresses" class="relative w-full">
          <v-select
            :disabled="loading"
            :class="{ 'bg-gray-200': loading }"
            class="text-sm transition-all duration-100 bg-white border border-gray-200 rounded has-icon dark:bg-gray-700 dark:border-gray-900 dark:text-white"
            :options="addresses"
            :label="'address'"
            :model-value="`
                     ${delivery_address && delivery_address.type ? delivery_address.type : ''} 
                     ${
                       delivery_address && delivery_address.full_name
                         ? delivery_address.full_name
                         : ''
                     } 
                     ${delivery_address ? delivery_address.address : $t('select address')}
                     ${delivery_address ? delivery_address.number : ''}
                     ${delivery_address ? delivery_address.zip_code : ''}
                     ${delivery_address ? delivery_address.city : ''}
                     ${delivery_address && delivery_address.region ? delivery_address.region : ''}
                     ${delivery_address ? delivery_address.country : ''}
                  `"
            @update:model-value="set_cart_address($event)"
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
          </v-select>
          <font-awesome-icon
            v-if="loading === 'address'"
            class="absolute top-0 right-0 z-10 mt-2 mr-2 text-theme-500 fa-spin"
            :icon="['fad', 'spinner-third']"
          />
        </div>
      </div>

      <div v-if="delivery_address" class="p-2 -mt-1 text-xs bg-gray-200 rounded-b dark:bg-gray-900">
        <div v-if="delivery_address.full_name" class="capitalize">
          <div class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'user']" />
          </div>
          {{ delivery_address.full_name }}
        </div>
        <div v-else class="flex items-center capitalize">
          <template v-if="me.profile">
            <span class="inline-block w-6">
              <font-awesome-icon class="mr-1" :icon="['fal', 'user']" />
            </span>
            {{ salutation }}
            {{ me.profile.first_name }}
            {{ me.profile.middle_name }}
            {{ me.profile.last_name }}
          </template>
        </div>
        <div v-if="delivery_address.company_name" class="capitalize">
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'building']" />
          </span>
          {{ delivery_address.company_name }}
        </div>
        <div v-if="delivery_address.tax_nr" class="capitalize">
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'mobile-android']" />
          </span>
          {{ delivery_address.tax_nr }}
        </div>
        <div v-if="delivery_address.phone_number" class="capitalize">
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'mobile-android']" />
          </span>
          {{ delivery_address.company_name }}
        </div>
        <div class="capitalize">
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'map-location-dot']" />
          </span>
          {{ delivery_address.address }}
          {{ delivery_address.number }}
        </div>
        <div>
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'city']" />
          </span>
          {{ delivery_address.zip_code }}
          {{ delivery_address.city }}
        </div>
        <div v-if="delivery_address.region">
          <span class="inline-block w-6">
            <font-awesome-icon class="mr-1" :icon="['fal', 'earth-europe']" />
          </span>
          {{ delivery_address.region }}
        </div>
      </div>
    </section>

    <!-- reference -->
    <section v-if="me" class="my-6">
      <p
        v-if="reference !== null"
        class="relative text-sm font-bold tracking-wide first-letter:uppercase"
      >
        {{ $t("with the following reference") }}
        <font-awesome-icon
          v-if="loading === 'ref'"
          class="text-theme-500 fa-spin"
          :icon="['fad', 'spinner-third']"
        />
      </p>

      <textarea
        class="w-full p-1 text-sm duration-100 bg-white border border-gray-200 rounded text-blacktransition-all has-icon dark:bg-gray-700 dark:border-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-theme-400"
        :disabled="loading !== false"
        :class="{ 'bg-gray-200': loading }"
        rows="2"
        placeholder="Some reference"
        maxlength="250"
        :value="reference"
        @input="set_cart_reference($event.target.value)"
      />
    </section>

    <footer
      class="fixed bottom-0 right-0 flex items-center justify-between w-full p-2 mb-10 bg-white shadow-md shadow-gray-200 dark:shadow-gray-900 md:w-2/3 lg:absolute lg:w-48 xl:w-full xl:absolute xl:mb-0 xl:mr-0 xl:rounded dark:bg-gray-700"
    >
      <button
        class="flex items-center px-2 py-1 my-2 mr-1 text-sm transition-colors duration-150 rounded-full text-theme-500 hover:text-theme-600 hover:bg-theme-100"
        @click="set_checkout(false)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" class="mr-1" />
        {{ $t("back") }}
      </button>
      <button
        v-if="delivery_address"
        class="w-full px-2 py-1 my-2 text-sm text-white transition-colors duration-150 bg-green-500 rounded-full hover:bg-green-600"
        @click="checkout()"
      >
        <font-awesome-icon :icon="['fas', 'check']" />
        {{ $t("place order") }}
      </button>
      <p v-else class="w-full italic text-right text-gray-500">
        <font-awesome-icon :icon="['fal', 'triangle-exclamation']" class="mr-2 text-yellow-400" />
        {{ $t("select an address") }}
      </p>
    </footer>

    <AddressFormModal
      v-if="showAddressFormModal"
      :is-creating-for-team="appUsesTeamAddresses"
      :teams="teams"
      @close-modal="showAddressFormModal = false"
      @create-address="handleAddressCreate"
      @on-team-change="handleTeamChange"
    />
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  emits: ["address-created"],
  setup() {
    const api = useAPI();
    const { handleError } = useMessageHandler();
    return { api, handleError };
  },
  data() {
    return {
      loading: false,
      showAddressFormModal: false,
      chosenTeamForAddress: null,
      teams: [],
    };
  },
  computed: {
    ...mapState({
      addresses: (state) => state.addresses.addresses,
      me: (state) => state.settings.me,
      settings: (state) => state.settings.settings,
      delivery_address: (state) => state.cart.delivery_address,
      reference: (state) => state.cart.reference,
      modalName: (state) => state.users.modalName,
      appUsesTeamAddresses: (state) => state.settings.isUsingTeamAddresses,
    }),
    salutation() {
      if (this.me.profile.gender) {
        if (this.me.profile.gender === "male") {
          return "Mr.";
        }
        if (this.me.profile.gender === "female") {
          return "Ms.";
        }
        if (this.me.profile.gender === "other") {
          return "Mx.";
        }
      }
      return "";
    },
  },
  watch: {
    delivery_address(newVal) {
      return newVal;
    },
    addresses: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  async beforeMount() {
    // if customer => select addresses
    if (this.me) {
      this.get_addresses({ user_id: { id: this.me.id, isMember: true, }, shop: true });
    }

    try {
      const response = await this.api.get("/account/me");
      this.teams = response.data.teams.filter((team) => team.admin);
    } catch (error) {
      this.handleError(error);
    }
  },
  methods: {
    ...mapMutations({
      store: "users/store",
      set_modal_name: "users/set_modal_name",
      select_user: "users/select_user",
      store_addresses: "addresses/store",
      set_checkout: "cart/set_checkout",
      set_cart_products: "cart/set_cart_products",
      set_cart_address: "cart/set_cart_address",
      set_cart_reference: "cart/set_cart_reference",
    }),
    ...mapActions({
      get_addresses: "addresses/get",
    }),
    handleTeamChange(teamId) {
      this.chosenTeamForAddress = teamId;
    },
    handleNewAddress() {
      this.showAddressFormModal = true;
    },
    async handleAddressCreate(address) {
      if (address.full_name === "") {
        address.full_name = this.me.profile.first_name + " " + this.me.profile.last_name;
      }
      const isMember = this.me.ctx[0].member;
      const endpoint = isMember ? "members" : "users";
      const userAddressEndpoint = `${endpoint}/${this.me.id}/addresses`;
      const teamAddressEndpoint = `teams/${this.chosenTeamForAddress}/addresses`;
      const apiUrl = this.appUsesTeamAddresses ? teamAddressEndpoint : userAddressEndpoint;
      try {
        await this.api.post(apiUrl, address);
        this.showAddressFormModal = false;
        await this.$store.dispatch("addresses/get", { id: this.me.id });
        this.$emit("address-created");
      } catch (error) {
        this.handleError(error);
      }
    },
    checkout() {
      const payload = {
        address: this.delivery_address.id,
      };

      if (this.reference) {
        Object.assign(payload, { reference: this.reference.toString() });
      }

      this.api
        .post("/cart/checkout", payload)
        .then((response) => {
          this.set_cart_address("");
          this.set_cart_reference("");
          this.set_checkout(false);
          this.set_cart_products([]);
          this.$parent.thanks = true;
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.set_modal_name("");
    },
  },
};
</script>
