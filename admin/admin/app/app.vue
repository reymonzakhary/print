<template>
  <NuxtLayout>
    <NuxtPage />
    <Teleport to="body">
      <ToastList />
    </Teleport>
  </NuxtLayout>
</template>

<script setup>
useHead({
  htmlAttrs: {
    style: "background: black",
  },
});
const authStore = useAuthStore();

const sessionRepository = useSessionRepository();
const sessionStore = useSessionStore();
onMounted(async () => {
  if (authStore.isAuthenticated) {
    const { data: session } = await sessionRepository.fetchSession();
    sessionStore.session = session;
  }

  if (!authStore.isAuthenticated) {
    authStore.$reset();
  }
});
</script>
