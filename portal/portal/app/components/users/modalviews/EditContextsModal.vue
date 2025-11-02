<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <!-- eslint-disable-next-line prettier/prettier -->
      {{ $t("Update {name}'s contexts", { name: `${user.profile.first_name} ${user.profile.last_name}` }) }}
    </template>

    <template #modal-body>
      <UILoader v-if="isLoading" />
      <ul v-else>
        <li
          v-for="(context, index) in contexts"
          :key="'user_role_' + index"
          class="group flex items-center justify-between p-2 hover:bg-gray-100 dark:hover:bg-gray-900"
        >
          <div class="flex w-full items-center justify-between">
            <ValueSwitch
              :name="$display_name(context.name)"
              :set-checked="selectedContexts.includes(context.id)"
              :disabled="isSameUser && context.name === 'mgr'"
              classes="justify-between w-full"
              @checked-value="updateUserContexts(context)"
            />
          </div>
        </li>
      </ul>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-green-500 px-4 py-1 text-sm text-white transition-colors hover:bg-green-400"
        aria-label="Close modal"
        @click.once="updateUser()"
      >
        {{ $t("Update contexts") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
export default {
  name: "EditContextsModal",
  inject: ["endpoint"],
  props: ["user"],
  emits: ["contexts-updated", "close-modal"],
  async setup() {
    const authStore = useAuthStore();
    const api = useAPI();
    const { handleError } = useMessageHandler();
    const { data, pending } = await useAsyncData(() => api.get("/contexts"));
    const contexts = ref(data.value.data);
    return {
      isLoading: pending,
      contexts,
      authStore,
      api,
      handleError,
    };
  },
  computed: {
    selectedContexts() {
      return this.user.ctx.map((role) => role.id);
    },
    isSameUser() {
      return this.user.id === this.authStore.theUser.id;
    },
  },
  methods: {
    updateUserContexts(ctx) {
      if (this.selectedContexts.includes(ctx.id)) {
        const index = this.selectedContexts.indexOf(ctx.id);
        this.selectedContexts.splice(index, 1);
      } else {
        this.selectedContexts.push(ctx.id);
      }
    },
    async updateUser() {
      this.api
        .put(`${this.endpoint}/${this.user.id}`, {
          ctx_id: this.selectedContexts,
          type: "individual",
        })
        .then(() => {
          this.$emit("contexts-updated");
          this.closeModal();
        })
        .catch((err) => {
          this.handleError(err);
        });
    },
    closeModal() {
      this.$emit("close-modal");
    },
  },
};
</script>
