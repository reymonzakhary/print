<template>
  <div>
    <NuxtLink
      class="studio-button relative inline-flex cursor-pointer items-center justify-center whitespace-nowrap rounded-full bg-gradient-to-tr from-white to-gray-100 px-4 py-2 text-xs dark:from-gray-800 dark:to-gray-700"
      :to="to"
    >
      <font-awesome-icon
        :icon="['far', 'hammer-brush']"
        class="[filter:drop-shadow(0_1px_0_rgb(0_0_0_/_40%))]"
      />
      <span class="mx-2 uppercase"> {{ $t("Studio") }} </span>
    </NuxtLink>
  </div>
</template>

<script setup>
defineProps({
  to: {
    type: String,
    required: true,
  },
});
</script>
