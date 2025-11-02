<template>
  <VDropdown v-model:shown="isOpen" theme="smart-search" no-auto-focus auto-size>
    <template #popper>
      <div
        class="w-full space-y-4 rounded-xl border bg-white p-4 shadow-lg dark:border-black dark:bg-gray-700"
      >
        <slot />
      </div>
    </template>
  </VDropdown>
</template>

<script setup>
const isOpen = defineModel("shown", { type: Boolean, required: true });
</script>
