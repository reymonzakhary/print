<template>
  <div>
    <header class="mb-4">
      <h2 class="text-lg font-bold tracking-wide text-center uppercase">
        <font-awesome-icon :icon="['fal', 'lock-alt']" class="mb-0.5 mr-1" /> Forgot Credentials
      </h2>
    </header>
    <main>
      <Form
        ref="forgotCredentialsForm"
        :validation-schema="ForgotCredentialsSchema"
        class="flex flex-col gap-2"
        @submit="handleSendVerificationCode"
      >
        <div>
          <label for="email">Email</label>
          <UIInputText
            v-model="email"
            label="Email"
            name="email"
            placeholder=""
            autocomplete="email"
            required
            input-class="!py-1 placeholder-shown:bg-gray-100"
          />
          <ErrorMessage name="email" as="span" class="text-xs text-red-500" />
        </div>
        <div class="my-2">
          <UIButton
            type="submit"
            class="w-full py-2 !rounded !text-base"
            variant="theme"
            :icon="['fal', 'paper-plane']"
            :class="{ 'opacity-50': loading }"
            :disabled="loading || !forgotCredentialsForm?.meta.valid"
          >
            Send Verification Code
            <font-awesome-icon
              v-show="loading"
              class="ml-1 text-white fa-spin"
              :icon="['fad', 'spinner-third']"
            />
          </UIButton>
        </div>
        <div class="italic text-center">
          <NuxtLink
            to="/auth/login"
            class="text-gray-500 transition-colors hover:text-gray-600 hover:underline"
          >
            Remembered your credentials? Login
          </NuxtLink>
        </div>
      </Form>
    </main>
  </div>
</template>

<script setup>
import * as yup from "yup";

definePageMeta({
  layout: "auth",
});

const ForgotCredentialsSchema = yup.object({
  email: yup.string().email().required(),
});

const authStore = useAuthStore();
const toastStore = useToastStore();

const forgotCredentialsForm = ref(null);
const loading = ref(false);

const email = ref("");

async function handleSendVerificationCode(_, formActions) {
  try {
    loading.value = true;
    await authStore.requestForgetPassword(email.value);
    return navigateTo({ path: "/auth/verify", query: { email: email.value } });
  } catch (error) {
    toastStore.addToast({
      message: error,
      type: "error",
    });
    formActions.setErrors({
      email: "It seems like this email is not registered.",
    });
  } finally {
    loading.value = false;
  }
}
</script>
