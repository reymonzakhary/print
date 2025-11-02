<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header class="capitalize">
      <p v-if="user && user.profile">
        {{ $t("remove") }} {{ type }} {{ user.profile.first_name }}
        {{ user.profile.last_name }}
      </p>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div
          v-if="user && !user_order_relation_warning"
          class="max-h-screen p-2"
          style="min-width: 400px"
        >
          {{ $t("This will remove") }}
          <span v-if="user.profile" class="font-bold">
            {{ user.profile }} {{ user.profile.last_name }}
          </span>
          . {{ $t("Are you sure") }}?
          <div class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800">
            <p v-if="user.profile" class="text-lg font-bold">
              <span class="text-base text-gray-500"> #{{ user.id }} </span>
              {{ user.profile.first_name }} {{ user.profile.last_name }}
            </p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("email") }}
            </p>
            <p>{{ user.email }}</p>
            <p
              class="mt-2 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t("username") }}
            </p>
            <!-- <p>{{ user.username }}</p> -->
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
                    :key="`details_${order.id}`"
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
        @click="deleteUser()"
      >
        {{ !user_order_relation_warning ? $t("yes") : $t("force delete") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
import { mapMutations, mapActions } from "vuex";
import moment from "moment";

export default {
  name: "RemoveModal",
  props: {
    user: Object,
    type: String,
  },
  setup() {
	const api = useAPI();
	return { api };
  },
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
    ...mapMutations({
      set_modal_name: "users/set_modal_name",
    }),
    ...mapActions({
      get_members: "users/get_members",
    }),
    deleteUser() {
      switch (this.type) {
        case "user":
          this.api
            .delete(`users/${this.user.id}`)
            .then((response) => {
              this.handleSuccess(response);
              this.$parent.$parent.get_users();
              this.closeModal();
            })
            .catch((error) => {
              this.handleError(error);
            });
          break;

        case "customer":
          this.api
            .delete(`members/${this.user.id}?force=${this.force}`)
            .then((response) => {
              this.handleSuccess(response);
              this.get_members();
              this.closeModal();
            })
            .catch((error) => {
              if (error.response && error.response.status === 422) {
                this.user_order_relation_warning = true;
                this.user_order_relation_data = error.response.data.data;
                this.user_order_relation_message = error.response.data.message;
                this.force = 1;

                this.handleError(error);
                this.get_members();
                this.closeModal();
              }
              this.handleError(error);
              this.get_members();
              this.closeModal();
            });
          break;

        default:
          break;
      }
    },
    closeModal() {
      this.set_modal_name("");
      this.$parent.closeModal();
    },
  },
}; //End Export
</script>
