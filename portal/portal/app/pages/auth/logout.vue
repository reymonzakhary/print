<template>
  <div>Logging out...</div>
</template>

<script setup>
const { signOut } = useAuthStore();

definePageMeta({
  layout: "login",
});

await signOut();
await navigateTo("/login");
</script>
