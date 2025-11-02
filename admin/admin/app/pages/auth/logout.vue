<template>
  <div>Logging out...</div>
</template>

<script setup>
definePageMeta({
  layout: "auth",
});

const authStore = useAuthStore();
const authRepository = useAuthRepository();

onMounted(async () => {
  await authRepository.invalidateToken();
  authStore.$reset();
  return navigateTo("/auth/login");
});
</script>
