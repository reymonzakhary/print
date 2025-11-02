<!-- TODO: Refactor all instances of this component to use the updated version in future development -->
<template>
  <div>
    <!-- TYPE -->
    <div v-if="!first" class="p-4 font-bold bg-gray-200 rounded">
      <span class="flex items-start justify-between">
        <div class="flex text-xs font-bold tracking-wide uppercase" for="type">
          {{ $t("address") }} {{ $t("type") }}
          <div
            class="relative w-10 h-4 mx-2 mr-4 transition duration-200 ease-linear rounded-full cursor-pointer"
            :class="[set_type === true ? 'bg-theme-400' : 'bg-gray-400']"
          >
            <label
              for="set_type_toggle"
              class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
              :class="[
                set_type === true
                  ? 'translate-x-6 border-theme-500'
                  : 'translate-x-0 border-gray-400',
              ]"
            ></label>
            <input
              id="set_type_toggle"
              type="checkbox"
              class="w-full h-full appearance-none active:outline-none focus:outline-none"
              :checked="set_type === true"
              @change="set_type = $event.target.checked"
            />
          </div>
        </div>

        <section v-if="set_type">
          <div v-for="(type, i) in types" :key="i">
            <input
              :id="type"
              v-model="inputdata.type"
              type="radio"
              name="type"
              :value="type"
            />
            <label :for="type" class="capitalize">{{ type }}</label>
          </div>
          <div>
            <input v-model="inputdata.type" type="radio" name="type" />
            <label for="">
              <input
                id="type"
                v-model="inputdata.type"
                class="px-2 py-1 mx-1 text-sm text-black bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                type="text"
                :placeholder="$t('custom address type')"
                value=""
              />
            </label>
          </div>
        </section>
      </span>
    </div>

    <!-- COUNTRY -->
    <div
      v-if="countries.length > 1"
      class="flex items-center justify-between p-3 font-bold"
    >
      <label
        class="block w-1/2 text-xs font-bold tracking-wide uppercase"
        for="country"
      >
        {{ $t("country") }}
      </label>

      <select
        id="countries"
        v-model="inputdata.country_id"
        name="countries"
        class="w-1/2 px-2 py-1 mx-2 text-black bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
      >
        <option disabled value="">Select option</option>
        <option
          v-for="country in countries"
          :key="country.id"
          :value="country.id"
        >
          {{ country.name }}
        </option>
      </select>
    </div>

    <section class="mt-3">
      <!-- EXTENDED FIELDS -->
      <div v-if="extended_fields" class="w-full p-4">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="state"
          >
            {{ $t("full name") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="full_name"
              v-model="inputdata.full_name"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="Full Name"
            />
            <span class="mb-2 ml-2 text-xs text-gray-500">
              {{ $t("if not filled the customer will be used") }}
            </span>
            <span class="absolute ml-2 text-xs text-gray-500 -left-2 -top-4">
              {{ $t("optional") }}
            </span>
          </div>
        </span>
      </div>

      <div class="flex items-center justify-start px-3 text-sm">
        <p>
          <font-awesome-icon class="mr-1" :icon="['fal', 'building']" />
          {{ $t("business user") }}
        </p>

        <div
          class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
          :class="[business_user === true ? 'bg-theme-400' : 'bg-gray-400']"
        >
          <label
            for="business_user_toggle"
            class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
            :class="[
              business_user === true
                ? 'translate-x-6 border-theme-500'
                : 'translate-x-0 border-gray-400',
            ]"
          ></label>
          <input
            id="business_user_toggle"
            v-model="business_user"
            type="checkbox"
            class="w-full h-full appearance-none active:outline-none focus:outline-none"
            :checked="business_user === true"
          />
        </div>
      </div>

      <div v-if="business_user" class="p-4 pb-0">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="company_name"
          >
            {{ $t("company") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="company_name"
              v-model="inputdata.company_name"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="My Company"
            />
          </div>
        </span>
      </div>

      <div v-if="business_user" class="p-4 pb-0">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="vat_nr"
          >
            {{ $t("vat") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="vat_nr"
              v-model="inputdata.vat_nr"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="VAT09876"
            />
          </div>
        </span>
      </div>

      <div v-if="business_user" class="p-4 border-b">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="company_name"
          >
            {{ $t("phone number") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="phone_nr"
              v-model="inputdata.phone_nr"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="012-3456789"
            />
          </div>
        </span>
      </div>

      <template v-if="inputdata.country_id">
        <!-- ZIP -->
        <div class="p-4 pb-0">
          <span class="flex items-center justify-between">
            <label
              class="w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
              for="zipcode"
            >
              {{ $t("zipcode") }}
            </label>

            <span class="relative w-1/2">
              <input
                id="zipcode"
                ref="zip"
                v-model="inputdata.zip_code"
                class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                type="text"
                placeholder="My zipcode"
                @keyup="searchAddress('zip_code', $event.target.value)"
              />
              <span
                v-if="required"
                class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
              >
                {{ $t("required") }}
              </span>
            </span>

            <div
              v-if="
                inputdata.zip_code &&
                inputdata.zip_code.length > 0 &&
                !new_address
              "
              class="bg-white rounded shadow-md"
            >
              <template
                v-for="found_address in available_addresses"
                :key="found_address.id"
              >
                <div
                  class="p-2 my-1 border-b cursor-pointer hover:bg-gray-100"
                  @click="
                    (new_address = true),
                      (inputdata.address = found_address.address),
                      (inputdata.number = found_address.number),
                      (inputdata.zip_code = found_address.zip_code),
                      (inputdata.city = found_address.city),
                      (inputdata.region = found_address.region)
                  "
                >
                  {{ found_address.address }}
                  {{ found_address.number }} <br />
                  {{ found_address.zip_code }} {{ found_address.city }}
                </div>
              </template>
              <button class="p-2 text-theme-500" @click="new_address = true">
                {{ $t("new address") }}
              </button>
            </div>
          </span>
        </div>

        <!-- NUMBER -->
        <div v-if="new_address" class="p-4 pb-0">
          <span class="flex items-center justify-between">
            <label
              class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
              for="state"
            >
              {{ $t("house number") }}
            </label>

            <div class="relative w-1/2">
              <input
                id="house_number"
                v-model="inputdata.number"
                class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                type="text"
                placeholder="123ab-c"
              />
              <span
                v-if="required"
                class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
              >
                {{ $t("required") }}
              </span>
            </div>
          </span>
        </div>
      </template>

      <div v-if="new_address" class="p-4 pb-0">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="address"
          >
            {{ $t("street") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="address"
              v-model="inputdata.address"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder=""
              autocomplete="off"
            />
            <span
              v-if="required"
              class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
            >
              {{ $t("required") }}
            </span>
          </div>
        </span>
      </div>

      <div v-if="new_address" class="p-4 pb-0">
        <span class="flex items-center justify-between">
          <label
            class="block w-1/2 mb-2 text-xs font-bold tracking-wide uppercase"
            for="state"
          >
            {{ $t("city") }}
          </label>

          <div class="relative w-1/2">
            <input
              id="city"
              v-model="inputdata.city"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="The city where I live in"
            />
            <span
              v-if="required"
              class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
            >
              {{ $t("required") }}
            </span>
          </div>
        </span>
      </div>

      <div v-if="new_address" class="p-4">
        <span class="flex items-center justify-between">
          <label
            class="block mb-2 text-xs font-bold tracking-wide uppercase"
            for="region"
          >
            {{ $t("region") }}
          </label>
          <div class="relative w-1/2">
            <input
              id="region"
              class="block w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
              type="text"
              placeholder="The region where I live in"
              @input="inputdata.region = String($event.target.value)"
            />
          </div>
        </span>
      </div>
    </section>
  </div>
</template>

<script>
import { mapMutations } from "vuex";
export default {
  props: {
    user_id: {
      type: Number,
    },
    first: {
      type: Boolean,
    },
    type: {
      type: String,
    },
    extended_fields: {
      type: Boolean,
    },
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      inputdata: {},
      countries: {},
      available_addresses: [],
      new_address: false,
      types: ["home", "work", "delivery", "invoice"],
      set_type: false,
      business_user: false,
      required: false,
    };
  },
  watch: {
    inputdata: {
      handler(val, oldVal) {
        this.set_inputdata(val);
      },
      deep: true,
    },
    new_address(newVal) {
      if (newVal === true) {
        setTimeout(() => {
          this.$refs.zip.focus();
        }, 100);
      }
    },
  },
  mounted() {
    this.api.get("/countries").then((response) => {
      this.countries = response.data;
      if (this.countries.length === 1) {
        this.inputdata.country_id = this.countries[0].id;
      }
    });

    if (this.first) {
      this.inputdata.type = "primary";
    }
  },
  methods: {
    ...mapMutations({
      set_inputdata: "addresses/set_inputdata",
    }),
    async searchAddress(key, value) {
      if (value.length < 4) return;
      await this.api
        .post(`countries/${this.inputdata.country_id}/addresses/search`, {
          [key]: value,
        })
        .then((response) => {
          if (response.data.length > 0) {
            this.available_addresses = response.data;
            this.new_address = false;
          } else {
            this.new_address = true;
          }
        })
        .catch((error) => {
          this.required = true;
          this.handleError(error);
        });
    },
  },
};
</script>
