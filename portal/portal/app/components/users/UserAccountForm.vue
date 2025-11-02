<template>
  <div>
    <div class="border-b">
      <div class="form-section">
        <label for="email">{{ $t("Email") }}</label>
        <UIInputText
          id="email"
          v-model="email"
          name="email"
          placeholder=""
          :icon="['fal', 'envelope']"
          :rules="Yup.string().email().required()"
        />
        <ErrorMessage name="email" as="span" class="text-xs text-red-500" />
      </div>

      <div v-if="!user" class="form-section">
        <label for="password">{{ $t("Password") }}</label>
        <div class="text-sm italic text-gray-400">
          <font-awesome-icon class="text-theme-400" :icon="['fal', 'circle-info']" />
          {{ $t("Password is generated and sent to customers email address.") }}
        </div>
      </div>
    </div>

    <div>
      <div class="form-section">
        <label for="gender">{{ $t("Salutation") }}</label>
        <div class="flex">
          <label
            for="male"
            class="default mr-2 flex items-center text-sm capitalize"
            :class="{
              'font-bold text-theme-500': gender === 'male',
            }"
          >
            <input
              id="male"
              v-model="gender"
              type="radio"
              name="gender"
              value="male"
              class="mr-1"
              checked
            />
            {{ $t("mr") }}
          </label>
          <label
            for="female"
            class="default mr-2 flex items-center text-sm capitalize"
            :class="{
              'font-bold text-theme-500': gender === 'female',
            }"
          >
            <input
              id="female"
              v-model="gender"
              type="radio"
              name="gender"
              value="female"
              class="mr-1"
            />
            {{ $t("ms") }}
          </label>
          <label
            for="other"
            class="default mr-2 flex items-center text-sm capitalize"
            :class="{
              'font-bold text-theme-500': gender === 'other',
            }"
          >
            <input
              id="other"
              v-model="gender"
              type="radio"
              name="gender"
              value="other"
              class="mr-1"
            />
            {{ $t("other") }}
          </label>
        </div>
      </div>

      <div class="form-section">
        <label for="first_name">{{ $t("First Name") }}</label>
        <UIInputText
          v-model="first_name"
          name="first_name"
          placeholder=""
          :rules="Yup.string().required()"
        />
        <ErrorMessage name="first_name" as="span" class="text-xs text-red-500" />
      </div>

      <div class="form-section">
        <label for="last_name">{{ $t("Last Name") }}</label>
        <UIInputText
          v-model="last_name"
          name="last_name"
          placeholder=""
          :rules="Yup.string().required()"
        />
        <ErrorMessage name="last_name" as="span" class="text-xs text-red-500" />
      </div>
    </div>
  </div>
</template>

<script>
import * as Yup from "yup";

export default {
  name: "UserAccountForm",
  props: {
    user: {
      type: Object,
      default: () => null,
    },
  },
  emits: ["form-data-change"],
  data() {
    return {
      email: "",
      gender: "male",
      first_name: "",
      last_name: "",
      Yup,
    };
  },
  watch: {
    $data: {
      handler: function () {
        this.$emit("form-data-change", this.retrieveFormData());
      },
      deep: true,
    },
  },
  created() {
    if (!this.user) return;
    this.email = this.user.email;
    this.gender = this.user.profile.gender;
    this.first_name = this.user.profile.first_name;
    this.last_name = this.user.profile.last_name;
  },
  methods: {
    retrieveFormData() {
      return {
        email: this.email,
        gender: this.gender,
        first_name: this.first_name,
        last_name: this.last_name,
      };
    },
  },
};
</script>

<style lang="scss" scoped>
label:not(.default) {
  @apply block w-1/2 text-xs font-bold uppercase tracking-wide;
}

.form-section {
  @apply grid grid-cols-2 items-center rounded py-4;
}
</style>
