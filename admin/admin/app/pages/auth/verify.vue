<template>
  <div>
    <header class="mb-4">
      <h2 class="text-lg font-bold tracking-wide text-center uppercase">
        <font-awesome-icon :icon="['fal', 'lock-alt']" class="mb-0.5 mr-1" /> Forgot Credentials
      </h2>
      <h3 class="mt-4 mb-1 text-sm font-bold tracking-wide uppercase">
        Verification code sent to your email
      </h3>
      <p class="text-sm text-gray-500">
        <font-awesome-icon :icon="['fal', 'info-circle']" />
        Please enter the verification code below
      </p>
    </header>
    <main>
      <Form
        ref="resetPasswordForm"
        :validation-schema="schema"
        class="flex flex-col gap-2"
        @submit="handleResetPassword"
      >
        <div class="mb-2">
          <pin-input-root
            id="token-input"
            v-model="token"
            placeholder="○"
            class="flex w-full gap-2"
            :class="{ 'animate-shake': tokenState === 'INVALID' }"
            type="number"
            :required="true"
            @complete="handleTokenComplete"
            @update:model-value="tokenState = 'EMPTY'"
          >
            <pin-input-input
              v-for="(id, index) in 6"
              :key="id"
              :index="index"
              class="text-center bg-white rounded aspect-square placeholder:text-gray-300 focus:outline focus:outline-1 focus:outline-offset-1 focus:outline-theme-500 input"
              :class="{
                'border-green-500 !bg-green-50 !text-green-500': tokenState === 'VALID',
                'border-red-300 !bg-red-50 !text-red-500': tokenState === 'INVALID',
              }"
              :disabled="tokenState === 'VALID' || tokenState === 'FETCHING' || loading"
            />
          </pin-input-root>
          <span v-if="tokenState === 'INVALID'" class="text-xs text-red-500">
            The verification code is invalid, please try again
          </span>
        </div>
        <div>
          <label for="password" class="col-form-label text-md-right">Password</label>
          <UIInputText
            ref="passwordField"
            v-model="newPassword"
            name="password"
            type="password"
            placeholder=""
            :disabled="tokenState !== 'VALID'"
            :icon="passwordField?.meta.valid ? ['fal', 'check-circle'] : []"
            :class="{
              '!text-green-500 outline outline-1 outline-green-500': passwordField?.meta.valid,
            }"
            :input-class="`!py-1 ${passwordField?.meta.valid && 'text-green-500'}`"
            no-autocomplete
            required
          />
          <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
        </div>
        <div>
          <label for="password" class="col-form-label text-md-right">Password Confirmation</label>
          <UIInputText
            ref="confirmPasswordField"
            v-model="newPasswordConfirmation"
            name="password_confirmation"
            type="password"
            placeholder=""
            :disabled="tokenState !== 'VALID'"
            :icon="confirmPasswordField?.meta.valid ? ['fal', 'check-circle'] : []"
            :class="{
              '!text-green-500 outline outline-1 outline-green-500':
                confirmPasswordField?.meta.valid,
            }"
            :input-class="`!py-1 ${confirmPasswordField?.meta.valid && 'text-green-500'}`"
            no-autocomplete
            required
          />
          <ErrorMessage name="password_confirmation" as="span" class="text-xs text-red-500" />
        </div>
        <div class="mt-4 mb-2">
          <UIButton
            type="submit"
            class="w-full py-2 !rounded !text-base"
            variant="theme"
            :icon="['fal', 'key']"
            :disabled="loading || resetPasswordForm?.meta.valid !== true"
          >
            Reset Password
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
import { PinInputInput, PinInputRoot } from "radix-vue";
import * as yup from "yup";

definePageMeta({
  layout: "auth",
});

const schema = yup.object({
  password: yup.string().min(8).required(),
  password_confirmation: yup
    .string()
    .min(8)
    .required()
    .label("Password Confirmation")
    .when(["password"], (password, schema) =>
      schema.test("passwords-match", "Passwords must match", (value) => value === password[0]),
    ),
});

const route = useRoute();
const authStore = useAuthStore();
const { addToast } = useToastStore();

const email = ref(route.query.email);
const passwordField = ref(null);
const confirmPasswordField = ref(null);
const resetPasswordForm = ref(null);

const loading = ref(false);
const tokenState = ref("EMPTY"); // "EMPTY" | "INVALID" | "VALID" | "FETCHING"

const newPassword = ref("");
const newPasswordConfirmation = ref("");
const token = ref([]);

onMounted(() => {
  if (!email.value) {
    navigateTo("/auth/forgot-credentials");
  }
});

async function handleTokenComplete() {
  try {
    tokenState.value = "FETCHING";
    await authStore.checkVerificationCode(email.value, token.value.join(""));
    tokenState.value = "VALID";
    nextTick(() => {
      passwordField.value.inputField.focus();
    });
  } catch (error) {
    tokenState.value = "INVALID";
    console.log(error);
  }
}

async function handleResetPassword() {
  try {
    loading.value = true;
    await authStore.resetPassword(email.value, newPassword.value, newPasswordConfirmation.value);
    addToast({
      message: "Password reset successfully, we'll redirect you to the login page",
      type: "success",
    });
    await navigateTo("/auth/login");
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
}
</script>
