<template>
  <div class="flex flex-col gap-4">
    <div>
      <h2>useAPI (top-level)</h2>
      <div class="flex gap-2">
        <UIButton @click="refresh">Refresh</UIButton>
        <UIButton @click="clear">Clear</UIButton>
      </div>
      <div>{{ status }}</div>
      <div v-if="data">
        <div>{{ data.data.email }}</div>
      </div>
    </div>
    <div>
      <h2>$api (programmatic)</h2>
      <div class="flex gap-2">
        <UIButton @click="fetch">Fetch</UIButton>
      </div>
      <div>{{ status }}</div>
      <div v-if="data">
        <div>{{ data.data.email }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * useAPI (top-level)
 */
const { data, status, error, refresh, clear } = await useAPI("/account/me");

if (error.value) {
}

/**
 * $api (programmatic)
 */
const fetchResponse = ref(null);
async function fetch() {
  fetchResponse.value = useAsyncData("data", () => $api("/account/me"));
}
</script>
