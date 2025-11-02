<template>
  <div
    class="relative break-words bottom-0 grid grid-cols-[1fr_,_14px] gap-2 p-2 mb-4 text-sm text-white -translate-x-1/2 rounded shadow-md max-w-md w-fit left-1/2 dissapear"
    :class="{
      'bg-green-500': type === 'success',
      'bg-red-500': type === 'critical',
      'bg-orange-500': type === 'error',
      'bg-amber-500': type === 'warning',
      'bg-blue-500': type === 'info',
    }"
  >
    <section class="flex items-center">
      <font-awesome-icon v-if="icon" :icon="['fal', icon]" class="ml-2 mr-4 text-2xl" />
      <span>{{ message }}</span>
    </section>

    <div>
      <button aria-label="delete" @click="deleteMessage(id)">
        <font-awesome-icon :icon="['fad', 'circle-xmark']" />
      </button>
    </div>
  </div>
</template>

<script setup>
const config = useRuntimeConfig();
const toastStore = useToastStore();

defineProps({
  type: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: false,
    default: "",
  },
  message: {
    type: String,
    required: true,
  },
  id: {
    type: String,
    required: true,
  },
});

function deleteMessage(id) {
  toastStore.deleteToast(id);
}
</script>

<style scoped>
.dissapear {
  animation-name: fadeOut;
  animation-duration: 300ms;
  animation-delay: v-bind("`${config.public.toast.dissapearanceTime - 300}ms`");
  animation-timing-function: ease-in-out;
  animation-fill-mode: forwards;
}

@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
</style>
