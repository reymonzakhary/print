<template>
  <Teleport to="body">
    <div class="fixed bottom-0 left-0 right-0 z-[9999] w-full overflow-hidden pointer-events-none">
      <Toast
        v-for="toast in toasts"
        :id="`${toast.id}`"
        :key="`${toast.id}`"
        :icon="toast.icon"
        :message="toast.message"
        :type="toast.type"
      />
    </div>
  </Teleport>
</template>

<script setup>
const { toasts } = storeToRefs(useToastStore());
</script>
