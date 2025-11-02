<template>
  <main class="flex h-full flex-col rounded p-4">
    <MarketPlaceHeader v-model:active-tab="activeTab" class="sticky top-0 z-10 rounded" />
    <Suspense>
      <NuxtPage class="relative z-0 flex-1" />
    </Suspense>
  </main>
</template>

<script setup>
const activeTab = computed({
  get: () => (useRoute().path.includes("product") ? "productFinder" : "producerFinder"),
  set: (v) => navigateTo(`/marketplace/${v === "productFinder" ? "product" : "producer"}-finder`),
});
</script>
