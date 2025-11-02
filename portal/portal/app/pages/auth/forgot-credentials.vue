<template>
  <section>
    <div class="mb-3">
      <h1 class="mb-1 text-sm font-bold tracking-wide uppercase">
        {{ $t("What is your email again?") }}
      </h1>
      <p class="text-sm text-gray-500">
        <font-awesome-icon :icon="['fal', 'circle-info']" />
        {{ $t("We will send the new info per email") }}
      </p>
    </div>

    <Form ref="forgotForm" :validation-schema="schema" @submit="sendForgotPassword">
      <div class="mb-3">
        <label class="capitalize">{{ $t("email") }}</label>
        <Field ref="emailField" v-model="email" class="input" type="text" name="email" />
        <ErrorMessage name="email" as="span" class="text-xs text-red-500" />
      </div>
      <div class="flex justify-end">
        <button
          type="submit"
          class="w-full px-4 py-2 mt-3 font-bold text-white transition duration-200 ease-in-out transform rounded shadow-lg bg-theme-400 hover:bg-theme-500 md:w-auto md:min-w-36"
          :class="{ 'opacity-50': loading }"
          :disabled="loading"
        >
          {{ $t("Send verification code") }}
          <font-awesome-icon
            v-show="loading"
            class="text-white fa-spin"
            :icon="['fad', 'spinner-third']"
          />
        </button>
      </div>
    </Form>
  </section>
</template>

<script setup>
import * as yup from "yup";

const { t: $t } = useI18n();
const authStore = useAuthStore();

const forgotForm = ref(null);
const emailField = ref(null);
const email = ref("");
const loading = ref(false);

definePageMeta({
  layout: "login",
});

const schema = yup.object({
  email: yup.string().email().required(),
});

async function sendForgotPassword() {
  loading.value = true;
  try {
    await authStore.sendForgotPassword(email.value);
    navigateTo({ path: "/auth/verify", query: { email: email.value } });
  } catch (error) {
    if (error.status === 422 || error.status === 404) {
      return emailField.value.setErrors([
        $t("It seems like this email address is not registered."),
      ]);
    }
    handleError(error);
  } finally {
    loading.value = false;
  }
}
</script>
