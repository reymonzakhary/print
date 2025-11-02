<template>
  <NuxtLayout>
    <LoadingScreen v-if="!authStore.initialized" class="fixed inset-0 z-50" />
    <NuxtPage :name="!authStore.initialized ? null : undefined" />
    <Teleport to="body">
      <DevOnly>
        <SupportDebugToolbar />
      </DevOnly>
      <ToastList />
      <ReloadAppModal v-if="showReload" />
    </Teleport>
  </NuxtLayout>
</template>

<script setup>
import { useStore } from "vuex";

const authStore = useAuthStore();
const store = useStore();

const showReload = computed(() => store.state.settings.showReload);
</script>
