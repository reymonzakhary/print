<template>
  <div>
    <header>
      <h2 class="mb-4 text-lg font-bold tracking-wide text-center uppercase">
        <font-awesome-icon :icon="['fal', 'lock-alt']" class="mr-1 mb-0.5" /> Login
      </h2>
    </header>
    <main>
      <Form
        ref="loginForm"
        :validation-schema="UserSchema"
        class="flex flex-col gap-2"
        @submit="handleSubmit"
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
        <div>
          <label for="password" class="col-form-label text-md-right">Password</label>
          <UIInputText
            v-model="password"
            label="Password"
            name="password"
            type="password"
            placeholder=""
            autocomplete="current-password"
            required
            input-class="!py-1 placeholder-shown:bg-gray-100"
          />
          <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
        </div>
        <div class="my-2">
          <UIButton
            type="submit"
            class="w-full py-2 !rounded !text-base"
            variant="theme"
            :icon="['fal', 'key']"
            :class="{ 'opacity-50': loading }"
            :disabled="loading"
          >
            Login
            <font-awesome-icon
              v-show="loading"
              class="ml-1 text-white fa-spin"
              :icon="['fad', 'spinner-third']"
            />
          </UIButton>
        </div>
        <div class="italic text-right">
          <NuxtLink
            to="/auth/forgot-credentials"
            class="text-gray-500 transition-colors hover:text-gray-600 hover:underline"
          >
            Forgot your password?
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

const email = ref("");
const password = ref("");
const UserSchema = yup.object({
  email: yup.string().email().required(),
  password: yup.string().required(),
});

/**
 * Log in
 */
const authStore = useAuthStore();
const loading = ref(false);
const authRepository = useAuthRepository();
const sessionRepository = useSessionRepository();
const sessionStore = useSessionStore();
const handleSubmit = async (_, formActions) => {
  try {
    loading.value = true;

    // Fetch tokens
    const { data: tokens } = await authRepository.fetchToken(email.value, password.value);
    authStore.setTokens({
      accessToken: tokens.access_token,
      refreshToken: tokens.refresh_token,
      expiration: tokens.expires_in,
    });

    // Fetch session
    const { data: session } = await sessionRepository.fetchSession();
    sessionStore.session = session;

    // Redirect to home
    return navigateTo("/");
  } catch (error) {
    console.log("error", error);
    formActions.setErrors({
      email: " ",
      password: "Invalid email or password",
    });
  } finally {
    loading.value = false;
  }
};
</script>
