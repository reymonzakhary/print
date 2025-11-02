<template>
  <div>
    <fieldset class="flex flex-col gap-4 p-4 rounded-md border bg-white">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Sales Information</legend>
      <div class="col-span-2">
        <label
          for="company_name"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Payment Terms:
        </label>
        <UISelector :options="options" />
      </div>
    </fieldset>
  </div>
</template>

<script setup>
const props = defineProps({
  tenant: Object,
});

const options = ref([
  {
    label: "15 days",
    value: "15 days",
  },
  {
    label: "30 days",
    value: "30 days",
  },
  {
    label: "60 days",
    value: "60 days",
  },
]);
</script>

<style scoped></style>
