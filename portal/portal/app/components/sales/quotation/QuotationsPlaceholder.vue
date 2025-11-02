<template>
  <main class="grid py-12 place-items-center">
    <p class="text-xl font-bold text-gray-400">
      {{ $t("no quotations found") }}
    </p>
    <div class="py-2 -ml-6">
      <font-awesome-icon :icon="['fal', 'clouds']" class="m-4 text-gray-300 fa-3x" />
      <font-awesome-icon :icon="['fad', 'bags-shopping']" class="my-4 text-gray-400 fa-5x" />
      <font-awesome-icon :icon="['fal', 'clouds']" class="my-4 text-gray-300 fa-2x" />
    </div>
    <div>
      <SalesAddButton
        v-if="permissions.includes('quotations-create')"
        variant="secondary"
        @click="emit('on-create')"
      >
        {{ $t("create new quotation") }}
      </SalesAddButton>
    </div>
  </main>
</template>

<script setup>
const { permissions } = storeToRefs(useAuthStore());

const emit = defineEmits(["on-create"]);
</script>
