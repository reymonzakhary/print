<template>
  <div>
    <form @submit.prevent="handleSubmit">
      <!-- username -->
      <div class="mb-6">
        <label class="capitalize">{{ $t("username") }}</label>
        <div class="relative w-full rounded">
          <input
            v-model="email"
            :class="[
              'input w-full rounded border-2 px-3 py-2 leading-tight shadow-none focus:border-theme-500 focus:outline-none',
              { 'border-green-500': validEmail(email) },
            ]"
            type="email"
            autocomplete="email"
            @input="() => {}"
            @keyup.enter="() => {}"
          />
          <span
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-green-500"
          >
            <font-awesome-icon v-if="validEmail(email)" :icon="['fal', 'check-circle']" />
          </span>
        </div>
      </div>
      <!-- password -->
      <div class="mb-6">
        <label class="capitalize">{{ $t("password") }}</label>
        <div class="relative w-full rounded">
          <input
            v-model="password"
            :class="[
              'input w-full rounded border-2 px-3 py-2 leading-tight shadow-none focus:border-theme-500 focus:outline-none',
              { 'border-green-500': password.length >= 8 },
            ]"
            :type="passwordType"
            @focus="active = true"
            @blur="active = false"
            @keyup.enter="signin"
          />
          <span class="absolute inset-y-0 right-0 flex items-center px-2">
            <font-awesome-icon
              :icon="['fal', showPassword ? 'eye-slash' : 'eye']"
              class="cursor-pointer"
              @click="showPassword = !showPassword"
            />
          </span>
          <span
            class="pointer-events-none absolute inset-y-0 right-6 flex items-center px-2 text-green-500"
          >
            <font-awesome-icon v-if="password.length >= 8" :icon="['fal', 'check-circle']" />
          </span>
        </div>
      </div>

      <div class="flex h-full flex-col justify-between">
        <!-- forget password -->
        <NuxtLink to="/auth/forgot-credentials" class="text-theme-500 hover:text-theme-600">
          {{ $t("I forgot my credentials") }}
        </NuxtLink>
        <button
          :disabled="loading"
          type="submit"
          class="mt-6 w-full transform self-end rounded bg-theme-400 px-4 py-2 font-bold capitalize text-white shadow-lg transition duration-200 ease-in-out hover:bg-theme-500 md:w-auto md:min-w-36"
        >
          {{ loading ? $t("Logging in") : $t("login") }}
          <font-awesome-icon
            v-show="loading"
            class="fa-spin text-white"
            :icon="['fad', 'spinner-third']"
          />
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
useHead({
  title: "Login",
  link: [{ rel: "icon", type: "image/png", href: "/lock/favicon.png" }],
});

const { signIn, fetchUser } = useAuthStore();
const messageHandler = useMessageHandler();
const email = ref("");
const password = ref("");
const loading = ref(false);

const showPassword = ref(false);
const passwordType = computed(() => (showPassword.value ? "text" : "password"));

definePageMeta({
  layout: "login",
});

async function handleSubmit() {
  loading.value = true;
  try {
    await signIn({ email: email.value, password: password.value });
    await fetchUser();
    // TODO: Find a better way of reauthenticating the user after login.
    // Currently, authentication is once initialized in the app.vue causing issues with the user not being authenticated after login.
    reloadNuxtApp();
  } catch (error) {
    messageHandler.handleError(error);
  } finally {
    loading.value = false;
  }
}

function validEmail(email) {
  const re =
    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
</script>

<style lang="scss" scoped>
form {
  opacity: 0;
  animation: fadeIn 0.4s 0.9s forwards cubic-bezier(0.075, 0.82, 0.165, 1);
}

@media (prefers-reduced-motion: reduce) {
  form {
    animation: none;
    opacity: 1;
  }
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    border-radius: 0;
  }
  100% {
    opacity: 1;
  }
}
</style>
