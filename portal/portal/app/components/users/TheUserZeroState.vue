<template>
  <section class="flex flex-col flex-wrap items-center justify-center w-full h-full text-center my-8 font-bold text-gray-300">
    <p class="text-xl">{{ $t(message) }}</p>
    <font-awesome-icon :icon="['fad', 'users-slash']" class="my-6 fa-5x"/>
  </section>
</template>

<script>
export default {
  name: 'TheUserZeroState',
  props: ['message']
}
</script>

<style scoped>

</style>