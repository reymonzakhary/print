<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <p v-if="role" class="capitalize">
        {{ $t("remove") }} {{ $display_name(role.display_name) }}
      </p>
    </template>

    <template #modal-body>
      <section class="flex max-w-lg flex-wrap">
        <div v-if="role" class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("This will remove") }}
          <b> {{ $display_name(role.display_name) }}. </b>
          {{ $t("Are you sure?") }}
          <div class="mt-2 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <p class="text-lg font-bold">
              <span class="text-base text-gray-500"> #{{ role.id }} </span>
              {{ $display_name(role.display_name) }}
            </p>
            <p class="text-sm text-gray-500">
              {{ role.description }}
            </p>
            <p class="mt-2 text-sm font-bold text-gray-500">
              {{ $t("last updated") }}:
              <span class="font-mono font-normal">
                {{ moment(role.updated_at).format("DD-MM-YYYY HH:MM") }}
              </span>
            </p>
          </div>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-red-500 px-5 py-1 text-sm text-white transition-colors hover:bg-red-700"
        @click="removeRole"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import moment from "moment";

export default {
  name: "RemoveRole",
  props: {
    role: {
      type: Object,
      required: true,
    },
  },
  emits: ["close", "remove-role"],
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    removeRole() {
      this.$emit("remove-role", this.role.id);
      this.$emit("close");
    },
    closeModal() {
      this.$emit("close");
    },
  },
};
</script>
