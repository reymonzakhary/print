<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      {{ $t("Update profile of") }} {{ profile && profile.first_name }}
      {{ profile && profile.last_name }}
    </template>

    <template #modal-body>
      <Form ref="profileForm">
        <div class="form-section">
          <label for="first_name">{{ $t("First name") }}</label>
          <UIInputText v-model="first_name" name="first_name" placeholder="First name" />
        </div>

        <div class="form-section">
          <label for="last_name">{{ $t("Last name") }}</label>
          <UIInputText
            id="last_name"
            v-model="last_name"
            name="last_name"
            placeholder="Last name"
          />
        </div>

        <div class="form-section">
          <label for="dateOfBirth">{{ $t("Date of birth") }}</label>
          <Field
            v-model="dateOfBirth"
            type="date"
            :rules="dateRules"
            name="dateOfBirth"
            class="w-full my-2 input"
          />
          <ErrorMessage name="dateOfBirth" as="p" class="text-red-500" />
        </div>

        <div class="form-section">
          <label for="bio">{{ $t("Biography") }}</label>
          <UIInputText id="bio" v-model="bio" name="bio" placeholder="Biography" />
        </div>
      </Form>
    </template>

    <template #confirm-button>
      <ModalButton variant="success" @click="submitProfileForm">
        {{ $t("Update profile") }}
      </ModalButton>
    </template>
  </ConfirmationModal>
</template>

<script>
import * as Yup from "yup";

export default {
  props: {
    profile: {
      type: Object,
      required: true,
    },
  },
  emits: ["close-modal", "profile-update"],
  data() {
    return {
      first_name: this.profile.first_name,
      last_name: this.profile.last_name,
      dateOfBirth: this.profile.dob,
      bio: this.profile.bio,
      Yup: Yup,
    };
  },
  computed: {
    dateRules() {
      return this.Yup.date().max(new Date(), "Date of birth must be in the past");
    },
  },
  methods: {
    getProfile() {
      return {
        first_name: this.first_name,
        last_name: this.last_name,
        dob: this.dateOfBirth,
        bio: this.bio,
      };
    },
    closeModal() {
      this.$emit("close-modal");
    },
    updateProfile() {
      this.$emit("profile-update", this.getProfile());
    },
    submitProfileForm() {
      this.$refs.profileForm.validate().then((success) => {
        if (success) {
          this.updateProfile();
          this.closeModal();
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
label {
  @apply block w-1/2 text-xs font-bold tracking-wide uppercase;
}

.form-section {
  @apply p-4 rounded grid grid-cols-2 items-center;
}
</style>
