<template>
  <NuxtLayout>
    <NotFound />
  </NuxtLayout>
</template>

<script setup>
// https://nuxt.com/docs/guide/directory-structure/error
// const error = useError();
// TODO: Handle different error status codes
// TODO: Handle auth initialization
const error = useError();
console.error(error.value);
</script>
