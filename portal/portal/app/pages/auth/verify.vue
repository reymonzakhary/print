<template>
  <Form :validation-schema="schema" @submit="handleResetPassword">
    <div>
      <h1 class="mb-1 text-sm font-bold uppercase tracking-wide">
        {{ $t("Verification code sent to your email") }}
      </h1>
      <p class="text-sm text-gray-500">
        <font-awesome-icon :icon="['fal', 'circle-info']" />
        {{ $t("Please enter the verification code below") }}
      </p>
    </div>

    <!-- verification input number -->
    <div class="my-6">
      <pin-input-root
        id="token-input"
        v-model="token"
        placeholder="○"
        class="flex w-full justify-between"
        :class="{ 'animate-shake': tokenState === 'INVALID' }"
        type="number"
        :required="true"
        @complete="handleTokenComplete"
      >
        <pin-input-input
          v-for="(id, index) in 6"
          :key="id"
          :index="index"
          class="input aspect-[5/6] max-w-12 rounded bg-white text-center placeholder:text-slate-800 focus:outline focus:outline-1 focus:outline-offset-1 focus:outline-theme-500 xl:max-w-14"
          :class="{
            'border-green-500 !bg-green-50': tokenState === 'VALID',
            'border-red-300 !bg-red-50': tokenState === 'INVALID',
          }"
          :disabled="tokenState === 'VALID' || loading"
        />
      </pin-input-root>
      <span v-if="tokenState === 'INVALID'" class="text-xs text-red-500">
        {{ $t("The verification code is invalid, please try again") }}
      </span>
    </div>

    <fieldset
      v-tooltip="{
        content: $t('add valid token above first'),
        'aria-label': $t('add valid token above first'),
      }"
      :class="{ 'pointer-events-none select-none opacity-50': tokenState !== 'VALID' }"
      :aria-disabled="tokenState !== 'VALID'"
    >
      <!-- Password Confirmation -->
      <div>
        <label class="capitalize">{{ $t("Password") }}</label>
        <div class="relative w-full rounded">
          <Field
            ref="passwordField"
            v-model="newPassword"
            name="password"
            :disabled="tokenState !== 'VALID'"
            :class="[
              'input w-full rounded border-2 px-3 py-2 leading-tight shadow-none focus:border-theme-500 focus:outline-none',
              { 'border-green-500': newPassword.length >= 8 },
            ]"
            type="password"
          />
          <span
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-green-500"
          >
            <font-awesome-icon v-if="newPassword.length >= 8" :icon="['fal', 'check-circle']" />
          </span>
        </div>
        <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
      </div>

      <!-- Password Confirmation -->
      <div class="mt-2">
        <label class="capitalize">{{ $t("Confirm Password") }}</label>
        <div class="relative w-full rounded">
          <Field
            v-model="newPasswordConfirmation"
            name="password_confirmation"
            :disabled="tokenState !== 'VALID'"
            :class="[
              'input w-full rounded border-2 px-3 py-2 leading-tight shadow-none focus:border-theme-500 focus:outline-none',
              {
                'border-green-500':
                  newPasswordConfirmation.length >= 8 && newPasswordConfirmation === newPassword,
              },
            ]"
            type="password"
          />
          <span
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-green-500"
          >
            <font-awesome-icon
              v-if="newPasswordConfirmation.length >= 8"
              :icon="['fal', 'check-circle']"
            />
          </span>
        </div>
        <ErrorMessage name="password_confirmation" as="span" class="text-xs text-red-500" />
      </div>
    </fieldset>
    <div class="flex w-full justify-end">
      <button
        :disabled="loading || invalid"
        class="mt-6 rounded bg-theme-400 px-4 py-2 font-bold capitalize text-themecontrast-400 text-white transition-colors hover:bg-theme-500"
        :class="{ 'cursor-not-allowed opacity-50 hover:!bg-theme-400': invalid }"
        @click="handleResetPassword"
      >
        {{ $t("Reset Password") }}
      </button>
    </div>
  </Form>
</template>

<script setup>
import * as yup from "yup";
import { PinInputInput, PinInputRoot } from "reka-ui";

const { t: $t } = useI18n();
const route = useRoute();
const API = useAPI();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

const schema = yup.object({
  password: yup.string().min(8).required(),
  password_confirmation: yup
    .string()
    .min(8)
    .required()
    .when(["password"], (password, schema) =>
      schema.test("passwords-match", $t("Passwords must match"), (value) => value === password),
    ),
});

const loading = ref(false);
const tokenState = ref("EMPTY"); // "EMPTY" | "INVALID" | "TRY AGAIN" | "VALID"
const passwordField = ref(null);

const invalid = computed(
  () =>
    token.value.length !== 6 ||
    newPassword.value.length < 8 ||
    newPassword.value !== newPasswordConfirmation.value,
);
const email = route.query.email;
const newPassword = ref("");
const newPasswordConfirmation = ref("");
const token = ref([]);

definePageMeta({
  layout: "login",
});

onMounted(() => {
  if (!email) {
    navigateTo("/auth/forgot");
  }

  if (route.query.token) {
    token.value = route.query.token.split("");
  }
});

async function handleTokenComplete() {
  try {
    loading.value = true;
    await API.post("/password/reset/verify", {
      email,
      token: token.value.join(""),
    });
    tokenState.value = "VALID";
    nextTick(() => {
      passwordField.value.$el.focus();
    });
  } catch (err) {
    if (err.status === 403) {
      tokenState.value = "INVALID";
      return addToast({
        type: "error",
        message: $t("The verification code you entered is invalid, please try again"),
      });
    }
    handleError(err);
  } finally {
    loading.value = false;
  }
}

async function handleResetPassword() {
  loading.value = true;
  try {
    await API.post("/password/reset", {
      email,
      password: newPassword.value,
      password_confirmation: newPasswordConfirmation.value,
    });
    addToast({
      message: $t("Password reset successfully, we'll redirect you to the login page"),
      type: "success",
    });
    await navigateTo("/auth/login");
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

watch(token, () => {
  tokenState.value = "TRY AGAIN";
});
</script>

<style scoped>
@media (prefers-reduced-motion: reduce) {
  .animate-shake {
    animation: none;
  }
}
</style>
