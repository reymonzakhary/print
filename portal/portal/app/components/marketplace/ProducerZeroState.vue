<template>
  <section
    v-if="message"
    class="my-8 flex h-full w-full flex-col flex-wrap items-center justify-center text-center font-bold text-gray-300"
  >
    <p class="text-xl">{{ $t(message) }}</p>
    <div class="my-4 -ml-10 flex justify-center">
      <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
      <font-awesome-icon :icon="['fad', 'parachute-box']" class="fa-5x my-4 text-gray-400" />
      <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
    </div>
  </section>
</template>

<script>
export default {
  name: "TheProducerZeroState",
  props: {
    message: {
      type: String,
      required: true,
    },
  },
};
</script>
