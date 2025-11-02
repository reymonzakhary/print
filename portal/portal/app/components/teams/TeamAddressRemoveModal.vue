<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header>
      <p v-if="user" class="capitalize">
        {{ $t("remove") }} {{ $t("address from") }} {{ type }}
        {{ user.profile.first_name }}
        {{ user.profile.last_name }}
      </p>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap">
        <div
          v-if="user && !user_order_relation_warning"
          class="max-h-screen p-2 mx-auto"
          style="min-width: 400px"
        >
          {{ $t("this will remove") }} {{ $t("address from") }}
          <b>{{ user.profile.first_name }} {{ user.profile.last_name }}</b
          >. {{ $t("are you sure") }}?
          <div class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800">
            <p class="text-lg font-bold">
              <span class="text-base text-gray-500">#{{ address.id }} </span
              >{{ address.address }} {{ address.number }}
            </p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("zipcode") }}
            </p>
            <p>{{ address.zip_code }}</p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("city") }}
            </p>
            <p>{{ address.city }}</p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("region") }}
            </p>
            <p>{{ address.region }}</p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("country") }}
            </p>
            <p>{{ address.country }}</p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("created at") }}
            </p>
            <p>
              {{ moment(user.created_at).format("DD-MM-YYYY HH:MM") }}
            </p>
          </div>
        </div>

        <div v-if="user_order_relation_warning" class="p-2">
          <div class="p-2 text-orange-500 bg-orange-200 rounded">
            {{ user_order_relation_message }}
          </div>

          <div class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800">
            <p class="font-bold tracking-wide uppercase">
              {{ $t("related orders") }}
            </p>
            <div v-for="order in user_order_relation_data" :key="order.id">
              <button
                class="flex flex-wrap items-center justify-between w-full px-2 border rounded hover:bg-gray-200"
                @click="
                  showDetails !== order.id
                    ? (showDetails = order.id)
                    : (showDetails = false)
                "
              >
                <span class="font-bold"
                  >{{ $t("order") }}:
                  <span class="font-mono text-gray-500"
                    >#{{ order.id }}</span
                  ></span
                >
                <font-awesome-icon :icon="['fad', 'angle-down']" />

                <template v-if="showDetails === order.id">
                  <span
                    v-for="(value, key) in order"
                    :key="`details_${value}`"
                    class="flex justify-between w-full border-b border-gray-300 cursor-default last:border-0 hover:bg-gray-300"
                  >
                    <b>{{ key }}</b>
                    {{ value ? value : "--" }} <br />
                  </span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="delete_address([address.id, user_id]), $parent.closeModal()"
      >
        {{ !user_order_relation_warning ? $t("yes") : $t("force delete") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  props: {
    user_id: {
      type: Number,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
    address: {
      type: Object,
      required: true,
    },
  },
emits: ['onClose'],

  data() {
    return {
      moment: moment,
      user_order_relation_warning: false,
      user_order_relation_data: {},
      showDetails: null,
      force: 0,
    };
  },
  methods: {
    ...mapActions({
      update: "addresses/update",
      delete_address: "addresses/delete",
    }),

    closeModal() {
      this.$emit("onClose");
    },
  },
}; //End Export
</script>
