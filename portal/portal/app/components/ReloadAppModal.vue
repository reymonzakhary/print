<template>
  <UICard class="absolute right-6 bottom-6 text-center max-w-64" rounded-full>
    <div class="p-4">
      <p>{{ $t("Reload the application to enable your new settings") }}</p>
      <UIButton class="mt-4 w-full" variant="neutral" @click="() => reloadNuxtApp()">
        {{ $t("Reload") }}
      </UIButton>
    </div>
  </UICard>
</template>

<script setup></script>
