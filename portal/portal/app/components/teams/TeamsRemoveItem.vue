<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header class="capitalize">
      {{ $t("remove") }} {{ item.name }}
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("this will remove") }}
          <b>{{ item.name }}</b
          >. {{ $t("are you sure") }}

          <div
            v-if="item.users.length > 0"
            class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800"
          >
            <h2 class="text-sm font-bold tracking-wide uppercase">
              <font-awesome-icon
                :icon="['fad', 'triangle-exclamation']"
                class="text-base text-orange-500"
              />
              {{ $t("team contains the following users") }}
            </h2>
            <h3 class="mt-2 ml-6 text-xs font-bold tracking-wide uppercase">
              {{ $t("users") }}:
            </h3>
            <ul class="ml-6 divide-y">
              <li v-for="user in item.users" :key="user">
                {{ user.email }}
              </li>
            </ul>
          </div>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="delete_team(item.id), closeModal()"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  name: "TeamsRemoveItem",
  props: {
    item: Object,
  },
  emits: ["onClose"],
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      get_teams: "teams/get_teams",
      delete_team: "teams/delete_team",
    }),
    closeModal() {
      this.$emit("onClose");
    },
  },
};
</script>
