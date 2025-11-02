<template>
  <div class="ui-tenant-logo">
    <figure class="z-0 max-w-none rounded object-contain">
      <img v-if="displayLogo" id="tenant-logo" :src="displayLogo" alt="Tenant Logo" class="" />
      <div
        v-else-if="props.skipFetch && !props.logoUrl"
        class="flex h-full w-full items-center justify-center bg-gray-200 text-gray-500 dark:bg-gray-600"
      >
        <!-- Placeholder while parent loads logo -->
        <div class="text-xs">Loading...</div>
      </div>
    </figure>
  </div>
</template>

<script setup>
// Props - allows parent to pass logo URL to avoid redundant API calls
const props = defineProps({
  logoUrl: {
    type: String,
    default: null,
  },
  // Add a prop to indicate if parent will provide logoUrl (prevents fallback API call)
  skipFetch: {
    type: Boolean,
    default: false,
  },
});

const api = useAPI();
const tenantLogo = ref("");

// Use provided logoUrl prop or fetch from API if not provided
const displayLogo = computed(() => props.logoUrl || tenantLogo.value);

// Only fetch from API if no logoUrl prop is provided AND skipFetch is false
onMounted(async () => {
  if (!props.skipFetch && !props.logoUrl) {
    await fetchTenantLogo();
  }
});

// Watch for changes in logoUrl prop (in case parent updates it later)
watch(
  () => props.logoUrl,
  (newLogoUrl) => {
    // If we get a logoUrl from parent, we don't need our own fetched logo
    if (newLogoUrl) {
      tenantLogo.value = "";
    }
  },
);

// methods
const fetchTenantLogo = async () => {
  try {
    const info = await api.get("/info");
    tenantLogo.value = info.logo;
  } catch (error) {
    console.error("Error fetching info:", error);
  }
};
</script>

<style scoped>
.ui-tenant-logo {
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-image {
  max-height: 40px;
  max-width: 100%;
  object-fit: contain;
}

.logo-placeholder {
  font-weight: 600;
  color: var(--color-text-secondary, #666);
}
</style>
