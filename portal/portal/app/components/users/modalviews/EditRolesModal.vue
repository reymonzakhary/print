<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <!-- eslint-disable-next-line prettier/prettier -->
      {{ $t("Update {name}'s' roles", { name: `${user.profile.first_name} ${user.profile.last_name}` }) }}
    </template>

    <template #modal-body>
      <UserRoleList :user="user" @roles-changed="updateSelectedRoles" />
    </template>

    <template #confirm-button>
      <ModalButton variant="success" @click="updateUser"> {{ $t("Update roles") }} </ModalButton>
    </template>
  </ConfirmationModal>
</template>

<script>
export default {
  name: "EditRolesModal",
  inject: ["endpoint"],
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  emits: ["roles-updated", "close-modal"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      selectedRoles: [],
    };
  },
  methods: {
    updateSelectedRoles(roles) {
      this.selectedRoles = roles.map((role) => role.id);
    },
    async updateUser() {
      this.api
        .put(`${this.endpoint}/${this.user.id}`, {
          roles: this.selectedRoles,
          type: "individual",
        })
        .then(() => {
          this.$emit("roles-updated");
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
